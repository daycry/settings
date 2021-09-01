<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Nexus\PHPUnit\Extension\Expeditable;

abstract class TestCase extends CIUnitTestCase
{
    use Expeditable;

    protected $namespace = 'Daycry\Settings';
    protected $refresh   = true;

    /**
     * @var string
     */
    protected $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = config( 'Settings' )->database[ 'table' ];
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
    }
}