<?php

namespace Tv2regionerne\StatamicPrivateApi\Managers;

class PrivateApiManager
{
    protected static $additionalRoutes = [];

    public static function additionalRoutes()
    {
        foreach (static::$additionalRoutes as $routes) {
            $routes();
        }
    }
}
