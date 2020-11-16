<?php
namespace Wokora\Pesapal;

use Illuminate\Support\ServiceProvider;

class PesapalServiceProvider extends ServiceProvider
{
    public function boot(){
        dd( 'It works' );
    }

    public function register(){

         $this->app->singleton(Pesapal::class, function($app) {
             return new Pesapal();
         });

    }
}
