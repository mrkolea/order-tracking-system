<?php

namespace App\Providers;

use App\Services\Contracts\ExternalOrderStatusServiceInterface;
use App\Services\Contracts\OrderTrackingServiceInterface;
use App\Services\ExternalOrderStatusService;
use App\Services\OrderTrackingService;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider class.
 */
class AppServiceProvider extends ServiceProvider
{

  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->bind(
      ExternalOrderStatusServiceInterface::class,
      ExternalOrderStatusService::class
    );

    $this->app->bind(
      OrderTrackingServiceInterface::class,
      OrderTrackingService::class
    );
  }
}
