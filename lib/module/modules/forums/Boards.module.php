<?php

/**
 * A module used to load the forum boards.
 * @author Adam Rodrigues
 *
 */
class Boards extends Module
{

    /**
     * Loads the boards of the forum.
     */
    public function loadBoards()
    {
        $fm = Registry::get("sys")->getForumManager();
        $boards = $this->database->query("SELECT * FROM " . FORUM_DB . ".boards");
        $templates = array();
        while ($board = $boards->fetch(PDO::FETCH_ASSOC)) {
            $b = Board::create($board);
            if ($board['hide'] == 1 || !$b->isViewable()) {
                continue;
            }
            $template = TemplateManager::load("ForumBoard");
            $template->insert("catId", $board['board_id']);
            $template->insert("title", $board['title']);
            $template->insert("description", $board['description']);
            $template->insert("numThreads", $fm->getTotalThreads($board['board_id']));
            $template->insert("numPosts", $fm->getTotalBoardPosts($board['board_id']));
            $templates[$board['board_id']] = $template;
        }
        $order = array();
        $setting = $fm->getForumSettings()->getSetting("board_order");
        $data = explode(',', $setting->getValue());
        foreach ($data as $id) {
            $order[$id] = $id;
        }
        foreach ($order as $id) {
            if (array_key_exists($id, $templates)) {
                $template = $templates[$id];
                $template->display();
                unset($order[$id]);
                unset($templates[$id]);
            }
        }
        if (sizeof($templates) > 0) {
            foreach ($templates as $template) {
                $template->display();
            }
        }
    }

    /**
     * Loads the board menu for thread creation.
     */
    public function loadBoardMenu()
    {
        $fm = Registry::get("sys")->getForumManager();
        $boards = $this->database->query("SELECT * FROM " . FORUM_DB . ".boards");
        $board;
        while ($boardData = $boards->fetch(PDO::FETCH_ASSOC)) {
            $board = $fm->getBoardByData($boardData);
            if ($board->isHidden() || !$board->isViewable() || !$board->isPostable()) {
                continue;
            }
            echo " <option value=\"" . $board->getBoardId() . "\"\>" . $board->getTitle() . "</option>";
        }
    }

}

?>