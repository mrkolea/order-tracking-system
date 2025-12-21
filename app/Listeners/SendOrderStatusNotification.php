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

  public function handle(OrderStatusChanged $event): void
  {
    Logger::info('=== SendOrderStatusNotification::handle() START ===', [
      'event_class' => get_class($event),
      'order_loaded' => $event->order ? 'yes' : 'no',
      'order_id' => $event->order ? $event->order->id() : 'N/A',
      'order_number' => $event->order ? $event->order->orderNumber() : 'N/A',
      'previous_status' => $event->previousStatus,
      'new_status' => $event->newStatus,
    ]);

    if (!$event->order) {
      Logger::error('=== Order is null in event ===');
      return;
    }

    try {
      Logger::info('=== Creating OrderStatusChangedNotification ===');
      $notification = new OrderStatusChangedNotification($event);
      Logger::info('=== Notification created successfully ===');

      $channels = [];

      Logger::info('=== Checking EMAIL_NOTIFICATIONS ===', [
        'EMAIL_NOTIFICATIONS' => env('EMAIL_NOTIFICATIONS'),
        'EMAIL_NOTIFICATIONS_bool' => (bool) env('EMAIL_NOTIFICATIONS', false),
      ]);

      if ((bool) env('EMAIL_NOTIFICATIONS', false)) {
        Logger::info('=== EMAIL_NOTIFICATIONS is true ===');

        $recipient_email = env('MAIL_ORDER_NOTIFICATION_RECIPIENT');
        $mail_from_address = config('mail.from.address');

        Logger::info('=== Email recipient resolved ===', [
          'MAIL_ORDER_NOTIFICATION_RECIPIENT' => $recipient_email,
          'mail.from.address' => $mail_from_address,
          'recipient_empty' => empty($recipient_email),
        ]);

        if (empty($recipient_email)) {
          Logger::warning('=== Email notification skipped - no recipient configured ===', [
            'order_number' => $event->order->orderNumber(),
          ]);
        } else {
          Logger::info('=== Calling Notification::route() ===', [
            'recipient' => $recipient_email,
          ]);

          try {
            Notification::route('mail', $recipient_email)->notify($notification);
            Logger::info('=== Notification::route() completed successfully ===');
            $channels[] = 'email';

            Logger::info('=== Email notification queued ===', [
              'order_number' => $event->order->orderNumber(),
              'recipient' => $recipient_email,
            ]);
          } catch (\Throwable $e) {
            Logger::error('=== ERROR in Notification::route() ===', [
              'exception' => get_class($e),
              'message' => $e->getMessage(),
              'file' => $e->getFile(),
              'line' => $e->getLine(),
              'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
          }
        }
      } else {
        Logger::info('=== EMAIL_NOTIFICATIONS is false or not set ===');
      }

      Logger::info('=== Checking PHONE_NOTIFICATIONS ===', [
        'PHONE_NOTIFICATIONS' => env('PHONE_NOTIFICATIONS'),
        'PHONE_NOTIFICATIONS_bool' => (bool) env('PHONE_NOTIFICATIONS', false),
      ]);

      if ((bool) env('PHONE_NOTIFICATIONS', false)) {
        Logger::info('=== PHONE_NOTIFICATIONS is true ===');

        $recipient_phone = env('PHONE_NOTIFICATION_RECIPIENT');

        Logger::info('=== Phone recipient resolved ===', [
          'PHONE_NOTIFICATION_RECIPIENT' => $recipient_phone,
          'recipient_empty' => empty($recipient_phone),
        ]);

        if ($recipient_phone) {
          Logger::info('=== Calling toSms() ===', [
            'recipient' => $recipient_phone,
          ]);

          try {
            $notification->toSms($recipient_phone);
            $channels[] = 'SMS';

            Logger::info('=== SMS notification logged ===', [
              'order_number' => $event->order->orderNumber(),
              'recipient' => $recipient_phone,
            ]);
          } catch (\Throwable $e) {
            Logger::error('=== ERROR in toSms() ===', [
              'exception' => get_class($e),
              'message' => $e->getMessage(),
              'file' => $e->getFile(),
              'line' => $e->getLine(),
              'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
          }
        } else {
          Logger::warning('=== SMS notification skipped - no recipient configured ===');
        }
      } else {
        Logger::info('=== PHONE_NOTIFICATIONS is false or not set ===');
      }

      Logger::info('=== SendOrderStatusNotification::handle() END ===', [
        'channels' => $channels,
      ]);
    } catch (\Throwable $e) {
      Logger::error('=== ERROR in SendOrderStatusNotification::handle() ===', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);
      throw $e;
    }
  }

  public function failed(OrderStatusChanged $event, Throwable $exception): void
  {
    Logger::error('=== SendOrderStatusNotification FAILED ===', [
      'order_number' => $event->order->orderNumber(),
      'exception' => get_class($exception),
      'message' => $exception->getMessage(),
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
      'trace' => $exception->getTraceAsString(),
    ]);
  }
}
