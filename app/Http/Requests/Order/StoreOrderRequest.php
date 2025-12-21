<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreOrderRequest extends FormRequest
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
      'order_number' => ['required', 'string', 'max:255', 'unique:orders,order_number'],
      'total_amount' => ['required', 'numeric', 'min:0'],
      'tags' => ['sometimes', 'array'],
      'tags.*' => ['string', 'max:255'],
      'items' => ['sometimes', 'array'],
      'items.*.product_name' => ['required_with:items', 'string', 'max:255'],
      'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
      'items.*.price' => ['required_with:items', 'numeric', 'min:0'],
    ];
  }

  /**
   * Handle a failed validation attempt.
   *
   * @param  \Illuminate\Contracts\Validation\Validator  $validator
   * @return void
   *
   * @throws \Illuminate\Http\Exceptions\HttpResponseException
   */
  protected function failedValidation(Validator $validator): void
  {
    throw new HttpResponseException(
      response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY)
    );
  }
}
