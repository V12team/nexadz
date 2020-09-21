<?php


namespace App\Http\Middleware;

use Closure;
use Log;

class secureWebhook
{
    public function handle($request, Closure $next)
    {
        try {
            $request->headers->remove('cookie');
            $authorization = $request->headers->get('Authorization');
            if (!$authorization) {
                $authorization = apache_request_headers()['Authorization'];
            }
            if (empty($authorization) || $authorization != env('AUTHORIZATION_KEY')) {
                Log::info('Non authorized call in webhook');
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Non authorized call'
                    ], 401
                );
            }
            return $next($request);
        } catch (\Exception $e) {
            Log::info('Error in : ' . $e->getFile() . ' line ' . $e->getLine());
            Log::info('Error message : ' . $e->getMessage());
        }
    }
}
