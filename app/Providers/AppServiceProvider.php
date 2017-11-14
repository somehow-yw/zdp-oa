<?php

namespace App\Providers;

use DB;
use Log;
use Auth;
use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     */
    public function boot()
    {
        $this->extendAuthManager();
        $this->extendValidator();
        DB::listen(
            function ($sql, $bindings, $time) {
                Log::info(PHP_EOL . '[SQL]' . $sql . " with: " . join(',', $bindings));
            }
        );
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

    private function extendAuthManager()
    {
        Auth::extend(
            'extended-eloquent',
            function ($app) {
                // AuthManager allows us only provide UserProvider instead of
                // the whole Guard implmenetation
                return new \App\Auth\UserProvider(new \App\Hashing\PasswordHasher(), $app['config']['auth.model']);
            }
        );
    }

    private function extendValidator()
    {
        // 手机号的验证
        Validator::extend(
            'mobile',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateMobile($attribute, $value, $parameters);
            }
        );

        // 判断值是否为空(但不包含空串)
        Validator::extend(
            'required_null',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateRequiredNull($attribute, $value, $parameters);
            }
        );

        // 判断数组中的指定键名是否存在
        Validator::extend(
            'arr_has_key',
            function ($attribute, $value, $parameters, $validator) {
                return \App\Extensions\MyValidator::validateArrHasKey($attribute, $value, $parameters, $validator);
            }
        );

        // 不可小于某值
        Validator::extend(
            'greater_than',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateGreaterThan($attribute, $value, $parameters);
            }
        );
    }
}
