<?php

/**
 * Handles the content shown on the sidebar.
 * @author Adam Rodrigues
 *
 */
class SidebarModule extends Module
{

    /**
     * Loads the online players.
     */
    public function loadOnlinePlayers()
    {
        $this->template = TemplateManager::load("StyledTable");
        $this->template->insert("title", "All Online Players");
        $players = $this->database->query("SELECT * FROM " . GLOBAL_DB . ".members WHERE online=1 AND lastWorld !=-1");
        $table = "";
        while ($playerData = $players->fetch(PDO::FETCH_ASSOC)) {
            $user = User::getUser($playerData['UID']);
            $table .= "<tr class=\"online\">
			<td class=\"name\"><span class='username' style=''>" . $user->getModule("UserTools")->getFormatUsername(true) . "</span></td>
			<td class=\"world\">World " . $user->getLastWorld() . "</td>
			</tr>";
        }
        if ($table == "") {
            $table = "There are currently no online players.";
        }
        $this->template->insert("icon", "globe");
        $this->template->insert("table", $table);
        $this->display();
    }

    /**
     * Loads the friend list.
     */
    public function loadFriendsList()
    {
        $this->template = TemplateManager::load("StyledTable");
        $this->template->insert("title", "Friends List");
        $friends = $this->user->getContactManager()->getFriends();
        $table = "";
        $online = array();
        $offline = array();
        foreach ($friends as $friend) {
            $owner = User::getByName($friend);
            if (!$owner) {
                continue;
            }
            if (!$owner->isOnline()) {
                $offline[$owner->getUid()] = $owner;
            } else {
                $online[$owner->getUid()] = $owner;
            }
        }
        foreach ($online as $owner) {
            $table .= "<tr class=\"online\">
			<td class=\"name\"><span class='username' style=''>" . $owner->getModule("UserTools")->getFormatUsername(true) . "</span></td>
			<td class=\"world\"> World " . $owner->getLastWorld() . "</td></tr>";
        }
        foreach ($offline as $owner) {
            $table .= "<tr class=\"offline\">
			<td class=\"name\"><span class='username' style='color: #9fabbf; '>" . $owner->getModule("UserTools")->getFormatUsername(true) . "</span></td>
			<td class=\"world\">Offline</td></tr>";
        }
        if ($table == "") {
            $table = "You don't have any friends in your contact list. Add friends in-game for them to appear here.";
        }
        $this->template->insert("icon", "user");
        $this->template->insert("table", $table);
        $this->display();
    }

    public function loadDevLog()
    {
        $this->template = TemplateManager::load("StyledTable");
        $this->template->insert("title", "Development Log");
        $this->template->insert("icon", "comment-o");
        $statement = $this->database->query("SELECT * FROM " . GLOBAL_DB . ".dev_log ORDER BY date DESC LIMIT 20");
        $table = "";
        while ($logData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $table .= "<tr class=\"online\">
			<td class=\"name\"><span class='username' style=''>" . $logData['content'] . "
			<td class=\"world\"><span class='online' style='color: #9fabbf; '><i>" . Utils::getFormatUsername($logData['username']) . "</i></span></td></tr>";
        }
        if ($table == "") {
            $table = "There have been no recent updates.";
        }
        $this->template->insert("icon", "globe");
        $this->template->insert("table", $table);
        $this->display();
    }
}

?>