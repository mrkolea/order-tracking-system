<?php

namespace App\Services\Contracts;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface for Order Tracking Service.
 */
interface OrderTrackingServiceInterface
{
  /**
   * Create a new order with the given data.
   *
   * @param array $data
   *
   * @return Order
   */
  public function createOrder(array $data): Order;

  /**
   * Retrieve an order by its order number.
   *
   * @param string $order_number
   *
   * @return Order
   */
  public function getOrderByNumber(string $order_number): Order;

  /**
   * List orders with optional filters.
   *
   * @param array $filters
   *
   * @return LengthAwarePaginator
   */
  public function listOrders(array $filters = []): LengthAwarePaginator;

  /**
   * Update an existing order with the given data.
   *
   * @param string $order_number
   * @param array  $data
   *
   * @return Order
   */
  public function updateOrder(string $order_number, array $data): Order;

  /**
   * Delete an order by its order number.
   *
   * @param string $order_number
   *
   * @return bool
   */
  public function deleteOrder(string $order_number): bool;

  /**
   * Get all possible order statuses.
   *
   * @return array
   */
  public function getOrderStatuses(): array;
}
