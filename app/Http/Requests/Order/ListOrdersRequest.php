<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'status' => [
        'sometimes',
        'string',
        Rule::in([
          Order::STATUS_PENDING,
          Order::STATUS_SHIPPED,
          Order::STATUS_DELIVERED,
          Order::STATUS_CANCELED,
        ]),
      ],
      'tag' => ['sometimes', 'string', 'max:255'],
    ];
  }
}
