<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when an order's status is changed.
 */
class OrderStatusChanged
{
  use Dispatchable;
  use SerializesModels;

  /**
   * Create a new event instance.
   *
   * @param Order $order The order whose status has changed.
   * @param string|null $previousStatus The previous status of the order, if any.
   * @param string $newStatus The new status of the order.
   */
  public function __construct(
    public Order $order,
    public ?string $previousStatus,
    public string $newStatus,
  ) {
  }
}
