<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Services\ExternalOrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ExternalApiIntegrationTest extends TestCase
{
  use RefreshDatabase;

  public function test_external_api_success_response(): void
  {
    // Fake events to prevent actual event listeners from running.
    Event::fake();

    // Create an order with pending status.
    $order = Order::factory()->create([
      'status' => Order::STATUS_PENDING,
    ]);

    // Mock the health check endpoint to return success.
    Http::fake([
      ExternalOrderStatusService::EXTERNAL_HEALTH_ENDPOINT . '*' => Http::response([], 200),
      ExternalOrderStatusService::EXTERNAL_STATUS_ENDPOINT . '*' => Http::response([
        'order_id' => $order->id(),
        'status' => Order::STATUS_SHIPPED,
      ], 200),
    ]);

    // Update the order status via API.
    $response = $this->putJson("/api/orders/{$order->orderNumber()}", [
      'status' => Order::STATUS_SHIPPED,
    ]);

    // Assert the API returns 200 OK.
    $response->assertOk();

    // Assert the order status was updated in the database.
    $this->assertDatabaseHas('orders', [
      'id' => $order->id(),
      'status' => Order::STATUS_SHIPPED,
    ]);

    // Assert that exactly two HTTP requests were sent (health check + status sync).
    Http::assertSentCount(2);
  }

  public function test_external_api_failure_response(): void
  {
    // Fake events to prevent actual event listeners from running.
    Event::fake();

    // Create an order with pending status.
    $order = Order::factory()->create([
      'status' => Order::STATUS_PENDING,
    ]);

    // Mock the health check endpoint to return 500 (service is down).
    Http::fake([
      ExternalOrderStatusService::EXTERNAL_HEALTH_ENDPOINT . '*' => Http::response(null, 500),
      ExternalOrderStatusService::EXTERNAL_STATUS_ENDPOINT . '*' => Http::response(null, 500),
    ]);

    // Expect that warning logging will occur (health check failure).
    Log::shouldReceive('warning')
      ->atLeast()
      ->once();

    // Attempt to update the order status via API
    $response = $this->putJson("/api/orders/{$order->orderNumber()}", [
      'status' => Order::STATUS_SHIPPED,
    ]);

    // Assert the API returns 200 OK even when external API is down (non-blocking).
    $response->assertOk();

    // Assert the order status was still updated in the database.
    $this->assertDatabaseHas('orders', [
      'id' => $order->id(),
      'status' => Order::STATUS_SHIPPED,
    ]);
  }
}
