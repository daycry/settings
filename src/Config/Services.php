<?php

namespace Daycry\Settings\Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Config\BaseConfig;
use Daycry\Settings\Settings;


/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Returns the Settings manager class.
     *
     * @param bool $getShared
     *
     * @return Settings
     */
    public static function settings( BaseConfig $config = null, Bool $getShared = true ) : Settings
    {
        if( $getShared )
        {
            return static::getSharedInstance( 'settings', $config );
        }

        $config = $config ?? config( 'Settings' );

        return new Settings( $config );
    }
}