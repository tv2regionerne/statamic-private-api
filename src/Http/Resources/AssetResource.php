<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Resources;

use Statamic\Http\Resources\API\AssetResource as StatamicResource;

class AssetResource extends StatamicResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            [
                'api_id' => base64_encode($this->resource->id()),
            ],
        );
    }
}
