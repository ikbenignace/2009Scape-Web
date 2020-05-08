<?php
define("login-protect", true);
define("TITLE", "View Profile");
define("_VALID_PHP", true);
define("FORUMS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
$name = "";
if (isset($_GET['name'])) {
    $name = $_GET['name'];
}
$tab = 0;
if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];
}
?>
    <div class="community">
        <div class="titleBar" style="width:910px;padding:13px; margin: 1px auto 0 auto; margin-bottom: 6px;">
            <a href="/community"><i class='fa fa-arrow-left'></i>
                Back</a><span>Viewing Member: <?php echo isset($_GET['name']) ? Utils::getFormatUsername($_GET['name']) : "?"; ?></span>
        </div>
    </div>
<?php
$user->getModule("ProfileViewer")->view($name, $tab);
?>
<?php $sys->displayStruct("footer"); ?>