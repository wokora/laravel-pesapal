<?php
namespace Wokora\Pesapal;

use Illuminate\Support\ServiceProvider;

class PesapalServiceProvider extends ServiceProvider
{
    public function boot(){

    }

    public function register(){

         $this->app->singleton('pesapal', function($app) {
             return new Pesapal($app);
         });

    }
}
