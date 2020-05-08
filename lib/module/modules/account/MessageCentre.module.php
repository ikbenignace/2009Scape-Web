<?php

/**
 * The module to hadle the message centre.
 * @author Adam Rodrigues
 *
 */
class MessageCentre extends Module
{

    /**
     * The array of messages.
     */
    private $messages = array();

    /**
     * The array of received messages.
     */
    private $received = array();

    /**
     * The array of sent messages.
     */
    private $sent = array();

    /**
     * The array of read messages.
     */
    private $read = array();

    /**
     * Handles a sent action.
     * @param action The action.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        $this->configure();
        switch ($action) {
            case "refresh":
                if (!Registry::get("sys")->getSecurityManager()->securityCheck("MESSAGE_CENTRE")) {
                    echo "Sorry, you have refreshed too many times in a short while, please wait.";
                    return;
                }
                $this->template = TemplateManager::load("MessageCentre");
                $messageTemplate = TemplateManager::load("Messages");

                $messageTemplate->insert("first", "From");
                $messageTemplate->insert("messages", $this->getMessages("received"));
                $this->template->insert("received", $messageTemplate->getContents());

                $messageTemplate->reset();
                $messageTemplate->insert("first", "To");
                $messageTemplate->insert("messages", $this->getMessages("sent"));
                $this->template->insert("sent", $messageTemplate->getContents());

                $messageTemplate->reset();
                $messageTemplate->insert("first", "From");
                $messageTemplate->insert("messages", $this->getMessages("read"));
                $this->template->insert("read", $messageTemplate->getContents());
                echo "SUCCESS " . $this->template->getContents();
                break;
            case "reply":
                $this->reply($cleaned);
                break;
            case "compose":
                $this->compose($cleaned);
                break;
            case "delete":
                $this->delete($cleaned['id']);
                break;
        }
    }

    /**
     * Composes a message.
     * @param cleaned The parameters.
     */
    private function compose($cleaned)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("MESSAGE_CENTRE")) {
            echo "Sorry, you have sent too many requests in a short amount of time. Please wait.";
            return;
        }
        if (!isset($cleaned['recipient']) || !isset($cleaned['subject']) || !isset($cleaned['subject'])) {
            echo "Error: Please contact a system administrator.";
            return;
        }
        if (strlen($cleaned['recipient']) < 1) {
            echo "Please enter the username you wish to send this message to.";
            return;
        }
        $recipient = strtolower(str_replace(" ", "_", $cleaned['recipient']));
        if ($recipient == $this->user->getUsername()) {
            echo "Sorry, you can't send messages to yourself!";
            return;
        }
        $ruser = User::getByName($recipient);
        if (!$ruser) {
            echo "Sorry, there is no user with that username " . $recipient;
            return;
        }
        $subject = $cleaned['subject'];
        if (strlen($subject) < 6) {
            echo "The subject must be 6 or more characters.";
            return;
        }
        if (!preg_match('/^[a-z0-9 .\-,!@#$%^&*();:]+$/i', $subject)) {
            echo "The subject you have entered is using invalid characters.";
            return;
        }
        $html = Utils::purify($cleaned['html']);
        if (strlen($html) < 6) {
            echo "Your message must be 6 characters or more.";
            return;
        }
        if (strlen($html) > 10000) {
            echo "Your message can't exceed 10000 characters.";
            return;
        }
        if (!$this->create($this->user->getUsername(), $recipient, $subject, $cleaned['html'])) {
            echo "Sorry, your request could not be approved at this time. Please try again later.";
            return;
        }
        echo "SUCCESS /account/index.php?tab=5";
        $u = User::getByName($recipient);
        if (!$u) {
            return true;
        }
        $id = $this->database->getConnection()->lastInsertId();
        $u->getNotificationManager()->sendNotification($this->user->getModule("UserTools")->getFormatUsername(true) . " sent you a message: <a href='/account/message/index.php?id=" . $id . "'>" . $subject . "</a>");

    }

    /**
     * Deletes a message.
     * @param id The id of the message.
     */
    private function delete($id)
    {
        $message = $this->getMessage($id);
        if (!$message) {
            echo "Sorry, that message could not be found. It may have been deleted already.";
            return;
        }
        $isMessage = $message->getSender() == $this->user->getUsername();
        if ($isMessage && $message->isRecipientDelete()) {
            $message->remove();
        } else {
            $message->delete($isMessage ? true : false);
        }
        echo "SUCCESS";
    }

    /**
     * Opens a message convo.
     * @param id The id.
     */
    public function open($id)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("MESSAGE_CENTRE")) {
            echo "Sorry, you have sent too many requests in a short amount of time. Please wait.";
            return;
        }
        $this->configure();
        $message = $this->getMessage($id);
        if (!$message) {
            echo "Sorry, that message could not be found. It may have been deleted.";
            return;
        }
        $owner = User::getByName($message->getSender());
        if (!$owner) {
            echo "Sorry, please contact a system administrator.";
            return;
        }
        $recipient = User::getByName($message->getRecipient());
        if (!$recipient) {
            echo "Sorry, please contact a system administrator. #3493";
            return;
        }
        $this->template = TemplateManager::load("MessageConvo");
        $this->template->insert("username", $owner->getModule("UserTools")->getFormatUsername(true));
        $this->template->insert("profileImage", $owner->getProfileImage());
        $this->template->insert("content", $message->getContent());
        $this->template->insert("date", Utils::formatTime($message->getDate()));
        $this->template->insert("subject", $message->getSubject());
        $this->template->insert("id", $message->getId());
        $this->template->insert("recipient", $recipient->getModule("UserTools")->getFormatUsername(true));
        $this->display();
        if (!$message->isRead() && $message->getSender() != $this->user->getUsername()) {
            $message->setRead();
        }
    }

    /**
     * Replys to a message.
     * @param cleaned The parameters.
     */
    public function reply($cleaned)
    {
        if (!Registry::get("sys")->getSecurityManager()->securityCheck("MESSAGE_CENTRE")) {
            echo "Sorry, you have sent too many requests in a short amount of time. Please wait.";
            return;
        }
        if (!isset($cleaned['id'])) {
            echo "Sorry, please a contact a system administrator. #454";
            return;
        }
        $this->configure();
        $message = $this->getMessage($cleaned['id']);
        if (!$message) {
            echo "Sorry, an error occured please contact your system administrator.";
            return;
        }
        $html = Utils::purify($cleaned['html']);
        if (strlen($html) < 6) {
            echo "Your message must be 6 or more characters.";
            return;
        } else if (strlen($html) > 10000) {
            echo "Your message can't exceed 10000 characters.";
            return;
        }
        $isMessage = $message->getSender() == $this->user->getUsername();
        $subject = $message->getSubject();
        if (!$isMessage && !$message->isReply()) {
            $subject = "RE: " . $message->getSubject();
        }
        if (!$this->create($this->user->getUsername(), $isMessage ? $message->getRecipient() : $message->getSender(), $subject, $cleaned['html'])) {
            echo "Sorry, your request could not be approved at this time. Please try again later.";
            return;
        }
        echo "SUCCESS /account/message/index.php?id=" . $this->database->getConnection()->lastInsertId();
    }

    /**
     * Loads the message centre.
     */
    public function load()
    {
        echo "<h3 style=\"margin-left:20px;\">Your Message Centre</h3>";
        echo "<div class=\"message-centre\">";
        $this->configure();
        $this->template = TemplateManager::load("MessageCentre");
        $messageTemplate = TemplateManager::load("Messages");

        $messageTemplate->insert("first", "From");
        $messageTemplate->insert("messages", $this->getMessages("received"));
        $this->template->insert("received", $messageTemplate->getContents());

        $messageTemplate->reset();
        $messageTemplate->insert("first", "To");
        $messageTemplate->insert("messages", $this->getMessages("sent"));
        $this->template->insert("sent", $messageTemplate->getContents());

        $messageTemplate->reset();
        $messageTemplate->insert("first", "From");
        $messageTemplate->insert("messages", $this->getMessages("read"));
        $this->template->insert("read", $messageTemplate->getContents());
        $this->display();
        echo "</div>";
    }

    /**
     * Configures the message centre.
     */
    public function configure()
    {
        $this->messages = array();
        $this->sent = array();
        $this->read = array();
        $this->receieved = array();
        $user = $this->user;
        $statement = $this->database->query("SELECT * FROM messages WHERE sender='" . $user->getUsername() . "' OR recipient='" . $user->getUsername() . "' ORDER BY date DESC");
        while ($message = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($message['sender'] == $user->getUsername() && $message['s_delete'] == 1 || $message['recipient'] == $user->getUsername() && $message['r_delete'] == 1) {
                continue;
            }
            $object = new Message($message);
            $this->messages[$message['id']] = $object;
            if ($message['sender'] == $user->getUsername()) {
                $this->sent[$message['id']] = $object;
            } else if ($message['recipient'] == $user->getUsername() && $message['is_read'] == 0) {
                $this->receieved[$message['id']] = $object;
            } else if ($message['recipient'] == $user->getUsername() && $message['is_read'] == 1) {
                $this->read[$message['id']] = $object;
            }
            //echo "Put message in messages id = " . $message['id'] . "\n";
        }
        //echo "Size of " . sizeof($this -> messages);
    }

    /**
     * Creates a message.
     * @param sender The sender of the message.
     * @param recipient The recipient of the message.
     * @param subject The message subject.
     * @param html The html content.
     */
    private function create($sender, $recipient, $subject, $html)
    {
        if (empty($sender) || empty($recipient) || empty($subject) || empty($html)) {
            Registry::get("sys")->log("Message Centre: Error creating a message.");
            return false;
        }
        if (strlen($sender) == 0 || strlen($recipient) == 0 || strlen($subject) == 0 || strlen($html) == 0) {
            Registry::get("sys")->log("Message Centre: Error creating a message.");
            return false;
        }
        $date = new DateTime('now');
        $statement = $this->database->query("INSERT INTO messages (sender, recipient, subject, content, date) VALUES('" . $sender . "', '" . $recipient . "', '" . $subject . "', '" . $html . "', NOW())");
        return true;
    }

    /**
     * Gets the html content for the table of messages.
     * @param identifier the identifier of which type.
     */
    public function getMessages($identifier)
    {
        $html = "";
        switch ($identifier) {
            case "received":
                foreach ($this->receieved as $message) {
                    $html .= $message->displayTable(true);
                }
                break;
            case "sent":
                foreach ($this->sent as $message) {
                    $html .= $message->displayTable(false);
                }
                break;
            case "read":
                foreach ($this->read as $message) {
                    $html .= $message->displayTable(true);
                }
                break;
        }
        return $html;
    }

    /**
     * Gets a message from the id.
     * @param id The message primary id.
     */
    public function getMessage($id)
    {
        if (!array_key_exists($id, $this->messages)) {
            return false;
        }
        return $this->messages[$id];
    }

}

