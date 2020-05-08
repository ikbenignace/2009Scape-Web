<?php
if (count(get_included_files()) <= 1) {
    die();
}
if (!Registry::get("sys")->getSecurityManager()->securityCheck("SITE_LOAD")) {
    die("You have loaded too many pages in a short amount of time.");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title><?php echo defined('TITLE') ? TITLE . " - " . BASE_TITLE : BASE_TITLE; ?></title>
    <meta http-equiv='content-Type' content='text/html; charset=utf-8'>
    <meta http-equiv='content-language' content='en-gb'>
    <meta name='author' content='<?= SITE_NAME ?>'>
    <meta name='description' content="<?php echo DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo KEYWORDS; ?>"/>
    <link rel="icon" type="image/png" href="/lib/images/favicon.png?v=102"/>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="/lib/style/style.css?rand=<?php echo rand(1000, 10000000); ?>" rel='stylesheet'>
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<body>
<?php if (isset($_GET['ref'])) { ?>
    <div class="fsDialogue loginpopup" title="Login to access page">
        <img src='/lib/images/logo.png' alt='<?= SITE_NAME ?>'/>
        <div class="content">
            <span class="error" style="display: none;"></span>
            <form method='post' action=''>
                <label for='input' class='styled-row'><p>Username</p></label>
                <input type='text' name='username' value='' placeholder='Username' maxlength='32'>
                <label for='input' class='styled-row'><p>Password</p></label>
                <input type='password' name='password' value='' placeholder='Password' maxlength='32'>
                </br><br>
                <input type='submit' name='submit' value='Login'>
            </form>
        </div>
        <div class="exit"><i class="fa fa-times"></i></div>
    </div>
<?php } ?>
<div class="fsDialogue playoptions">
    <div class="content">
        <img src="/lib/images/Bandos.png"/>
        <a href="/lib/live/arios-launcher.jar?v=1" id="download"></a>
        <p class="help"><b>To Register:</b> Download the client and select 'Create an Account' on the main client
            screen.</p>
        <p class="help"><b>Trouble playing?</b> Head on over to our <a href="/community">Community forums</a> and make a
            bug report!</p>
    </div>
    <div class="exit"><i class="fa fa-times"></i></div>
</div>
<div class='wrapper'>
    <div class='head'>
        <div class='logo'>
            <a href='/'>
                <img src='/lib/images/logo.png' alt='<?= SITE_NAME ?>'/>
            </a>
        </div>
        <div class='playercount'>There are currently <?php echo Registry::get("sys")->getPlayersOnline() ?> players
            online!
        </div>
        <div class='header'>
            <a href='#' class='play'><p></p><span></span> </a>
            <ul class='navigation'>
                <li><a id='home' href='/'><p></p><span></span></a>
                </li>
                <li><a id='community' href='/community'><p></p><span></span></a>
                </li>
                <li><a id='play' href='#'><p></p><span></span></a>
                </li>
                <li><a id='hiscores' href='/hiscores'><p></p><span></span></a>
                </li>
                <li><a id='vote' href='/vote'><p></p><span></span> </a>
                </li>
                <li><a id='donate' href='/donate'><p></p> <span></span> </a></li>
                <li><a id='account' href='/account'><p></p> <span></span></a>
                </li>
            </ul>
        </div><!-- END HEADER -->
    </div>
    <div class='main-body'>
        <div class='inner-body'>