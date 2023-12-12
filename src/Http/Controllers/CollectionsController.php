<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Collections\CollectionsController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\CollectionResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class CollectionsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('collections', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\Collection::all());
        return CollectionResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($collection)
    {
        $collection = $this->collectionFromHandle($collection);

        return CollectionResource::make($collection);
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('collections', ''), 404);

        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $collection)
    {
        $collection = $this->collectionFromHandle($collection);

        return (new CpController($request))->update($request, $collection);
    }

    public function destroy(Request $request, $collection)
    {
        $collection = $this->collectionFromHandle($collection);

        return (new CpController($request))->destroy($collection);
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
