<?php

class Register extends Action
{

    /**
     * Handles the POST action of a login action.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        echo $this->checkRegister($cleaned['email'], $cleaned['username'], $cleaned['password1'], $cleaned['password2'], $cleaned['bot']);
    }

    /**
     * Checks the information given and registers a new account.
     * @param email The email to register.
     * @param username The username to register.
     * @param password The password to register.
     * @param passwordVerify The password verifier.
     * @param bot The bot answer.
     * @return SUCCESS or error message.
     */
    public function checkRegister($email, $username, $password, $password2, $bot)
    {
        $username = strtolower(str_replace(" ", "_", $username));//this is how runescape formats.
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("REGISTRATIONS")) {
            return "Sorry, you have had too many registration attempts. Try again later.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "You have entered an invalid email address.";
        }
        if (!$this->checkEmail($email)) {
            return "Sorry, that e-mail address has already been taken.";
        }
        if (strlen($username) > 12 || strlen($username) < 1) {
            return "A username must be between 1 and 12 characters long.";
        }
        if (preg_match('/[^a-z_\-0-9 ]/i', $username)) {
            return "The username you have entered is invalid.";
        }
        if (User::getByName($username) != false) {
            return "Sorry, that username is already in use.";
        }
        if (strlen($password) < 5 || strlen($password) > 20) {
            return "Passwords must be between 5 and 20 characters long.";
        }
        if (strcasecmp($password, $password2) !== 0) {
            return "Sorry, the passwords you have entered do not match.";
        }
        if ($bot != "10") {
            return "Sorry, the answer you put does not match the question.";
        }
        $salt = Utils::random_salt();
        $crypt = crypt($password, $salt);
        Registry::get("database")->query("INSERT INTO " . GLOBAL_DB . ".members (username, rights, email, password, salt, lastActive) VALUES('" . $username . "', 0, '" . $email . "', '" . $crypt . "', '" . $salt . "', now());") or die(mysql_error());
        $uid = Registry::get("database")->query("SELECT * FROM " . GLOBAL_DB . ".members WHERE username='" . $username . "'");
        $uid = $uid->fetch()['UID'];
        Registry::get("sys")->log(ucwords($username) . " registered.", 4);
        $_SESSION['uid'] = $uid;
        session_write_close();
        $instanced = User::getByName($username);
        if ($instanced->exists()) {
            Registry::get("sys")->sendEmail($instanced, "Hi " . $instanced->getFormatUsername() . ",<br>Please visit the following link to update your email address! </br> " . VALIDATE_LINK . Registry::get("sys")->getValidationManager()->newValidation($instanced->getUsername(), 1, $email) . "<br>Kind Regards,<br>" . SITE_NAME . " Account Support", $email);
        }
        return "SUCCESS";
    }

    /**
     * Checks if an email is already in use.
     * @param email The email to check.
     */
    private function checkEmail($email)
    {
        $statement = Registry::get("database")->prepare("SELECT * FROM members WHERE email=? LIMIT 1");
        $statement->bindParam(1, $email);
        if (!$statement->execute()) {
            return false;
        }
        return $statement->rowCount() == 0;
    }

}

?>