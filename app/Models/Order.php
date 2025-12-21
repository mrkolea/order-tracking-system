<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Order Model
 *
 * Basic properties:
 * - @property int $id
 * - @property string $order_number
 * - @property string $status
 *   - Valid values: 'pending', 'shipped', 'delivered', 'canceled'
 * - @property float $total_amount
 */
class Order extends Model
{
  use HasFactory;
  use SoftDeletes;

  public const STATUS_PENDING = 'pending';
  public const STATUS_SHIPPED = 'shipped';
  public const STATUS_DELIVERED = 'delivered';
  public const STATUS_CANCELED = 'canceled';

  protected $table = 'orders';

  protected $fillable = [
    'order_number',
    'status',
    'total_amount',
  ];

  protected $casts = [
    'total_amount' => 'decimal:2',
  ];

  /**
   * The tags that belong to the order.
   *
   * @return BelongsToMany
   */
  public function tags(): BelongsToMany
  {
    return $this->belongsToMany(Tag::class)->withTimestamps();
  }

  /**
   * Get the items for the order.
   *
   * @return HasMany
   */
  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  /**
   * Get the order's ID.
   *
   * @return int
   */
  public function id(): int
  {
    return $this->getAttribute('id');
  }

  /**
   * Get the order's number.
   *
   * @return string
   */
  public function orderNumber(): string
  {
    return $this->getAttribute('order_number');
  }

  /**
   * Get the order's status.
   *
   * @return string
   */
  public function status(): string
  {
    return $this->getAttribute('status') ?? self::STATUS_PENDING;
  }

  /**
   * Get the order's total amount.
   *
   * @return float
   */
  public function totalAmount(): float
  {
    return (float) $this->getAttribute('total_amount');
  }
}
