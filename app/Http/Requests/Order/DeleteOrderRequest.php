<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class DeleteOrderRequest extends FormRequest
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
