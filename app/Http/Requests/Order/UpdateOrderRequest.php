<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'order_number' => ['required', 'string', 'max:255'],
      'status' => [
        'sometimes',
        'required_without:tags',
        'string',
        Rule::in([
          Order::STATUS_PENDING,
          Order::STATUS_SHIPPED,
          Order::STATUS_DELIVERED,
          Order::STATUS_CANCELED,
        ]),
      ],
      'tags' => ['sometimes', 'required_without:status', 'array'],
      'tags.*' => ['string', 'max:255'],
    ];
  }

  protected function prepareForValidation(): void
  {
    $this->merge([
      'order_number' => $this->route('order_number'),
    ]);
  }
}
