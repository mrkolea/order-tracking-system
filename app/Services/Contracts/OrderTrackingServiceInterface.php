<?php

namespace App\Services\Contracts;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderTrackingServiceInterface
{
  public function createOrder(array $data): Order;

  public function getOrderByNumber(string $order_number): Order;

  public function listOrders(array $filters = []): LengthAwarePaginator;

  public function updateOrder(string $order_number, array $data): Order;

  public function deleteOrder(string $order_number): bool;

  public function getOrderStatuses(): array;
}
