<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Assets\AssetContainersController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\AssetContainerResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class AssetContainersController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('assets', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\AssetContainer::all());

        return AssetContainerResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($container)
    {
        $container = $this->containerFromHandle($container);

        return AssetContainerResource::make($container);
    }

    public function store(Request $request)
    {
        try {
            (new CpController($request))->store($request);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }

        $container = $this->containerFromHandle($request->input('handle'));

        return AssetContainerResource::make($container);
    }

    public function update(Request $request, $handle)
    {
        $container = $this->containerFromHandle($handle);

        // cp controller expects the full payload, so merge with existing values
        $mergedData = collect($container->fileData())->merge($request->all());

        $request->merge($mergedData->all());

        try {
            (new CpController($request))->update($request, $container);

            return AssetContainerResource::make($this->containerFromHandle($handle));
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function destroy(Request $request, $container)
    {
        $container = $this->containerFromHandle($container);

        return (new CpController($request))->destroy($container);
    }

    private function containerFromHandle($container)
    {
        $container = is_string($container) ? Facades\AssetContainer::find($container) : $container;

        if (! $container) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('assets', $container->handle()), 404);

        return $container;
    }
}
