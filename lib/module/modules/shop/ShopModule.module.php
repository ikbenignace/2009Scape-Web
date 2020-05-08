<?php


/**
 * The module used to handle the shop.
 * @author Adam Rodrigues
 *
 */
class ShopModule extends Module
{

    /**
     * Handles a sent action.
     * @param action The action.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
        switch ($action) {
            case "checkout":
                echo $this->checkout($cleaned);
                break;
        }
    }

    /**
     * Handles the checkout procedure (credits);
     */
    private function checkout($cleaned)
    {
        $giftee = $_POST['giftee'];
        $arr = explode(",", $_POST['arr']);
        $array = array();
        for ($i = 0; $i < sizeof($arr); $i++) {
            $array[$i] = $arr[$i];
        }
        $instanced = $this->user;
        if (!empty($giftee)) {
            $instanced = User::getByName($giftee);
            if (!$instanced) {
                return "Sorry, the username you entered for the giftee is invalid.";
            }
        }
        $instanced->getShopManager()->configure();
        $totalCredits = 0;
        foreach ($array as $id) {
            $perk = $instanced->getShopManager()->getPerk($id);
            if (!$perk) {
                return "Please contact a system administrator. #434 id= " . $id;
            }
            if ($perk->getPrice() < 0) {
                return "Please contact a system administrator. #435 id = " . $id;
            }
            $totalCredits += $perk->getPrice();
        }
        if ($this->user->getCredits() < $totalCredits) {
            return "You don't have enough credits!";
        }
        foreach ($array as $id) {
            $instanced->getShopManager()->addPerk($id);
        }
        $instanced->getShopManager()->write();
        $this->user->addCredits(-$totalCredits);
        if ($this->user->write() && $instanced->write()) {
            Registry::get("sys")->log($this->user->getUsername() . " bought perks " . implode(",", $array) . " with " . $totalCredits . " credits." . (empty($giftee) ? "" : " Giftee:" . $giftee), DONATION_LOG);
            return "SUCCESS";
        }
        return "Please contact a system administrator. #436";
    }

    /**
     * Loads the shop.
     */
    public function loadShop()
    {
        $sm = $this->user->getShopManager();
        $sm->configure();
        $this->template = TemplateManager::load("ShopItem");
        foreach ($sm::$PERKS as $perk) {
            $this->template->insert("credits", $perk->getPrice(false));
            $this->template->insert("usd", $perk->getPrice(true));
            $this->template->insert("productId", $perk->getProductId());
            $this->template->insert("description", $perk->getDescription());
            $this->template->insert("name", $perk->getName() . " - ");
            $this->template->insert("price", $perk->getPrice(true));
            $this->template->insert("owned", $sm->hasPerk($perk->getProductId()) ? "              <i class=\"fa fa-check-circle\" style=\"color: green;\"></i>" : "");
            $this->display();
        }
    }

    /**
     * Loads the shopping cart.
     */
    public function loadCart()
    {
        $sm = $this->user->getShopManager();
        $this->template = TemplateManager::load("Cart");
        $this->display();
    }
}

?>