<?php

namespace Daycry\Settings\Config;

use Daycry\Settings\Handlers\ArrayHandler;
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
     * Array handler settings.
     */
    public $array = [
        'class'     => ArrayHandler::class,
        'writeable' => true,
    ];

    /**
     * Database handler settings.
     */
    public $database = [
        'class' => DatabaseHandler::class,
        'table' => 'settings',
        'group' => null,
        'writeable' => true
    ];
}
