<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Mission Control - public channel for subagent activity
Broadcast::channel('mission-control', function () {
    return true; // Public channel, no auth required
});

// Chat session channel - authenticated users can join their chat sessions
Broadcast::channel('chat.{sessionId}', function ($user, $sessionId) {
    // For now, allow all authenticated users access
    // In production, you may want to verify user owns the session
    return [
        'id' => $user->id,
        'name' => $user->name ?? 'User',
    ];
});
