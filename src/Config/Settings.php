<?php

namespace Daycry\Settings\Config;

use Daycry\Settings\Handlers\DatabaseHandler;
use CodeIgniter\Config\BaseConfig;

class Settings extends BaseConfig
{
    /**
     * The available handlers. The alias must
     * match a public class var here with the
     * settings array containing 'class'.
     *
     * @var string[]
     */
    public $handlers = [ 'database' ];

    /**
     * Database handler settings.
     */
    public $database = [
        'class' => DatabaseHandler::class,
        'table' => 'settings',
        'writeable' => true
    ];
}