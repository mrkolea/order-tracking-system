<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class StoreOrderRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

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
