<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'name' => $this->name,
      'slug' => $this->slug,
      'attached_at' => $this->when(
        $this->pivot,
        optional($this->pivot->created_at)->toDateTimeString()
      ),
    ];
  }
}
