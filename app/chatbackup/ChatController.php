<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Itinerary;
use Illuminate\Http\Request;
use App\Events\NewMessage;

class ChatController extends Controller
{
    public function getMessages(Itinerary $itinerary)
    {
        $messages = Message::with('user')
            ->where('itinerary_id', $itinerary->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Itinerary $itinerary)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'user_id' => auth()->id(),
            'itinerary_id' => $itinerary->id,
            'content' => $request->content
        ]);

        $message->load('user');

        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }
} 