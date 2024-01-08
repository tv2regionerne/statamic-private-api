# Statamic Private Api

> Statamic Private Api is a Statamic addon that enables a private REST API.
> The routes should be protected by Laravel Passport, Sanctum or similar.

## Features
Add's private API seperated from the Public API build into statamic.  
Uses Laravel's build in auth guards, so you may use Laravel Passport, Laravel Sanctum or something else.

See https://statamic.com/addons/tv2reg/laravel-passport-ui for Laravel Passport integration into Statamic.

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

## How to Use

Update the config to enable private API's.

Configure your .env with the following values to enable the private API with a prefix on "api/private".
```env
PRIVATE_API_ENABLED=true
PRIVATE_API_ROUTE="api/private"
```
This will enable routes on 
* ```/api/private/asset-containers```
* ```/api/private/globals```
* ```/api/private/collections```
* ```/api/private/forms```
