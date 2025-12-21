<?php

namespace App\Mail;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
  use Queueable, SerializesModels;

  protected ?string $recipient_email = null;

  public function __construct(
    public OrderStatusChanged $event,
    ?string $to = null
  ) {
    $this->recipient_email = $to;
    Logger::info('=== OrderStatusChangedMail CONSTRUCTED ===', [
      'to' => $this->recipient_email,
      'order_number' => $event->order->orderNumber(),
    ]);
  }

  public function envelope(): Envelope
  {
    Logger::info('=== OrderStatusChangedMail::envelope() called ===', [
      'to' => $this->recipient_email,
      'mail_from_address' => config('mail.from.address'),
    ]);

    try {
      $status_labels = [
        'pending' => 'Pending',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'canceled' => 'Canceled',
      ];

      $new_status_label = $status_labels[$this->event->newStatus] ?? $this->event->newStatus;

      $to_address = $this->recipient_email ? [$this->recipient_email] : [config('mail.from.address')];

      Logger::info('=== Creating Envelope ===', [
        'to_address' => $to_address,
        'subject' => "Order #{$this->event->order->orderNumber()} Status Changed to {$new_status_label}",
      ]);

      $envelope = new Envelope(
        to: $to_address,
        subject: "Order #{$this->event->order->orderNumber()} Status Changed to {$new_status_label}",
      );

      Logger::info('=== Envelope created successfully ===');

      return $envelope;
    } catch (\Throwable $e) {
      Logger::error('=== ERROR in envelope() ===', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);
      throw $e;
    }
  }

  public function content(): Content
  {
    Logger::info('=== OrderStatusChangedMail::content() called ===');

    try {
      $content = new Content(
        view: 'emails.order-status-changed',
        with: [
          'order' => $this->event->order,
          'previousStatus' => $this->event->previousStatus,
          'newStatus' => $this->event->newStatus,
        ],
      );

      Logger::info('=== Content created successfully ===');

      return $content;
    } catch (\Throwable $e) {
      Logger::error('=== ERROR in content() ===', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);
      throw $e;
    }
  }
}
