<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mission Control - public channel for subagent activity
Broadcast::channel('mission-control', function () {
    return true; // Public channel, no auth required
});
