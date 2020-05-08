<?php

/**
 * Handles the email preference account tab.
 * @author Adam Rodrigues
 *
 */
class EmailPreference extends Module
{

    /**
     * Loads the email preference.
     */
    public function load()
    {
        $this->template = TemplateManager::load("EmailPreference");
        $this->template->insert("email", $this->user->getEmail());
        $this->display();
    }

    /**
     * Handles a sent action.
     * @param action The action.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        switch ($action) {
            case "update":
                $sec = Registry::get("sys")->getSecurityManager();
                if (!$sec->securityCheck("EMAIL")) {
                    echo "You have requested too many email actions in a short amount of time.";
                    exit;
                }
                $sys = Registry::get("sys");
                $user = Registry::get("user");
                $sys->sendEmail($this->user, "Hi " . $user->getFormatUsername() . ",<br>We have received a request to change the registered email address for your " . SITE_NAME . " account.<br>To start this change, please follow the link below or copy and paste it into your browser:<br> " . VALIDATE_LINK . Registry::get("sys")->getValidationManager()->newValidation($user->getUsername(), 2) . "<br>Kind regards,<br> " . SITE_NAME . " Account Support");
                echo "SUCCESS";
                break;
        }
    }

    /**
     * Handles the validation request.
     * @param code The validation code.
     * @param validation The validation object.
     */
    public function handleValidation($code, $validation)
    {
        $db = Registry::get("database");
        if ($validation->getValue() != "") {
            $statement = $db->prepare("SELECT * FROM members WHERE email=?");
            if ($statement->execute(array($validation->getValue())) && $statement->rowCount() != 0) {
                Registry::get("sys")->getValidationManager()->deleteCode($code);
                return "An error occured, the email you requested is already in use by someone else.";
            }
            $statement = $db->prepare("UPDATE members SET email_activated=1, email='" . $validation->getValue() . "' WHERE username=?");
            if ($statement->execute(array($validation->username))) {
                Registry::get("sys")->getValidationManager()->deleteCode($code);
                return "You've successfully changed your email.<br>";
            }
            return "An error occured, please contact a system administrator.";
        }
        $statement = $db->prepare("UPDATE members SET email_activated=1 WHERE username=?");
        if ($statement->execute(array($validation->username))) {
            Registry::get("sys")->getValidationManager()->deleteCode($code);
            return "You've successfully registered your account.<br>";
        }
        return "An error occured, please contact a system administrator.";
    }

}

?>