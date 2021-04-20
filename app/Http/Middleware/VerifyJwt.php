<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Okta\JwtVerifier\Adaptors\FirebasePhpJwt;
use Okta\JwtVerifier\JwtVerifierBuilder;

class VerifyJwt
{
    public function handle(Request $request, Closure $next)
    {
        // Instantiate the Okta JWT verifier
        $jwtVerifier = (new JwtVerifierBuilder())
            ->setAdaptor(new FirebasePhpJwt())
            ->setAudience('https://dev-uaec-9lt.eu.auth0.com/api/v2/')
            ->setClientId('C6DxVIvHG0dkEjb0XOSYZgH4UTrqe6TD')
            ->setIssuer('epXWpNBx7Q2uGYG7ADC_bhF1P5hRUCNZWz2ZvGnGfrYhShcUkaZfMRGT585GacyU')
            ->build();

        try {
            // Verify the JWT passed as a bearer token
            $jwtVerifier->verify($request->bearerToken());
            return $next($request);
        } catch (\Exception $exception) {
            // Log exceptions
            Log::error($exception);
        }

        // If we couldn't verify, assume the user is unauthorized
        return response('Unauthorized', 401);
    }
}
