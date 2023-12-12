<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Navigation\NavigationTreeController as CpController;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class NavTreesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function show(Request $request, $nav)
    {
        $nav = $this->navFromHandle($nav);

        return (new CpController($request))->index($request, $nav->handle());
    }

    public function update(Request $request, $nav)
    {
        $nav = $this->navFromHandle($nav);

        if (! $request->input('site')) {
            $request->merge(['site' => Facades\Site::selected()->handle()]);
        }

        return (new CpController($request))->update($request, $nav->handle());
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
