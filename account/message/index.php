<?php
define("_VALID_PHP", true);
define("TITLE", "Message Centre");
define("login-protect", true);
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
if (!isset($_GET['id'])) {
    header("Location: /account");
    die();
}
?>
    <div class="left-content" style="width:100%;">
        <div class="container-one">
            <?php
            $user->getModule("MessageCentre")->open($_GET['id']);
            ?>
        </div>
    </div>
<?php
$sys->displayStruct("footer");
?>