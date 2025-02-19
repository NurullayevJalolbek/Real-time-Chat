<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Models\Message;

use Illuminate\Http\Request;

use App\Events\GotMessage;

class MessageController extends Controller
{
    public function show($id)
    {
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

    public function store(Request $request)
    {

        $message = \App\Models\Message::create([
            'text' => $request->input('text'),
            'receiver_id' => $request->input('receiver_id'),
            'sender_id' => auth()->id()
        ]);
        SendMessage::dispatch($message);

        // broadcast(new GotMessage($message->toArray()));

        return response()->json([
            'success' => true,
        ]);
    }
    public function media(Request $request)
    {
        $receiverId = $request->input('receiver_id');
        $file = $request->file('file');
        
        if (!$file) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
        
        // Fayl nomini unikallashtirish
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Faylni saqlash (public diskida)
        $filePath = $file->storeAs('Media', $fileName, 'public');
        
        // Databasega fayl yo'lini saqlash
        $message = new Message();
        $message->sender_id = auth()->id();
        $message->receiver_id = $receiverId;
        $message->file_path = $fileName; // Fayl nomi saqlanadi
        $message->file_type = $file->getMimeType();
        $message->save();
    
        // Xabar yuborish
        SendMessage::dispatch($message);
        
        return response()->json([
            'success' => 'File uploaded successfully!',
            'file_url' => asset('storage/media/' . $fileName) // To'g'ri URL
        ]);
    }
    
    
    
}
