<?php
if (!defined("_VALID_PHP")) {
    die ('Direct access to this location is not allowed.');
}
require_once("Template.php");

/**
 * Manages the multitude of templates.
 * @author Adam Rodrigues
 *
 */
class TemplateManager
{

    /**
     * An array of HTML templates.
     */
    private static $TEMPLATES = array();

    /**
     * Initializes the templates into an array.
     */
    static function init()
    {
        foreach (glob($_SERVER['DOCUMENT_ROOT'] . "/lib/style/templates/*", GLOB_ONLYDIR) as $folder) {
            foreach (glob($folder . "/*.html") as $file) {
                self::$TEMPLATES[pathinfo($file, PATHINFO_FILENAME)] = file_get_contents($file);
            }
        }
    }

    /**
     * Loads a Template from the $TEMPLATES array.
     * @param $templateName The template name.
     * @return The template object.
     */
    public static function load($templateName)
    {
        if (array_key_exists($templateName, self::$TEMPLATES)) {
            return new Template(self::$TEMPLATES[$templateName]);
        }
        return false;
    }

    /**
     * Displays an advertisment.
     */
    public static function displayAd($width, $height)
    {
        $template = self::load("Advertisment");
        $template->insert("width", $width);
        $template->insert("height", $height);
        $template->display();
    }

}

TemplateManager::init();
?>