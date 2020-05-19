<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/ContactManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/shop/ShopManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/notification/NotificationManager.php");

/**
 * Represents the users information of an Arios account.
 * @author Adam Rodrigues
 *
 */
class User
{

    /**
     * The aray of loaded users.
     */
    private static $LOADED = array();

    /**
     * The table keys for PDO dynamic saving and loading.
     */
    private static $TABLE_KEYS = array();

    /**
     * The connection to the database for the user.
     */
    private $db;

    /**
     * The users data in the database.
     */
    private $data;

    /**
     * If the user is logged in.
     */
    private $loggedIn;

    /**
     * If the user exists or not.
     */
    private $exists = false;

    /**
     * The module manager.
     */
    private $moduleManager;

    /**
     * The contact manager.
     */
    private $contactManager;

    /**
     * The shop manager.
     */
    private $shopManager;

    /**
     * The notification manager.
     */
    private $notificationManager;

    /**
     * Constructs the User object.
     */
    public function __construct()
    {
        $this->db = Registry::get("database");
        $metaQuery = $this->db->query("SELECT * FROM members LIMIT 1");
        for ($i = 0; $i < $metaQuery->columnCount(); $i++) {
            $meta = $metaQuery->getColumnMeta($i);
            array_push(self::$TABLE_KEYS, $meta['name']);
        }
    }

    /**
     * Retrieves a users instance.
     * @param uid The uid.
     */
    public static function getUser($uid)
    {
        if (isset($_SESSION['uid']) && $uid == $_SESSION['uid']) {
            return Registry::get("user");
        }
        if (array_key_exists($uid, self::$LOADED)) {
            return self::$LOADED[$uid];
        }
        $user = new User();
        if (!$user->load($uid)) {
            return false;
        }
        self::$LOADED[$uid] = $user;
        return $user;
    }

    /**
     * Gets user by name.
     * @param username The username.
     */
    public static function getByName($username)
    {
        $username = str_replace(' ', '_', $username);
        if (Registry::get("user")->isLoggedIn() && Registry::get("user")->getUsername() == $username) {
            return Registry::get("user");
        }
        foreach (self::$LOADED as $user) {
            if ($user->getUsername() == $username) {
                return $user;
            }
        }
        $user = new User();
        $user->create($username);
        if (!$user->exists) {
            return false;
        }
        return SELF::$LOADED[$user->getUid()] = $user;
    }

    /**
     * Gets the user by the data.
     * @param data The data to use.
     */
    public static function getByData($data)
    {
        $user = new User();
        $user->data = $data;
        $user->exists = true;
        $user->setup();
        return $user;
    }

    /**
     * Creates the user class for a username.
     * @param username The username.
     */
    public function create($username)
    {
        $statement = $this->db->prepare("SELECT * FROM members WHERE username=? LIMIT 1") or die($this->db->getError());
        $statement->execute(array(str_replace(" ", "_", $username)));
        $result = $statement->fetch();
        $this->load($result['UID']);
        SELF::$LOADED[$result['UID']] = $this;
    }

    /**
     * Configures the user.
     */
    public function configure()
    {
        $this->setup();
        $this->loggedIn = $this->load(isset($_SESSION['uid']) ? $_SESSION['uid'] : "Guest");
        if (!$this->loggedIn) {
            return;
        }
        $this->updateLastActive();
        $this->write();

    }

    /**
     * Sets up the users handlers.
     */
    public function setup()
    {
        $this->moduleManager = new ModuleManager($this);
        $this->shopManager = new ShopManager($this);
        $this->contactManager = new ContactManager($this);
        $this->notificationManager = new NotificationManager($this);
    }

    /**
     * Loads the users data.
     * @param $uid the uid to load from.
     */
    public function load($uid)
    {
        if (isset($uid) && $uid != "Guest") {
            $query = $this->db->query("SELECT * FROM " . GLOBAL_DB . ".members WHERE UID=" . $uid . " LIMIT 1");
            if ($query->rowCount() > 0) {
                $row = $query->fetch(PDO::FETCH_ASSOC);
                $this->data = $row;
                $this->exists = true;
                $this->setup();
                return true;
            }
        }
        $this->setup();
        return false;
    }

