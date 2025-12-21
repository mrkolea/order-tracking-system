<?php

namespace App\Services\Contracts;

use App\Models\Order;

interface ExternalOrderStatusServiceInterface
{
  public function syncStatus(Order $order): Order;

  public function isHealthy(): bool;
}
