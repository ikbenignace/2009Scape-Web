<?php

/**
 * The module assigned for navigation purposes.
 * @author Adam Rodrigues
 *
 */
class Navigation
{

    /**
     * Loads the navigation bar for the forums.
     */
    public function loadNavigation()
    {
        $fm = Registry::get("sys")->getForumManager();
        $span = "Forums Home";
        $a = "<a><i class='fa fa-arrow-right'></i>";
        if (isset($_GET['board_id']) && isset($_GET['page'])) {
            $board = $fm->getBoard($_GET['board_id']);
            if (!$board) {
                return;
            }
            $span = "" . $board->getTitle();
            $a = "<a href=\"/community\"><i class='fa fa-arrow-left'></i> Back";
        }
        echo "<div class=\"titleBar\" style=\"width:650px;padding:13px; margin: 1px auto 0 auto; margin-bottom: 6px;\">
		" . $a . "</a><span>Viewing: " . $span . "</span>
		</div>";
    }

}

?>