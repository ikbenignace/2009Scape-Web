<?php

/**
 * A post on a thread.
 * @author Adam Rodrigues
 *
 */
class Post
{

    /**
     * The post id.
     */
    private $post_id;

    /**
     * The post data.
     */
    private $data;

    /**
     * The thanks on this post.
     */
    private $thanks = array();

    /**
     * Constructs a post object.
     * @param post_id The post id.
     */
    public function __construct($post_id, $load = true)
    {
        $this->post_id = $post_id;
        if ($load) {
            $this->data = self::getPostData($post_id);
        }
        $this->setThankData();
    }

    /**
     * Creates a post object.
     * @param postData the post data.
     */
    public static function create($postData)
    {
        $thread = new Post($postData['post_id'], false);
        $thread->setData($postData);
        return $thread;
    }

    /**
     * Gets the post data for a post id.
     * @param post_id The id of the post.
     */
    public static function getPostData($post_id)
    {
        $database = Registry::get("database");
        $posts = $database->query("SELECT * FROM " . FORUM_DB . ".posts WHERE post_id=" . $post_id . " LIMIT 1");
        return $posts->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Sets the thank data.
     */
    public function setThankData()
    {
        $this->thanks = array();
        $db = Registry::get("database");
        $statement = $db->query("SELECT * FROM " . FORUM_DB . ".thanks WHERE post_id='" . $this->post_id . "'");
        while ($thank = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->thanks[$thank['thanker']] = $thank['thanker'];
        }
    }

    /**
     * Deletes the post.
     */
    public function delete()
    {
        $db = Registry::get("database");
        $statement = $db->prepare("DELETE FROM " . FORUM_DB . ".posts WHERE post_id=?");
        $statement->bindParam(1, $this->post_id);
        if (!$statement->execute()) {
            echo "Error: The post was not able to be removed.";
            return false;
        }
        echo "SUCCESS";
        return true;
    }

    /**
     * Checks if the post is thanked by the user.
     * @param uid The uid.
     */
    public function isThankedBy($uid)
    {
        return array_key_exists($uid, $this->thanks);
    }

    /**
     * Checks if the post has thanks.
     */
    public function hasThanks()
    {
        return sizeof($this->thanks) > 0;
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
     * Checks if it is the first post on a thread.
     */
    public function isFirstPost()
    {
        return $this->data['first_post'] == 1;
    }

    /**
     * Gets the content of the post.
     */
    public function getContent()
    {
        return $this->data['content'];
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
     * Sets the post data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the post data.
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
        return $this->data['thread_id'];
    }

    /**
     * Gets the poster.
     */
    public function getPoster()
    {
        return $this->data['poster'];
    }

    /**
     * Gets the post id.
     */
    public function getPostId()
    {
        return $this->post_id;
    }

    /**
     * Gets the thanks data.
     */
    public function getThanks()
    {
        return $this->thanks;
    }
}

?>