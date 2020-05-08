<?php
define("FRIENDS_PER_PAGE", 5);
define("VISITOR_MESSAGES", 5);

/**
 * The module to handle profile viewing.
 * @author Adam Rodrigues
 *
 */
class ProfileViewer extends Module
{

    /**
     * If this profile is the users profile.
     */
    private $myProfile;

    /**
     * Handles the post action.
     * @param name The name of the action.
     * @param cleaned The parameters.
     */
    public function handleAction($name, $cleaned)
    {
        if (!isset($cleaned['name'])) {
            echo "Sorry, please contact a system administrator. #543";
        }
        $profile = User::getByName($cleaned['name']);
        if (!$profile) {
            echo "Sorry, please contact a system administrator. #934";
        }
        $this->myProfile = $this->user->getUsername() == $cleaned['name'];
        switch ($name) {
            case "postMessage":
                if (!isset($cleaned['html'])) {
                    echo "You need to enter a message to reply.";
                }
                $this->postMessage($profile, $cleaned['html']);
                break;
            case "viewTab":
                if (!isset($cleaned['tabId'])) {
                    echo "Sorry, please contact a system administrator. #343";
                    return;
                }
                echo "SUCCESS " . $this->getTabContent($cleaned['tabId'], $profile) . "";
                break;
            case "showFriends":
                echo "SUCCESS " . $this->showFriends($profile, $cleaned['page']);
                break;
            case "showVisitorMessages":
                echo "SUCCESS " . $this->showVisitorMessages($profile, $cleaned['page']);
                break;
            case "userAction":
                if (!isset($cleaned['id'])) {
                    echo "Sorry, please contact a system administrator. #243";
                    return;
                }
                $id = $cleaned['id'];
                if ($profile->getUsername() == $this->user->getUsername() && $id != 2) {
                    echo "Sorry, you can't complete that action.";
                    return;
                }
                $friends = $this->user->getContactManager()->getFriends();
                $blocked = $this->user->getContactManager()->getBlocked();
                switch ($id) {
                    case 0://add friend
                        if (sizeof($friends) + 1 > 200) {
                            echo "Sorry, you can't add anymore friends.";
                            return;
                        }
                        $this->user->getContactManager()->add($profile->getUsername(), false);
                        break;
                    case 1://add ignore
                        if (sizeof($blocked) + 1 > 200) {
                            echo "Sorry, you can't block anymore users.";
                            return;
                        }
                        $this->user->getContactManager()->add($profile->getUsername(), true);
                        break;
                    case 3://remove friend
                        if (!array_key_exists($profile->getUsername(), $friends)) {
                            echo "Sorry, that user is not on your friends list.";
                            return;
                        }
                        $this->user->getContactManager()->remove($profile->getUsername(), false);
                        break;
                    case 4://remove ignore
                        if (!array_key_exists($profile->getUsername(), $blocked)) {
                            echo "Sorry, that user is not on your ignore list.";
                            return;
                        }
                        $this->user->getContactManager()->remove($profile->getUsername(), true);
                        break;
                }
                echo "SUCCESS";
                break;
        }
    }

