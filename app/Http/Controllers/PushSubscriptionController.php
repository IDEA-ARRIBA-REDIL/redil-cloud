<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushSubscriptionController extends Controller
{
    /**
     * Store or update a push subscription for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $this->validate($request, [
                'endpoint'    => 'required',
                'keys.p256dh' => 'required',
                'keys.auth'   => 'required',
            ]);

            $endpoint        = $request->endpoint;
            $key             = $request->keys['p256dh'];
            $token           = $request->keys['auth'];
            $contentEncoding = $request->input('content_encoding', 'aesgcm');

            /** @var \App\Models\User $user */
            $user = $request->user();

            $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

            return response()->json([
                'success' => true,
                'message' => 'Suscripción guardada correctamente.',
            ]);

        } catch (\Exception $e) {
            Log::error('[PushSubscription] Error al guardar suscripción: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user'  => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a push subscription for the authenticated user.
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
            'message' => 'Suscripción eliminada correctamente.',
        ], 200);
    }
}
