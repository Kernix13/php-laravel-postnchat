<?php

namespace App\Http\Controllers;

use App\Events\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function sendChatMessage(Request $request) {
        $formFields = $request->validate([
        'textvalue' => 'required'
        ]);

        if (!trim(strip_tags($formFields['textvalue']))) {
            return response()->noContent();
        }

        broadcast(new ChatMessage([
            'username'  => auth()->user()->username, 
            'textvalue' => strip_tags($request->textvalue), 
            'avatar'    => auth()->user()->avatar
        ]))->toOthers();

        return response()->noContent();
    }
}
