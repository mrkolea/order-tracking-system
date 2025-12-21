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

class OrderController extends Controller
{
  public function __construct(
    protected OrderTrackingServiceInterface $orderTrackingService
  ) {
  }

  public function index(ListOrdersRequest $request)
  {
    try {
      $filters = $request->validated();
      $orders = $this->orderTrackingService->listOrders($filters);
      return OrderResource::collection($orders);
    } catch (\Exception $e) {
      Logger::error('Error in OrderController::index()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'message' => 'An error occurred while fetching orders',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function store(StoreOrderRequest $request): JsonResponse
  {
    try {
      $order = $this->orderTrackingService->createOrder($request->validated());
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    } catch (ValidationException $e) {
      Logger::warning('Validation error in OrderController::store()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'errors' => $e->errors(),
      ]);

      return response()->json([
        'message' => 'Validation failed',
        'errors' => $e->errors(),
      ], Response::HTTP_UNPROCESSABLE_ENTITY);
    } catch (\Exception $e) {
      Logger::error('Error in OrderController::store()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);

      return response()->json([
        'message' => 'An error occurred while creating the order',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function show(ShowOrderRequest $request): JsonResponse
  {
    try {
      $order = $this->orderTrackingService->getOrderByNumber($request->validated()['order_number']);
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    } catch (ModelNotFoundException $e) {
      Logger::warning('Order not found in OrderController::show()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'order_number' => $request->validated()['order_number'] ?? 'unknown',
      ]);

      return response()->json([
        'message' => 'Order not found',
        'error' => config('app.debug') ? $e->getMessage() : 'Order not found',
      ], Response::HTTP_NOT_FOUND);
    } catch (\Exception $e) {
      Logger::error('Error in OrderController::show()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $request->validated()['order_number'] ?? 'unknown',
      ]);

      return response()->json([
        'message' => 'An error occurred while fetching the order',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function update(UpdateOrderRequest $request): JsonResponse
  {
    $order_number = null;
    try {
      $validated = $request->validated();
      $order_number = $validated['order_number'];
      unset($validated['order_number']);

      $order = $this->orderTrackingService->updateOrder($order_number, $validated);
      return (new OrderResource($order))
        ->response()
        ->setStatusCode(Response::HTTP_OK);
    } catch (\Exception $e) {
      Logger::error('Error in OrderController::update()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $order_number ?? 'unknown',
      ]);

      return response()->json([
        'message' => 'An error occurred while updating the order',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function destroy(DeleteOrderRequest $request): JsonResponse
  {
    try {
      $order_number = $request->validated()['order_number'];
      $deleted = $this->orderTrackingService->deleteOrder($order_number);

      if ($deleted) {
        return response()->json([], Response::HTTP_NO_CONTENT);
      }

      return response()->json([
        'message' => 'Order already deleted',
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      Logger::error('Error in OrderController::destroy()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'order_number' => $request->validated()['order_number'] ?? 'unknown',
      ]);

      return response()->json([
        'message' => 'An error occurred while deleting the order',
        'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
      ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
