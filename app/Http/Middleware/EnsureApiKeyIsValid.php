<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedApiKey = env('API_KEY', config('app.api_key'));

        // If no API_KEY configured in server env, fail safe
        if (empty($expectedApiKey)) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'API Key is not configured on the server environment (.env).',
                'error' => 'API_KEY_UNCONFIGURED'
            ], 500);
        }

        // Retrieve API Key from headers or query params
        $providedApiKey = $request->header('X-API-KEY')
            ?? $request->header('x-api-key')
            ?? $request->header('ApiKey')
            ?? $request->bearerToken()
            ?? $request->query('api_key')
            ?? $request->query('apiKey');

        if (!$providedApiKey) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthorized: API Key is required. Please provide it via X-API-KEY header, Bearer token, or api_key parameter.',
                'error' => 'MISSING_API_KEY'
            ], 401);
        }

        if (!hash_equals($expectedApiKey, (string) $providedApiKey)) {
            return response()->json([
                'status' => 'error',
                'code' => 401,
                'message' => 'Unauthorized: The provided API Key is invalid.',
                'error' => 'INVALID_API_KEY'
            ], 401);
        }

        return $next($request);
    }
}
