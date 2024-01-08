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
PATCH requestst will be handled as a PUT, so ensure to send the full object for any updates.  
Asset upload is not implemented.  
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

## How to Use

Update the config or env to enable private API's.

Configure your .env with the following values to enable the private API with a prefix on "api/private".
```env
PRIVATE_API_ENABLED=true
PRIVATE_API_ROUTE="api/private"
```
