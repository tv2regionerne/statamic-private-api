<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Assets\AssetsController as CpController;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\AssetResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class AssetsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index($container)
    {
        $container = $this->containerFromHandle($container);

        return AssetResource::collection(
            $this->filterSortAndPaginate($container->queryAssets())
        );
    }

    public function show($container, $id)
    {
        $id = $this->idFromCrypt($id);

        $container = $this->containerFromHandle($container);

        $asset = $container->asset($id);

        if (! $asset) {
            abort(404);
        }

        return AssetResource::make($asset);
    }

    public function destroy(Request $request, $container, $id)
    {
        $container = $this->containerFromHandle($container);

        $asset = $container->asset($this->idFromCrypt($id));

        if (! $asset) {
            abort(404);
        }

        return (new CpController($request))->destroy($id);
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

    private function idFromCrypt($id)
    {
        $id = base64_decode($id);

        return Str::after($id, '::');
    }
}
