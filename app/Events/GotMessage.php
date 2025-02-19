<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GotMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct( public  $message)
    {
        //
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.0-0');

    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->text, // Matnli xabar bo‘lsa
            'file_path' => $this->message->file_path, // Media fayl bo‘lsa
            'file_type' => $this->message->file_type, // Fayl turi
            'receiver_id' => $this->message->receiver_id,
            'sender_id' => $this->message->sender_id,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
