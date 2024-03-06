<?php

namespace Tv2regionerne\StatamicPrivateApi\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
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

            // add a prefix to the data if there's only a single term
            if ($field->type() === 'terms' && is_string($data) && count($field->config()['taxonomies']) === 1) {
                $data = $field->config()['taxonomies'][0].'::'.$data;
            }

            // add a container prefix to the assets
            if (in_array($field->type(), ['assets', 'advanced_assets'])) {
                if (is_string($data)) {
                    $data = $field->config()['container'].'::'.$data;
                } else {
                    $data = collect($data)->transform(function ($asset) use ($field) {
                        return $field->config()['container'].'::'.$asset;
                    })->toArray();
                }
            }

            $data = match ($field->type()) {
                'bard' => is_string($data) ? $data : json_encode($data),
                'assets' => is_string($data) ? [$data] : $data,
                'advanced_assets' => is_string($data) ? [$data] : $data,
                'entries' => is_string($data) ? [$data] : $data,
                'terms' => is_string($data) ? [$data] : $data,
                'users' => is_string($data) ? [$data] : $data,
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
    
    public function returnValidationErrors(ValidationException $e)
    {
        return response()->json([
            'error' => true,
            'errors' => $e->errors(),
        ], 422);
    }
}
