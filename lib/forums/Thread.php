<?php

/**
 * Represents a thread.
 * @author Adam Rodrigues
 *
 */
class Thread
{

    /**
     * The thread id.
     */
    private $thread_id;

    /**
     * The thread data.
     */
    private $data;

    /**
     * The posts for the thread.
     */
    private $posts = array();

    /**
     * Constructs a thread.
     * @param thread_id The thread id.
     */
    public function __construct($thread_id, $load = true)
    {
        $this->thread_id = $thread_id;
        if ($load) {
            $this->data = self::getThreadData($thread_id);
        }
    }

    /**
     * Creates a thread object.
     * @param threadData the thread data.
     */
    public static function create($threadData)
    {
        $thread = new Thread($threadData['thread_id'], false);
        $thread->setData($threadData);
        return $thread;
    }

    /**
     * Gets the thread data.
     * @param thread_id The thread id.
     */
    public static function getThreadData($thread_id)
    {
        $database = Registry::get("database");
        $threads = $database->query("SELECT * FROM " . FORUM_DB . ".threads WHERE thread_id=" . $thread_id . " LIMIT 1");
        if ($threads->rowCount() == 0) {
            return false;
        }
        return $threads->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a thread.
     */
    public function delete()
    {
        $db = Registry::get("database");
        $statement = $db->prepare("DELETE FROM " . FORUM_DB . ".threads WHERE thread_id=?");
        $statement->bindParam(1, $this->thread_id);
        if (!$statement->execute()) {
            echo "Error: The thread was not able to be removed.";
            return false;
        }
        $statement = $db->prepare("DELETE FROM " . FORUM_DB . ".posts WHERE thread_id=?");
        $statement->bindParam(1, $this->thread_id);
        $statement->execute();
        echo "SUCCESS";
        return true;
    }

    /**
     * Gets a post from a thread page.
     * @param post_id The post id.
     * @param page The page.
     */
    public function getPost($post_id, $page)
    {
        if (!array_key_exists($page - 1, $this->posts)) {
            $posts = $this->getPosts($page);
            if (!$posts) {
                return false;
            }
            $this->posts[$page - 1] = $posts;
        }
        return $this->posts[$page - 1][$post_id];
    }

    /**
     * Creates the post objects.
     * @param page The page number.
     */
    public function getPosts($page)
    {
        if (array_key_exists($page - 1, $this->posts)) {
            return $this->posts[$page - 1];
        }
        $database = Registry::get("database");
        $offset = "";
        if ($page != 1) {
            $offset = "OFFSET " . (POSTS_PER_PAGE * ($page - 1));
        }
        $posts_array = array();
        $query = $database->query("SELECT * FROM " . FORUM_DB . ".posts WHERE thread_id=" . $this->thread_id . " ORDER by date LIMIT " . POSTS_PER_PAGE . " " . $offset);
        while ($post = $query->fetch(PDO::FETCH_ASSOC)) {
            $posts_array[$post['post_id']] = Post::create($post);
        }
        if (sizeof($posts_array) == 0) {
            return false;
        }
        $this->posts[$page - 1] = $posts_array;
        return $this->posts[$page - 1];
    }

    /**
     * Gets the last poster on a thread.
     */
    public function getLastPoster()
    {
        $database = Registry::get("database");
        $query = $database->query("SELECT * FROM " . FORUM_DB . ".posts WHERE thread_id=" . $this->thread_id . " ORDER by date DESC LIMIT 1");
        if ($query->rowCount() == 0) {
            $user = User::getUser($this->getStarterUid());
        } else {
            $post = $query->fetch(PDO::FETCH_ASSOC);
            $user = User::getUser($post['poster']);
        }
        if (!$user) {
            return "Unknown";
        }
        return $user->getModule("UserTools")->getFormatUsername(true);
    }

    /**
     * Gets the amount of pages.
     */
    public function getPages()
    {
        $fm = Registry::get("sys")->getForumManager();
        $totalPosts = $fm->getTotalPosts($this->thread_id);
        $pages = intval(ceil($totalPosts / POSTS_PER_PAGE));
        if ($pages < 1) {
            $pages = 1;
        }
        return $pages;
    }

    /**
     * Checks if a thread is edited.
     */
    public function isEdited()
    {
        $created = $this->getDate();
        $edited = $this->getEditedDate();
        return strtotime($edited) > strtotime($created);
    }

    /**
     * Checks if the thread is viewable.
     */
    public function isViewable()
    {
        $fm = Registry::get("sys")->getForumManager();
        $board = $fm->getBoard($this->data['board_id']);
        $user = Registry::get("user");
        return $board->isSelfView() ? ($user->getUid() == $this->getStarterUid() || $user->getRights() > 0) : $board->isViewable();
    }

    /**
     * Gets the starter uid.
     */
    public function getStarterUid()
    {
        return $this->data['starter_uid'];
    }

    /**
     * Gets the title.
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * Gets the date.
     */
    public function getDate()
    {
        return $this->data['date'];
    }

    /**
     * Gets the edited date.
     */
    public function getEditedDate()
    {
        return $this->data['lastEdit'];
    }

    /**
     * Sets the thread data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the thread data.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets the thread id.
     */
    public function getThreadId()
    {
        return $this->thread_id;
    }

}


?>