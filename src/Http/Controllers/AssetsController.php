<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades;
use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
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

    public function store(Request $request, $container)
    {
        $request->merge([
            'container' => $container,
        ]);

        if ($file = $request->input('file_url')) {
            $contents = file_get_contents($file);

            if (! $contents) {
                abort(406);
            }

            $filename = Str::afterLast($file, '/');
            Storage::disk('local')->put('tmp/'.$filename, $contents);

            $request->files->set('file', new UploadedFile(storage_path('tmp/'.$filename), $filename));
        }

        try {

            $response = (new CpController($request))->store($request);
            $asset = $response->resource;
            $fields = $asset->blueprint()->fields()->addValues($request->all());

            $fields->validate();

            $values = $fields->process()->values()->merge([
                'focus' => $request->focus,
            ]);

            foreach ($values as $key => $value) {
                $asset->set($key, $value);
            }

            $asset->save();
            Blink::forget("eloquent-asset-{$asset->id()}");
            Blink::forget("asset-meta-{$asset->id()}");
            $asset = Asset::findById($asset->id());

            return AssetResource::make($asset);

        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function update(Request $request, $container, $asset)
    {
        $request->merge([
            'container' => $container,
        ]);

        $response = (new CpController($request))->update($request, $asset);
        $assetId = $response['asset']['id'];

        Blink::forget("eloquent-asset-{$assetId}");
        Blink::forget("asset-meta-{$assetId}");

        $asset = Asset::findById($assetId);

        return AssetResource::make($asset);
    }

    public function destroy(Request $request, $container, $id)
    {
        $container = $this->containerFromHandle($container);

        $asset = $container->asset($this->idFromCrypt($id));

        if (! $asset) {
            abort(404);
        }

        $this->authorize('delete', [AssetContract::class, $container]);

        $asset->delete();

        return response('', 204);
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
        if (! str_contains($id, '::')) {
            $id = base64_decode($id);
        }

        return Str::after($id, '::');
    }
}
