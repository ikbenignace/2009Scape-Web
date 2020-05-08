<?php

/**
 * Handles the top poster module.
 * @author Adam Rodrigues
 *
 */
class TopPoster extends Module
{

    /**
     * Loads the recent posts.
     */
    public function load()
    {
        $db = Registry::get("database");
        echo "<div class=\"recentPosts\"><div class=\"recentPostsHeader\"><h4>Top Poster</h4><a class=\"minimize\"><i class=\"fa fa-minus\"></i></a></div><div class=\"mod_recentPosts\"><ul>";
        $this->template = TemplateManager::load("TopPoster");
        $statement = $db->query("SELECT * FROM members ORDER BY posts DESC LIMIT 3");
        while ($member = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->template->reset();
            $owner = User::getByData($member);
            $this->template->insert("username", $owner->getModule("UserTools")->getFormatUsername(true));
            $this->template->insert("imgURL", $owner->getProfileImage());
            $this->template->insert("posts", $owner->getPostCount());
            $this->template->insert("suburl", "/community/members/index.php?name=" . $member['username']);
            $this->template->display();
        }
        echo "</ul></div></div>";
    }
}

?>