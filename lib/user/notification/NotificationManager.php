<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/notification/Notification.php");

/**
 * A managing class for notifications.
 * @author Adam Rodrigues
 *
 */
class NotificationManager
{

    /**
     * The user instance.
     */
    private $user;

    /**
     * The database instance.
     */
    private $database;

    /**
     * The notification array.
     */
    private $notifications = array();

    /**
     * The notify count.
     */
    private $count;

    /**
     * Constrcuts the notification manager.
     * @param user The user instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->database = $user->getDatabase();
        if ($this->user->getUsername() == "vexia") {
            $this->configure();
        }
    }

    /**
     * Configures the notification manager.
     */
    public function configure()
    {
        $statement = $this->database->query("SELECT * FROM " . FORUM_DB . ".notifications WHERE uid='" . $this->user->getUid() . "' ORDER BY date DESC");
        while ($notification = $statement->fetch(PDO::FETCH_ASSOC)) {
            $this->notifications[$notification['id']] = new Notification($notification);
            if ($notification['opened'] == 0) {
                $this->count++;
            }
        }
    }

    /**
     * Sends a notificiation.
     * @param notification The notification.
     */
    public function sendNotification($notification)
    {
        $date = new DateTime('now');
        $statement = $this->database->prepare("INSERT INTO " . FORUM_DB . ".notifications (uid,notification,date) VALUES(?,?,?)");
        $statement->execute(array($this->user->getUid(), $notification, $date->format('Y-m-d H:i:s')));
    }

    /**
     * Deletes a notification.
     * @param id The notification id.
     */
    public function delete($id)
    {
        if (!$this->hasNotification($id)) {
            return false;
        }
    }

    /**
     * Gets the notification count.
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Checks if there is a notification with this id.
     * @param id The id.
     */
    public function hasNotification($id)
    {
        return array_key_exists($id, $this->notifications);
    }

    /**
     * Gets the notifications.
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

}

?>