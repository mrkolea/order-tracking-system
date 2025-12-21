<?php

namespace App\Http\Requests\Order;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends FormRequest
{

  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
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
