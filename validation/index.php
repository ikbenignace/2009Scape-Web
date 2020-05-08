<?php
if (!isset($_GET['code'])) {
    header("Location: /");
    die();
}
define("_VALID_PHP", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
?>
    <div class="left-content">
        <div class='sub-page-header'>
            <h5 class="sub-head">Account Support</h5>
        </div>
        <div class='container-one padding-top-bot' style="padding: 20px 0;">
            <div class="content" style="margin-left:20px; padding:5px;">
                <h4 class="error" style="margin-left:-10px; display: none;"></h4>
                <?php if (isset($_GET['code'])) {
                    echo $sys->getValidationManager()->handleValidation($_GET['code']);
                } ?>
            </div>
        </div>
    </div>
    <div class="right-content">
        <?php $user->getModule("SidebarModule")->loadOnlinePlayers(); ?>
    </div>
<?php
$sys->displayStruct("footer") ?>