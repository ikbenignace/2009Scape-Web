<?php

/**
 * Handles the password recovery process.
 * @author Adam Rodrigues
 *
 */
class RecoverPassword extends Module
{

    /**
     * Handles the validation request.
     * @param code The validation code.
     * @param validation The validation object.
     */
    public function handleValidation($code, $validation)
    {
        $db = Registry::get("database");
        $this->template = TemplateManager::load("RecoverPassword");
        $this->template->insert("code", $code);
        $this->display();
        return "";
    }

}

?>