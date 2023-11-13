<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlobalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'handle' => $this->resource->handle(),
            'blueprint' => $this->resource->blueprint(),
        ];
    }
}
