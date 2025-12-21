<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use App\Models\Order;
use App\Services\Contracts\ExternalOrderStatusServiceInterface;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;

/**
 * Service class to interact with external order status API.
 */
class ExternalOrderStatusService implements ExternalOrderStatusServiceInterface
{
  public const EXTERNAL_STATUS_ENDPOINT = 'https://external-api.test/orders/status';
  public const EXTERNAL_HEALTH_ENDPOINT = 'https://external-api.test/up';

  public function __construct(
    protected HttpFactory $http
  ) {
  }

  public function isHealthy(): bool
  {
    try {
      $response = $this->http->timeout(5)->get(self::EXTERNAL_HEALTH_ENDPOINT);

      return $response->successful();
    } catch (\Exception $e) {
      Logger::warning('External order status API health check failed', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
      ]);

      return false;
    }
  }

  public function syncStatus(Order $order): Order
  {
    $response = $this->http->post(self::EXTERNAL_STATUS_ENDPOINT, [
      'order_id' => $order->id(),
      'status' => $order->status(),
    ]);

    if (!$response->successful()) {
      $this->logFailure($order, $response);
      throw new \RuntimeException('External order status API failed.');
    }

    $data = $response->json();
    $new_status = $data['status'] ?? null;
    if (! is_string($new_status)) {
      $this->logFailure(
        $order,
        $response,
        'Missing or invalid status in response.'
      );
      throw new \RuntimeException('External order status API returned invalid data.');
    }

    $valid_statuses = [
      Order::STATUS_PENDING,
      Order::STATUS_SHIPPED,
      Order::STATUS_DELIVERED,
      Order::STATUS_CANCELED,
    ];

    if (!in_array($new_status, $valid_statuses, true)) {
      $this->logFailure(
        $order,
        $response,
        "Invalid status received: {$new_status}"
      );
      throw new \RuntimeException("External order status API returned invalid status: {$new_status}");
    }

    $current_status = $order->status();
    if ($current_status !== $new_status) {
      $order->status = $new_status;
      $order->save();
      OrderStatusChanged::dispatch($order, $current_status, $new_status);
    }

    return $order;
  }

  protected function logFailure(
    Order $order,
    Response $response,
    ?string $reason = null
    ): void
  {
    Logger::error('External order status API failed', [
      'order_id' => $order->id(),
      'order_number' => $order->orderNumber(),
      'status_code' => $response->status(),
      'response_body' => $response->body(),
      'reason' => $reason,
    ]);
  }
}
