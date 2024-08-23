<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Tests\BaseTestCase;

class ListCommandTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createModule();
    }

    public function tearDown(): void
    {
        $this->app[RepositoryInterface::class]->delete('Blog');
        parent::tearDown();
    }

    public function test_it_can_list_modules()
    {
        $code = $this->artisan('module:list');
        $this->assertSame(0, $code);
    }
}
