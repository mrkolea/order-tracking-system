<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
  use HasFactory;

  protected $table = 'order_items';

  protected $fillable = [
    'order_id',
    'product_name',
    'quantity',
    'price',
  ];

  protected $casts = [
    'quantity' => 'integer',
    'price' => 'decimal:2',
  ];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  public function id(): int
  {
    return $this->getAttribute('id');
  }

  public function orderId(): int
  {
    return $this->getAttribute('order_id');
  }

  public function productName(): string
  {
    return $this->getAttribute('product_name');
  }

  public function quantity(): int
  {
    return (int) $this->getAttribute('quantity');
  }

  public function price(): float
  {
    return (float) $this->getAttribute('price');
  }
}
