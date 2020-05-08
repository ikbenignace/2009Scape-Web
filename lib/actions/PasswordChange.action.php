<?php

/**
 * Handles the changing of a users password.
 * @author Adam Rodrigues
 *
 */
class PasswordChange extends Action
{

    /**
     * Handles the action of changing your password.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        $this->checkLogin();
        echo $this->changePassword($cleaned['currentpw'], $cleaned['password1'], $cleaned['password2']);

    }

    /**
     * Changes a users password.
     * @param current The current password.
     * @param password1 The first password.
     * @param passwordVerify The verification password.
     */
    private function changePassword($current, $password1, $passwordVerify)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("CHANGE_PASSWORD")) {
            return "Too many invalid password attempts.";
        }
        if (empty($current) || empty($password1) || empty($passwordVerify)) {
            return "Please fill out all fields.";
        }
        $checkPass = Utils::checkPasswords($password1, $passwordVerify);
        if ($checkPass != "") {
            return $checkPass;
        }
        $user = Registry::get("user");
        if (!$user->verifyPassword($current)) {
            return "Your current password is incorrect.";
        }
        $user->setData("salt", Utils::random_salt());
        $user->setData("password", crypt($password1, $user->getSalt()));
        $user->write();
        return "SUCCESS";

    }


}

?>