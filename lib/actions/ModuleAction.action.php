<?php
define("FORUMS", true);

/**
 * Sends post data to a module.
 * @author Adam Rodrigues
 *
 */
class ModuleAction extends Action
{

    /**
     * Handles the sending of post data to a module.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        Registry::get("sys")->configureForums();
        if (!isset($_GET['name']) || !isset($_GET['modAction'])) {
            exit;
        }
        $this->checkLogin();
        $user = Registry::get("user");
        $user->getModule($_GET['name'])->handleAction($_GET['modAction'], $cleaned);
    }


}

?>