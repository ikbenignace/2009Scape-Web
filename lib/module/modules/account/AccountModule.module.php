<?php

/**
 * The module to handle the account page.
 * @author Adam Rodrigues
 *
 */
class AccountModule extends Module
{

    /**
     * The tabs to load in the account module.
     */
    private static $TABS = array(
        array("Player Information", "PlayerInformation"),
        array("Change Password", "ChangePassword"),
        array("Email Preferences", "EmailPreference"),
        array("Forum Preferences", "ForumPreference", true),
        array("Message Centre", "MessageCentre", true),
        array("Donator Preferences", "DonatorPreferences", "donator"),
        array("Moderator Preferences", "ModeratorPreferences", 1, true),
        array("Administrator Preferences", "AdministratorPreferences", 2, true)
    );

    /**
     * Loads the account page.
     */
    public function load()
    {
        $isEmailUpdate = isset($_GET['req']) && isset($_GET['code']) && $_GET['req'] == "update-email";
        if (!$this->user->isEmailActivated() || $isEmailUpdate) {
            $this->template = TemplateManager::load("EmailActivation");
            if ($isEmailUpdate) {
                $validation = Registry::get("sys")->getValidationManager()->getValidation($_GET['code']);
                if ($validation && $validation->getUsername() == $this->user->getUsername()) {
                    $this->template->insert("code", $_GET['code']);
                    $this->template->insert("content", "<strong>Warning:</strong> You are requesting to change your email address! Please use the form below to update your email address if <strong>YOU</strong> made this request.");
                } else {
                    header("Location: /account");
                    return;
                }
            } else {
                $this->template->insert("content", "<strong>Warning:</strong> You have not confirmed your e-mail address yet! Please check the e-mail you registered with for a validation link. If you would like to resend/validate another e-mail, please use the form below.");
            }
            $this->template->insert("email", $this->user->getEmail());
            $this->display();
            return;
        }
        echo "<div class=\"accordion\">";
        foreach (self::$TABS as $tab) {
            if (sizeof($tab) == 3 && gettype($tab[2]) == "string" && $this->user->getDonatorType() != 1) {
                continue;
            }
            if (sizeof($tab) == 4 && gettype($tab[2]) == "integer" && $this->user->getRights() < $tab[2]) {
                continue;
            }
            $fullscreen = false;
            if (sizeof($tab) >= 3) {
                if (gettype($tab[2]) == "integer" && sizeof($tab) == 4) {
                    if ($tab[3]) {
                        $fullscreen = true;
                    }
                } else if (gettype($tab[2]) == "boolean") {
                    if ($tab[2]) {
                        $fullscreen = true;
                    }
                }
            }
            $fullscreenText = $fullscreen ? " data-fullscreen='enable'" : "";
            echo "<h3" . $fullscreenText . ">" . $tab[0] . "</h3><div>";
            $this->user->getModule($tab[1])->load();
            echo "</div>";
        }
        echo "</div>";
    }

}

?>