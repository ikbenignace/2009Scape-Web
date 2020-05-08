<?php
define("DISCOUNT", 0.0);

/**
 * Represents a perk.
 * @author Adam Rodrigues
 *
 */
class Perk
{

    /**
     * The product id.
     */
    private $product_id;

    /**
     * The perk name.
     */
    private $name;

    /**
     * The price.
     */
    private $price;

    /**
     * The description.
     */
    private $description;

    /**
     * Constructs a perk object.
     * @param product_id The product id.
     * @param name The name of the perk.
     * @param price The price of the perk.
     * @param description The description of the perk.
     */
    public function __construct($product_id, $name, $price, $description)
    {
        $this->product_id = $product_id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }

    /**
     * Gets the product id.
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Gets the name of the perk.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the price of the perk.
     */
    public function getPrice($usd = false)
    {
        $p = $usd ? ($this->price / 10) : $this->price;
        if ($usd && DISCOUNT != 0) {
            $p = $p - ($p * DISCOUNT);
        }
        return $p;
    }

    /**
     * Gets the description.
     */
    public function getDescription()
    {
        return $this->description;
    }

}

?>