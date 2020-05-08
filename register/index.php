<?php
define("_VALID_PHP", true);
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/init.php");
?>
<div class="left-content">
    <div class='sub-page-header'>
        <h5 class="sub-head">Register Account</h5>
    </div>
    <div class='container-one padding-top-bot'>
        <div class="green-msg" style="margin: -20px 20px 20px 20px;">Please take the time to create a quick account. You
            are not required to validate your e-mail to play <?= SITE_NAME ?>, but secure website functionality will be
            disabled until you do so.
        </div>
        <h4 class="error" style="display: none;"></h4>
        <div class='form-holder'>
            <form class="register" name='register' method='post' action=''
                  onsubmit='return checkform(this);'>
                <label for='input' class='styled-row'><p>Username</p> <input
                            type='text' id='username' name='username' value='' maxlength='32'></label>
                <label for='input' class='styled-row'><p>Password</p> <input
                            type='password' name='password1' value='' maxlength='32'></label> <label
                        for='input' class='styled-row'><p>Repeat Password</p> <input
                            type='password' name='password2' value='' maxlength='32'></label> <label
                        for='input' class='styled-row'><p>Email</p> <input type='text'
                                                                           name='email' value='' maxlength='64'></label>
                <label for='input'
                       class='styled-row'><p>What is 5 + 5?</p> <input
                            type='text' name='bot' value='' maxlength='64'></label> <br> <input
                        class="submitRegister" type='submit' name='submit'
                        value='Register Account'>
            </form>
        </div>
    </div>
</div>

<?php
$sys->displayStruct("sidebar");
$sys->displayStruct("footer");
?>
<script>
    $(document).ready(function () {
        $(".submitRegister").click(function (e) {
            e.preventDefault();
            $.post("/lib/sys/ActionHandler.php?action=Register", $(".register").serialize(), function (data) {
                if (data == "SUCCESS") {
                    $(".container-one").html("<div class='green-msg'>You've successfully registered and can now log into the game. Don't forget to check your e-mail for a validation link to use our website features!</div>");
                } else {
                    $(".error").html(data);
                    $(".error").fadeIn(500, function () {
                        setTimeout(function () {
                            $(".error").fadeOut(500);
                        }, 3000);
                    });
                }
            });
        });
    });

</script>