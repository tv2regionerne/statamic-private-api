<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        if (! config('private-api.enabled')) {
            abort(404);
        }
    }

    public function resourcesAllowed(string $type, string $key): bool
    {
        $resources = config('private-api.resources.'.$type);
        if (! $resources) {
            return false;
        }

        if ($resources === true) {
            return true;
        }

        if (is_array($resources) && array_key_exists($key, $resources)) {
            return true;
        }

        return false;
    }
}
