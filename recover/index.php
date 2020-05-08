<?php
define("_VALID_PHP", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
if ($user->isLoggedIn()) {
    header("Location: /");
}
?>
<div class="left-content">
    <div class='sub-page-header'>
        <h5 class="sub-head">Account Recovery</h5>
    </div>
    <div class='container-one padding-top-bot' style="padding: 20px 0;">
        <div class="content" id="recover" style="margin-left:20px; padding:5px;">
            <h4 class="error" style="margin-left:-10px; display: none;"></h4>
            <li>
                Please enter the username of the account you would wish to recover. <br><strong>Warning:</strong> Your
                account must have had a set and validated email in order to complete the recovery process.
                <label class="styled-row" for="input" style="margin: 20px auto;"><p>Username:</p><input type="text"
                                                                                                        class="usernameval"
                                                                                                        name="username"
                                                                                                        placeholder="Enter a username to recover"
                                                                                                        value=""/></label>
                <input type="submit" value="Recover Password" name="submit" class="recover-password">
            </li>
        </div>
    </div>
</div>
<div class="right-content">
    <?php $user->getModule("SidebarModule")->loadOnlinePlayers(); ?>
</div>
<?php
$sys->displayStruct("footer") ?>
<script>
    $(document).ready(function () {
        var busy = false;
        $(".recover-password").click(function () {
            if (!busy) {
                busy = true;
                var code = $(".email-code").val();
                $.post("/lib/sys/ActionHandler.php?action=PasswordRecovery", {username: $(".usernameval").val()}, function (data) {
                    if (data.lastIndexOf("SUCCESS", 0) === 0) {
                        data = data.replace("SUCCESS", "");
                        $("#recover").html(data);
                    } else {
                        sendError(".error", data);
                    }
                    busy = false;
                });
            }
        });
    });
</script>