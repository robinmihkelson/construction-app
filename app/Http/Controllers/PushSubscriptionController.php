<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'expirationTime' => ['nullable'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['contentEncoding'] ?? 'aesgcm',
        );

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        $endpoint = $request->validate([
            'endpoint' => ['required', 'string'],
        ])['endpoint'];

        $request->user()->deletePushSubscription($endpoint);

        return response()->noContent();
    }
}
