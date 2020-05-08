<?php

/**
 * A class to hold the forum settings.
 * @author Adam Rodrigues
 *
 */
class ForumSettings
{

    /**
     * The array of forum settings.
     */
    private $settings = array();

    /**
     * The forum manager instance.
     */
    private $forumManager;

    /**
     * The database instance.
     */
    private $db;

    /**
     * Constructs the forum settings.
     * @param forumManager the forum manager.
     */
    public function __construct($forumManager)
    {
        $this->forumManager = $forumManager;
        $this->db = $forumManager->getDatabase();
    }

    /**
     * Loads the forum settings.
     */
    public function load()
    {
        $statement = $this->db->query("SELECT * FROM " . FORUM_DB . ".forum_settings");
        while ($settingData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->settings[$settingData['key']] = new ForumSetting($settingData['key'], $settingData['value']);
        }
    }

    /**
     * Loads the sidebar modules.
     */
    public function loadSidebarModules()
    {
        $setting = $this->getSetting("sidebar_modules");
        if (!$setting) {
            echo "No sidebar modules found.";
            return;
        }
        $user = Registry::get("user");
        $modules = explode(',', $setting->getValue());
        foreach ($modules as $module) {
            $user->getModule($module)->load();
        }
    }

    /**
     * Gets a forum setting.
     * @param key The forum setting.
     */
    public function getSetting($key)
    {
        if (!$this->exists($key)) {
            return false;
        }
        return $this->settings[$key];
    }

    /**
     * Checks if a setting exists.
     * @param key The setting key.
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Gets the database.
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * Gets the forum manager instance.
     */
    public function getForumManager()
    {
        return $this->forumManager;
    }

    /**
     * Gets the array of settings.
     */
    public function getSettings()
    {
        return $this->settings;
    }
}

/**
 * A forum setting.
 * @author Adam Rodrigues
 *
 */
class ForumSetting
{

    /**
     * The key of the setting.
     */
    private $key;

    /**
     * The value of the setting.
     */
    private $value;

    /**
     * Constructs a forum setting.
     * @param key The setting key.
     * @param vaue The setting value.
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Gets the setting key.
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the forum value.
     */
    public function getValue()
    {
        return $this->value;
    }

}

?>