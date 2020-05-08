<?php
define("_VALID_PHP", true);
define("NO_STRUCT", true);
include_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");

/**
 * The information of an action.
 * @author Adam Rodrigues
 *
 */
class ActionInfo
{

    /**
     * The command name.
     */
    protected $action;

    /**
     * The location of the action.
     */
    protected $location;

    /**
     * Constructs the information of an action.
     * @param action The action name.
     * @param location The location.
     */
    public function __construct($action, $location)
    {
        $this->action = $action;
        $this->location = $location;
    }

    /**
     * Gets the action.
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Gets the location of the action.
     */
    public function getLocation()
    {
        return $this->location;
    }

}

/**
 * An action command.
 * @author Adam Rodrigues
 *
 */
class Action
{
    public function handle($cleaned)
    {
    }

    public function checkLogin()
    {
        $user = Registry::get("user");
        if (!$user->isLoggedIn()) {
            echo "Error - Not logged in.";
            exit;
        }
    }
}

/**
 * Handles action commands.
 * @author Adam Rodrigues
 *
 */
class ActionHandler
{

    /**
     * An array of action objects identified by a key.
     */
    static $ACTIONS = array();


    /**
     * Initializes the action handler.
     */
    static function init()
    {
        foreach (glob($_SERVER['DOCUMENT_ROOT'] . "/lib/actions", GLOB_ONLYDIR) as $folder) {
            foreach (glob($folder . "/*.action.php") as $file) {
                $fileName = str_replace(".action", "", pathinfo($file, PATHINFO_FILENAME));
                self::$ACTIONS[$fileName] = new ActionInfo($fileName, $file);
            }
        }
    }

    /**
     * Handles an action command.
     * @param command The name of the action.
     */
    static function handle($command)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("ACTION_HANDLER")) {
            echo "You have sent too many requests in a short amount of time.";
            return;
        }
        $actionInfo = self::get($command);
        if (!$actionInfo) {
            echo "No command found " . $command;
            return;
        }
        $db = Registry::get("database");
        require_once($actionInfo->getLocation());
        $className = $actionInfo->getAction();
        $action = new $className();
        $cleaned = array();
        foreach (array_keys($_POST) as $key) {
            $cleaned[$key] = $db->escape($_POST[$key]);
        }
        $action->handle($cleaned);
    }

    /**
     * Gets an action from the repository.
     * @param action The action name.
     * @return The action object.
     */
    public static function get($action)
    {
        return self::exists($action) ? self::$ACTIONS[$action] : false;
    }

    /**
     * Checks if the action exists in the repository.
     * @param action The action name.
     */
    public static function exists($action)
    {
        return array_key_exists($action, self::$ACTIONS);
    }

}

ActionHandler::init();
if (isset($_GET['action'])) {
    ActionHandler::handle($_GET['action']);
}
?>