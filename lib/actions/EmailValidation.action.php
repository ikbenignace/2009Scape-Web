<?php

/**
 * Validates an email address for a user.
 * @author Adam Rodrigues
 *
 */
class EmailValidation extends Action
{

    /**
     * Handles the request to send an email validation.
     * @param cleaned The params.
     */
    public function handle($cleaned)
    {
        $sec = Registry::get("sys")->getSecurityManager();
        if (!$sec->securityCheck("EMAIL")) {
            echo "You have requested too many email actions in a short amount of time.";
            exit;
        }
        $sys = Registry::get("sys");
        $user = Registry::get("user");
        $emailUpdate = isset($cleaned['code']) && !empty($cleaned['code']);
        $this->checkLogin();
        $validation;
        if ($emailUpdate) {
            $validation = $sys->getValidationManager()->getValidation($cleaned['code']);
            if (!$validation) {
                echo "The validation code may be invalid or has expired.";
                return;
            }
            if (!$validation->getUsername() == $user->getUsername()) {
                echo "Error contact a system administrator.";
                return;
            }
        }
        $email = $cleaned['email'];
        $db = Registry::get("database");
        if ($user->isEmailActivated() && $email == $user->getEmail()) {
            echo "This email is already activated.";
            return;
        }
        if ($emailUpdate && $email == $user->getEmail()) {
            echo "The email you want to update to is the same as your current email.";
            return;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($emailUpdate) {
                $sys->getValidationManager()->deleteCode($cleaned['code']);
                $sys->sendEmail($user, "Hi " . $user->getFormatUsername() . ",<br>Please visit the following link to update your email address! </br> " . VALIDATE_LINK . Registry::get("sys")->getValidationManager()->newValidation($user->getUsername(), 1, $email) . "<br> Kind Regards,<br>" . SITE_NAME . " Account Support", $email);
                echo "Success! Verification link sent to " . $email;
                return;
            }
            $statement = $db->prepare("SELECT * FROM members WHERE email=?");
            if ($statement->execute(array($email))) {
                if ($statement->rowCount() == 0 || $user->getEmail() == $email) {
                    $user->setData("email", $email);
                    if ($user->write()) {
                        $sys->sendEmail($user, "Hi " . $user->getFormatUsername() . ",<br>Please visit the following link to activate your " . SITE_NAME . " account: </br> " . VALIDATE_LINK . Registry::get("sys")->getValidationManager()->newValidation($user->getUsername(), 1) . "<br>Kind Regards,<br>" . SITE_NAME . " Account Support");
                        echo "Success! Verification link sent to " . $email;
                    }
                } else {
                    echo "That email already in use.";
                }
            }
        } else {
            echo "That email is invalid.";
        }
    }

}

?>