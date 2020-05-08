<?php

/**
 * Handles the voting module.
 * @author Adam Rodrigues
 *
 */
class VotingModule extends Module
{

    /**
     * Loads the voting sites.
     */
    public function load()
    {
        $this->template = TemplateManager::load("VoteBox");
        $statement = $this->database->query("SELECT * FROM voting_sites");
        while ($site = $statement->fetch(PDO::FETCH_ASSOC)) {
            $canVote = $this->canVote($site, $this->user->getUsername());
            $this->template->insert("name", $site['name']);
            $this->template->insert("color", $canVote ? "blue" : "red");
            $this->template->insert("canVote", $canVote ? "can" : "cannot");
            $this->display();
        }
    }

    /**
     * Checks if the user can vote on a site.
     * @param site The site.
     */
    public function canVote($site, $username)
    {
        $statement = $this->database->prepare("SELECT * FROM votes WHERE username=? AND site=? AND timestamp >= DATE_SUB(NOW(), INTERVAL " . $site['wait'] . " HOUR)") or die ($this->database->getError());
        if ($statement->execute(array($username, $site['name']))) {
            if ($statement->rowCount() > 0) {
                return false;
            }
        }
        return true;
    }

}

?>