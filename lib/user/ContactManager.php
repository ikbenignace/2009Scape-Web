<?php

/**
 * Manages a users contacts.
 * @author Adam Rodrigues
 *
 */
class ContactManager
{

    /**
     * The friend names.
     */
    private $friends = array();

    /**
     * The blocked names.
     */
    private $blocked = array();

    /**
     * The ranks of friends.
     */
    private $ranks = array();

    /**
     * The user instance.
     */
    private $user;

    /**
     * Constructs the contact manager.
     * @param user The user.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->configure();
    }

    /**
     * Configures the manager.
     */
    public function configure()
    {
        $split = explode('~', $this->user->getData("contacts"));
        foreach ($split as $data) {
            $data = str_replace("}", "", str_replace("{", "", $data));
            $l = explode(',', $data);
            $name = $l[0];
            if ($name == "" || strlen($name) < 1) {
                continue;
            }
            if (sizeof($l) < 2) {
                continue;
            }
            $this->ranks[$name] = $l[1];
            $this->friends[$name] = $name;
        }
        $split = explode(',', $this->user->getData("blocked"));
        foreach ($split as $name) {
            $this->blocked[$name] = $name;
        }
    }

    /**
     * Writes the contact manager to the database.
     */
    public function write()
    {
        $f = "";
        $count = 0;
        foreach ($this->friends as $friend) {
            $f .= "{" . $friend . "," . $this->ranks[$friend] . "}" . ($count == sizeof($this->friends) - 1 ? "" : "~");
            $count++;
        }
        $count = 0;
        $b = "";
        foreach ($this->blocked as $block) {
            $b .= $block . ($count == sizeof($this->blocked) - 1 ? "" : ",");
            $count++;
        }
        $this->user->setData("contacts", $f);
        $this->user->setData("blocked", $b);
        $this->user->write();
    }

    /**
     * Adds a contact.
     * @param name The name.
     * @param ignore If an ignore.
     */
    public function add($name, $ignore)
    {
        $ignore ? $this->blocked[$name] = $name : $this->friends[$name] = $name;
        if (!$ignore) {
            $this->ranks[$name] = 1;
        }
        $this->write();
    }

    /**
     * Removes a contact.
     * @param name The name.
     * @param ignore If an ignore.
     */
    public function remove($name, $ignore)
    {
        if (!$ignore) {
            unset($this->friends[$name]);
            unset($this->ranks[$name]);
        } else {
            unset($this->blocked[$name]);
        }
        $this->write();
    }

    /**
     * Checks if a user is a friend.
     * @param name The name.
     */
    public function isFriend($name)
    {
        return array_key_exists($name, $this->friends);
    }

    /**
     * Checks if a name is blocked.
     * @param name The name.
     */
    public function isBlocked($name)
    {
        return array_key_exists($name, $this->blocked);
    }

    /**
     * Gets the friend list.
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * Gets the blocked list.
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

}

?>