<?php

/**
 * A repository holding the modules.
 * @author Adam Rodrigues
 *
 */
class ModuleRepository
{

    /**
     * An array of the availble modules.\
     */
    public static $MODULES = array();

    /**
     * Builds all available modules into an active array
     */
    public static function buildModules()
    {
        foreach (glob($_SERVER['DOCUMENT_ROOT'] . "/lib/module/modules/*", GLOB_ONLYDIR) as $folder) {
            foreach (glob($folder . "/*.module.php") as $file) {
                $fileName = str_replace(".module", "", pathinfo($file, PATHINFO_FILENAME));
                self::$MODULES[$fileName] = new ModuleInfo($fileName, $file);
            }
        }
    }

    /**
     * Returns {ModuleInfo} for the given module name
     * @param string $moduleName
     * @return {ModuleInfo}
     */
    public static function get($moduleName)
    {
        return self::exists($moduleName) ? self::$MODULES[$moduleName] : false;
    }

    /**
     * Returns if a module exists in the repository
     * @param string $moduleName
     */
    public static function exists($moduleName)
    {
        return array_key_exists($moduleName, self::$MODULES);
    }

}

ModuleRepository::buildModules();

/**
 * A modules information.
 * @author Adam Rodrigues
 *
 */
class ModuleInfo
{

    /**
     * The name of the module.
     */
    private $name;

    /**
     * The location of the module.
     */
    private $location;

    /**
     * Constructs the modules information.
     * @param name The name of the module.
     * @param location The location of the module.
     */
    public function __construct($name, $location)
    {
        $this->name = $name;
        $this->location = $location;
    }

    /**
     * Gets the name of the module.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the location.
     */
    public function getLocation()
    {
        return $this->location;
    }

}

?>
