<?php

namespace App\Http\Controllers;

use App\Models\BoardSession;
use Illuminate\Http\Request;

class BoardSessionController extends Controller
{
    public function show(string $sessionId)
    {
        $session = BoardSession::with(['responses'])->findOrFail($sessionId);
        
        return view('pages.executive-board-result', [
            'session' => $session,
            'sessionId' => $sessionId,
        ]);
    }
    
    public function createProject(string $sessionId)
    {
        $session = BoardSession::findOrFail($sessionId);
        
        if ($session->status !== 'decided' || empty($session->final_decision)) {
            return redirect()->back()
                ->with('error', 'No decision available to create project from');
        }

        // Redirect to project creation page with board session for AI analysis
        return redirect()->route('projects.create', [
            'board_session' => $sessionId,
        ]);
    }
    
    public function delete(string $sessionId)
    {
        $session = BoardSession::findOrFail($sessionId);
        $session->delete();
        
        return redirect()->route('board')->with('success', 'Board session deleted');
    }
}
