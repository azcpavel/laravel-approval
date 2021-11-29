<?php

namespace Exceptio\ApprovalPermission\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String  $permissions default ''
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions = '')
    {
        if(!config('approval-config.rolepermission-enable'))
            return $next($request);
        
        if ($request->user() === null) {
            return redirect()->route(config('approval-config.login-route'));
        }

        return response()->view('laravel-approval::errors.403', ['error' => 'This action is unauthorized.'], 403);
    }
}
