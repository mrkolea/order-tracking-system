<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Queue;

abstract class TestCase extends BaseTestCase
{
  /**
   * Setup the test environment.
   */
  protected function setUp(): void
  {
    parent::setUp();

    // Fake queues to prevent actual queue processing during tests
    // This prevents queued listeners from running and causing infinite loops
    Queue::fake();
  }
}
