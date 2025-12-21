<?php

namespace App\Mail;

use App\Events\OrderStatusChanged;
use App\Logging\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Class OrderStatusChangedMail
 *
 * @package App\Mail
 */
class OrderStatusChangedMail extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * The recipient email address.
   *
   * @var string|null
   */
  protected ?string $recipient_email = null;

  /**
   * Create a new message instance.
   *
   * @param OrderStatusChanged $event
   * @param string|null $to
   */
  public function __construct(
    public OrderStatusChanged $event,
    ?string $to = null
  ) {
    $this->recipient_email = $to;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    Logger::debug('OrderStatusChangedMail::envelope()', [
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

      $envelope = new Envelope(
        to: $to_address,
        subject: "Order #{$this->event->order->orderNumber()} Status Changed to {$new_status_label}",
      );
      return $envelope;
    }
    catch (\Throwable $e) {
      Logger::error('OrderStatusChangedMail::envelope()', [
        'exception' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
      ]);
      throw $e;
    }
  }

  /**
   * Get the message content definition.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content(): Content
  {
    Logger::debug('OrderStatusChangedMail::content()');

    try {
      $content = new Content(
        view: 'emails.order-status-changed',
        with: [
          'order' => $this->event->order,
          'previousStatus' => $this->event->previousStatus,
          'newStatus' => $this->event->newStatus,
        ],
      );

      return $content;
    }
    catch (\Throwable $e) {
      Logger::error('OrderStatusChangedMail::content()', [
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
