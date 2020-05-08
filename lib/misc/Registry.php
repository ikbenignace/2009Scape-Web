<?php
if (!defined("_VALID_PHP")) {
    die('Direct access to this location is not allowed.');
}

/**
 * A registry class for system objects.
 * @author Adam Rodrigues
 *
 */
abstract class Registry
{

    /**
     * An array of system objects.
     */
    static $objects = array();

    /**
     * Gets an object from the identifier.
     * @param The name of the object.
     * @return The object.
     */
    public static function get($name)
    {
        return isset(self::$objects[$name]) ? self::$objects[$name] : null;
    }

    /**
     * Sets an object.
     * @param The name of the object.
     * @param The object to set.
     * @return The set object.
     */
    public static function set($name, $object)
    {
        self::$objects[$name] = $object;
    }
}

?>