<?php

namespace Tv2regionerne\StatamicPrivateApi\Managers;

use Closure;

class PrivateApiManager
{
    protected array $additionalRoutes = [];

    public function additionalRoutes()
    {
        foreach ($this->additionalRoutes as $routes) {
            $routes();
        }
    }
    
    public function addRoute(Closure $route)
    {
        $this->additionalRoutes[] = $route;
        
        return $this;
    }
}
