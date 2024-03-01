<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Statamic\Http\Controllers\CP\Globals\GlobalVariablesController as CpController;
use Statamic\Http\Resources\API\GlobalSetResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class GlobalVariablesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function show($global)
    {
        $global = $this->globalFromHandle($global);

        return app(GlobalSetResource::class)::make($global);
    }

    public function update(Request $request, $global)
    {
        $global = $this->globalFromHandle($global);

        // cp controller expects the full payload, so merge with existing values
        $originalData = collect((new CpController($request))->edit($request, $global->id())->get('values'))->filter();
        $originalData = $originalData->merge($request->all());

        $request->merge($originalData->all());

        return (new CpController($request))->update($request, $global->handle());
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
