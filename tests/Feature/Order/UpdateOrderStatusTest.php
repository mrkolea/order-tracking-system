<?php

namespace Tests\Feature\Order;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Services\ExternalOrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_is_updated(): void
    {
        // Create an order with pending status
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
        ]);

        // Mock the external API to return a successful response
        Http::fake([
            ExternalOrderStatusService::EXTERNAL_STATUS_ENDPOINT . '*' => Http::response([
                'order_id' => $order->id(),
                'status' => Order::STATUS_SHIPPED,
            ], 200),
        ]);

        // Prepare the update payload with new status
        $payload = [
            'status' => Order::STATUS_SHIPPED,
        ];

        // Send PUT request to update the order status
        $response = $this->putJson("/api/orders/{$order->orderNumber()}", $payload);

        // Assert the response is 200 OK with updated status in JSON
        $response
            ->assertOk()
            ->assertJsonPath('data.status', Order::STATUS_SHIPPED);

        // Assert the order status was updated in the database
        $this->assertDatabaseHas('orders', [
            'id' => $order->id(),
            'status' => Order::STATUS_SHIPPED,
        ]);
    }

    public function test_order_status_changed_event_is_dispatched(): void
    {
        // Fake events to capture dispatched events for assertion
        Event::fake();

        // Create an order with pending status
        $order = Order::factory()->create([
            'status' => Order::STATUS_PENDING,
        ]);

        // Mock the external API to return a successful response
        Http::fake([
            ExternalOrderStatusService::EXTERNAL_STATUS_ENDPOINT . '*' => Http::response([
                'order_id' => $order->id(),
                'status' => Order::STATUS_DELIVERED,
            ], 200),
        ]);

        // Prepare the update payload with new status
        $payload = [
            'status' => Order::STATUS_DELIVERED,
        ];

        // Send PUT request to update the order status
        $this->putJson("/api/orders/{$order->orderNumber()}", $payload)
            ->assertOk();

        // Assert that OrderStatusChanged event was dispatched with correct data
        Event::assertDispatched(OrderStatusChanged::class, function (OrderStatusChanged $event) use ($order) {
            return $event->order->is($order) && $event->newStatus === Order::STATUS_DELIVERED;
        });
    }
}
