<?php

namespace App\Providers;

use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider class.
 */
class EventServiceProvider extends ServiceProvider
{
  /**
   * The event to listener mappings for the application.
   *
   * @var array
   */
  protected $listen = [
    OrderStatusChanged::class => [
      SendOrderStatusNotification::class,
    ],
  ];
}
