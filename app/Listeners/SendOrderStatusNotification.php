<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendOrderStatusNotification implements ShouldQueue
{
  use InteractsWithQueue;

  public $tries = 3;
  public $timeout = 60;

  /**
   * Handle the OrderStatusChanged event.
   *
   * @param  OrderStatusChanged $event
   */
  public function handle(OrderStatusChanged $event): void
  {
    if (!$event->order) {
      return;
    }

    try {
      $notification = new OrderStatusChangedNotification($event);

      if ((bool) env('EMAIL_NOTIFICATIONS', false)) {
        $recipient_email = env('MAIL_ORDER_NOTIFICATION_RECIPIENT');

        if (!empty($recipient_email)) {
          Notification::route('mail', $recipient_email)->notify($notification);
        }
      }

      if ((bool) env('PHONE_NOTIFICATIONS', false)) {
        $recipient_phone = env('PHONE_NOTIFICATION_RECIPIENT');

        if ($recipient_phone) {
          $notification->toSms($recipient_phone);
        }
      }
    }
    catch (Throwable $e) {
      Logger::error('Error in SendOrderStatusNotification::handle()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'order_number' => $event->order->orderNumber(),
      ]);
      throw $e;
    }
  }

  /**
   * Handle a job failure.
   *
   * @param  OrderStatusChanged $event
   * @param  \Throwable $exception
   */
  public function failed(OrderStatusChanged $event, Throwable $exception): void
  {
    Logger::error('SendOrderStatusNotification failed', [
      'order_number' => $event->order->orderNumber(),
      'exception' => get_class($exception),
      'message' => $exception->getMessage(),
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
    ]);
  }
}