/**
 * Represents a message in the message centre.
 * @author Adam Rodrigues
 *
 */
class Message
{

    /**
     * The message data.
     */
    private $data;

    /**
     * Constructs a message.
     * @param data The data.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Displays the table content.
     */
    public function displayTable($sender)
    {
        return "<tr><td><i>" . Utils::getFormatUsername(($sender ? $this->getSender() : $this->getRecipient())) . "</i></td> <td><a class='table-message' data-id='" . $this->getId() . "' href='#'>" . $this->getSubject() . "</a></td> <td>" . $this->formatTime($this->getDate()) . " <a href='#'><i data-id='" . $this->getId() . "' class='fa fa-trash' id='delete'></i></a></td></tr>";
    }

    /**
     * Removes the message.
     */
    public function remove()
    {
        $statement = Registry::get("database")->query("DELETE FROM messages WHERE id=?");
        $statement->bindParam(1, $this->getId());
        $statement->execute();
    }

    /**
     * Formats a time stamp.
     * @param time The time stamp.
     */
    public function formatTime($time)
    {
        $timestamp = strtotime($time);
        return date("F d, Y - g:i A", $timestamp);
    }

    /**
     * Sets the message to being read.
     */
    public function setRead()
    {
        $statement = Registry::get("database")->query("UPDATE messages SET is_read=1 WHERE id=?");
        $statement->bindParam(1, $this->getId());
        $statement->execute();
    }

