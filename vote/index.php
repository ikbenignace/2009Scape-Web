<?php
define("_VALID_PHP", true);
define("login-protect", true);
define("TITLE", "Vote");
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<div class="left-content">
    <div class='sub-page-header'>
        <h5 class="sub-head"><?= SITE_NAME ?> Voting</h5>
    </div>
    <div class="container-one" style="min-height:400px;">
        <div class="vote">
            <br>
            <div class="green-msg"> Voting on the sites below allow you to gain credits. Credits can be used to purchase
                <i>perks</i> from the <i><?= SITE_NAME ?></i> shop located <a href="/donate"><b>here</b></a></div>
            <?php $user->getModule("VotingModule")->load(); ?>
        </div>
        <div class="forms">
            <form data-formsite="runelocus" action="http://www.runelocus.com/toplist/index.php" method="get"
                  target="_blank">
                <input type="hidden" name="action" value="vote"/>
                <input type="hidden" name="id" value="40963"/>
                <input type="hidden" name="id2" value="<?php echo $user->getUsername(); ?>"/>
            </form>
            <form data-formsite="rune-server" action="http://www.rune-server.org/toplist.php" method="get"
                  target="_blank">
                <input type="hidden" name="do" value="vote"/> <input type="hidden" name="sid" value="9500"/>
                <input type="hidden" name="incentive" value="<?php echo $user->getUsername(); ?>"/>
            </form>
            <form data-formsite="top-100-arena" action="http://www.top100arena.com/in.asp" method="get" target="_blank">
                <input type="hidden" name="id" value="85351"/>
                <input type="hidden" name="incentive" value="<?php echo $user->getUsername(); ?>"/>
            </form>
            <form data-formsite="topg" action="http://topg.org/Runescape/in-419664-<?php echo $user->getUsername() ?>"
                  method="get" target="_blank">
            </form>
        </div>
    </div>
    <br>
    <center>
        <?php TemplateManager::displayAd(468, 60); ?>
    </center>
</div>
<?php
$sys->displayStruct("sidebar");
$sys->displayStruct("footer");
?>
<script>
    $(document).ready(function () {
        $(document).delegate(".voteLink", "click", function () {
            var site = $(this).data("site");
            $("[data-formsite=" + site + "]").submit();
        });

    });
</script>