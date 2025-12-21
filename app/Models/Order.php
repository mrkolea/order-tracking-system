<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

  public function tags(): BelongsToMany
  {
    return $this->belongsToMany(Tag::class)->withTimestamps();
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function id(): int
  {
    return $this->getAttribute('id');
  }

  public function orderNumber(): string
  {
    return $this->getAttribute('order_number');
  }

  public function status(): string
  {
    return $this->getAttribute('status') ?? self::STATUS_PENDING;
  }

  public function totalAmount(): float
  {
    return (float) $this->getAttribute('total_amount');
  }
}
