<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderItem Model
 *
 * Basic properties:
 * - @property int $id
 * - @property int $order_id
 * - @property string $product_name
 * - @property int $quantity
 * - @property float $price
 */
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

  /**
   * Get the order that owns the item.
   *
   * @return BelongsTo
   */
  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class);
  }

  /**
   * Get the order item's ID.
   *
   * @return int
   */
  public function id(): int
  {
    return $this->getAttribute('id');
  }

  /**
   * Get the order ID associated with the item.
   *
   * @return int
   */
  public function orderId(): int
  {
    return $this->getAttribute('order_id');
  }

  /**
   * Get the product name of the order item.
   *
   * @return string
   */
  public function productName(): string
  {
    return $this->getAttribute('product_name');
  }

  /**
   * Get the quantity of the order item.
   *
   * @return int
   */
  public function quantity(): int
  {
    return (int) $this->getAttribute('quantity');
  }

  /**
   * Get the price of the order item.
   *
   * @return float
   */
  public function price(): float
  {
    return (float) $this->getAttribute('price');
  }
}
