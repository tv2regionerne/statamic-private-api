<?php

namespace Tv2regionerne\StatamicPrivateApi\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Fields\Blueprint;

trait VerifiesPrivateAPI
{
    public function mergeBlueprintAndRequestData(Blueprint $blueprint, Collection $existingData, Request $request): Collection
    {
        $fields = $blueprint->fields();

        $fields->all()->each(function ($field) use ($existingData, $request) {
            $handle = $field->handle();

            $data = null;

            if ($existingData->has($handle)) {
                $data = $existingData->get($handle);
            }

            if ($request->has($handle)) {
                $data = $request->input($handle);
            }

            if ($data === null) {
                return;
            }

            $data = match ($field->type()) {
                'bard' => is_string($data) ? $data : json_encode($data),
                'assets' => is_string($data) ? [$data] : $data,
                'entries' => is_string($data) ? [$data] : $data,
                default => $data
            };

            $existingData->put($handle, $data);
        });

        return $existingData;
    }

    public function resourcesAllowed(string $type, string $key): bool
    {
        $resources = config('private-api.resources.'.$type);

        if (! $resources) {
            return false;
        }

        if ($resources === true) {
            return true;
        }

        if (is_array($resources) && array_key_exists($key, $resources)) {
            return true;
        }

        return false;
    }
}
