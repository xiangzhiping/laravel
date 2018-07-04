<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setErrorHandle();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    /**
     * 覆盖框架底层错误处理机制
     */
    private function setErrorHandle()
    {
        // 生产环境 notice 和 warning 不抛异常
        config('app.env') === 'production' && error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

        // 重写错误处理
        set_error_handler(function ($level, $message, $file = '', $line = 0) {

            if (((E_NOTICE | E_WARNING) & $level)) { // 记录PHP错误：
                Log::warning(sprintf('PHP Error(%s): %s in file %s(%s)', $level, $message, $file, $line));
            }

            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });

        // 脚本结束处理
        // register_shutdown_function(function () {});
    }
}
