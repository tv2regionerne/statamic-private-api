<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge([
            'handle' => $this->resource->handle(),
        ], $this->resource->fileData()
        );
    }
}
