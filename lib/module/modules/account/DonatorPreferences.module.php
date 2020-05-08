<?php
define("PATH", "/lib/images/icons/");

/**
 * Handles the donator preferences.
 * @author Adam Rodrigues
 *
 */
class DonatorPreferences extends Module
{

    /**
     * The array of icons to choose from.
     */
    private static $ICONS = array("green", "red", "yellow", "blue", "orange", "pink", "purple", "brown");

    /**
     * Loads the donator preferences.
     */
    public function load()
    {
        if ($this->user->getDonatorType() != 1) {
            return;
        }
        $this->template = TemplateManager::load("DonatorPreferences");
        $s = "";
        $i = 0;
        $userIcon = $this->user->getData("icon", 0);
        foreach (self::$ICONS as $icon) {
            if ($i + 1 == $userIcon) {
                $s .= "<li data-icon='" . ($i + 1) . "' class='active'><img src='" . PATH . $icon . ".png?v=24'></li>";
            } else {
                $s .= "<li data-icon='" . ($i + 1) . "'><img src='" . PATH . $icon . ".png?v=24'></li>";
            }
            $i++;
        }
        $this->template->insert("icons", $s);
        $this->display();
    }

    /**
     * Handles a post action.
     * @param action The action name.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        switch ($action) {
            case "change":
                if ($this->user->getDonatorType() != 1) {
                    echo "Sorry, you don't have permission to do that.";
                    return;
                }
                $this->user->setData("icon", $cleaned['icon']);
                $this->user->write();
                echo "SUCCESS";
                break;
        }
    }

}

?>