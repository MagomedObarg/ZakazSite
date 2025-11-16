<?php

namespace Tests;

use App\Support\Http;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::reset();
    }

    protected function tearDown(): void
    {
        Http::reset();
        parent::tearDown();
    }
}
