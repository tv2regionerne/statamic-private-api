<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Globals\GlobalsController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\GlobalResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class GlobalsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('globals', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\GlobalSet::all());

        return GlobalResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('globals', ''), 404);

        return (new CpController($request))->store($request);
    }

    public function show($global)
    {
        $global = $this->globalFromHandle($global);

        return GlobalResource::make($global);
    }

    public function update(Request $request, $global)
    {
        $global = $this->globalFromHandle($global);

        return (new CpController($request))->update($request, $global->handle());
    }

    public function destroy(Request $request, $global)
    {
        $global = $this->globalFromHandle($global);

        abort_if(! $this->resourcesAllowed('globals', $global->handle()), 404);

        $global->delete();

        return response('', 204);
    }

    private function globalFromHandle($global)
    {
        $global = is_string($global) ? Facades\GlobalSet::find($global) : $global;

        if (! $global) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('globals', $global->handle()), 404);

        return $global;
    }
}
