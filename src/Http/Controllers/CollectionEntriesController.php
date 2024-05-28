<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Collections\EntriesController as CpController;
use Statamic\Http\Resources\API\EntryResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class CollectionEntriesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index($collection)
    {
        $collection = $this->collectionFromHandle($collection);
        $this->authorize('view', $collection);

        $with = $collection->entryBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(EntryResource::class)::collection(
            $this->filterSortAndPaginate($collection->queryEntries()->with($with))
        );
    }

    public function show($collection, $entry)
    {
        $collection = $this->collectionFromHandle($collection);
        $entry = $this->entryFromId($entry);
        $this->authorize('view', $entry);

        $this->abortIfInvalid($entry, $collection);

        return app(EntryResource::class)::make($entry);
    }

    public function store(Request $request, $collection)
    {
        $collection = $this->collectionFromHandle($collection);

        try {
            $response = (new CpController($request))->store($request, $collection, Facades\Site::current());
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }

        if (! $id = Arr::get($response, 'data.id')) {
            abort(403);
        }

        $entry = $this->entryFromId($id);

        return app(EntryResource::class)::make($entry);
    }

    public function update(Request $request, $collection, $entry)
    {
        $collection = $this->collectionFromHandle($collection);
        $entry = $this->entryFromId($entry);

        $this->abortIfInvalid($entry, $collection);

        $request->headers->add(['accept' => 'application/json']);

        $originalData = collect((new CpController($request))->edit($request, $collection, $entry)->get('values'))->filter();
        $originalData = $originalData->merge($request->all());

        $request->merge($originalData->all());

        try {
            $response = (new CpController($request))->update($request, $collection, $entry);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }

        if (! $id = Arr::get($response, 'data.id')) {
            abort(403);
        }

        $entry = $this->entryFromId($id);

        return app(EntryResource::class)::make($entry);
    }

    public function destroy(Request $request, $collection, $entry)
    {
        $collection = $this->collectionFromHandle($collection);
        $entry = $this->entryFromId($entry);

        $this->abortIfInvalid($entry, $collection);

        $this->authorize('delete', $entry);

        $entry->delete();

        return response('', 204);
    }

    private function abortIfInvalid($entry, $collection)
    {
        if (! $entry || $entry->collection()->id() !== $collection->id()) {
            abort(404);
        }
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

    private function entryFromId($entry)
    {
        $entry = is_string($entry) ? Facades\Entry::find($entry) : $entry;

        if (! $entry) {
            abort(404);
        }

        return $entry;
    }
}
