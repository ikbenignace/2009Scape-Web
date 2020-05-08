<?php

/**
 * Handles the administrator preferences.
 * @author Adam Rodrigues
 *
 */
class AdministratorPreferences extends Module
{

    /**
     * The list of worlds.
     */
    private $worlds = array();

    /**
     * The array of server configurations.
     */
    private $configs = array();

    /**
     * Handles a post action.
     * @param action The action name.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        if ($this->user->getRights() < 2) {
            echo "Permission denied.";
            return;
        }
        $this->configure();
        switch ($action) {
            case "save":
                if (!isset($cleaned['configs'])) {
                    echo "Error, please contact a system administrator.";
                    return;
                }
                $this->saveConfigs(rtrim($cleaned['configs'], "~"));
                break;
            case "updateLog":
                if (!isset($cleaned['update']) || empty($cleaned['update']) || strlen($cleaned['update']) == 0) {
                    echo "Sorry, you haven't entered an update.";
                    return;
                }
                if ($this->user->getRights() != 2) {
                    echo "Sorry, you need special privilleges to use this feature.";
                    return;
                }
                $statement = $this->database->query("INSERT INTO dev_log (username,content,date) VALUES('" . $this->user->getUsername() . "', '" . $cleaned['update'] . "', NOW())");
                echo "SUCCESS";
                break;
        }
    }

    /**
     * Loads the administrator preferences.
     */
    public function load()
    {
        if ($this->user->getRights() < 2) {
            echo "Permission denied.";
            return;
        }
        $this->configure();
        $this->template = TemplateManager::load("AdministratorPreferences");
        $this->template->insert("worlds", $this->getWorlds());
        $this->template->insert("configs", $this->getConfigs());
        $this->display();
    }

    /**
     * Configures the preferences for this tab.
     */
    private function configure()
    {
        $statement = $this->database->query("SELECT * FROM worlds");
        while ($worldData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->worlds[$worldData['world']] = new World($worldData);
        }
        $statement = $this->database->query("SELECT * FROM " . SERVER_DB . ".configs");
        while ($configData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->configs[$configData['key_']] = new Config($configData['key_'], $configData['value'], $configData['dataType']);
        }
    }

    /**
     * Saves the configs.
     * @param configArray The string array.
     */
    private function saveConfigs($configArray)
    {
        $split = explode('~', $configArray);
        foreach ($split as $s) {
            $c = explode('`', str_replace("}", "", str_replace("{", "", $s)));
            $key = $c[0];
            $value = $c[1];
            $config = $this->getConfig($key);
            if (!$config || $config->getValue() == $value) {
                continue;
            }
            $config->setValue($value);
            $config->write();
        }
        echo "Successfully";
    }

    /**
     * Gets the html content for the world.
     */
    public function getWorlds()
    {
        $html = "";
        foreach ($this->worlds as $world) {
            $html .= $world->getTableContent();
        }
        return $html;
    }

    /**
     * Gets the html content for the configs.
     */
    public function getConfigs()
    {
        $html = "";
        foreach ($this->configs as $config) {
            $html .= $config->getTableContent();
        }
        return $html;
    }

    /**
     * Gets the config.
     * @param key The config key.
     */
    public function getConfig($key)
    {
        if (!array_key_exists($key, $this->configs)) {
            return false;
        }
        return $this->configs[$key];
    }
}

/**
 * Represents a world hosted by Arios.
 */
class World
{

    /**
     * The world data.
     */
    private $data;

    /**
     * Constructs a world.
     * @param data The world data.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the table content.
     */
    public function getTableContent()
    {
        return "<tr><td>" . $this->getWorldId() . "</td><td>" . $this->getPlayers() . "</td><td>" . $this->getIp() . "</td><td>" . Utils::time_elapsed_string(strtotime($this->getLastResponse())) . " ago</tr>";
    }

    /**
     * Gets the world id.
     */
    public function getWorldId()
    {
        return $this->data['world'];
    }

    /**
     * Gets the ip.
     */
    public function getIp()
    {
        return $this->data['ip'];
    }

    /**
     * Get the players amount.
     */
    public function getPlayers()
    {
        return $this->data['players'];
    }

    /**
     * Gets the last response from the world.
     */
    public function getLastResponse()
    {
        return $this->data['lastResponse'];
    }
}

/**
 * Represents a configuration.
 * @author Adam Rodrigues
 *
 */
class Config
{

    /**
     * The key value.
     */
    private $key;

    /**
     * The config value.
     */
    private $value;

    /**
     * The data type.
     */
    private $dataType;

    /**
     * Constructs the configuration.
     * @param key The config name.
     * @param value The config value.
     * @param dataType The data type.
     */
    public function __construct($key, $value, $dataType)
    {
        $this->key = $key;
        $this->value = $value;
        $this->dataType = $dataType;
    }

    /**
     * Writes the config to the database.
     */
    public function write()
    {
        $database = Registry::get("database");
        $v = "value='" . $this->value . "'";
        $statement = Registry::get("database")->query("UPDATE " . SERVER_DB . ".configs SET " . $v . " WHERE key_='" . $this->key . "'");
    }

    /**
     * Sets the value.
     * @param value The value.
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the html table content.
     */
    public function getTableContent()
    {
        return "<tr data-key='" . $this->getKey() . "'><td>" . $this->key . "</td><td contenteditable='true'>" . $this->value . "</td><td>" . $this->dataType . "</td></tr>";
    }

    /**
     * Gets the data type.
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Gets the value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Gets the key.
     */
    public function getKey()
    {
        return $this->key;
    }

}

?>