    /**
     * Views a profile.
     * @param name The name of the account.
     */
    public function view($name, $tab)
    {
        $this->myProfile = $this->user->getUsername() == $name;
        $sys = Registry::get("sys");
        $profile = $name == "" ? "" : User::getByName($name);
        if ($name == "" || !$profile) {
            $sys->getForumManager()->error("This user has not registered and therefore does not have a profile to view.");
            return;
        }
        $this->setViewed($profile);
        $this->template = TemplateManager::load("ProfileViewer");
        $this->template->insert("name", $name);
        $this->template->insert("username", $profile->getModule("UserTools")->getFormatUsername(true));
        $this->template->insert("status", $profile->isActive() ? "online" : "offline");
        $this->template->insert("profileImage", $profile->getProfileImage());
        $this->template->insert("groupName", $profile->getModule("UserTools")->getGroupName());
        $this->template->insert("groupStyle", $profile->getModule("UserTools")->getUsernameStyle());
        $this->template->insert("rank", $profile->getModule("UserTools")->getGroupRank());
        $this->template->insert("group", $profile->getModule("UserTools")->getGroups());
        $this->template->insert("posts", $profile->getPostCount());
        $this->template->insert("addFriendStyle", ($profile->getUid() == Registry::get("user")->getUid() || Registry::get("user")->getContactManager()->isFriend($name) || Registry::get("user")->getContactManager()->isBlocked($name)) ? "display:none;" : "");
        $this->template->insert("addIgnoreStyle", ($profile->getUid() == Registry::get("user")->getUid() || Registry::get("user")->getContactManager()->isBlocked($name) || Registry::get("user")->getContactManager()->isFriend($name)) ? "display:none;" : "");
        $this->template->insert("removeFriendStyle", ($profile->getUid() == Registry::get("user")->getUid() || !Registry::get("user")->getContactManager()->isFriend($name)) ? "display:none;" : "");
        $this->template->insert("removeIgnoreStyle", ($profile->getUid() == Registry::get("user")->getUid() || !Registry::get("user")->getContactManager()->isBlocked($name)) ? "display:none;" : "");
        $this->template->insert("editorDisplay", $tab == 2 ? "" : "display:none");
        $joinDate = Utils::formatTime($profile->getData("joined_date"));
        $this->template->insert("joinDate", substr($joinDate, 0, strpos($joinDate, "-")));
        $this->template->insert("lastActive", $this->getLastActive($profile));
        $this->template->insert("thanksGiven", $this->getThanksGiven($profile));
        $this->template->insert("thanksRecieved", $this->getThanksRecieved($profile));
        $tabs = array($profile->getFormatUsername() . "'s Activity", "Friends", "Visitor Messages");
        if ($this->myProfile) {
            $notifyCount = $this->user->getNotificationManager()->getCount();
            array_push($tabs, "Notifications" . ($notifyCount > 0 ? " (" . $notifyCount . ")" : ""));
        }
        $this->template->insert("tabs", $this->getTabs($tabs, $tab));
        $this->template->insert("tabContent", $this->getTabContent($tab, $profile));
        $this->template->insert("visitors", $this->getLastVisitors($profile));
        $this->display();
    }

