<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        try {
            $value = $request->header('x-api-key');
            if(!$value || $value !== '12345678'){
                throw new Exception("Unauthorised access");
            }

            return $next($request);

        } catch (\Exception $ex ) {

            $error = [];
            $error['success'] = (boolean) false;
            $error['error'] = $ex->getMessage();
            return response()->json($error);
        }
        

        
    }
}
