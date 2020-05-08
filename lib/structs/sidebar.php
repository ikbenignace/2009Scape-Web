<?php
if (count(get_included_files()) <= 1) {
    die();
}
$user = Registry::get("user");
?>
<div class='right-content'>
    <?php if (!$user->isLoggedIn()) {
        ?>
        <div class='sidebar-box'>
            <div class='sbox-head'>Login</div>
            <div class='sbox-content'>
                <form class='login' action="<?php echo ACTION_PATH; ?>action=Login" method="post">
                    <input type="text" name="username" placeholder="Username"/>
                    <input type="password" name="password" class="password" placeholder="Password"/>
                    <a href="#" class="btn" style='float:right'>Login</a>
                    <h3 class="error"></h3>
                </form>
                <div class='clear'></div>
                <a href="/register/" style='margin-left:158px;'>Register Account</a>
                <a href="/recover/" style='margin-left:158px;'>Forgot password?</a>
            </div>
        </div>
        <?php
    } else {
        ?>
        <div class='sidebar-box'>
            <div class='sbox-head'>Welcome, <?php echo $user->getUsername() ?></div>
            <div class='sbox-content'>
                <a href="/community" class="btn" style='float:right'>Community</a>
                <a href="/account" class="btn" style='float:right'>Account</a>
                <a href="/vote" class="btn" style='float:right'>Vote</a>
                <a href="/donate" class="btn" style='float:right'><?= SITE_NAME ?> Shop</a>
                <a href="<?php echo ACTION_PATH; ?>action=Logout" class="btn" style='float:right'>Logout</a>
                <a href="/hiscores" class="btn" style='float:right'>Hiscores</a>
                <div class='clear'></div>
            </div>
        </div>
        <?php $user->getModule("SidebarModule")->loadFriendsList(); ?>
    <?php } ?>
    <?php $user->getModule("SidebarModule")->loadOnlinePlayers(); ?>
    <?php $user->getModule("SidebarModule")->loadDevLog(); ?>
</div>	