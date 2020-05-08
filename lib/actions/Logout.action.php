<?php

/**
 * Handles the logging out action of a user.
 * @author Adam Rodrigues
 *
 */
class Logout extends Action
{

    /**
     * Handles the action of logging out.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        session_unset();
        unset($_SESSION["uid"]);
        session_destroy();
        header("Location:/index.php");
        exit();
    }

}

?>
