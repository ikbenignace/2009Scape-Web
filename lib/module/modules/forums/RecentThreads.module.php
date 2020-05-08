<?php
define("THREAD_LIMIT", 5);

/**
 * Handles the recent threads module.
 * @author Adam Rodrigues
 *
 */
class RecentThreads extends Module
{

    /**
     * Loads the recent threads.
     */
    public function load()
    {
        echo "<div class=\"recentPosts\"><div class=\"recentPostsHeader\"><h4>Recent Threads</h4><a class=\"minimize\"><i class=\"fa fa-minus\"></i></a></div><div class=\"mod_recentPosts\"><ul>";
        $db = Registry::get("database");
        $fm = Registry::get("sys")->getForumManager();
        $this->template = TemplateManager::load("RecentThread");
        $statement = $db->query("SELECT * FROM " . FORUM_DB . ".threads ORDER by date DESC LIMIT " . THREAD_LIMIT);
        while ($postData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->template->reset();
            $thread = $fm->getThreadById($postData['thread_id']);
            if (!$thread) {
                continue;
            }
            $owner = User::getUser($postData['starter_uid']);
            if (!$owner) {
                continue;
            }
            if (!$thread->isViewable()) {
                continue;
            }
            $this->template->insert("username", $owner->getModule("UserTools")->getFormatUsername(true));
            $this->template->insert("suburl", "/community/thread/index.php?board_id=" . $postData['board_id'] . "&id=" . $postData['thread_id'] . "&page=" . $thread->getPages());
            $this->template->insert("imgURL", $owner->getProfileImage());
            $this->template->insert("postTitle", $thread->getTitle());
            $this->template->display();
        }
        echo "</ul></div></div>";
    }

}

?>