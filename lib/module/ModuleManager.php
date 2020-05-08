<?php
require_once("Module.php");
require_once("ModuleRepository.php");

/**
 * Manages the users modules.
 * @author Adam Rodrigues
 *
 */
class ModuleManager
{

    /**
     * The active modules.
     */
    private $activeModules = array();

    /**
     * The user instance.
     */
    private $user;

    /**
     * Constructs a module manager.
     * @param user The user.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Loads a module for the name.
     * @param moduleName the name of the module.
     */
    public function loadModule($moduleName)
    {
        $moduleInfo = ModuleRepository::get($moduleName);
        if ($moduleInfo) {
            if ($this->isModuleActive($moduleInfo->getName())) {
                return $this->activeModules[$moduleInfo->getName()];
            } else {
                require_once($moduleInfo->getLocation());
                $className = $moduleInfo->getName();
                $this->activeModules[$moduleInfo->getName()] = new $className($this->user);
                return $this->activeModules[$moduleInfo->getName()];
            }
        } else {
            die("[ModuleManager] Error - Module '" . $moduleName . "' doesn't exist!");
        }
        return false;
    }

    /**
     * Calls a method by the class name.
     * @param className the class name.
     * @param method The method name.
     * @param arguments The arguments to run.
     */
    public function callMethodByClass($className, $method, $arguments)
    {
        foreach ($this->activeModules as &$module) {
            if (is_a($module, $className)) {
                call_user_func_array(array($module, $method), $arguments);
            }
        }
    }

    /**
     * Checks if a module is active.
     * @param moduleName the name of the module.
     */
    private function isModuleActive($moduleName)
    {
        return array_key_exists($moduleName, $this->activeModules);
    }
}

?>