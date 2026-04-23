<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update a push subscription for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'endpoint' => 'required',
            'keys.p256dh' => 'required',
            'keys.auth' => 'required',
        ]);

        $endpoint = $request->endpoint;
        $key = $request->keys['p256dh'];
        $token = $request->keys['auth'];
        $contentEncoding = $request->input('content_encoding', 'aesgcm'); // Default for most browsers

        /** @var \App\Models\User $user */
        $user = $request->user();
        
        // El trait HasPushSubscriptions proporciona el método updatePushSubscription
        $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        return response()->json([
            'success' => true,
            'message' => 'Suscripción guardada correctamente.'
        ]);
    }

    /**
     * Remove a push subscription for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->validate($request, [
            'endpoint' => 'required',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $user->deletePushSubscription($request->endpoint);

        return response()->json([
            'success' => true,
            'message' => 'Suscripción eliminada correctamente.'
        ], 200);
    }
}
