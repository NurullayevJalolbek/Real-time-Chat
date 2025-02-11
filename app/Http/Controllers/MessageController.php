<?php

namespace App\Http\Controllers;
use App\Jobs\SendMessage;

use Illuminate\Http\Request;

use App\Events\GotMessage;

class MessageController extends Controller
{
    public function show($id) {
        // Foydalanuvchi tizimga kirganini tekshirish
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        $receiver_id = $id;
        $sender_id = auth()->id();
    
        // Xabarlarni olish
        $messages = \App\Models\Message::where(function ($query) use ($receiver_id, $sender_id) {
            $query->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($receiver_id, $sender_id) {
            $query->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
        })->get();
    
        // Xabarlarni JSON formatda qaytarish
        return response()->json($messages);
    }

    public function store(Request $request) {

         $message = \App\Models\Message::create([
            'text' => $request->input('text'),
            'receiver_id' => $request ->input('receiver_id'),
            'sender_id' => auth()->id()
        ]);
        SendMessage::dispatch($message);

        // broadcast(new GotMessage($message->toArray()));

        return response()->json([
            'success' => true,
        ]);
    }
    
}
