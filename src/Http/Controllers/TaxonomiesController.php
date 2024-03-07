<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Taxonomies\TaxonomiesController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\TaxonomyResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class TaxonomiesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('taxonomies', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\Taxonomy::all());

        return TaxonomyResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($taxonomy)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);

        return TaxonomyResource::make($taxonomy);
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('taxonomies', ''), 404);

        try {
            (new CpController($request))->store($request);

            $taxonomy = $this->taxonomyFromHandle($request->input('handle'));

            return TaxonomyResource::make($taxonomy);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function update(Request $request, $handle)
    {
        $taxonomy = $this->taxonomyFromHandle($handle);

        try {
            $mergedData = collect($this->show($handle)->toArray($request))->merge($request->all());

            $request->merge($mergedData->all());

            (new CpController($request))->update($request, $taxonomy);

            $taxonomy = $this->taxonomyFromHandle($handle);

            return TaxonomyResource::make($taxonomy);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function destroy(Request $request, $taxonomy)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);

        return (new CpController($request))->destroy($taxonomy);
    }

    private function taxonomyFromHandle($taxonomy)
    {
        $taxonomy = is_string($taxonomy) ? Facades\Taxonomy::find($taxonomy) : $taxonomy;

        if (! $taxonomy) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('taxonomies', $taxonomy->handle()), 404);

        return $taxonomy;
    }
}
