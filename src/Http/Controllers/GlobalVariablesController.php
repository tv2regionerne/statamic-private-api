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

    public function show($global, ?string $site = null)
    {
        $global = $this->globalFromHandle($global);
        $site = $site ? Facades\Site::get($site) : Facades\Site::default();
        if (! $site) {
            abort(404);
        }

        return GlobalVariablesResource::make($global->in($site->handle()));
    }

    public function update(Request $request, $handle, ?string $site = null)
    {
        $global = $this->globalFromHandle($handle);
        $site = $site ? Facades\Site::get($site) : Facades\Site::default();
        $set = $global->in($site->handle());


        try {
            $data = $this->show($handle, $site->handle())->toArray($request)['data'] ?? [];
            $mergedData = collect($data)->merge($request->all());

            $fields = $set->blueprint()->fields()->addValues($mergedData->toArray());

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
