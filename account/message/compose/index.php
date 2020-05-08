<?php
define("_VALID_PHP", true);
define("TITLE", "Compose Message");
define("login-protect", true);
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<script type="text/javascript" src="/lib/style/js/ckeditor/ckeditor.js"></script>
<div class="left-content" style="width:100%;">
    <div class="container-one">

        <div id="content" class="community">
            <div class="container board full-width">
                <div class="content" style="padding: 0 !important;">

                    <div class="titleBar" style="width:690px;">
                        <span>Viewing: Message Composition</span>
                    </div>

                    <div class="autoinput newthread">
                        <input class="recipient" type="text"
                               placeholder="<?php echo isset($_GET['recipient']) ? $_GET['recipient'] : "Enter recipient username" ?>">
                        <input class="subject" type="text" placeholder="Enter subject">
                        <div class="loading"></div>
                    </div>

                    <div class="editor">
                        <h4 class="error">Testing an error message</h4>
                        <div class="core">
                            <textarea class="ckeditor" name="editor" rows="8" cols="80"></textarea>
                            <button class="btn send">Send Message</button>
                            <button class="btn back">Message Centre</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php
$sys->displayStruct("footer");
?>

<script>
    $(document).ready(function () {
        var editor = CKEDITOR.replace("editor");
        $(document).delegate(".editor > .core > .send", "click", function () {
            $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=MessageCentre&modAction=compose", {
                html: editor.getData(),
                recipient: $(".recipient").val(),
                subject: $(".subject").val()
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
        $(document).delegate(".editor > .core > .back", "click", function () {
            window.location.href = "/account/index.php?tab=5";
        });
    });
</script>