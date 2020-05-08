<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/shop/Perk.php");

/**
 * Manages the users local perk shop.
 * @author Adam Rodrigues
 *
 */
class ShopManager
{

    /**
     * The list of available perks to chose from.
     */
    public static $PERKS;

    /**
     * The user instance.
     */
    private $user;

    /**
     * The database instance.
     */
    private $db;

    /**
     * The array of perks.
     */
    private $perks = array();

    /**
     * If the shop manager is configured.
     */
    private $configured = false;

    /**
     * Constructs the shop manager.
     * @param user The user.
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->db = $user->getDatabase();
    }

    /**
     * Configures the shop manager.
     */
    public function configure()
    {
        if ($this->configured) {
            return;
        }
        self::$PERKS = array();
        $statement = $this->db->query("SELECT * FROM perks ORDER BY product_id DESC");
        while ($perk = $statement->fetch(PDO::FETCH_ASSOC)) {
            SELF::$PERKS[$perk['product_id']] = new Perk($perk['product_id'], $perk['name'], $perk['price'], $perk['description']);
        }
        $perks = $this->user->getData("perks");
        $split = explode(',', $perks);
        foreach ($split as $string) {
            $this->addPerk(intval($string));
        }
        $this->configured = true;
    }

    /**
     * Writes the shop managers data to the users SQL Info.
     */
    public function write()
    {
        $perks = "";
        $count = 0;
        foreach ($this->perks as $perk) {
            $perks .= $perk->getProductId() . ($count == sizeof($this->perks) - 1 ? "" : ",");
            $count++;
        }
        $this->user->setData("perks", $perks);
        $this->user->write();
    }

    /**
     * Adds a perk to the shop manager.
     * @param product_id The product id.
     */
    public function addPerk($product_id)
    {
        if (!array_key_exists($product_id, self::$PERKS)) {
            return false;
        }
        $this->perks[$product_id] = self::$PERKS[$product_id];
    }

    /**
     * Gets a perk.
     * @param product id The product id.
     */
    public function getPerk($product_id)
    {
        if (!array_key_exists($product_id, self::$PERKS)) {
            return false;
        }
        return self::$PERKS[$product_id];
    }

    /**
     * Checks if the shop manager has a perk owned.
     * @param product_id The product id.
     */
    public function hasPerk($product_id)
    {
        return array_key_exists($product_id, $this->perks);
    }

    /**
     * Gets the owned perks.
     */
    public function getPerks()
    {
        return $this->perks;
    }

    /**
     * Gets the user.
     */
    public function getUser()
    {
        return $this->user;
    }
}

?>