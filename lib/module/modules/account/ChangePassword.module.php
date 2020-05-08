<?php

/**
 * The changing password module.
 * @author Adam Rodrigues
 *
 */
class ChangePassword extends Module
{

    /**
     * Loads the change password module.
     */
    public function load()
    {
        $this->template = TemplateManager::load("ChangePassword");
        $this->display();
    }

}

?>