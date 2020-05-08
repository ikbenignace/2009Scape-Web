<?php
define("POST_LIMIT", 5);

/**
 * Handles the recent posts module.
 * @author Adam Rodrigues
 *
 */
class RecentPosts extends Module
{

    /**
     * Loads the recent posts.
     */
    public function load()
    {
        echo "<div class=\"recentPosts\"><div class=\"recentPostsHeader\"><h4>Recent Posts</h4><a class=\"minimize\"><i class=\"fa fa-minus\"></i></a></div><div class=\"mod_recentPosts\"><ul>";
        $db = Registry::get("database");
        $fm = Registry::get("sys")->getForumManager();
        $this->template = TemplateManager::load("RecentPost");
        $statement = $db->query("SELECT * FROM " . FORUM_DB . ".posts WHERE first_post='0' ORDER by date DESC LIMIT " . POST_LIMIT);
        while ($postData = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->template->reset();
            $post = $fm->getPostById($postData['post_id']);
            $thread = $fm->getThreadById($postData['thread_id']);
            if (!$post || !$thread) {
                continue;
            }
            $owner = User::getUser($postData['poster']);
            if (!$owner) {
                continue;
            }
            if (!$thread->isViewable()) {
                continue;
            }
            $threadOwner = User::getUser($thread->getStarterUid());
            if (!$threadOwner) {
                continue;
            }
            $this->template->insert("username", $owner->getModule("UserTools")->getFormatUsername(true));
            $this->template->insert("suburl", "/community/thread/index.php?board_id=" . $postData['board_id'] . "&id=" . $postData['thread_id'] . "&page=" . $thread->getPages());
            $this->template->insert("imgURL", $threadOwner->getProfileImage());
            $this->template->insert("postTitle", $thread->getTitle());
            $this->template->display();
        }
        echo "</ul></div></div>";
    }
}

?>