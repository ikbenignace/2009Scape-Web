<?php
define("login-protect", true);
define("TITLE", "Community");
define("_VALID_PHP", true);
define("FORUMS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<script type="text/javascript" src="/lib/style/js/ckeditor/ckeditor.js"></script>
<div id="content" class="community">
    <div class="container board full-width">
        <div class="content" style="padding: 0 !important;">
            <select class="categorySelect">
                <?php $user->getModule("Boards")->loadBoardMenu(); ?>
            </select>
            <div class="autoinput newthread">
                <input class="title" type="text" placeholder="Enter a thread title" data-action="setTitle">
                <div class="loading"></div>
            </div>
            <div class="editor">
                <h4 class="error">Testing an error message</h4>
                <div class="core">
                    <textarea class="ckeditor" name="editor" rows="8" cols="80"></textarea>
                    <button class="btn submit">Submit Thread</button>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<center>
    <?php TemplateManager::displayAd(728, 90); ?>
</center>
<?php
$sys->displayStruct("footer");
?>
<script>
    $(document).ready(function () {
        var editor = CKEDITOR.replace("editor");
        $(document).delegate(".editor > .core > .submit", "click", function () {
            var option = $(".categorySelect").children(":selected");
            var value = $(option).attr("value");
            $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=Threads&modAction=newThread", {
                html: editor.getData(),
                title: $(".title").val(),
                cid: value
            }, function (data) {
                if (data.lastIndexOf("SUCCESS", 0) === 0) {
                    data = data.replace("SUCCESS", "");
                    window.location.href = data;
                } else {
                    $(".editor > .error").html(data);
                    $(".editor > .error").css("display", "block");
                }
            });
        });
    });
</script>