<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Order $order)
    {
        try {
            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->currency),
                        'unit_amount' => $order->amount * 100, // Convert to cents
                        'product_data' => [
                            'name' => $order->itinerary->title,
                            'description' => 'Itinerary Purchase',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('orders.show', $order),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ],
            ]);

            return response()->json([
                'url' => $checkout_session->url
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Checkout Session Creation Failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);

            return response()->json([
                'error' => 'Unable to create checkout session'
            ], 500);
        }
    }

    public function handleSuccessfulPayment(Request $request)
    {
        try {
            $session = Session::retrieve($request->session_id);
            $order = Order::where('id', $session->metadata->order_id)->first();

            if (!$order) {
                return redirect()->route('orders.index')
                    ->with('error', 'Order not found');
            }

            $order->update([
                'payment_status' => 'completed',
                'stripe_payment_id' => $session->payment_intent,
                'stripe_customer_id' => $session->customer,
                'payment_details' => [
                    'payment_method' => $session->payment_method_types[0],
                    'payment_status' => $session->payment_status
                ],
                'paid_at' => now()
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment successful! Your order has been completed.');
        } catch (\Exception $e) {
            Log::error('Payment Success Handler Failed', [
                'error' => $e->getMessage(),
                'session_id' => $request->session_id
            ]);

            return redirect()->route('orders.index')
                ->with('error', 'There was an error processing your payment. Please contact support.');
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $order = Order::where('id', $session->metadata->order_id)->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 'completed',
                        'stripe_payment_id' => $session->payment_intent,
                        'stripe_customer_id' => $session->customer,
                        'payment_details' => [
                            'payment_method' => $session->payment_method_types[0],
                            'payment_status' => $session->payment_status
                        ],
                        'paid_at' => now()
                    ]);
                }
                break;

            case 'checkout.session.expired':
                $session = $event->data->object;
                $order = Order::where('id', $session->metadata->order_id)->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 'failed',
                        'stripe_payment_id' => $session->payment_intent,
                        'stripe_customer_id' => $session->customer,
                        'payment_details' => [
                            'payment_method' => $session->payment_method_types[0],
                            'payment_status' => $session->payment_status
                        ]
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
