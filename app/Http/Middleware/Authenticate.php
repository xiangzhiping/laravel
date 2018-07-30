<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/30
 */

namespace App\Http\Middleware;


use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        if (false === Auth::guard($guard)->check()) {
            if($request->ajax()){
                return ['status'=>1,'msg'=>'need login','data'=>''];
            }else{
                return redirect('/user/login',302);
            }
        }

        return $next($request);
    }
}