<?php

/**
 * Handles the moderator preferences.
 * @author Adam Rodrigues
 *
 */
class ModeratorPreferences extends Module
{

    public function load()
    {
        if ($this->user->getRights() < 2) {
            echo "Permission denied.";
            return;
        }
        $this->template = TemplateManager::load("ModeratorPreferences");
        $this->display();
    }


}

?>