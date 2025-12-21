<?php

namespace App\Services\Contracts;

use App\Models\Order;

/**
 * Interface for External Order Status Service.
 */
interface ExternalOrderStatusServiceInterface
{
  /**
   * Sync the status of the given order with the external system.
   *
   * @param Order $order
   *
   * @return Order
   */
  public function syncStatus(Order $order): Order;

  /**
   * Check if the external service is healthy.
   *
   * @return bool
   */
  public function isHealthy(): bool;
}
