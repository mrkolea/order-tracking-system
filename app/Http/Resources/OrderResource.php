<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id(),
      'order_number' => $this->orderNumber(),
      'status' => $this->status(),
      'total_amount' => $this->totalAmount(),
      'tags' => TagResource::collection($this->whenLoaded('tags')),
      'items' => OrderItemResource::collection($this->whenLoaded('items')),
      'created_at' => $this->when($this->created_at, $this->created_at->toDateTimeString()),
      'updated_at' => $this->when($this->updated_at, $this->updated_at->toDateTimeString()),
    ];
  }
}
