<?php
define('ICON_DIR', "/lib/images/icons");
define('ICON_MOD', ICON_DIR . "/moderator.png");
define('ICON_ADMIN', ICON_DIR . "/admin.png");
define('ICON_EXT_DONATOR', ICON_DIR . "/extreme.png");
define('ICON_DONATOR', ICON_DIR . "/regular.png");
define("USERBARS", "/lib/images/forums/userbars/");

/**
 * Tools relating to the user account.
 * @author Adam Rodrigues
 *
 */
class UserTools extends Module
{

    /**
     * The post count ranks.
     */
    private static $RANKS = array(
        array(0, 'bronze'),
        array(20, 'iron'),
        array(40, 'steel'),
        array(60, 'black'),
        array(80, 'mithril'),
        array(110, 'adamant'),
        array(150, 'rune'),
        array(350, 'dragon'),
        array(500, 'bandos')
    );

    /**
     * An array of tempates used.
     */
    private $templates = array();

    /**
     * Gets the formatted username.
     * @param htmlEffects if we should show html affects.
     */
    public function getFormatUsername($htmlEffects = false)
    {
        if (array_key_exists("username", $this->templates)) {
            return $this->templates['username']->getTemplateContents();
        }
        $this->template->reset();
        $username = $this->user->getFormatUsername();
        if ($htmlEffects) {
            $this->appendOutput("<a href='/community/members/index.php?name=" . $this->user->getUsername() . "'><span class='username' style='" . $this->getUsernameStyle() . "'>");
            $iconPath = $this->getIcon();
            if ($iconPath) {
                $this->appendOutput("<img style='margin-right: 1px;' src='" . $iconPath . "'>");
            }
            $this->appendOutput($username);
            $this->appendOutput("</span></a>");
        } else {
            $this->appendOutput($username);
        }
        $templates['username'] = $this->template;
        return $this->getTemplateContents();
    }

    /**
     * Gets the group name.
     */
    public function getGroupName()
    {
        switch ($this->user->getRights()) {
            case 2:
                return "Administrator";
            case 1:
                return "Moderator";
        }
        switch ($this->user->getDonatorType()) {
            case 1:
                return "Extreme Donator";
            case 0:
                return "Donator";
        }
        return "Member";
    }

    /**
     * Gets the group rank.
     */
    public function getGroupRank()
    {
        $posts = Registry::get("user")->getPostCount();
        for ($i = sizeof(self::$RANKS) - 1; $i >= 0; $i--) {
            if ($posts >= self::$RANKS[$i][0]) {
                return "<img src=\"/lib/images/forums/ranks/" . SELF::$RANKS[$i][1] . ".png\">";
            }
        }
        return "";
    }

    /**
     * Gets the icon path of a user.
     */
    private function getIcon()
    {
        $rights = $this->user->getRights();
        $donatorType = $this->user->getDonatorType();
        switch ($rights) {
            case 2:
                return ICON_ADMIN;
            case 1:
                return ICON_MOD;
        }
        switch ($this->user->getDonatorType()) {
            case 1:
                return ICON_EXT_DONATOR;
            case 0:
                return ICON_DONATOR;
        }
    }

    /**
     * Gets the username style.
     */
    public function getUsernameStyle()
    {
        switch ($this->user->getRights()) {
            case 2:
                return "color: #ffc600;";
            case 1:
                return "color: #bebebe;";
        }
        switch ($this->user->getDonatorType()) {
            case 1:
                return "color: #990000;";
            case 0:
                return "color: #006600;";
        }
    }

    /**
     * Gets the group userbars.
     */
    public function getGroups()
    {
        $groups = "";
        if ($this->user->getRights() == 0) {
            $groups .= $this->getGroupLink("Member.png");
            switch ($this->user->getDonatorType()) {
                case 1:
                    $groups .= $this->getGroupLink("ExtremeDonator.png");
                    break;
                case 0:
                    $groups .= $this->getGroupLink("Donator.png");
                    break;
            }
            switch ($this->user->getIronmanMode()) {
                case 'STANDARD';
                    $groups .= $this->getGroupLink("Ironman.png");
                    break;
                case 'ULTIMATE';
                    $groups .= $this->getGroupLink("UltimateIronman.png");
                    break;
            }
        } else {
            switch ($this->user->getRights()) {
                case 2:
                    $groups .= $this->getGroupLink("Administrator.png");
                    break;
                case 1:
                    $groups .= $this->getGroupLink("Moderator.png");
                    break;
            }
        }
        return $groups;
    }

    /**
     * Gets the group link.
     * @param name The name of the group image.
     */
    private function getGroupLink($name)
    {
        return "<img src=\"" . USERBARS . $name . "\">";
    }

}

?>	