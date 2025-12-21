<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class ShowOrderRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'order_number' => ['required', 'string', 'max:255'],
    ];
  }

  protected function prepareForValidation(): void
  {
    $this->merge([
      'order_number' => $this->route('order_number'),
    ]);
  }
}
