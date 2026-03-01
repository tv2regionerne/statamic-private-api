<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

class ApiController extends \Statamic\Http\Controllers\API\ApiController
{
    protected function filterSortAndPaginate($query)
    {
        if (method_exists(parent::class, 'filterSortAndPaginate')) {
            return parent::filterSortAndPaginate($query);
        }

        return parent::updateAndPaginate($query);
    }
}
