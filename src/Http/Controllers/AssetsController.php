<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades;
use Statamic\Facades\Asset;
use Statamic\Facades\Blink;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Assets\AssetsController as CpController;
use Symfony\Component\Mime\MimeTypes;
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

            $tmpFilename = Str::afterLast(parse_url($file, PHP_URL_PATH), '/');
            if (empty($tmpFilename)) {
                $tmpFilename = 'default_'.uniqid();
            }

            // Get filename from reuest
            $filename = $request->input('filename');

            $tmpFilename = $this->sanitizeFilename($tmpFilename);

            // Save content to tmp file
            $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$tmpFilename;
            file_put_contents($tmpPath, $contents);

            // Check mimetype of the file and detect extension for the file
            $mimetype = mime_content_type($tmpPath);
            $mimetypes = new MimeTypes();
            $extension = collect($mimetypes->getExtensions($mimetypes->guessMimeType(storage_path('tmp/'.$tmpPath))))->first();

            // Create filename if not set through request
            $filename ??= $this->sanitizeFilename($tmpFilename, $extension);

            // Check extension one last time and sanitize filename and extension
            $pathinfo = pathinfo($filename);
            if ($pathinfo['extension'] !== $extension) {
                $filename = $this->sanitizeFilename($filename, $extension);
            }

            // Create a new uploadfile object to pass to cp routes
            $fileUpload = new UploadedFile($tmpPath, $filename, $mimetype, null, true);
            $request->files->set('file', $fileUpload);
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
            // Delete the asset again if validations fail for the blueprint
            if (isset($asset)) {
                $asset->delete();
            }

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

    private function sanitizeFilename($filename, $extension = null)
    {
        $pathinfo = pathinfo($filename);

        $filename = Str::slug(Str::limit($pathinfo['filename'], 100, ''), '-');
        if ($extension ??= $pathinfo['extension'] ?? null) {
            $filename .= '.'.$extension;
        }

        return $filename;
    }
}
