<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

        $query = (new ItemQueryBuilder)->withItems(Facades\Nav::all());

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

        try {
            (new CpController($request))->store($request);

            $nav = $this->navFromHandle($request->input('handle'));

            return NavResource::make($nav);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
    }

    public function update(Request $request, $handle)
    {
        $nav = $this->navFromHandle($handle);

        try {
            $mergedData = collect($this->show($handle)->toArray($request))->merge($request->all());

            $request->merge($mergedData->all());

            (new CpController($request))->update($request, $nav->handle());

            $nav = $this->navFromHandle($handle);

            return NavResource::make($nav);
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
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
