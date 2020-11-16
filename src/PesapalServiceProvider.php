<?php
namespace Wokora\Pesapal;

use Illuminate\Support\ServiceProvider;

class PesapalServiceProvider extends ServiceProvider
{
    public function boot(){
        // Publish config
        $configPath = __DIR__ . '/config/pesapal.php';
        $this->publishes([$configPath => config_path('pesapal.php')], 'config');
    }

    public function register(){

         $this->app->singleton('pesapal', function($app) {
             return new Pesapal();
         });

    }
}