    /**
     * Deletes the message for a user.
     * @param sender The sender.
     */
    public function delete($sender)
    {
        $delete = ($sender ? "r" : "s") . "_delete=1";
        $statement = Registry::get("database")->query("UPDATE messages SET " . $delete . " WHERE id=?");
        $statement->bindParam(1, $this->getId());
        $statement->execute();
    }

    /**
     * Checks if the message is a reply.
     */
    public function isReply()
    {
        return Utils::startsWith($this->getSubject(), "RE:");
    }

    /**
     * Checks if the message has been read.
     */
    public function isRead()
    {
        return $this->data['is_read'] == 1;
    }

    /**
     * Checks if the recipient has deleted the message.
     */
    public function isRecipientDelete()
    {
        return $this->data['r_delete'];
    }

    /**
     * Checks if the sender deleted the message.
     */
    public function isSenderDelete()
    {
        return $this->data['s_delete'];
    }

    /**
     * Gets the date.
     */
    public function getDate()
    {
        return $this->data['date'];
    }

    /**
     * Gets the content.
     */
    public function getContent()
    {
        return $this->data['content'];
    }

    /**
     * Gets the subject.
     */
    public function getSubject()
    {
        return $this->data['subject'];
    }

    /**
     * Gets the sender uid.
     */
    public function getSender()
    {
        return $this->data['sender'];
    }

    /**
     * Gets the recipient uid.
     */
    public function getRecipient()
    {
        return $this->data['recipient'];
    }

    /**
     * Gets the id of the message.
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Gets the data.
     */
    public function getData()
    {
        return $this->data;
    }

}

?>