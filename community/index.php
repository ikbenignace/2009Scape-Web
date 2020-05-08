<?php
define("login-protect", true);
define("TITLE", "Community");
define("_VALID_PHP", true);
define("FORUMS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<script type="text/javascript" src="/lib/style/js/ckeditor/ckeditor.js"></script>
<div class="community" oncontextmenu="return false;">
    <div class="board">
        <?php $user->getModule("Navigation")->loadNavigation(); ?>
        <div class="content">
            <?php if (isset($_GET['board_id']) && isset($_GET['page'])) {
                $user->getModule("Threads")->showThreads($_GET['board_id'], $_GET['page']);
            } else {
                $user->getModule("Boards")->loadBoards();
            } ?>
        </div>
        <?php $user->getModule("ForumStatistics")->loadUserStatistics(); ?>
    </div>
    <div class="sidebar">
        <a class="createthread" href="/community/thread/new/"><i class="fa fa-plus"></i> New Thread</a>
        <a class="createthread" href="/community/members/index.php?name=<?php echo $user->getUsername(); ?>"><i
                    class="fa fa-user"></i> Profile <?php $count = $user->getNotificationManager()->getCount();
            echo $count > 0 ? "(" . $count . ")" : "" ?></a>
        <?php $sys->getForumManager()->getForumSettings()->loadSidebarModules(); ?>
    </div>
</div>
<?php
$sys->displayStruct("footer");
?>
<script>
    $(document).ready(function () {
        $(document).delegate(".community li.cat", "click", function () {
            window.location.href = "index.php?board_id=" + $(this).data("cat") + "&page=1";
        });
        $(document).delegate(".mod_recentPosts > ul > li", "click", function () {
            window.location.href = $(this).data("suburl");
        });
        $(".collapsed").each(function () {
            $(this).find(".collapsible").hide();
            $(this).find(".minimize").html('<i class="fa fa-plus"></i>');
        });
        $(".minimize").click(function () {
            $(this).closest(".recentPosts").find(".mod_recentPosts").slideToggle(500);
            $(this).html($(this).html() == '<i class="fa fa-plus"></i>' ? '<i class="fa fa-minus"></i>' : '<i class="fa fa-plus"></i>');
        });
        <?php if (isset($_GET['board_id']) && isset($_GET['page'])) { ?>
        $(document).delegate(".pagination > li", "click", function () {
            window.location.href = "/community/index.php?board_id=" + <?php echo $_GET['board_id']; ?> +"&page=" + $(this).html() + "";
        });
        $(document).delegate(".community li.topic", "click", function () {
            window.location.href = "/community/thread/index.php?board_id=" + <?php echo $_GET['board_id']?> +"&id=" + $(this).data("tseo") + "&page=" + <?php echo $_GET['page']?> +"";
        });
        <?php } ?>

    });
</script>