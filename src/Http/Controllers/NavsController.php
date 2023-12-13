<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Navigation\NavigationController as CpController;
use Statamic\Query\ItemQueryBuilder;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\NavResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class NavsController extends ApiController
{
    use VerifiesPrivateAPI;

    public function index()
    {
        abort_if(! $this->resourcesAllowed('navs', ''), 404);

        $query = (new ItemQueryBuilder)->withItems(Facades\Collection::all());

        return NavResource::collection(
            $this->filterSortAndPaginate($query)
        );
    }

    public function show($nav)
    {
        $nav = $this->navFromHandle($nav);

        return NavResource::make($nav);
    }

    public function store(Request $request)
    {
        abort_if(! $this->resourcesAllowed('navs', ''), 404);

        return (new CpController($request))->store($request);
    }

    public function update(Request $request, $nav)
    {
        $nav = $this->navFromHandle($nav);

        return (new CpController($request))->update($request, $nav->handle());
    }

    public function destroy(Request $request, $nav)
    {
        $nav = $this->navFromHandle($nav);

        return (new CpController($request))->destroy($nav->handle());
    }

    private function navFromHandle($nav)
    {
        $nav = is_string($nav) ? Facades\Nav::find($nav) : $nav;

        if (! $nav) {
            abort(404);
        }

        abort_if(! $this->resourcesAllowed('navs', $nav->handle()), 404);

        return $nav;
    }
}
