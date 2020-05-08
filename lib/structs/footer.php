<?php
if (count(get_included_files()) <= 1) {
    die();
}
?>
</div>
</div>
<div class='clear'></div>
<div class='footer'>
    <div id='rights'>
        <font color='#9fabbf'>&#169;</font> <?= SITE_NAME ?> Website v1.6 <br> <?= SITE_NAME ?>
        is not affiliated with Jagex or Runescape in any way.
    </div>
    <div class='social-media'>
        <ul>
            <li><a href="<?php echo TWITTER; ?>">
                    <div id="twitter"></div>
                </a></li>
            <li><a href="<?php echo YOUTUBE; ?>">
                    <div id="youtube"></div>
                </a></li>
        </ul>
    </div>
</div>
</div>
<script>
    (adsbygoogle = window.adsbygoogle || []).push({});
</script>
<script>
    $(document).ready(function () {


        $(".password").keypress(function (e) {
            if (e.which == 13) {
                $(this).closest("form").submit();
            }
        });

        $(".btn").click(function (e) {
            $(".login").submit();
        });

        $(".login").submit(function (e) {
            e.preventDefault();
            login(".login", ".login");
        });

        <?php
        if (isset($_GET['ref'])) { ?>
        $(".loginpopup").show();
        $(".loginpopup input[type=submit]").click(function (e) {
            e.preventDefault();
            $.post("/lib/sys/ActionHandler.php?action=Login", $(".loginpopup form").serialize(), function (data) {
                if (data === "SUCCESS") {
                    window.location.href = "<?php echo $_GET['ref']; ?>";
                } else {
                    sendError(".loginpopup .error", data);
                }
            });
        }); <?php
        } ?>

        $(".play, #play").click(function (e) {
            e.preventDefault();
            $(".playoptions").show();
        });

        $(".fsDialogue > .exit").click(function () {
            $(this).closest(".fsDialogue").hide();
        });

    });

    function sendError(element, error) {
        $(element).html(error);
        $(element).fadeIn(500);
        if (element != ".green-msg") {
            setTimeout(function () {
                $(element).fadeOut(500);
            }, 3000);
        }
    }

    function login(container, form) {
        $.post("/lib/sys/ActionHandler.php?action=Login", $(form).serialize(), function (data) {
            if (data == "SUCCESS") {
                window.location.href = "/account/";
            } else {
                $(form).find(".error").html(data);
                $(form).find(".error").css("display", "block");
                $(form).find(".password").val('');
            }
        });
    }

    (adsbygoogle = window.adsbygoogle || []).push({});
</script>