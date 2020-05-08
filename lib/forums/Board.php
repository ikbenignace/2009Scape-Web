<?php

/**
 * Represents a forum board.
 * @author Adam Rodrigues
 *
 */
class Board
{

    /**
     * The board id.
     */
    private $board_id;

    /**
     * The board data.
     */
    private $data;

    /**
     * The threads in the board.
     */
    private $threads = array();

    /**
     * The member rights to view a board.
     */
    private $viewRight;

    /**
     * The post right.
     */
    private $postRight;

    /**
     * If the board threads can only be viewed by the owner/staff.
     */
    private $selfView;

    /**
     * IF the board threads can only be viewed by donators/staff.
     */
    private $donator;

    /**
     * Constructs a board object.
     * @param board_id The board id.
     */
    public function __construct($board_id, $load = true)
    {
        $this->board_id = $board_id;
        if ($load) {
            $this->data = self::getBoardData($board_id);
        }
        if (isset($this->data)) {
            $this->setPermissions();
        }
    }

    /**
     * Creates a board object.
     * @param boardData the board data.
     */
    public static function create($boardData)
    {
        $board = new Board($boardData['board_id'], false);
        $board->setData($boardData);
        $board->setPermissions();
        return $board;
    }

    /**
     * Gets the board data.
     * @param board_id The baord id.
     */
    private static function getBoardData($board_id)
    {
        $database = Registry::get("database");
        $boards = $database->query("SELECT * FROM " . FORUM_DB . ".boards WHERE board_id=" . $board_id . " LIMIT 1");
        return $boards->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Sets the permissions for a board.
     */
    public function setPermissions()
    {
        $viewReq = $this->data['view_requirement'];
        $reqs = explode(",", $viewReq);
        foreach ($reqs as $req) {
            if (Utils::startsWith($req, "rights:")) {
                $this->viewRight = intval(substr($req, strrpos($req, ':') + 1));
            } elseif ($req == "self") {
                $this->selfView = true;
            } elseif ($req == "donator") {
                $this->donator = true;
            }
        }
        $postReq = $this->data['post_requirement'];
        $reqs = explode(",", $postReq);
        foreach ($reqs as $req) {
            if (Utils::startsWith($req, "rights:")) {
                $this->postRight = intval(substr($req, strrpos($req, ':') + 1));
            }
        }
    }

    /**
     * Gets the threads for a page.
     * @param page The page number.
     */
    public function getThreads($page)
    {
        if (array_key_exists($page - 1, $this->threads)) {
            return $this->threads[$page - 1];
        }
        $database = Registry::get("database");
        $offset = "";
        if ($page != 1) {
            $offset = " OFFSET " . (THREADS_PER_PAGE * ($page - 1));
        }
        $threads_array = array();
        $query = $database->query("SELECT * FROM " . FORUM_DB . ".threads WHERE board_id=" . $this->board_id . " ORDER by date DESC LIMIT " . THREADS_PER_PAGE . "" . $offset);
        while ($thread = $query->fetch(PDO::FETCH_ASSOC)) {
            $threads_array[$thread['thread_id']] = Thread::create($thread);
        }
        if (sizeof($threads_array) == 0) {
            return false;
        }
        $this->threads[$page - 1] = $threads_array;
        return $this->threads[$page - 1];
    }

    /**
     * Checks if the board is postable for the user.
     */
    public function isPostable()
    {
        if (isset($this->donator) && Registry::get("user")->getDonatorType() == -1) {
            return false;
        }
        return isset($this->postRight) ? Registry::get("user")->getRights() >= $this->postRight : true;
    }

    /**
     * Checks if the board is viewable by the user.
     */
    public function isViewable()
    {
        $user = Registry::get("user");
        if (isset($this->donator) && $user->getDonatorType() == -1 && $user->getRights() == 0) {
            return false;
        }
        return isset($this->viewRight) ? $user->getRights() >= $this->viewRight : true;
    }

    /**
     * Gets the thread.
     * @param thread_id The thread id.
     * @param page The page.
     */
    public function getThread($thread_id, $page)
    {
        $threads = $this->getThreads($page);
        if (!$threads) {
            return false;
        }
        return $threads[$thread_id];
    }

    /**
     * Sets the board data.
     */
    private function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Checks if the threads are self viewed/staff viewed.
     */
    public function isSelfView()
    {
        return $this->selfView;
    }

    /**
     * Gets the title.
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * Gets the board id.
     */
    public function getBoardId()
    {
        return $this->board_id;
    }

    /**
     * Checks if the board is hidden.
     */
    public function isHidden()
    {
        return $this->data['hide'] == 1;
    }

    /**
     * Gets the board data.
     */
    public function getData()
    {
        return $this->data;
    }

}

?>