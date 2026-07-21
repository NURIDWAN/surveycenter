<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Receives webhooks forwarded by the central Payment Callback Hub.
 *
 * Verifies the shared HMAC signature (X-Hub-Signature) using HUB_SECRET,
 * then delegates the request to the existing gateway handler so that
 * downstream business logic (Transaction status update, etc.) runs unchanged.
 */
class HubWebhookController extends Controller
{
    public function singapay(Request $request, TransactionController $delegate)
    {
        $this->verifyHubSignature($request);
        return $delegate->handleInvoice($request);
    }

    private function verifyHubSignature(Request $request): void
    {
        $secret = (string) env('HUB_SECRET', '');

        if ($secret === '') {
            Log::error('HUB_SECRET not configured; rejecting hub webhook');
            throw new HttpException(500, 'hub secret not configured');
        }

        $rawBody  = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        $received = (string) $request->header('X-Hub-Signature', '');

        if ($received === '' || !hash_equals($expected, $received)) {
            Log::warning('Hub webhook signature mismatch', [
                'event_id' => $request->header('X-Hub-Event-Id'),
                'reff_no'  => $request->header('X-Hub-Reff-No'),
                'provider' => $request->header('X-Hub-Provider'),
            ]);
            throw new HttpException(401, 'invalid hub signature');
        }

        Log::info('Hub webhook signature verified', [
            'event_id' => $request->header('X-Hub-Event-Id'),
            'reff_no'  => $request->header('X-Hub-Reff-No'),
            'provider' => $request->header('X-Hub-Provider'),
            'attempt'  => $request->header('X-Hub-Attempt'),
        ]);
    }
}
