<?php
define("_VALID_PHP", true);
require_once('paypal.class.php');
$p = new paypal_class;
$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
$this_script = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");

if (empty($_GET['action']))
    $_GET['action'] = 'process';

function filt($var)
{
    return strlen($var) > 0;
}

switch ($_GET['action']) {
    case 'process':

        if (!$user->isLoggedIn()) {
            die("Invalid Session");
        }

        $username = $user->getUsername();
        $user->getShopManager()->configure();
        if (!isset($_POST['data']) || empty($_POST['data'])) {
            echo "Error! No data set.";
            return;
        }
        $perkArray = explode(",", $_POST['data']);
        $price = 0.0;
        foreach ($perkArray as $perk) {
            if (!$perk = $user->getShopManager()->getPerk(intval($perk))) {
                continue;
            }
            $price += $perk->getPrice(true);
        }
        if ($price == 0) {
            echo "Error! Price is not valid.";
            return;
        }
        $p->add_field('custom', $username);
        $p->add_field('business', PAYPAL_EMAIL);
        $p->add_field('return', $this_script . '?action=success');
        $p->add_field('cancel_return', $this_script . '?action=cancel');
        $p->add_field('notify_url', $this_script . '?action=ipn');
        $p->add_field('item_name', SITE_NAME . " Shop");
        $p->add_field('item_number', $_POST['data']);
        $p->add_field('mc_currency', 'EUR');
        $p->add_field('amount', $price);
        $p->add_field('lc', 'BE');
        $p->add_field('image_url', 'http://puu.sh/jbhf2/005e78f3c8.gif');
        $p->submit_paypal_post();
        break;
    case 'success':
        echo "<h2>Donation Successful</h2>";
        break;
    case 'cancel':
        echo "<h2>Donation Cancelled</h2><p>Your donation was cancelled.</p>";
        break;
    case 'ipn':
        if (!$p->validate_ipn()) {
            $sys->log("Paypal: Invalid IPN!", DONATION_LOG);
            return;
        }
        if (!$p->ipn_data["mc_gross"] > -1) {
            $sys->log("Paypal: Invalid mc gross data.", DONATION_LOG);
            return;
        }
        $sys->log("Paypal: Incoming paypal IPN request.", DONATION_LOG);
        $username = $p->ipn_data["custom"];
        $data = $p->ipn_data["item_number"];
        $amount = $p->ipn_data["mc_gross"];
        $user = User::getByName($username);
        if (!$user) {
            $sys->log("Paypal: The username " . $username . " was not found & the IPN could not be processed.", DONATION_LOG);
            return;
        }
        $arr = explode(",", $data);
        $array = array();
        for ($i = 0; $i < sizeof($arr); $i++) {
            $array[$i] = $arr[$i];
        }
        $user->getShopManager()->configure();
        $totalPrice = 0;
        foreach ($array as $perkId) {
            $perk = $user->getShopManager()->getPerk($perkId);
            if (!$perk) {
                $sys->log("Paypal: During the IPN process the perk " . $perkId . " was not found.", DONATION_LOG);
                continue;
            }
            $totalPrice += $perk->getPrice(true);
            $user->getShopManager()->addPerk($perkId);
        }
        $user->getShopManager()->write();
        $user->addDonationTotal($totalPrice);
        $total = $user->getDonationTotal();
        if ($total >= 25) {
            $user->setDonatorType(0);
            $user->setData("icon", 1);
        }
        if ($total >= 50) {
            $user->setDonatorType(1);
            $user->setData("icon", 2);
        }
        if (!$user->write()) {
            $sys->log("Paypal: Error! Could not write the user " . $username . " profile to the database.", DONATION_LOG);
            return;
        }
        $sys->log("Paypal: Donation successfull Username: " . $user->getUsername() . " Perks: " . $data . " Total Price: " . $totalPrice, DONATION_LOG);
        break;
}
$sys->displayStruct("footer");
?>