    /**
     * Writes the users changes.
     */
    public function write()
    {
        foreach (self::$TABLE_KEYS as $key) {
            $statement = $this->db->prepare("UPDATE " . GLOBAL_DB . ".members SET " . $key . "=? WHERE username='" . $this->getUsername() . "'");
            if (!$statement->execute(array($this->data[$key]))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sends an email to the user.
     * @param message The user.
     */
    public function sendEmail($message)
    {
        Registry::get("sys")->sendEmail($this, $message);
    }

    /**
     * Adds a donation amount to the total
     * @param add The amount to add.
     */
    public function addDonationTotal($add)
    {
        $amount = $this->getData("donationTotal") + $add;
        if ($amount < 0) {
            $amount = 0;
        }
        $this->setData("donationTotal", $amount);
    }

    /**
     * Adds credits to the user account.
     * @param credits The credits to add.
     */
    public function addCredits($add)
    {
        $credits = $this->getData("credits") + $add;
        if ($credits < 0) {
            $credits = 0;
        }
        $this->setData("credits", $credits);
    }

    /**
     * Updates the last active field.
     */
    public function updateLastActive()
    {
        $this->setData("lastActive", date("Y-m-d H:i:s"));
    }

    /**
     * Checks if the user is active on the site.
     */
    public function isActive()
    {
        $lastActive = $this->getData("lastActive");
        return strtotime($lastActive) > strtotime("-10 minutes");
    }

    /**
     * Verify a password with the user's password.
     * @param password The comparing password.
     * @return True if verified to be the same.
     */
    public function verifyPassword($password)
    {
        if (strcasecmp($this->getPassword(), crypt($password, $this->getSalt())) != 0) {
            return false;
        }
        return true;
    }

    /**
     * Sets the posts amount.
     * @param posts The post amount.
     */
    public function setPosts($posts)
    {
        $this->setData("posts", $posts);
    }

    /**
     * Adds to the post count.
     * @param add The amount to add.
     */
    public function addPosts($add)
    {
        $this->setData("posts", $this->getPostCount() + $add);
    }

    /**
     * Removes posts from the post count.
     * @param remove The amount to remove.
     */
    public function removePosts($remove)
    {
        $this->setData("posts", $this->getPostCount() - $remove);
        if ($this->getPostCount() < 0) {
            $this->setData("posts", 0);
        }
    }

    /**
     * Sets the donator type.
     * @param type The type to set.
     */
    public function setDonatorType($type)
    {
        $this->setData("donatorType", $type);
    }

    /**
     * Sets the icon.
     * @param icon The icon.
     */
    public function setIcon($icon)
    {
        $this->setData("icon", $icon);
    }

    /**
     * Sets the data for a SQL column.
     * @param column The column to set.
     * @param value The value of the column.
     */
    public function setData($column, $value)
    {
        if (isset($this->data[$column])) {
            $this->data[$column] = $value;
            return true;
        }
        return false;
    }

    /**
     * Gets the data from a column.
     * @param column The column name.
     */
    public function getData($column)
    {
        if (isset($this->data[$column])) {
            return $this->data[$column];
        }
        return false;
    }

    /**
     * Gets the post count of a user.
     */
    public function getPostCount()
    {
        return $this->data['posts'];
    }

    /**
     * Gets a module by name.
     * @param name The name of the module.
     */
    public function getModule($name)
    {
        return $this->moduleManager->loadModule($name);
    }

    /**
     * Gets the UID of the user.
     */
    public function getUid()
    {
        return $this->data['UID'];
    }

    /**
     * Gets the username.
     */
    public function getUsername()
    {
        return $this->data['username'];
    }

    /**
     * Gets the password of the user.
     */
    public function getPassword()
    {
        return $this->data['password'];
    }

    /**
     * Gets the salt of the password.
     */
    public function getSalt()
    {
        return $this->data['salt'];
    }

    /**
     * Gets the rights of the user.
     */
    public function getRights()
    {
        return $this->data['rights'];
    }

    /**
     * Gets the iron man mode.
     */
    public function getIronmanMode()
    {
        return $this->data['ironManMode'];
    }

    /**
     * Gets the formatted username.
     */
    public function getFormatUsername()
    {
        return ucwords(str_replace("_", " ", $this->data['username']));
    }

    /**
     * Gets the email.
     */
    public function getEmail()
    {
        return $this->data['email'];
    }

    /**
     * Gets the amount of arios credits.
     */
    public function getCredits()
    {
        return $this->data['credits'];
    }

    /**
     * Gets the donation total.
     */
    public function getDonationTotal()
    {
        return $this->data['donationTotal'];
    }

    /**
     * Gets the profile image of the user.
     */
    public function getProfileImage()
    {
        if ($this->data['profileImage'] == null) {
            return DOMAIN.'/lib/images/defaultprofile1.png';
        }
        return $this->data['profileImage'];
    }

    /**
     * Gets the signature.
     */
    public function getSignature()
    {
        return $this->data['signature'];
    }

    /**
     * Gets the donator type.
     */
    public function getDonatorType()
    {
        return $this->data['donatorType'];
    }

    /**
     * Gets the last world logged in.
     */
    public function getLastWorld()
    {
        return $this->data['lastWorld'];
    }

    /**
     * Checks if the user is online.
     */
    public function isOnline()
    {
        return $this->data['online'] == 1;
    }

    /**
     * Checks if the email is activated.
     */
    public function isEmailActivated()
    {
        return $this->getData("email_activated") == 1;
    }

    /**
     * Checks if the user exists.
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Checks if the user is logged in.
     */
    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Gets the module manager.
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }

    /**
     * Gets the contact manager.
     */
    public function getContactManager()
    {
        return $this->contactManager;
    }

    /**
     * Gets the shop manager.
     */
    public function getShopManager()
    {
        return $this->shopManager;
    }

    /**
     * Gets the notification manager.
     */
    public function getNotificationManager()
    {
        return $this->notificationManager;
    }

    /**
     * Gets the database instance.
     */
    public function getDatabase()
    {
        return $this->db;
    }
}

?>