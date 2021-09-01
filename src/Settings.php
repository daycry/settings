<?php

namespace Daycry\Settings;

use CodeIgniter\Config\BaseConfig;

/**
 * Allows developers a single location to store and
 * retrieve settings that were original set in config files
 * in the core application or any third-party module.
 */

class Settings
{
    /**
     * An array of Setting Stores that handle
     * the actual act of getting/setting the values.
     *
     * @var array
     */
    private $handlers = [];

    /**
     * The name of the handler that handles writes.
     *
     * @var string
     */
    private $writeHandler;

    /**
     * Grabs instances of our handlers.
     */
    public function __construct( ?BaseConfig $config = null )
    {
        if( empty( $config ) )
        {
            $config = config( 'Settings' );
        }

        foreach( $config->handlers as $handler )
        {
            $class = $config->{ $handler }[ 'class' ] ?? null;

            if( $class === null ){ continue; }

            $this->handlers[ $handler ] = new $class();

            $writeable = $config->{$handler}[ 'writeable' ] ?? null;

            if( $writeable )
            {
                $this->writeHandler = $handler;
            }
        }
    }

    /**
     * Retrieve a value from either the database
     * or from a config file matching the name
     * file.arg.optionalArg
     *
     * @param string $key
     */
    public function get( String $key )
    {
        [ $class, $property, $config ] = $this->prepareClassAndProperty( $key );

        // Try grabbing the values from any of our handlers
        foreach( $this->handlers as $name => $handler )
        {
            $value = $handler->get( $class, $property );

            if( $value !== null )
            {
                return $value;
            }
        }

        return $config->{ $property } ?? null;
    }

    /**
     * Save a value to the writable handler for later retrieval.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void|null
     */
    public function set( String $key, $value = null )
    {
        [ $class, $property ] = $this->prepareClassAndProperty( $key );

        $handler = $this->getWriteHandler();

        return $handler->set( $class, $property, $value );
    }

    /**
     * Removes a setting from the persistent storage,
     * effectively returning the value to the default value
     * found in the config file, if any.
     *
     * @param string $key
     */
    public function forget(string $key)
    {
        [$class, $property] = $this->prepareClassAndProperty($key);

        $handler = $this->getWriteHandler();

        return $handler->forget( $class, $property );
    }

    /**
     * Returns the handler that is set to store values.
     *
     * @return mixed
     */
    private function getWriteHandler()
    {
        if( empty( $this->writeHandler ) || !isset( $this->handlers[ $this->writeHandler ] ) )
        {
            throw new \RuntimeException( 'Unable to find a Settings handler that can store values.' );
        }

        return $this->handlers[ $this->writeHandler ];
    }

    /**
     * Analyzes the given key and breaks it into the class.field parts.
     *
     * @param string $key
     *
     * @return string[]
     */
    private function parseDotSyntax( String $key ) : Array
    {
        // Parse the field name for class.field
        $parts = explode( '.', $key );

        if( count( $parts ) === 1 )
        {
            throw new \RuntimeException( '$field must contain both the class and field name, i.e. Foo.bar' );
        }

        return $parts;
    }

    /**
     * Given a key in class.property syntax, will split the values
     * and determine the fully qualified class name, if possible.
     *
     * @param string $key
     * @return array
    */
    private function prepareClassAndProperty( String $key ) : Array
    {
        [ $class, $property ] = $this->parseDotSyntax( $key );

        $config = config( $class );

        // Use a fully qualified class name if the
        // config file was found.
        if( $config !== null )
        {
            $class = get_class( $config );
        }

        return [ $class, $property, $config ];
    }
}