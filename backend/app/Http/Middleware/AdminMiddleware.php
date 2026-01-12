<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse token and authenticate user
            $user = JWTAuth::parseToken()->authenticate();

            // Check if user exists
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Token may be invalid.'
                ], 404);
            }

            // Check if user has admin role
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Admin access required.',
                    'your_role' => $user->role,
                    'required_role' => 'admin'
                ], 403);
            }

            // User is admin, proceed with request
            return $next($request);

        } catch (TokenExpiredException $e) {
            // Token has expired
            return response()->json([
                'success' => false,
                'message' => 'Token has expired. Please login again.',
                'error' => 'token_expired'
            ], 401);

        } catch (TokenInvalidException $e) {
            // Token is invalid
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid. Please login again.',
                'error' => 'token_invalid'
            ], 401);

        } catch (JWTException $e) {
            // Token is not provided or couldn't be parsed
            return response()->json([
                'success' => false,
                'message' => 'Authorization token not found. Please provide a valid token.',
                'error' => 'token_absent'
            ], 401);

        } catch (\Exception $e) {
            // Generic error handler
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during authentication.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
