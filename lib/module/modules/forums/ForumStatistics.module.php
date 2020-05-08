<?php
define("TIME_RANGE", "30 hours");

/**
 * A forum statistics module
 * @author Adam Rodrigues
 *
 */
class ForumStatistics extends Module
{

    /**
     * Loads user statistics.
     */
    public function loadUserStatistics()
    {
        $this->template = TemplateManager::load("ForumStatistics");
        $from_date = date('Y-m-d H:i:s', strtotime("-" . TIME_RANGE));
        $to_date = date('Y-m-d H:i:s');
        $users = $this->database->query("SELECT * FROM members WHERE lastActive BETWEEN '" . $from_date . "' AND '" . $to_date . "'");
        $numOnline = 0;
        $usersOnline = "";
        while ($userData = $users->fetch(PDO::FETCH_ASSOC)) {
            $user = User::getUser($userData['UID']);
            if (!$user) {
                continue;
            }
            $usersOnline .= $user->getModule("UserTools")->getFormatUsername(true) . ", ";
            $numOnline++;
        }
        $usersOnline = rtrim($usersOnline, ', ');
        $this->template->insert("numOnline", $numOnline);
        $this->template->insert("usersOnline", $usersOnline);
        $this->display();
    }

}

?>