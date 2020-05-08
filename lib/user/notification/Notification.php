<?php

/**
 * Represents a notification.
 * @author Adam Rodrigues
 *
 */
class Notification
{

    /**
     * The notification data.
     */
    private $data;

    /**
     * Constructs the notification object.
     * @param data The data of the notification.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the user id of the notification.
     * @return The user id associative.
     */
    public function getUid()
    {
        return $this->data['uid'];
    }

    /**
     * Gets the entry id of the notification.
     * @return The entry id of the notification.
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * Gets the date of the notification.
     */
    public function getDate()
    {
        return $this->data['date'];
    }

    /**
     * Gets the notification html content.
     * @return The notification html content.
     */
    public function getNotification()
    {
        return $this->data['notification'];
    }

    /**
     * Gets the data for the notification.
     * @return The notification data.
     */
    public function getData()
    {
        return $this->data;
    }

}

?>