<?php

/**
 * The player information module.
 * @author adam
 *
 */
class PlayerInformation extends Module
{

    public function load()
    {
        $this->template = TemplateManager::load("PlayerInfo");
        $this->template->insert("username", $this->user->getModule("UserTools")->getFormatUsername(true));
        $this->template->insert("email", $this->user->getEmail());
        $this->template->insert("credits", $this->user->getCredits());
        $this->template->insert("donationTotal", $this->user->getDonationTotal());
        $this->template->insert("joinedClan", $this->user->getData("currentClan"));
        $this->template->insert("clanName", empty($this->user->getData("clanName")) ? "No clan name" : $this->user->getData("clanName"));
        $this->display();
    }

}

?>