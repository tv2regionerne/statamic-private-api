<?php

namespace Tv2regionerne\StatamicPrivateApi\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GlobalVariablesResource extends JsonResource
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
            'data' => $this->resource->fileData(),
        ];
    }
}
