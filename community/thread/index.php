<?php
define("login-protect", true);
define("TITLE", "Community");
define("_VALID_PHP", true);
define("FORUMS", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<script type="text/javascript" src="/lib/style/js/ckeditor/ckeditor.js"></script>
<div class="community">
    <div class="board full-width">
        <div class="content">
            <?php if ((isset($_GET['id']) && isset($_GET['board_id']) && isset($_GET['page'])) && $user->getModule("Threads")->showThread($_GET['board_id'], $_GET['id'], $_GET['page'])) { ?>
            <div style="margin-left:160px;" class="editor">
                <h4 class="error">Testing an error message</h4>
                <div class="core">
                    <textarea class="ckeditor" name="editor" rows="8" cols="80"></textarea>
                    <button class="btn submit">Submit Reply</button>
                    <button class="btn cancel">Cancel</button>
                </div>
            </div>
        </div>
        <?php } else { ?>
    </div>
    <?php } ?>
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
        var actionType = "post";
        var pid = 0;
        var replyId = 0;
        $(document).delegate(".pagination > li", "click", function () {
            window.location.href = "/community/thread/index.php?board_id=" + <?php echo $_GET['board_id']; ?> +"&id=" + <?php echo $_GET['id'];?> +"&page=" + $(this).html() + "";
        });
        $(document).delegate(".editor > .core > .submit", "click", function () {
            $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=Threads&modAction=" + actionType, {
                html: editor.getData(),
                tid: <?php echo $_GET['id']?>,
                pid: pid,
                bid: <?php echo $_GET['board_id'];?>,
                page: <?php echo $_GET['page']?>,
                replyId: replyId
            }, function (data) {
                if (data.lastIndexOf("SUCCESS", 0) === 0) {
                    data = data.replace("SUCCESS", "");
                    window.location.href = "/community/thread/index.php?board_id=" + <?php echo $_GET['board_id'];?> +"&id=" + <?php echo $_GET['id'];?> +"&page=" + data;
                } else {
                    sendEditorError(data);
                }
            });
        });
        $(document).delegate(".post-actions > span.thank-btn, .post-actions > span.edit, .post-actions > span.reply, .post-actions > span.delete", "click", function (e) {
            e.preventDefault();
            var userPost = $(this).closest(".user-post");
            pid = $(this).closest(".user-post").data("pid");
            if ($(this).hasClass("edit")) {
                actionType = "edit";
                $(".editor .submit").html("Save Changes");
            } else if ($(this).hasClass("reply")) {
                replyId = pid;
                $(".editor .submit").html("Submit Reply");
                pid = 0;
            } else if ($(this).hasClass("delete")) {
                if (window.confirm("Are you sure?")) {
                    $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=Threads&modAction=delete", {
                        tid: <?php echo $_GET['id']?>,
                        pid: pid,
                        bid: <?php echo $_GET['board_id'];?>,
                        page: <?php echo $_GET['page']?>}, function (data) {
                        if (data == "SUCCESS") {
                            if (typeof pid === "undefined") {
                                window.location.href = "/community/index.php?board_id=" + <?php echo $_GET['board_id'];?> +"&page=1";
                            } else {
                                window.location.href = "/community/thread/index.php?board_id=" + <?php echo $_GET['board_id'];?> +"&id=" + <?php echo $_GET['id'];?> +"&page=" +  <?php echo $_GET['page'] ?>;
                            }
                        } else {
                            alert(data);
                        }
                    });
                }
                return;
            } else if ($(this).hasClass("thank-btn")) {
                var text = $(this).text();
                var thx = $(this);
                var thxid = $(this).data("id");
                $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=Threads&modAction=thanks", {pid: pid}, function (data) {
                    if (data.charAt(1) == "S" && data.charAt(2) == "U" || data.charAt(1) == "U" && data.charAt(2) == "C") {
                        data = data.replace("SUCCESS", "");
                        if (text == " Remove Thanks") {
                            thx.html("<i class=\"fa fa-thumbs-up\"> Thanks</i>");
                        } else {
                            thx.html("<i class=\"fa fa-thumbs-down\"> Remove Thanks</i>");
                        }
                        if (!$("#thx-" + thxid).is(":visible")) {
                            $("#thx-" + thxid).show();
                        }
                        if (data.length > 1) {
                            $("#thx-" + thxid).html(data);
                        } else {
                            $("#thx-" + thxid).hide();
                        }
                    } else {
                        alert(data);
                    }
                });
                pid = 0;
                return;
            }
            $("[data-pid='" + pid + "']").after($(".editor"));
            CKEDITOR.remove(editor);
            $("#cke_editor").remove();
            editor = CKEDITOR.replace("editor");
            if ($(this).hasClass("edit")) {
                editor.setData($(this).closest(".user-post").find(".poster-html").html());
            } else {
                editor.setData("<blockquote>" + $(this).closest(".user-post").find(".poster-html").html() + "<br><br>- Posted by " + (userPost.find(".username").html()) + "</blockquote></br><p></p>");
            }
            $(".editor .cancel").toggle(true);
        });
        $(document).delegate(".editor .cancel", "click", function () {
            actionType = "post";
            pid = 0;
            replyId = 0;
            $(".pagination:eq(1)").after($(".editor"));
            CKEDITOR.remove(editor);
            $("#cke_editor").remove();
            editor = CKEDITOR.replace("editor");
            editor.setData("");
            $(this).toggle();
        });
    });

    function sendEditorError(error) {
        $(".editor > .error").html(error);
        $(".editor > .error").css("display", "block");
    }
</script>