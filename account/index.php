<?php
define("_VALID_PHP", true);
define("TITLE", "Account");
define("login-protect", true);
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<div class="left-content">
    <div class='sub-page-header'>
        <h5 class="sub-head">Account Settings: <?php echo "<i>" . $user->getFormatUsername() . "</i>"; ?></h5>
    </div>
    <div class='container-one'>
        <h4 class="error" style="display: none;"></h4>
        <?php $user->getModule("AccountModule")->load(); ?>
        <br>
        <center>
            <?php TemplateManager::displayAd(468, 60); ?>
        </center>
    </div>
</div>
<div class="right-content">
    <?php $user->getModule("SidebarModule")->loadFriendsList(); ?>
</div>
<?php
$sys->displayStruct("footer");
?>

<script>
    var busy = false;
    $(document).ready(function () {
        $(".accordion").accordion({
            heightStyle: "content",
            collapsible: true,
            activate: function (event, ui) {
                var header;
                if (ui.newHeader != undefined) {
                    header = ui.newHeader;
                    if (header.data("fullscreen") == "enable") {
                        setFullScreen();
                        return;
                    }
                }
                if (ui.oldHeader != undefined) {
                    header = ui.oldHeader;
                    if (header.data("fullscreen") == "enable") {
                        removeFullScreen();
                    }
                }
            }
        });
        <?php if (isset($_GET['tab'])) {?>
        $(".accordion").accordion({active: <?php echo $_GET['tab'] - 1?>});
        <?php }?>
    });

    function setFullScreen() {
        $(".left-content").animate({
            width: "100%"
        }, 1000);
        $(".right-content").animate({
            width: "0px"
        }, 1000, function () {
            $(".right-content").css("overflow", "hidden");
        });
        $(".sub-page-header").animate({
            width: "100%"
        }, 1000);
    }

    function removeFullScreen() {
        $(".left-content").animate({
            width: "620px"
        }, 1000);
        $(".right-content").animate({
            width: "300px"
        }, 1000);
    }

</script>