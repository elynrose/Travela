<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
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
            $paymentIntent = PaymentIntent::create([
                'amount' => $order->amount * 100, // Convert to cents
                'currency' => $order->currency,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent Creation Failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);

            return response()->json([
                'error' => 'Unable to create payment intent'
            ], 500);
        }
    }

    public function handleSuccessfulPayment(Request $request)
    {
        $paymentIntent = PaymentIntent::retrieve($request->payment_intent);
        $order = Order::where('id', $paymentIntent->metadata->order_id)->first();

        if (!$order) {
            return redirect()->route('orders.index')
                ->with('error', 'Order not found');
        }

        if ($paymentIntent->status === 'succeeded') {
            $order->update([
                'payment_status' => 'completed',
                'stripe_payment_id' => $paymentIntent->id,
                'stripe_customer_id' => $paymentIntent->customer,
                'payment_details' => $paymentIntent->charges->data[0]->payment_method_details,
                'paid_at' => now()
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment successful! Your order has been completed.');
        }

        return redirect()->route('orders.show', $order)
            ->with('error', 'Payment failed. Please try again.');
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
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $order = Order::where('id', $paymentIntent->metadata->order_id)->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 'completed',
                        'stripe_payment_id' => $paymentIntent->id,
                        'stripe_customer_id' => $paymentIntent->customer,
                        'payment_details' => $paymentIntent->charges->data[0]->payment_method_details,
                        'paid_at' => now()
                    ]);
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $order = Order::where('id', $paymentIntent->metadata->order_id)->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 'failed',
                        'stripe_payment_id' => $paymentIntent->id,
                        'stripe_customer_id' => $paymentIntent->customer,
                        'payment_details' => $paymentIntent->last_payment_error
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
