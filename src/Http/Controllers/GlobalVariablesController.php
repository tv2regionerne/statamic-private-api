<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Facades;
use Statamic\Http\Controllers\API\ApiController;
use Tv2regionerne\StatamicPrivateApi\Http\Resources\GlobalVariablesResource;
use Tv2regionerne\StatamicPrivateApi\Traits\VerifiesPrivateAPI;

class GlobalVariablesController extends ApiController
{
    use VerifiesPrivateAPI;

    public function show($global, $site)
    {
        $global = $this->globalFromHandle($global);

        return GlobalVariablesResource::make($global->in($site->handle()));
    }

    public function update(Request $request, $handle, $site)
    {
        $global = $this->globalFromHandle($handle);

        try {
            $data = $this->show($handle, $site)->toArray($request);
            $mergedData = collect($data)->merge($request->all());

            $set = $global->in($site->handle());

            $fields = $set->blueprint()->fields()->addValues($request->all());

            $fields->validate();

            $values = $fields->process()->values();

            if ($set->hasOrigin()) {
                $values = $values->only($request->input('_localized'));
            }

            $set->data($values);

            $set->globalSet()->addLocalization($set)->save();

            $global = $this->globalFromHandle($handle);

            return GlobalVariablesResource::make($global->in($site->handle()));

        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        }
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
