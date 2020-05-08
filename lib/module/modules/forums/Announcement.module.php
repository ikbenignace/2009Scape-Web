<?php

/**
 * The announcment module.
 * @author Adam Rodrigues
 *
 */
class Announcement extends Module
{

    /**
     * Loads the announcment module.
     */
    public function load()
    {
        $setting = Registry::get("sys")->getForumManager()->getForumSettings()->getSetting("announcement");
        $this->template = TemplateManager::load("Announcement");
        $data = explode('~', $setting->getValue());
        $this->template->insert("title", $data[0]);
        $this->template->insert("message", $data[1]);
        $this->display();
    }

}

?>