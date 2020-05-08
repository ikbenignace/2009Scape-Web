<?php

/**
 * Handles the login action of a user.
 * @author Adam Rodrigues
 *
 */
class Login extends Action
{

    /**
     * Handles the POST action of a login action.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        if (Registry::get("sys")->getSecurityManager()->securityCheck("LOGIN_ATTEMPT")) {
            if (isset($cleaned['username']) && isset($cleaned['password'])) {
                $username = $cleaned['username'];
                $user = new User($username);
                $user->create($username);
                if ($user->exists()) {
                    if (DEV_MODE && $user->getRights() == 0) {
                        echo "You are not able to login at this time.";
                        return;
                    }
                    if ($user->verifyPassword($cleaned['password'])) {
                        $_SESSION['uid'] = $user->getUid();
                        $user->write();
                        echo "SUCCESS";
                        return;
                    }
                    echo "Incorrect Username or Password";
                    return;
                }
                echo "Username does not exist.";
            }
            return;
        }
        echo "You have had too many failed login attempts in a short while.";
    }

}

?>