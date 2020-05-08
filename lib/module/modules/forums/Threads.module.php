<?php

/**
 * The module to handle the displaying & managing of threads.
 * @author Adam Rodrigues
 *
 */
class Threads extends Module
{

    /**
     * Handles a sent action.
     * @param action The action.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        if (!$this->checkLogin()) {
            echo "Please contact your system administrator: error-code#43293";
            return;
        }
        switch ($action) {
            case "newThread":
                $this->createThread($cleaned);
                break;
            case "post":
            case "edit":
                $this->submitPost($cleaned);
                break;
            case "delete":
                $this->delete($cleaned);
                break;
            case "thanks":
                $this->thanks($cleaned);
                break;
        }
    }

    /**
     * Creates a thread with the given parameters.
     * @param cleaned The parameters.
     */
    private function createThread($cleaned)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("FORUM_POST")) {
            echo "Sorry, you need to wait 30 seconds between posts.";
            return;
        }
        $title = trim($cleaned['title']);
        $board_id = $cleaned['cid'];
        $html = Utils::purify(trim($cleaned['html']));
        if (strlen($title) < 2) {
            echo "The title must be 2 or more characters.";
            return;
        }
        if (strlen($title) > 56) {
            echo "The title must be less than 56 characters.";
            return;
        }
        if (empty($board_id)) {
            echo "You need to select the category.";
            return;
        }
        if (strlen($html) < 10 || strlen(trim(preg_replace('/\xc2\xa0/', ' ', $html))) == 0) {
            echo "Your post must be 10 or more characters.";
            return;
        }
        $fm = Registry::get("sys")->getForumManager();
        if (!$fm->exists("boards", "board_id", $board_id)) {
            echo "That category could not be found.";
            return;
        }
        $board = $fm->getBoard($board_id);
        if ($board->isHidden()) {
            echo "This board is unavailable at this time.";
            return;
        }
        if (!$board->isPostable()) {
            echo "You don't have permission to create a thread in this board.";
            return;
        }
        $fm = Registry::get("sys")->getForumManager();
        $threadAmt = $fm->getTotalThreads($board_id);
        $currentPageAmt = ceil($threadAmt / THREADS_PER_PAGE);
        $threadAmt += 1;
        $newPageAmt = ceil($threadAmt / THREADS_PER_PAGE);
        $page = $currentPageAmt;
        if ($newPageAmt > $currentPageAmt) {
            $page = $currentPageAmt + 1;
        }
        $db = Registry::get("database");
        $user = $this->user;
        $date = new DateTime('now');
        $fm = $date->format('Y-m-d H:i:s');
        $useruid = $user->getUid();
        $statement = $db->prepare("INSERT INTO " . FORUM_DB . ".threads (board_id, title, starter_uid, date) VALUES(?,?,?,?)");
        $statement->bindParam(1, $board_id);
        $statement->bindParam(2, $title);
        $statement->bindParam(3, $useruid);
        $statement->bindParam(4, $fm);
        if (!$statement->execute()) {
            echo "An error occured, please contact a system administrator.";
            return;
        }
        $first_post = true;
        $id = $db->getConnection()->lastInsertId();
        $statement = $db->prepare("INSERT INTO " . FORUM_DB . ".posts (thread_id, board_id, poster, content, date, first_post) VALUES(?,?,?,?,?,?)");
        $statement->bindParam(1, $id);
        $statement->bindParam(2, $cleaned['cid']);
        $statement->bindParam(3, $useruid);
        $statement->bindParam(4, $cleaned['html']);
        $statement->bindParam(5, $fm);
        $statement->bindParam(6, $first_post);
        if (!$statement->execute()) {
            echo "An error occured, please contact a system administrator. #434";
            return;
        }
        $user->addPosts(1);
        echo "SUCCESS /community/thread/index.php?board_id=" . $board_id . "&id=" . $id . "&page=" . $page;
    }

    /**
     * Deletes a thread/post.
     */
    private function delete($cleaned)
    {
        $fm = Registry::get("sys")->getForumManager();
        if (!$thread = $fm->getThreadById($cleaned['tid'])) {
            echo "The thread may have been deleted or is unavailable at this time.";
            return;
        }
        $post = $thread->getPost($cleaned['pid'], $cleaned['page']);
        if (!$post) {
            echo "The post may have been deleted or is unavailable at this time.";
            return;
        }
        $isThread = $post->isFirstPost();
        if ($this->user->getRights() > 0) {
            Registry::get("sys")->log(($isThread ? "Thread" : "Post") . " deleted by " . $this->user->getUsername() . " : " . ($isThread ? ("Thread name=" . $thread->getTitle()) : " post_id=" . $post->getPostId() . " poster_uid=" . $post->getPoster()), FORUM_LOG);
        }
        if ($isThread) {
            $thread->delete();
            return;
        }

        $u = User::getUser($post->getPoster());
        if (!$u) {
            return;
        }
        $u->removePosts(1);
        $u->write();
        $post->delete();
    }

    /**
     * Shows the threads for a board.
     * @param board_id The board id.
     * @param page The page number.
     */
    public function showThreads($board_id, $page)
    {
        $fm = Registry::get("sys")->getForumManager();
        if (empty($board_id) || empty($page) || $board_id < 1 || $page < 1) {
            $fm->error();
            return;
        }
        $board = $fm->getBoard($board_id);
        if (!$board) {
            $fm->error();
            return;
        }
        if ($board->isHidden()) {
            $fm->error("This board is unavailable at this time.");
            return;
        }
        if (!$board->isViewable()) {
            $fm->error("You don't have permission to view this board.");
            return;
        }
        $this->template = TemplateManager::load("ThreadListing");
        $threads = $fm->getThreads($board_id, $page);
        if (!$threads && $page != 1) {
            header("Location: /community/index.php?board_id=" . $board_id . "&page=1");
            return;
        }
        if (!$threads) {
            $fm->error("There aren't any threads created yet in this board, be the first to do so by clicking <i><a href=\"/community/thread/new\">here</></i>.</a>", "Oops, Sorry about that!");
            return;
        }
        foreach ($threads as $thread) {
            $owner = User::getUser($thread->getStarterUid());
            if (!$owner || !$thread->isViewable()) {
                continue;
            }
            $this->template->insert("title", $thread->getTitle());
            $this->template->insert("thread_id", $thread->getThreadId());
            $this->template->insert("starter", $owner->getModule("UserTools")->getFormatUsername(true));
            $this->template->insert("latestReply", $thread->getLastPoster());
            $this->template->insert("numReplies", $fm->getTotalPosts($thread->getThreadId()));
            $this->template->insert("threadTime", Utils::time_elapsed_string(strtotime($thread->getDate())));
            $this->display();
        }
        $this->showThreadPages($board_id, $page);
    }

    /**
     * Shows the number of pages of threads for a baord.
     * @param board_id The board id.
     * @param page The current page.
     */
    private function showThreadPages($board_id, $page)
    {
        if (empty($board_id) || empty($page) || $board_id < 1 || $page < 1) {
            return;
        }
        $fm = Registry::get("sys")->getForumManager();
        $pages = "";
        $totalThreads = $fm->getTotalThreads($board_id);
        $pageAmt = ceil($totalThreads / THREADS_PER_PAGE);
        for ($i = 0; $i < $pageAmt; $i++) {
            if ($i + 1 == $page) {
                $pages .= "<li class=\"selected\">" . ($i + 1) . "</li>";
            } else {
                $pages .= "<li>" . ($i + 1) . "</li>";
            }
        }
        echo "<ul class='pagination'>" . $pages . "</ul>";
    }

    /**
     * Shows a thread.
     * @param thread_id The thread id.
     */
    public function showThread($board_id, $thread_id, $page)
    {
        $fm = Registry::get("sys")->getForumManager();
        if (empty($board_id) || empty($thread_id) || empty($page) || $board_id < 1 || $thread_id < 1 || $page < 1) {
            $fm->error();
            return false;
        }
        $board = $fm->getBoard($board_id);
        if (!$board) {
            $fm->error();
            return;
        }
        if ($board->isHidden()) {
            $fm->error("This board is unavailable at this time.");
            return;
        }
        if (!$board->isViewable()) {
            $fm->error("You don't have permission to view this board.");
            return;
        }
        $thread = $fm->getThreadById($thread_id);
        if (!$thread) {
            $fm->error("This thread may have been moved or deleted.");
            return false;
        }
        if (!$thread->isViewable()) {
            $fm->error("You don't have permission to view this thread.");
            return false;
        }
        if ($page > $thread->getPages() && $page != 1) {
            $page = $thread->getPages();
        }
        $owner = User::getUser($thread->getStarterUid());
        if (!$owner) {
            $fm->error();
            return false;
        }
        $posts = $thread->getPosts($page);
        if ($page != 1 && !$posts) {
            $fm->error();
            return false;
        }
        echo "<div class=\"titleBar\"><a href='/community/index.php?board_id=" . $board_id . "&page=1'><i class='fa fa-arrow-left'></i> Back to Threads</a> <span>Viewing Thread: " . $thread->getTitle() . "</span></div>";
        $this->showPostPages($thread, $page);
        if (!$posts) {
            $fm->error();
            return true;
        }
        $this->template = TemplateManager::load("Post");
        foreach ($posts as $post) {
            $owner = User::getUser($post->getPoster());
            if (!$owner) {
                continue;
            }
            $this->template->reset();
            $this->template->insert("pid", $post->getPostId());
            $this->template->insert("tid", $post->getThreadId());
            $this->template->insert("posts", $owner->getPostCount());
            $this->template->insert("poster", $owner->getModule("UserTools")->getFormatUsername(true));
            $this->template->insert("profileImage", $owner->getProfileImage());
            $this->template->insert("group", $owner->getModule("UserTools")->getGroups());
            $this->template->insert("groupName", $owner->getModule("UserTools")->getGroupName());
            $this->template->insert("groupStyle", $owner->getModule("UserTools")->getUsernameStyle());
            $this->template->insert("displayDelete", Registry::get("user")->getRights() > 0 ? "initial" : "none");
            $this->template->insert("rank", $owner->getModule("UserTools")->getGroupRank());
            $this->template->insert("signatureStyle", !empty($owner->getSignature()) ? "" : "display:none;");
            $this->template->insert("signature", $owner->getSignature());
            $thanked = $post->isThankedBy($this->user->getUid());
            if ($this->user->getUid() != $owner->getUid()) {
                $this->template->insert("displayThank", "display:initial;");
                $this->template->insert("thankName", $thanked ? "Remove Thanks" : "Thanks");
                $this->template->insert("thankIcon", $thanked ? "thumbs-down" : "thumbs-up");
                $this->template->insert("id", $post->getPostId());
            } else {
                $this->template->insert("displayThank", "display:none;");
            }
            if ($post->isEdited()) {
                $this->template->insert("lastEdit", "Last edited: " . $fm->formatTime($post->getEditedDate()));
            } else {
                $this->template->insert("lastEdit", $fm->formatTime($post->getDate()));
            }
            if ($owner->getRights() == 2) {
                $this->template->insert("postStyle", "box-shadow: inset 0px 0px 7px 0px rgba(139, 110, 11, 0.3); border: 1px solid #8b6e0b;");
            }
            $this->template->insert("postContent", $post->getContent());
            $this->display();
            $t = TemplateManager::load("Thanks");
            $thanks = "";
            foreach ($post->getThanks() as $thanker) {
                $o = User::getUser($thanker);
                if (!$o) {
                    continue;
                }
                $thanks .= " " . $o->getModule("UserTools")->getFormatUsername(true) . ",";
            }
            $thanks = rtrim($thanks, ',');
            if (strlen($thanks) > 0) {
                if ($owner->getRights() == 2) {
                    $t->insert("thankStyle", "box-shadow: inset 0px 0px 7px 0px rgba(139, 110, 11, 0.3); border: 1px solid #8b6e0b;");
                }
                $t->insert("thanks", $thanks);
            } else {
                $t->insert("hide", "display:none;");
            }
            $t->insert("id", $post->getPostId());
            $t->display();
        }
        $this->showPostPages($thread, $page);
        return true;
    }


    /**
     * Submits a post to a thread.
     * @param cleaned The parameters.
     */
    private function submitPost($cleaned)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("FORUM_POST")) {
            echo "Sorry, you need to wait 30 seconds between posts.";
            return;
        }
        $fm = Registry::get("sys")->getForumManager();
        $board = $fm->getBoard($cleaned['bid']);
        if (!$board) {
            echo "Error, contact a system administrator.";
            return;
        }
        $html = $cleaned['html'];
        $content = Utils::purify(trim($cleaned['html']));
        if ($this->user->getRights() < 2 && (strlen($content) < 10 || strlen(trim(preg_replace('/\xc2\xa0/', ' ', $content))) == 0)) {
            echo "Your post must contain at least 10 characters.";
            return;
        }
        $db = Registry::get("database");
        $user = $this->user;
        $date = new DateTime('now');
        $page = 1;
        if (!isset($cleaned['pid']) || $cleaned['pid'] == 0) {
            $postAmt = $fm->getTotalPosts($cleaned['tid']);
            $currentPageAmt = ceil($postAmt / POSTS_PER_PAGE);
            $postAmt += 1;
            $newPageAmt = ceil($postAmt / POSTS_PER_PAGE);
            $page = $currentPageAmt;
            if ($newPageAmt > $currentPageAmt) {
                $page = $currentPageAmt + 1;
            }
            $time = $date->format('Y-m-d H:i:s');
            $userUid = $user->getUid();
            $statement = $db->prepare("INSERT INTO " . FORUM_DB . ".posts (thread_id, board_id, poster, content, date) VALUES(?,?,?,?,?)");
            $statement->bindParam(1, $cleaned['tid']);
            $statement->bindParam(2, $cleaned['bid']);
            $statement->bindParam(3, $userUid);
            $statement->bindParam(4, $html);
            $statement->bindParam(5, $time);
            $user->addPosts(1);
            $user->write();
        } else {
            $post = $fm->getPostById($cleaned['pid']);
            if (!$post || ($post->getPoster() != $user->getUid() && $user->getRights() == 0)) {
                echo "Please contact a system adminsitrator: error#494";
                return;
            }
            $page = isset($cleaned['page']) ? $cleaned['page'] : 1;
            $statement = $db->prepare("UPDATE " . FORUM_DB . ".posts SET content=?,lastEdit=? WHERE post_id=?");
            $statement->bindParam(1, $html);
            $statement->bindParam(2, $time);
            $statement->bindParam(3, $cleaned['pid']);
            if ($user->getRights() > 0) {
                Registry::get("sys")->log("Post edited: poster_uid=" . $post->getPoster() . " post_id=" . $post->getPostId() . " edited by: " . $user->getUsername(), FORUM_LOG);
            }
        }
        if (!$statement->execute()) {
            echo "An error occured, please contact a system administrator.";
            return;
        }
        echo "SUCCESS " . $page;
        if (isset($cleaned['replyId']) && $cleaned['replyId'] != 0) {
            $replyPost = $fm->getPostById($cleaned['replyId']);
            if ($replyPost) {
                $u = User::getUser($replyPost->getPoster());
                if ($u) {
                    $thread = $fm->getThreadById($cleaned['tid']);
                    if ($thread) {
                        $u->getNotificationManager()->sendNotification($user->getModule("UserTools")->getFormatUsername(true) . " quoted you on: <a href='/community/thread/index.php?board_id=" . $cleaned['bid'] . "&id=" . $cleaned['tid'] . "&page=" . $cleaned['page'] . "'>" . $thread->getTitle() . "</a>");
                    }
                }
            }
        }
    }

    /**
     * Shows the number of pages of posts for a thread.
     * @param thread The thread object.
     * @param page The current page.
     */
    private function showPostPages($thread, $page)
    {
        $fm = Registry::get("sys")->getForumManager();
        if (empty($page) || !$thread || $page < 1) {
            $fm->error();
            return;
        }
        $pages = "";
        $pageAmt = $thread->getPages();
        for ($i = 0; $i < $pageAmt; $i++) {
            if ($i + 1 == $page) {
                $pages .= "<li class=\"selected\">" . ($i + 1) . "</li>";
            } else {
                $pages .= "<li>" . ($i + 1) . "</li>";
            }
        }
        echo "<ul class='pagination'>" . $pages . "</ul>";
    }

    /**
     * Sends/Removes thanks from a post.
     * @param cleaned The parameters.
     */
    private function thanks($cleaned)
    {
        $fm = Registry::get("sys")->getForumManager();
        $post = $fm->getPostById($cleaned['pid']);
        if (!$post) {
            echo "The post may have been deleted or is unavailable at this time.";
            return;
        }
        $owner = User::getUser($post->getPoster());
        if (!$owner) {
            echo "Sorry, an error occured, please contact a system administrator.";
            return;
        }
        if ($post->getPoster() == $this->user->getUid()) {
            echo "Sorry, you can't thank your own post.";
            return;
        }
        if ($post->isThankedBy($this->user->getUid())) {
            $statement = $this->database->prepare("DELETE FROM " . FORUM_DB . ".thanks WHERE thanker=? AND post_id=?");
            if (!$statement->execute(array($this->user->getUid(), $post->getPostId()))) {
                echo "Sorry, an error occured, please contact a system administrator.";
                return;
            }
        } else {
            $statement = $this->database->prepare("INSERT INTO " . FORUM_DB . ".thanks (thanker,recipient_id,post_id) VALUES(?,?,?)");
            if (!$statement->execute(array($this->user->getUid(), $owner->getUid(), $post->getPostId()))) {
                echo "Sorry, an error occured, please contact a system administrator.";
                return;
            }
        }
        $post->setThankData();
        $thanks = "<p>";
        foreach ($post->getThanks() as $thanker) {
            $o = User::getUser($thanker);
            if (!$o) {
                continue;
            }
            $thanks .= $o->getModule("UserTools")->getFormatUsername(true) . ",";
        }
        $thanks = rtrim($thanks, ",");
        $thanks .= "</p>";
        echo "SUCCESS " . ($thanks == "<p></p>" ? "" : "<h3>Thankful users:</h3>" . $thanks);
    }
}

?>