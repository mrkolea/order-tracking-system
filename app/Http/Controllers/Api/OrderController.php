<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\DeleteOrderRequest;
use App\Http\Requests\Order\ListOrdersRequest;
use App\Http\Requests\Order\ShowOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Logging\Logger;
use App\Services\Contracts\OrderTrackingServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;

/**
 * Controller for managing orders via the API.
 */
class OrderController extends Controller
{
  public function __construct(
    protected OrderTrackingServiceInterface $orderTrackingService
  ) {
  }

  /**
   * List orders with optional filtering.
   *
   * @param ListOrdersRequest $request
   * @return OrderResource|JsonResponse
   */
  public function index(ListOrdersRequest $request)
  {
    try {
      $filters = $request->validated();
      $orders = $this->orderTrackingService->listOrders($filters);
      return OrderResource::collection($orders);
    }
    catch (\Exception $e) {
      Logger::error('Error in OrderController::index()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'message' => 'Unable to retrieve orders. Please try again or contact support.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Store a newly created order.
   *
   * @param StoreOrderRequest $request
   * @return JsonResponse
   */
  public function store(StoreOrderRequest $request): JsonResponse
  {
    try {
      $order = $this->orderTrackingService->createOrder($request->validated());
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }
    catch (ValidationException $e) {
      Logger::warning('Validation error in OrderController::store()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'errors' => $e->errors(),
      ]);

      return response()->json([
        'message' => 'Please check the provided information and correct the errors below.',
        'errors' => $e->errors(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    catch (\Exception $e) {
      Logger::error('Error in OrderController::store()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'message' => 'Unable to create order. Please try again or contact support.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Display the specified order.
   *
   * @param ShowOrderRequest $request
   * @return JsonResponse
   */
  public function show(ShowOrderRequest $request): JsonResponse
  {
    $order_number = null;
    try {
      $validated = $request->validated();
      $order_number = $validated['order_number'] ?? 'none';
      $order = $this
        ->orderTrackingService
        ->getOrderByNumber($order_number);
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    }
    catch (ModelNotFoundException $e) {
      Logger::warning('Order not found in OrderController::show()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Order not found. Please verify the order number.',
      ], Response::HTTP_NOT_FOUND);
    }
    catch (\Exception $e) {
      Logger::error('Error in OrderController::show()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Unable to retrieve order details. Please try again or contact support.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Update the specified order.
   *
   * @param UpdateOrderRequest $request
   * @return JsonResponse
   */
  public function update(UpdateOrderRequest $request): JsonResponse
  {
    $order_number = null;
    try {
      $validated = $request->validated();
      $order_number = $validated['order_number'] ?? 'none';
      unset($validated['order_number']);

      $order = $this
        ->orderTrackingService
        ->updateOrder($order_number, $validated);
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    }
    catch (ModelNotFoundException $e) {
      Logger::warning('Order not found in OrderController::update()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Order not found. Please verify the order number.',
      ], Response::HTTP_NOT_FOUND);
    }
    catch (ValidationException $e) {
      Logger::warning('Validation error in OrderController::update()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'errors' => $e->errors(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Please correct the errors below.',
        'errors' => $e->errors(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    catch (\Exception $e) {
      Logger::error('Error in OrderController::update()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Unable to update order. Please try again or contact support.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Remove the specified order.
   *
   * @param DeleteOrderRequest $request
   * @return JsonResponse
   */
  public function destroy(DeleteOrderRequest $request): JsonResponse
  {
    $order_number = null;
    try {
      $validated = $request->validated();
      $order_number = $validated['order_number'] ?? 'none';
      $deleted = $this->orderTrackingService->deleteOrder($order_number);

      if ($deleted) {
        return response()->json([], Response::HTTP_NO_CONTENT);
      }

      return response()->json([
        'message' => 'Order already deleted.',
      ], Response::HTTP_OK);
    }
    catch (ModelNotFoundException $e) {
      Logger::warning('Order not found in OrderController::destroy()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Order not found. Please verify the order number.',
      ], Response::HTTP_NOT_FOUND);
    }
    catch (\Exception $e) {
      Logger::error('Error in OrderController::destroy()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $order_number ?? 'none',
      ]);

      return response()->json([
        'message' => 'Unable to delete order. Please try again or contact support.',
        'error' => config('app.debug') ? $e->getMessage() : null,
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

}
