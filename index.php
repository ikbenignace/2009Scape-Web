<?php
define("_VALID_PHP", true);
define("TITLE", "Home");
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<style>
    .ui-effects-transfer {
        border: 2px solid black;
    }
</style>
<div class='left-content'>
    <div id='slider-wrapper'>
        <div id='slider'>
            <iframe width="620" height="350" src="https://www.youtube.com/embed/ZAEcAS1JQ-U" frameborder="0"
                    allowfullscreen></iframe>
        </div>
    </div>
    <div class='news-container'>
        <ul>
            <?php $user->getModule("NewsFeed")->load(); ?>
        </ul>
    </div>
</div>
<?php
$sys->displayStruct("sidebar");
$sys->displayStruct("footer");
?>
<script>
    $(document).ready(function () {
        $(".collapsed").each(function () {
            $(this).find(".collapsible").hide();
            $(this).find(".minimize").html('<i class="fa fa-plus"></i>');
        });
        $(".minimize").click(function () {
            $(this).closest(".news-row").find(".collapsible").slideToggle(500);
            $(this).html($(this).html() == '<i class="fa fa-plus"></i>' ? '<i class="fa fa-minus"></i>' : '<i class="fa fa-plus"></i>');

        });
    });
</script>