    /**
     * Gets the tab content.
     * @param index The tab index.
     * @param profile The profile.
     */
    public function getTabContent($index, $profile)
    {
        $html = "";
        $fm = Registry::get("sys")->getForumManager();
        switch ($index) {
            case 0:
                $fm = Registry::get("sys")->getForumManager();
                $html = "";
                $template = TemplateManager::load("RecentActivity");
                $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".posts WHERE poster='" . $profile->getUid() . "' ORDER BY post_id DESC LIMIT 10");
                while ($post = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $s = $this->database->query("SELECT * FROM " . FORUM_DB . ".threads WHERE thread_id='" . $post['thread_id'] . "'");
                    if ($s->rowCount() == 1) {
                        $thread = $s->fetch(PDO::FETCH_ASSOC);
                        $t = Thread::create($thread);
                        if (!$t->isViewable()) {
                            continue;
                        }

                        $template->insert("content", $post['content']);
                        $template->insert("timestamp", Utils::formatTime($post['date']));
                        $s = $this->database->query("SELECT * FROM " . FORUM_DB . ".posts WHERE thread_id='" . $thread['thread_id'] . "'");
                        $template->insert("title", $profile->getFormatUsername() . " " . ($post['first_post'] == 0 ? "replied to a thread" : "made a thread") . ":  <a href='/community/thread/index.php?board_id=" . $thread['board_id'] . "&id=" . $thread['thread_id'] . "&page=1' >" . $thread['title'] . "</a>");
                        $template->insert("meta", "Replies: " . $s->rowCount());
                        $html .= $template->getContents();
                        $template->reset();
                    }
                }
                break;
            case 1:
                $html = $this->showFriends($profile, 1);
                break;
            case 2:
                $html = $this->showVisitorMessages($profile, 1);
                break;
            case 3:
                $html = $this->showNotifications($profile);
                break;
            default:
                return $this->getTabContent(0, $profile);
                break;
        }
        return $html;
    }

    /**
     * Gets the tabs html.
     * @param tabs The tabs.
     */
    private function getTabs($tabs, $active)
    {
        if ($active > sizeof($tabs) - 1) {
            $active = 0;
        }
        $html = "";
        for ($i = 0; $i < sizeof($tabs); $i++) {
            $html .= "<li><a data-id='" . $i . "' " . ($i == $active ? "class='active'" : "") . " href='#'>" . $tabs[$i] . "</a></li>";
        }
        return $html;
    }

    /**
     * Posts the message to the profiles visitor messages.
     * @param profile The profile.
     * @param html The html content.
     */
    private function postMessage($profile, $html)
    {
        if ($profile->getUid() == $this->getUser()->getUid()) {
            echo "Sorry, you can't post a visitor message on your own profile.";
            return;
        }
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("FORUM_POST")) {
            echo "Sorry, you need to wait 30 seconds between posts.";
            return;
        }
        $content = Utils::purify(trim($html));
        if (strlen($content) < 6 || strlen(trim(preg_replace('/\xc2\xa0/', ' ', $content))) == 0) {
            echo "Your post must contain at least 6 characters.";
            return;
        }
        if (strlen($content) > 500) {
            echo "Your message can't be more than 500 characters.";
            return;
        }
        $date = new DateTime('now');
        $statement = $this->database->prepare("INSERT INTO " . FORUM_DB . ".visitor_messages (visitor_id,recipient_id,content,date) VALUES(?,?,?,?)");
        $statement->bindParam(1, $this->user->getUid());
        $statement->bindParam(2, $profile->getUid());
        $statement->bindParam(3, $html);
        $statement->bindParam(4, $date->format('Y-m-d H:i:s'));
        if (!$statement->execute()) {
            echo "Sorry, please contact your system administrator.";
        }
        echo "SUCCESS/community/members/index.php?name=" . $profile->getUsername() . "&tab=2";
        $profile->getNotificationManager()->sendNotification($this->user->getModule("UserTools")->getFormatUsername(true) . " left a message on your visitor wall");

    }

    /**
     * Shows the visitor messages.
     * @param profile The profile.
     * @param page The page number.
     */
    private function showVisitorMessages($profile, $page)
    {
        $html = "";
        $offset = "";
        if ($page != 1) {
            $offset = "OFFSET " . (VISITOR_MESSAGES * ($page - 1));
        }
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".visitor_messages WHERE recipient_id='" . $profile->getUid() . "' ORDER BY id DESC LIMIT " . VISITOR_MESSAGES . " " . $offset);
        $template = TemplateManager::load("VisitorMessage");
        $owner;
        while ($message = $statement->fetch(PDO::FETCH_ASSOC)) {
            $owner = User::getUser($message['visitor_id']);
            if (!$owner) {
                continue;
            }
            $template->insert("title", "<span>" . $owner->getModule("UserTools")->getFormatUsername(true) . " SAYS </span>");
            $template->insert("content", $message['content']);
            $template->insert("timestamp", Utils::formatTime($message['date']));
            $template->insert("meta", "");
            $html .= $template->getContents();
            $template->reset();
        }
        if ($html == "") {
            $html .= "<h4 style='padding:20px;'>There are no visitor messages to be displayed for this profile.</h4>";
        }
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".visitor_messages WHERE recipient_id='" . $profile->getUid() . "'");
        $messages = $statement->rowCount();
        $pageAmt = ceil($messages / VISITOR_MESSAGES);
        $pages = "";
        for ($i = 0; $i < $pageAmt; $i++) {
            if ($i + 1 == $page) {
                $pages .= "<li class=\"selected\">" . ($i + 1) . "</li>";
            } else {
                $pages .= "<li>" . ($i + 1) . "</li>";
            }
        }
        $html .= "<ul class='pagination' data-id='1'>" . $pages . "</ul>";
        return $html;
    }

    /**
     * Shows the friends for a profile.
     * @param profile The profile.
     * @param page The page.
     */
    private function showFriends($profile, $page)
    {
        $html = "";
        $friends = $this->getFriends($profile, $page);
        $template = TemplateManager::load("FriendBlock");
        $f;
        foreach ($friends as $friend) {
            $f = User::getByName($friend);
            if (!$f) {
                continue;
            }
            $template->insert("username", $f->getModule("UserTools")->getFormatUsername(true));
            $template->insert("groupName", $f->getModule("UserTools")->getGroupName());
            $template->insert("profileImage", $f->getProfileImage());
            $template->insert("groupStyle", $f->getModule("UserTools")->getUsernameStyle());
            $html .= $template->getContents();
            $template->reset();
        }
        if ($html == "") {
            return "<br><h3 style='padding:20px;'>This user does not have any friends in their contact list.</h3>";
        }
        $friends = $this->user->getContactManager()->getFriends();
        $totalThreads = sizeof($friends);
        $pageAmt = ceil($totalThreads / FRIENDS_PER_PAGE);
        $pages = "";
        for ($i = 0; $i < $pageAmt; $i++) {
            if ($i + 1 == $page) {
                $pages .= "<li class=\"selected\">" . ($i + 1) . "</li>";
            } else {
                $pages .= "<li>" . ($i + 1) . "</li>";
            }
        }
        $html .= "<ul class='pagination' data-id='0'>" . $pages . "</ul>";
        return $html;
    }

    /**
     * Shows the users notifications.
     * @param profile The profile.
     */
    private function showNotifications($profile)
    {
        if ($profile->getUid() != $this->user->getUid()) {
            return "Error!";
        }
        if (sizeof($this->user->getNotificationManager()->getNotifications()) == 0) {
            return "<h4 style='padding:20px;'>You do not have any notifications.</h4>";
        }
        $this->database->query("UPDATE " . FORUM_DB . ".notifications SET opened='1' WHERE uid='" . $profile->getUid() . "' AND opened='0'");
        $from_date = date('Y-m-d H:i:s', strtotime("-7 DAYS"));
        $this->database->query("DELETE FROM " . FORUM_DB . ".notifications WHERE uid='" . $profile->getUid() . "' AND opened='1' AND date < '" . $from_date . "'");
        $html = "";
        $template = TemplateManager::load("Notification");
        foreach ($this->user->getNotificationManager()->getNotifications() as $notification) {
            $template->insert("notification", $notification->getNotification());
            $template->insert("timestamp", Utils::formatTime($notification->getDate()));
            $html .= $template->getContents();
            $template->reset();
        }
        return $html;
    }

    /**
     * Sets the profile to being viewed by this user.
     */
    private function setViewed($profile)
    {
        if ($profile->getUid() == $this->user->getUid()) {
            return;
        }
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".recent_visitors WHERE visitor_id='" . $this->user->getUid() . "' AND recipient_id='" . $profile->getUid() . "'");
        if ($statement->rowCount() == 0) {
            $date = new DateTime('now');
            $statement = $this->database->prepare("INSERT INTO " . FORUM_DB . ".recent_visitors (visitor_id,recipient_id,date) VALUES(?,?,?)");
            $statement->bindParam(1, $this->user->getUid());
            $statement->bindParam(2, $profile->getUid());
            $statement->bindParam(3, $date->format('Y-m-d H:i:s'));
            $statement->execute();
        } else {
            $date = new DateTime('now');
            $fm = $date->format('Y-m-d H:i:s');
            $userUid = $this->user->getUid();
            $profileUid = $profile->getUid();
            $statement = $this->database->prepare("UPDATE " . FORUM_DB . ".recent_visitors SET date=? WHERE visitor_id=? AND recipient_id=?");
            $statement->bindParam(1, $fm);
            $statement->bindParam(2, $userUid);
            $statement->bindParam(3, $profileUid);
            $statement->execute();
        }
    }

    /**
     * Gets the last visitors.
     * @param profile The profile.
     */
    private function getLastVisitors($profile)
    {
        $html = "";
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".recent_visitors WHERE recipient_id='" . $profile->getUid() . "' ORDER BY date DESC LIMIT 10");
        $count = 0;
        $rowCount = $statement->rowCount();
        while ($recent = $statement->fetch(PDO::FETCH_ASSOC)) {
            $owner = User::getUser($recent['visitor_id']);
            if (!$owner) {
                continue;
            }
            $html .= $owner->getModule("UserTools")->getFormatUsername(true) . ($count == $rowCount - 1 ? "" : ",");
            $count++;
        }
        return $html;
    }


    /**
     * Gets the friends for a page.
     * @param profile The profile.
     * @param page The page number.
     */
    private function getFriends($profile, $page)
    {
        $friends = $profile->getContactManager()->getFriends();
        $send = array();
        $count = 0;
        foreach ($friends as $friend) {
            if ($count++ < (FRIENDS_PER_PAGE * ($page - 1))) {
                continue;
            }
            if (FRIENDS_PER_PAGE * ($page - 1) + FRIENDS_PER_PAGE == $count - 1) {
                break;
            }
            $send[$friend] = $friend;
        }
        return $send;
    }

    /**
     * Gets the time a user has last been active.
     * @param profile The user profile.
     */
    private function getLastActive($profile)
    {
        $lastActive = $profile->getData("lastActive");
        $timestamp = strtotime($lastActive);
        if (strtotime($lastActive) > strtotime("-24 hours")) {
            return "Today " . date("g:i A", $timestamp);
        }
        $lastActive = Utils::formatTime($lastActive);
        return substr($lastActive, 0, strpos($lastActive, "-"));
    }

    /**
     * Gets the amount of thanks given.
     * @param profile The profile.
     */
    private function getThanksGiven($profile)
    {
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".thanks WHERE thanker='" . $profile->getUid() . "'");
        return $statement->rowCount();
    }

    /**
     * Gets the amount of thanks recieved.
     * @param profile The profile.
     */
    private function getThanksRecieved($profile)
    {
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".thanks WHERE recipient_id='" . $profile->getUid() . "'");
        return $statement->rowCount();

    }
}

?>