<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateOptionally extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        


        if (env('API_KEY') != $request->code){


            return response()->json("Invalid access key", 403);
            
        }

         return $next($request);
    

    }
}
