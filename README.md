# Pesapal for Laravel API

## Installation

### Add this package using Composer

From the command line inside your project directory, simply type:

`composer require wokora/pesapal`

Add the service provider to the providers array in config/app.php:

`Wokora\Pesapal\PesapalServiceProvider::class,`

Add the facade to the aliases array in config/app.php:

`'Pesapal' => Wokora\Pesapal\Pesapal::class,` 

### Publish the package configuration (for Laravel 5.4 and below)

Publish the configuration file and migrations by running the provided console command:

`php artisan vendor:publish --provider="Wokora\Pesapal\PesapalServiceProvider"`

The ENV Variables can also be set from here.
```
#### Example ENV

```
PESAPAL_LIVE=true
PESAPAL_CONSUMER_KEY=""
PESAPAL_CONSUMER_SECRET=""
PESAPAL_CALLBACK_URL="http://127/0.0.1:8000/confirm"
```
#### All Done
Feel free to report any issues


