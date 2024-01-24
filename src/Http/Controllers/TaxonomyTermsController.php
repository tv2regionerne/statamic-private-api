<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Taxonomies\TermsController as CpController;
use Statamic\Http\Resources\API\TermResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class TaxonomyTermsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index($taxonomy)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);

        $with = $taxonomy->termBlueprints()
            ->flatMap(fn ($blueprint) => $blueprint->fields()->all())
            ->filter->isRelationship()->keys()->all();

        return app(TermResource::class)::collection(
            $this->filterSortAndPaginate($taxonomy->queryTerms()->with($with))
        );
    }

    public function show($taxonomy, $term)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);
        $term = $this->termFromSlug($term, $taxonomy);

        $this->abortIfInvalid($term, $taxonomy);

        return app(TermResource::class)::make($term);
    }

    public function store(Request $request, $taxonomy)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);

        return (new CpController($request))->store($request, $taxonomy, Facades\Site::current());
    }

    public function update(Request $request, $taxonomy, $term)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);
        $term = $this->termFromSlug($term, $taxonomy);

        $this->abortIfInvalid($term, $taxonomy);

        // cp controller expects the full payload, so merge from existing values
        $request->merge($term->blueprint()->fields()->addValues($term->data()->all())->values()->except($request->keys())->all());

        return (new CpController($request))->update($request, $taxonomy, $term, Facades\Site::current());
    }

    public function destroy(Request $request, $taxonomy, $term)
    {
        $taxonomy = $this->taxonomyFromHandle($taxonomy);
        $term = $this->termFromSlug($term, $taxonomy);

        $this->abortIfInvalid($term, $taxonomy);

        $term->delete();

        return response('', 204);
    }

    private function abortIfInvalid($term, $taxonomy)
    {
        if (! $term || $term->taxonomy()->handle() !== $taxonomy->handle()) {
            abort(404);
        }
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

    private function termFromSlug($term, $taxonomy)
    {
        $term = is_string($term) ? Facades\Term::find($taxonomy->handle().'::'.$term) : $term;

        if (! $term) {
            abort(404);
        }

        return $term;
    }
}
