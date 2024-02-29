## Addons
Other addons can register their private API endpoints in the private API.  
Consider below example which checks if the Private API addon is installed and then adds the addon's private API endpoints.  
The routes will be protected by the same auth guard as the other Private API endpoints.  
Authorisation and policies should be handled inside of the addon's controllers.

### Example
```php
<?php
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