<?php

namespace App\Services;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use App\Models\Order;
use App\Models\Tag;
use App\Services\Contracts\ExternalOrderStatusServiceInterface;
use App\Services\Contracts\OrderTrackingServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Main bussiness logic of Service for Order Tracking.
 */
class OrderTrackingService implements OrderTrackingServiceInterface
{
  public function __construct(
    protected ExternalOrderStatusServiceInterface $externalOrderStatusService
  ) {
  }

  public function createOrder(array $data): Order
  {
    if (Order::where('order_number', $data['order_number'])->exists()) {
      throw ValidationException::withMessages([
        'order_number' => ['The order number has already been taken.'],
      ]);
    }

    return DB::transaction(function () use ($data) {
      $order = Order::create([
        'order_number' => $data['order_number'],
        'total_amount' => $data['total_amount'],
      ]);

      if (!empty($data['tags'] ?? [])) {
        $this->syncTags($order, $data['tags']);
      }

      if (!empty($data['items'] ?? [])) {
        $order->items()->createMany($data['items']);
      }

      return $order->fresh(['tags', 'items']);
    });
  }

  public function getOrderByNumber(string $order_number): Order
  {
    return Order::with(['tags', 'items'])
      ->where('order_number', $order_number)
      ->firstOrFail();
  }

  public function listOrders(array $filters = []): LengthAwarePaginator
  {
    $query = Order::query()->with(['tags', 'items']);

    if (!empty($filters['status'] ?? null)) {
      $query->where('status', $filters['status']);
    }

    if (!empty($filters['tag'] ?? null)) {
      $query->whereHas('tags', function (Builder $q) use ($filters) {
        $q->where('slug', $filters['tag'])
          ->orWhere('name', $filters['tag']);
      });
    }

    return $query->paginate();
  }

  public function updateOrder(string $order_number, array $data): Order
  {
    $order = Order::with('tags')
      ->where('order_number', $order_number)
      ->firstOrFail();
    $previous_status = $order->status();

    $order = DB::transaction(function () use ($order, $data) {
      if (array_key_exists('status', $data)) {
        $order->status = $data['status'];
        $order->save();
      }

      if (!empty($data['tags'] ?? [])) {
        $tag_ids = $this->syncTags($order, $data['tags']);
        $order->setRelation('tags', Tag::whereIn('id', $tag_ids)->get());
      }

      return $order->fresh(['tags', 'items']);
    });

    if ($previous_status !== $order->status()) {
      if ($this->externalOrderStatusService->isHealthy()) {
        try {
          $this->externalOrderStatusService->syncStatus($order);
        } catch (\RuntimeException $e) {
          Logger::error(
            'External order status sync failed, continuing with order update', [
              'order_id' => $order->id(),
              'order_number' => $order->orderNumber(),
              'exception' => get_class($e),
              'message' => $e->getMessage(),
          ]);
        }
      } else {
        Logger::warning(
          'External order status API is unhealthy, skipping sync', [
            'order_id' => $order->id(),
            'order_number' => $order->orderNumber(),
        ]);
      }

      // Dispatch OrderStatusChanged event.
      OrderStatusChanged::dispatch($order, $previous_status, $order->status());
    }

    return $order;
  }

  protected function syncTags(Order $order, array $tags): array
  {
    $tag_ids = [];

    foreach ($tags as $value) {
      $name = (string) $value;
      $slug = Str::slug($name);

      $tag = Tag::firstOrCreate(
        ['slug' => $slug],
        ['name' => $name]
      );

      $tag_ids[] = $tag->id;
    }

    $pivot_data = array_fill_keys($tag_ids, [
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $order->tags()->sync($pivot_data);

    return $tag_ids;
  }

  public function deleteOrder(string $order_number): bool
  {
    $order = Order::where('order_number', $order_number)->firstOrFail();

    if ($order->trashed()) {
      return false;
    }

    $order->delete();

    return true;
  }

  public function getOrderStatuses(): array
  {
    return [
      Order::STATUS_PENDING,
      Order::STATUS_SHIPPED,
      Order::STATUS_DELIVERED,
      Order::STATUS_CANCELED,
    ];
  }
}
