<?php

namespace App\Notifications;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use App\Mail\OrderStatusChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notification for Order Status Change 
 */
class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public $tries = 3;
  public $timeout = 60;

  /**
   * Create a new notification instance.
   *
   * @param OrderStatusChanged $event
   */
  public function __construct(
    public OrderStatusChanged $event
  ) {
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed $notifiable
   *
   * @return array
   */
  public function via($notifiable): array
  {
    // dd($notifiable);

    $channels = [];

    if (env('EMAIL_NOTIFICATIONS', false)) {
      $channels[] = 'mail';
    }
    // dd($channels);

    return $channels;
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed $notifiable
   *
   * @return OrderStatusChangedMail
   */
  public function toMail($notifiable): OrderStatusChangedMail
  {
    // dd($notifiable);
    try {
      $email = is_string($notifiable) ? $notifiable : $notifiable->routeNotificationFor('mail');
      $mailable = new OrderStatusChangedMail($this->event, $email);
      return $mailable;

    }
    catch (\Throwable $e) {
      Logger::error('OrderStatusChangedNotification::toMail()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
      ]);
      throw $e;
    }
  }

  /**
   * Get the SMS representation of the notification.
   *
   * @param  mixed $notifiable
   *
   * @return string
   */
  public function toSms($notifiable): string
  {
    $phone_number = is_string($notifiable) ? $notifiable : $notifiable->routeNotificationFor('sms');

    Logger::info('SMS notification', [
      'to' => $phone_number,
      'order_number' => $this->event->order->orderNumber(),
      'previous_status' => $this->event->previousStatus,
      'new_status' => $this->event->newStatus,
    ]);

    return '';
  }

  /**
   * Handle a job failure.
   *
   * @param \Throwable $exception
   */
  public function failed(\Throwable $exception): void
  {
    Logger::error('OrderStatusChangedNotification::failed()', [
      'exception' => get_class($exception),
      'message' => $exception->getMessage(),
    ]);
  }
}
