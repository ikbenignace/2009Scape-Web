<?php

/*
 * The main highscores manager. 
 */

class Highscores
{

    private $user;

    /**
     * Dumps scores for a selected skill
     * @param number $skill - skill to be displayed
     * @param number $start - (optional) starting display number (for pages)
     */
    public static function dumpScores($skill, $type, $start = 1)
    {
        echo "<ul class='player-row stats-header'>
				<li class='rank'>#</li>
				<li class='player'>Username</li>
				<li class='lvl'>Level</li>
				<li class='exp'>Experience / Weekly Gain</li>
			  </ul>";
        $database = Registry::get("database");
        $query;
        if ($skill == -1) {
            $query = $database->query("SELECT * FROM highscores WHERE ironManMode='" . $type . "' ORDER BY total_level DESC, overall_xp DESC LIMIT 30 OFFSET " . ($start - 1) . "");
        } else {
            $query = $database->query("SELECT * FROM highscores WHERE ironManMode='" . $type . "' ORDER BY xp_" . $skill . " DESC LIMIT 30 OFFSET " . ($start - 1) . "");
        }
        if ($query) {
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $icon = "";
                if ($type == "NONE") {
                    $query3 = $database->query("SELECT rights, donatorType FROM members WHERE username='" . $row['username'] . "' LIMIT 1");
                    $row3 = $query3->fetch();
                    $donator = $row3['donatorType'];
                    $rights = $row3['rights'];
                    if ($rights == 1) {
                        $icon = "<img src='/lib/images/icons/moderator.png'/>";
                    } else if ($donator == 0) {
                        $icon = "<img src='/lib/images/icons/regular.png'/>";
                    } else if ($donator == 1) {
                        $icon = "<img src='/lib/images/icons/extreme.png'/>";
                    }
                } else if ($type == "standard") {
                    $icon = "<img src='/lib/images/icons/standard.png'/>";
                } else if ($type == "ultimate") {
                    $icon = "<img src='/lib/images/icons/ultimate.png'/>";
                }
                if ($skill == -1) {
                    echo "
					<ul class='player-row'>
						<li class='rank'>" . $start++ . "</li>
						<li class='player'><div data-username='" . $row['username'] . "'>" . $icon . " " . ucwords(str_replace("_", " ", $row['username'])) . "</div></li>
						<li class='lvl'>" . number_format($row['total_level']) . "</li>
						<li class='exp'><span style='width: 80px; display:block; float: left;'>" . number_format($row['overall_xp']) . "</span></li>
					</ul>
					";
                } else {
                    echo "
					<ul class='player-row'>
						<li class='rank'>" . $start++ . "</li>
						<li class='player'><div data-username='" . $row['username'] . "'>" . $icon . " " . ucwords(str_replace("_", " ", $row['username'])) . "</div></li>
						<li class='lvl'>" . number_format(self::getLevelForXP($row['xp_' . $skill])) . "</li>
						<li class='exp'><span style='width: 80px; display:block; float: left;'>" . number_format($row['xp_' . $skill]) . "</span></li>
					</ul>
					";
                }
            }
        }

    }

    /**
     * Returns equivalent experience for a given level
     * @param int $level
     * @return number
     */
    private static function getExperienceForLevel($level)
    {
        $points = 0;
        $output = 0;
        for ($lvl = 1; $lvl <= $level; $lvl++) {
            $points += floor($lvl + 300.0 * pow(2.0, $lvl / 7.0));
            if ($lvl >= $level) {
                return $output;
            }
            $output = floor($points / 4);
        }
        return 0;
    }

    /**
     * Returns a level for the given experience
     * @param int $experience
     * @return number
     */
    public static function getLevelForXP($experience)
    {
        $level = 0;
        for ($i = 1; $i <= 99; $i++) {
            if (self::getExperienceForLevel($i) <= $experience) {
                $level = $i;
            }
        }
        return $level;
    }

    /*
     * Begin user based functions
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Dumps a user's personal highscores
     */
    public function dumpPersonal()
    {
        $row = Registry::get("database")->query("SELECT * FROM highscores WHERE username='" . $this->user->getUsername() . "'")->fetch();
        if ($row) {
            echo "<span class='hs-back'><i class='fa fa-arrow-left'></i> Back</span><span style='display: block; padding: 10px 0; text-align: center;'>Viewing skills for player: " . $this->user->getFormatUsername() . "</span>";
            for ($i = 0; $i < 24; $i++) {
                $xp = $row['xp_' . $i];
                $level = self::getLevelForXP($xp);
                $percentage = 0.0;
                if ($level == 99) {
                    $percentage = 1.0;
                } else {
                    $base = self::getExperienceForLevel($level);
                    $nextxp = self::getExperienceForLevel($level + 1);
                    $percentage = ($xp - $base) / ($nextxp - $base);
                }
                echo '<div class="personal-skill" id="skill' . $i . '" data-percentage="' . $percentage . '"><img src="/lib/images/skills/hd/' . $i . '.png"/><span>' . $level . '</span><div class="hangdown"><div class="exp">' . number_format($xp) . '</div></div></div>';
            }
        }
    }


}

?>