<?php

namespace Daycry\Settings\Handlers;

use CodeIgniter\I18n\Time;

/**
 * Provides database storage for Settings.
 */
class DatabaseHandler extends BaseHandler
{
    /**
     * Stores our cached settings retrieved
     * from the database on the first get() call
     * to reduce the number of database calls
     * at the expense of a little bit of memory.
     *
     * @var array
     */
    private $settings = [];

    /**
     * Have the settings been read and cached
     * from the database yet?
     *
     * @var boolean
     */
    private $hydrated = false;

    /**
     * The settings table
     *
     * @var string
     */
    private $table;

    /**
     * The settings database connection
     *
     * @var string
     */
    private $group;

    /**
     * Attempt to retrieve a value from the database.
     * To boost performance, all of the values are
     * read and stored in $this->settings the first
     * time, and then used from there the rest of the request.
     *
     * @param string $class
     * @param string $property
     *
     * @return mixed|null
     */
    public function get( String $class, String $property )
    {
        $this->hydrate();

        if( !isset( $this->settings[ $class ] ) || !isset( $this->settings[ $class ][ $property ] ) )
        {
            return null;
        }

        return $this->parseValue( ...$this->settings[ $class ][ $property ] );
    }

    /**
     * Stores values into the database for later retrieval.
     *
     * @param string $class
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed|void
     */
    public function set( String $class, String $property, $value = null )
    {
        $this->hydrate();

        $time  = Time::now()->format('Y-m-d H:i:s');
        $type  = gettype( $value );

        $value = $this->prepareValue( $value );

        // If we found it in our cache, then we need to update
        if( isset( $this->settings[ $class ][ $property ] ) )
        {
            $result = db_connect( $this->group )->table( $this->table )
                ->where( 'class', $class )
                ->where( 'key', $property )
                ->update(
                    [
                        'value'      => $value,
                        'type'       => $type,
                        'updated_at' => $time,
                    ]
                );
        } else {
            $result = db_connect( $this->group )->table( $this->table )
               ->insert(
                   [
                        'class'      => $class,
                        'key'        => $property,
                        'value'      => $value,
                        'type'       => $type,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]
                );
        }

        // Update our cache
        if( $result === true )
        {
            if( !array_key_exists( $class, $this->settings ) )
            {
                $this->settings[$class] = [];
            }

            $this->settings[ $class ][ $property ] = [
                $value,
                $type,
            ];
        }

        return $result;
    }

    /**
     * Deletes the record from persistent storage, if found,
     * and from the local cache.
     *
     * @param string $class
     * @param string $property
     */
    public function forget( String $class, String $property )
    {
        $this->hydrate();

        // Delete from persistent storage
        $result = db_connect( $this->group )->table( $this->table )
            ->where( 'class', $class )
            ->where( 'key', $property )
            ->delete();

        if( !$result )
        {
            return $result;
        }

        // Delete from local storage
        unset( $this->settings [$class ][ $property ] );

        return $result;
    }

    /**
     * Ensures we've pulled all of the values from the database.
     */
    private function hydrate()
    {
        if( $this->hydrated )
        {
            return;
        }

        $this->table = config( 'Settings' )->database[ 'table' ] ?? 'settings';
        $this->group = config( 'Settings' )->database[ 'group' ] ?? 'default';

        $rawValues = db_connect( $this->group )->table( $this->table )->get();

        if( is_bool( $rawValues ) )
        {
            throw new \RuntimeException( db_connect( $this->group )->error()[ 'message' ] ?? 'Error reading from database.' );
        }

        $rawValues = $rawValues->getResultObject();

        foreach( $rawValues as $row )
        {
            if( !array_key_exists( $row->class, $this->settings ) )
            {
                $this->settings[ $row->class ] = [];
            }

            $this->settings[ $row->class ][ $row->key ] = [
                $row->value,
                $row->type,
            ];
        }

        $this->hydrated = true;
    }
}