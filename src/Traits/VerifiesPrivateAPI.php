<?php

namespace Tv2regionerne\StatamicPrivateApi\Traits;

use Illuminate\Validation\ValidationException;

trait VerifiesPrivateAPI
{
    public function resourcesAllowed(string $type, string $key): bool
    {
        $resources = config('private-api.resources.' . $type);

        if (! $resources) {
            return false;
        }

        if ($resources === true) {
            return true;
        }

        if (is_array($resources)) {
            if (array_key_exists('allowed_filters', $resources)) {
                unset($resources['allowed_filters']);

                if (empty($resources)) {
                    return true;
                }
            }

            if (array_key_exists($key, $resources)) {
                return true;
            }
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

    protected function allowedFilters()
    {
        $config = config("private-api.resources.{$this->resourceConfigKey}.allowed_filters");

        return collect(is_array($config) ? $config : [])
            ->reject(fn($field) => in_array($field, ['password', 'password_hash']))
            ->all();
    }
}
