<?php

namespace App\Providers;

use App\Services\Contracts\ExternalOrderStatusServiceInterface;
use App\Services\Contracts\OrderTrackingServiceInterface;
use App\Services\ExternalOrderStatusService;
use App\Services\OrderTrackingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
