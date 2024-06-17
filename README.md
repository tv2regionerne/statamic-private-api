# Statamic Private Api

<!-- statamic:hide -->
[![Packagist](https://img.shields.io/packagist/v/tv2regionerne/statamic-passport.svg?style=flat-square)](https://packagist.org/packages/tv2regionerne/statamic-passport)
[![Downloads](https://img.shields.io/packagist/dt/tv2regionerne/statamic-passport.svg?style=flat-square)](https://packagist.org/packages/tv2regionerne/statamic-passport)
[![License](https://img.shields.io/github/license/tv2regionerne/statamic-passport.svg?style=flat-square)](LICENSE)
[![Supported Statamic version](https://img.shields.io/badge/Statamic-4.0%2B-FF269E)](https://github.com/statamic/cms/releases)
<!-- /statamic:hide -->

> Statamic Private Api is a Statamic addon that enables a private REST API.  
> The routes should be protected by Laravel Passport, Sanctum or similar.

## Features
Add's private API seperated from the Public API build into statamic.  
Uses Laravel's build in auth guards, so you may use Laravel Passport, Laravel Sanctum or something else.  
Permissions will foloww the permissions assigned to the user inside of Statamic.

See https://statamic.com/addons/tv2reg/laravel-passport-ui for Laravel Passport integration into Statamic.

## Limitations
The Private API uses the Statamic CP controllers.  
Collection Entry revisions is not implemented. 

## How to Install

Run the following command from your project root:

``` bash
composer require tv2regionerne/statamic-private-api
```

Make sure you have an 'api' guard in your config/auth.php configured.  
Below example is using Laravel Passport for api authentication.

```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
```

Publish the config file:
```
php artisan vendor:publish --tag=private-api-config
```

Then, in /config/private-api.php enable the routes you would like to use, for example:
```
    'resources' => [
        'collections' => true,
        'navs' => false,
        'taxonomies' => false,
        'assets' => false,
        'globals' => false,
        'forms' => false,
        'users' => true,
    ],
```

## How to Use

Update the config or env to enable private API's.

Configure your .env with the following values to enable the private API with a prefix on "api/private".
```env
PRIVATE_API_ENABLED=true
PRIVATE_API_ROUTE="api/private"
```


## Addon endpoints in private API
Other addons can register their private API endpoints in the private API.  
Consider below example which checks if the Private API addon is installed and then adds the addon's private API endpoints.  
The routes will be protected by the same auth guard as the other Private API endpoints.  
Authorisation and policies should be handled inside of the addon's controllers.
```php
use Illuminate\Support\Facades\Route;
use Tv2regionerne\StatamicPrivateApi\Facades\PrivateApi;
if (class_exists(PrivateApi::class)) {
    PrivateApi::addRoute(function () {
        Route::prefix('/statamic-events/handlers')
            ->group(function () {
                Route::get('/', [Controller::class, 'index']);
                Route::get('{id}', [Controller::class, 'show']);
                Route::post('/', [Controller::class, 'store']);
                Route::patch('{id}', [Controller::class, 'update']);
                Route::delete('{id}', [Controller::class, 'destroy']);
            });
    });
}
```
