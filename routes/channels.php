<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{sortIds}-{id}', function ($user, $sortIds, $id) {
    return true;
});
