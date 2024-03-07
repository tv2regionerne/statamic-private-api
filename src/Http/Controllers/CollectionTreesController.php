<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Collections\CollectionTreeController as CpController;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class CollectionTreesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function show(Request $request, $collection)
    {
        $collection = $this->collectionFromHandle($collection);

        return (new CpController($request))->index($request, $collection);
    }

    public function update(Request $request, $collection)
    {
        $collection = $this->collectionFromHandle($collection);

        try {
            (new CpController($request))->update($request, $collection);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }

        return (new CpController($request))->index($request, $collection);
    }

    private function collectionFromHandle($collection)
    {
        $collection = is_string($collection) ? Facades\Collection::find($collection) : $collection;

        if (! $collection) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('collections', $collection->handle()), 404);

        return $collection;
    }
}
