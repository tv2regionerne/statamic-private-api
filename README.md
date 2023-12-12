# Statamic Private Api

> Statamic Private Api is a Statamic addon that enables a private REST API.
> The routes should be protected by Laravel Passport, Sanctum or similar.

## Features
Add's private API seperated from the Public API build into statamic.  
Uses Laravel's build in auth guards, so you may use Laravel Passport, Laravel Sanctum or something else.

## How to Install

Add this to the repositories section in composer.json
```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:tv2regionerne/statamic-private-api.git"
    }
]
```

Run the following command from your project root:

``` bash
composer require tv2regionerne/statamic-private-api
```

## How to Use

Update the config to enable private API's.
