<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_is_created_with_valid_data(): void
    {
        // Fake events to prevent actual event listeners from running
        Event::fake();

        // Prepare the order creation payload with all required data
        $payload = [
            'order_number' => 'ORD-1001',
            'total_amount' => 22.22,
            'tags' => ['vip', 'urgent'],
            'items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => 2,
                    'price' => 11.11,
                ],
            ],
        ];

        // Send POST request to create the order
        $response = $this->postJson('/api/orders', $payload);

        // Assert the response is 201 Created with correct JSON structure
        $response
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'total_amount',
                    'tags',
                    'items',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.order_number', 'ORD-1001')
            ->assertJsonPath('data.status', 'pending');

        // Assert the order was saved to the database with correct data
        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-1001',
            'status' => 'pending',
            'total_amount' => 22.22,
        ]);

        // Assert the order items were created correctly
        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Product A',
            'quantity' => 2,
            'price' => 11.11,
        ]);

        // Assert the tags were created correctly
        $this->assertDatabaseHas('tags', [
            'slug' => 'vip',
        ]);
    }

    public function test_order_creation_fails_with_invalid_data(): void
    {
        // Prepare invalid payload with missing/empty required fields
        $payload = [
            'order_number' => '',
            'total_amount' => null,
        ];

        // Send POST request with invalid data
        $response = $this->postJson('/api/orders', $payload);

        // Assert the response is 422 Unprocessable Entity with validation errors
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_number',
                'total_amount',
            ]);

        // Assert no order was created in the database
        $this->assertDatabaseCount('orders', 0);
    }
}
