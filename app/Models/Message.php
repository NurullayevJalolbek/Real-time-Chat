<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'text',
        'delete_message_at',
    ];

   

    
}
