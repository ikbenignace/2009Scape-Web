<?php

/**
 * Handles the password recovery action.
 * @author Adam Rodrigues
 *
 */
class PasswordRecovery extends Action
{

    /**
     * Handles the request to recover a password.
     * @param cleaned The params.
     */
    public function handle($cleaned)
    {
        if (sizeof($cleaned) == 3) {
            echo $this->changePassword($cleaned['code'], $cleaned['password1'], $cleaned['password2']);
            return;
        }
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("CHANGE_PASSWORD")) {
            echo "You have sent too many password recovery requests in a short amount of time.";
            return;
        }
        if (!isset($cleaned['username']) || empty($cleaned['username'])) {
            echo "Sorry, no account was found with that username.";
            return;
        }
        $username = $cleaned['username'];
        $user = User::getByName($username);
        if (!$user) {
            echo "Sorry, no account was found with that username.";
            return;
        }
        if (!$user->isEmailActivated()) {
            echo "Sorry, that account doesn't have a validated email address.";
            return;
        }
        $user->sendEmail("Hi " . $user->getFormatUsername() . "<br><br> We have received a request to reset the password for your " . SITE_NAME . " account.<br><br>To reset your password, click the link below or copy and paste it into your browser: <br><br> " . VALIDATE_LINK . Registry::get("sys")->getValidationManager()->newValidation($user->getUsername(), 4) . "<br><br>If you did not submit this password reset request you can safely ignore this email - your account will remain secure.<br>Kind regards,<br>" . SITE_NAME . " Account Support");
        $email = preg_replace('/(?<=.).(?=.*@)/u', '*', $user->getEmail());
        echo "SUCCESS " . "We have sent an email to <strong>" . $email . ".</strong><br> Please click on the link in the email to set a new password for your account.<br>Remember to check your spam/junk folders if you can't find the email.";
    }

    /**
     * Changes a users password.
     * @param code The validation code.
     * @param password1 The first password.
     * @param passwordVerify The verification password.
     */
    private function changePassword($code, $password1, $passwordVerify)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("CHANGE_PASSWORD")) {
            return "Too many invalid password attempts.";
        }
        if (empty($password1) || empty($passwordVerify)) {
            return "Please fill out all fields.";
        }
        $validation = Registry::get("sys")->getValidationManager()->getValidation($code);
        if (!$validation) {
            return "This validation code is either invalid or has expired.";
        }
        $checkPass = Utils::checkPasswords($password1, $passwordVerify);
        if ($checkPass != "") {
            return $checkPass;
        }
        $user = User::getByName($validation->getUsername());
        if (!$user) {
            return "Error occured, please contact a system administrator.";
        }
        $user->setData("salt", Utils::random_salt());
        $user->setData("password", crypt($password1, $user->getSalt()));
        $user->write();
        Registry::get("sys")->getValidationManager()->deleteCode($code);
        return "SUCCESS";

    }

}

?>