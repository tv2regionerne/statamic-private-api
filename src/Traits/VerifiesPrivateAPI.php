<?php

namespace Tv2regionerne\StatamicPrivateApi\Traits;

trait VerifiesPrivateAPI
{
    public function resourcesAllowed(string $type, string $key): bool {
        $resources = config('private-api.resources.'. $type);

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
