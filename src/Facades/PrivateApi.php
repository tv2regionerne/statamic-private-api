<?php

namespace Tv2regionerne\StatamicPrivateApi\Facades;

use Illuminate\Support\Facades\Facade;
use Tv2regionerne\StatamicPrivateApi\Managers\PrivateApiManager;

class PrivateApi extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PrivateApiManager::class;
    }
}
