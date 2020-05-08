<?php

/**
 * Handles a highscore data request.
 * @author Adam Rodrigues
 *
 */
class HiscoresRequest extends Action
{

    /**
     * Handles a hiscore request.
     */
    public function handle($cleaned)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/hiscores/Highscores.php");
        if (sizeof($cleaned) == 1) {
            $instanced = new User();
            $instanced->create($cleaned['username']);
            if ($instanced->exists()) {
                if ($instanced->getRights() < 2) {
                    $hs = new Highscores($instanced);
                    $hs->dumpPersonal();
                } else {
                    echo "<span style='display: block; padding: 10px 0; text-align: center;'>Administrators not included.</span>";
                }
            } else {
                echo "<span style='display: block; padding: 10px 0; text-align: center;'>User not found</span>";
            }
        } else {
            Highscores::dumpScores($cleaned['skill'], $cleaned['type'], $cleaned['start']);
        }
    }

}

?>