<?php
define("POSTS_PER_PAGE", 10);
define("THREADS_PER_PAGE", 10);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/forums/ForumSettings.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/forums/Thread.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/forums/Board.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/forums/Post.php");

/**
 * A class used to manage the forums.
 * @author Adam Rodrigues
 *
 */
class ForumManager
{

    /**
     * The forum settings.
     */
    private $forumSettings;

    /**
     * The loaded boards.
     */
    private $boards = array();

    /**
     * The loaded stand-alone thread objects.
     */
    private $threads = array();

    /**
     * The loaded stand-alone post objects.
     */
    private $posts = array();

    /**
     * If the forums have been configured.
     */
    private $configured;

    /**
     * The database instance.
     */
    private $db;

    /**
     * Constructs the database.
     */
    public function __construct()
    {
        $this->db = Registry::get("database");
    }

    /**
     * Configures the forums.
     */
    public function configure()
    {
        $this->forumSettings = new ForumSettings($this);
        $this->forumSettings->load();
        $this->configured = true;
    }


    /**
     * Gets a board for a board_id.
     * @param board_id The board id.
     */
    public function getBoard($board_id)
    {
        if ($this->isLoaded($this->boards, $board_id)) {
            return $this->boards[$board_id];
        }
        if (!$this->exists("boards", "board_id", $board_id)) {
            return false;
        }
        $board = new Board($board_id);
        $this->boards[$board_id] = $board;
        return $board;
    }

    /**
     * Gets a board object through the board data.
     * @param boardData the board data.
     */
    public function getBoardByData($boardData)
    {
        if ($this->isLoaded($this->boards, $boardData['board_id'])) {
            return $this->boards[$board_id];
        }
        if (!$this->exists("boards", "board_id", $boardData['board_id'])) {
            return false;
        }
        $board = Board::create($boardData);
        $this->boards[$boardData['board_id']] = $board;
        return $board;
    }

    /**
     * Gets a thread from the board.
     * @param board_id The board id.
     * @param thread_id The thread id.
     * @param page The page number.
     */
    public function getThread($board_id, $thread_id, $page)
    {
        $board = $this->getBoard($board_id);
        if (!$board) {
            return false;
        }
        return $board->getThread($thread_id, $page);
    }

    /**
     * Gets an array of threads.
     * @param board_id The board id.
     * @param page The page number.
     */
    public function getThreads($board_id, $page)
    {
        $board = $this->getBoard($board_id);
        if (!$board) {
            return false;
        }
        return $board->getThreads($page);
    }

    /**
     * Gets a stand alone thread object.
     * @param thread_id The thread id.
     */
    public function getThreadById($thread_id)
    {
        if (array_key_exists($thread_id, $this->threads)) {
            return $this->threads[$thread_id];
        }
        $threadData = Thread::getThreadData($thread_id);
        if (!$threadData) {
            return false;
        }
        $thread = Thread::create($threadData);
        $this->threads[$thread_id] = $thread;
        return $thread;
    }

    /**
     * Gets a stand alone post object.
     * @param post_id The post id.
     */
    public function getPostById($post_id)
    {
        if (array_key_exists($post_id, $this->posts)) {
            return $this->posts[$post_id];
        }
        $postData = Post::getPostData($post_id);
        if (!$postData) {
            return false;
        }
        $post = Post::create($postData);
        $this->posts[$post_id] = $post;
        return $post;
    }

    /**
     * Gets the total amount of board posts.
     * @param board_id The board id.
     */
    public function getTotalBoardPosts($board_id)
    {
        $data = $this->db->query("SELECT * FROM " . FORUM_DB . ".posts WHERE board_id=" . $board_id);
        return $data->rowCount();
    }

    /**
     * Gets the total amount of threads for a board.
     * @param board id The board id.
     */
    public function getTotalThreads($board_id)
    {
        $data = $this->db->query("SELECT * FROM " . FORUM_DB . ".threads WHERE board_id=" . $board_id);
        return $data->rowCount();
    }

    /**
     * Gets the total amount of posts for a thread.
     * @param thread id The thread id.
     */
    public function getTotalPosts($thread_id)
    {
        $data = $this->db->query("SELECT * FROM " . FORUM_DB . ".posts WHERE thread_id=" . $thread_id);
        return $data->rowCount();
    }

    /**
     * Sends an error message on the content page.
     * @param title The title.
     * @param message The message.
     */
    public function error($message = "Please contact your system administrator.", $title = "Oops, Sorry something went wrong!")
    {
        $error = TemplateManager::load("Error");
        $error->insert("title", $title);
        $error->insert("message", $message);
        $error->display();
    }

    /**
     * Formats a time stamp.
     * @param time The time stamp.
     */
    public function formatTime($time)
    {
        $timestamp = strtotime($time);
        return date("F d, Y - g:i A", $timestamp);
    }

    /**
     * Checks if an array has an exisiting key.
     * @param array The array to check.
     * @param key The key to check.
     */
    public function isLoaded($array, $key)
    {
        return array_key_exists($key, $array);
    }

    /**
     * Checks if data exists in a table.
     * @param table The table.
     * @param identifier The identifier.
     * @param value The value.
     */
    public function exists($table, $identitfier, $value)
    {
        $data = $this->db->query("SELECT * FROM " . FORUM_DB . "." . $table . " WHERE " . $identitfier . "=" . $value . "");
        return $data->rowCount() > 0;
    }

    /**
     * Gets the forums settings.
     */
    public function getForumSettings()
    {
        return $this->forumSettings;
    }

    /**
     * Gets the database instance.
     */
    public function getDatabase()
    {
        return $this->db;
    }

    /**
     * Checks if the forums are configured.
     */
    public function isConfigured()
    {
        return $this->configured;
    }
}

?>