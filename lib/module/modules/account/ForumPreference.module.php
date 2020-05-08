<?php
define("SIGNATURE_LENGTH", 600);
define("IMAGE_AMOUNT", 2);
define("LINE_AMOUNT", 8);

/**
 * The module to handle forum preferences.
 * @author Adam Rodrigues
 *
 */
class ForumPreference extends Module
{

    /**
     * Loads the forum preference tab.
     */
    public function load()
    {
        $this->template = TemplateManager::load("ForumPreference");
        $this->template->insert("profileImage", $this->user->getProfileImage());
        $this->template->insert("signature", $this->user->getSignature());
        $this->display();
    }

    /**
     * Handles a sent action.
     * @param action The action.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        switch ($action) {
            case "updateProfileImage":
                $this->updateProfileImage($cleaned);
                break;
            case "editSignature":
                $this->editSignature($cleaned['html']);
                break;
        }
    }

    /**
     * Updates the profile image.
     * @param cleaned The cleaned parameters.
     */
    private function updateProfileImage($cleaned)
    {
        $value = $cleaned['profileImage'];
        $purify = Utils::purify($value);
        if ($purify == $value && @getImageSize($purify) != false) {
            $this->user->setData("profileImage", $value);
            $this->user->write();
            echo "SUCCESS " . $purify;
            return;
        }
        echo "Sorry, that image URL is invalid.";
    }

    /**
     * Edits the signature.
     * @param html The html content.
     */
    private function editSignature($html)
    {
        if (strlen($html) > SIGNATURE_LENGTH) {
            echo "Your signature cannot be longer than " . SIGNATURE_LENGTH . " characters including HTML code markup.";
            return;
        }
        $imgAmount = substr_count($html, "<img") + substr_count($html, "&lt;img");
        if ($imgAmount > IMAGE_AMOUNT) {
            echo "You have included a total of " . $imgAmount . " images in your message. The maximum number that you may include is " . IMAGE_AMOUNT . ". Please correct the problem and then continue again.";
            return;
        }
        $lineAmount = substr_count($html, "<br") + substr_count($html, "&lt;br");
        if ($lineAmount > LINE_AMOUNT) {
            echo "Your signature contains too many lines and must be shortened. You may only have up to " . LINE_AMOUNT . " line(s). Long text may have been implicitly wrapped, causing it to be counted as multiple lines.";
            return;
        }
        $user = $this->user;
        $user->setData("signature", $html);
        $user->write();
        echo "SUCCESS";
    }
}

?>