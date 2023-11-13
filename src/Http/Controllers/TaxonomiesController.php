<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\Cp\Taxonomies\TaxonomiesController as CpController;
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

        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $taxonomy)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);

        return (new CpController($request))->update($request, $taxonomy);
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
