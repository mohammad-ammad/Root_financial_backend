<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Cookie;
use App\Models\PersonalAccessToken;

class auth_token
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if(! empty(Cookie::get('token')))
        {
            [$id, $token] = explode('|', Cookie::get('token') , 2);

            $accessToken = PersonalAccessToken::find($id);

            if (hash_equals($accessToken->token, hash('sha256', $token))) {
                return $next($request);
            }
            else 
            {
                return response()->json([
                    "message"=>"Unauthorized User",
                    "status"=>false,
                ]);
            }
        }
        else 
        {
            return response()->json([
                "message"=>"Access Denied",
                "status"=>false,
            ]);
        }

    }
}
