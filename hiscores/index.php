<?php
define("_VALID_PHP", true);
define("TITLE", "Hiscores");
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
require_once("Highscores.php");
$sys->displayStruct("header");

$start = 1;
if (isset($_GET['start'])) {
    $s = $_GET['start'];
    if (is_numeric($s)) {
        $start = $s;
    }
}

?>
<div class='left-content'>
    <div class='sub-page-header'>
        <h5 class="sub-head">Overall Highscores</h5>
    </div>
    <div class='container-one'>
        <div class='pvp-statistics-table'>
            <div class="scores">
                <?php
                Highscores::dumpScores(-1, "NONE", $start);
                ?>
            </div>
        </div>
    </div>
</div>
<div class='right-content'>
    <div class='sidebar-box hiscore'>
        <div class='sbox-head'>Search User</div>
        <div class='sbox-content'>
            <form class="searchpersonal">
                <input type='text' class="usernamesearch" name='username' placeholder='Username'> <input
                        type='submit' class="btn searchbtn" name='search' value='search' autocomplete="off">
            </form>
            <div class='clear'></div>
        </div>
    </div>
    <div class='sidebar-box hiscore hs' style="height: 140px;">
        <div class='sbox-head'>Filter Options</div>
        <div class='sbox-content'>
            <div class="btn active" style="width: 238px;" data-type="none">Regular</div>
            <div class="btn" data-type="standard">Standard Iron Man</div>
            <div class="btn" data-type="ultimate">Ultimate Iron Man</div>
        </div>
    </div>
    <div class='sidebar-box hiscore'>
        <div class='sbox-head'>Skill Table</div>
        <div class='sbox-content'>
            <ul class='skills'>
                <li><a class="active" data-skill="-1" href=''><img
                                src='../lib/images/skills/overall.png'><span>Overall</span></a></li>
                <li><a data-skill="0" href=''><img
                                src='../lib/images/skills/attack.png'><span>Attack</span></a></li>
                <li><a data-skill="1" href=''><img
                                src='../lib/images/skills/defence.png'><span>Defence</span></a></li>
                <li><a data-skill="2" href=''><img
                                src='..lib/images/skills/strength.png'><span>Strength</span></a></li>
                <li><a data-skill="3" href=''><img
                                src='../lib/images/skills/hitpoints.png'><span>Hitpoints</span></a></li>
                <li><a data-skill="4" href=''><img
                                src='../lib/images/skills/range.png'><span>Ranged</span></a></li>
                <li><a data-skill="5" href=''><img
                                src='../lib/images/skills/prayer.png'><span>Prayer</span></a></li>
                <li><a data-skill="6" href=''><img
                                src='../lib/images/skills/magic.png'><span>Magic</span></a></li>
                <li><a data-skill="7" href=''><img
                                src='../lib/images/skills/cooking.png'><span>Cooking</span></a></li>
                <li><a data-skill="8" href=''><img
                                src='../lib/images/skills/woodcutting.png'><span>Woodcutting</span></a></li>
                <li><a data-skill="9" href=''><img
                                src='../lib/images/skills/fletching.png'><span>Fletching</span></a></li>
                <li><a data-skill="10" href=''><img
                                src='../lib/images/skills/fishing.png'><span>Fishing</span></a></li>
                <li><a data-skill="11" href=''><img
                                src='../lib/images/skills/firemaking.png'><span>Firemaking</span></a></li>
                <li><a data-skill="12" href=''><img
                                src='../lib/images/skills/crafting.png'><span>Crafting</span></a></li>
                <li><a data-skill="13" href=''><img
                                src='../lib/images/skills/smithing.png'><span>Smithing</span></a></li>
                <li><a data-skill="14" href=''><img
                                src='../lib/images/skills/mining.png'><span>Mining</span></a></li>
                <li><a data-skill="15" href=''><img
                                src='../lib/images/skills/herblore.png'><span>Herblore</span></a></li>
                <li><a data-skill="16" href=''><img
                                src='../lib/images/skills/agility.png'><span>Agility</span></a></li>
                <li><a data-skill="17" href=''><img
                                src='../lib/images/skills/thieving.png'><span>Thieving</span></a></li>
                <li><a data-skill="18" href=''><img
                                src='../lib/images/skills/slayer.png'><span>Slayer</span></a></li>
                <li><a data-skill="19" href=''><img
                                src='../lib/images/skills/farming.png'><span>Farming</span></a></li>
                <li><a data-skill="20" href=''><img
                                src='../lib/images/skills/runecrafting.png'><span>Runecrafting</span></a></li>
                <li><a data-skill="21" href=''><img
                                src='../lib/images/skills/hunter.png'><span>Hunter</span></a></li>
                <li><a data-skill="22" href=''><img
                                src='../lib/images/skills/construction.png'><span>Construction</span></a></li>
                <li><a data-skill="23" href=''><img
                                src='../lib/images/skills/summoning.png'><span>Summoning</span></a></li>
            </ul>
            <input class="btn previous" type="submit" style="display: block; width: 100%; margin: 25px auto 10px auto;"
                   value="Previous Page">
            <input class="btn next" type="submit" style="display: block; width: 100%; margin: 2px auto;"
                   value="Next Page">
            <div class='clear'></div>
        </div>
    </div>
    <br>
    <center>
        <?php TemplateManager::displayAd(250, 250); ?>
    </center>
</div>
<?php
$sys->displayStruct("footer");
?>

<script type="text/javascript" src="/assets/js/progressbar.min.js"></script>
<script>

    var start = <?php echo $start; ?>;
    var activeSkill = -1;
    var type = "none";

    function changeScores() {
        $(".scores").html("<span style='display: block; padding: 10px 0; text-align: center;'>Loading Players...</span>");
        $.post("/lib/sys/ActionHandler.php?action=HiscoresRequest", {
            skill: activeSkill,
            type: type,
            start: start
        }, function (data) {
            $(".scores").html(data);
        });
    }

    function setActiveSkill(skill) {
        activeSkill = skill;
        start = 1;
        $("[data-skill]").each(function () {
            $(this).removeClass("active");
        });
        $("[data-skill='" + skill + "']").addClass("active");
    }

    function changePage(change) {
        start += change;
        if (start < 1) {
            start = 1;
        }
        changeScores();
    }

    function reloadPersonal() {
        $(".personal-skill").each(function () {
            var id = $(this).attr("id");
            var circle = new ProgressBar.Circle("#" + id, {
                color: '#FCB03C',
                trailColor: '#222222',
                duration: 3000,
                easing: 'easeInOut',
                strokeWidth: 3
            });
            circle.animate($(this).data("percentage"));
        });
    }

    function searchUser() {
        $.post("/lib/sys/ActionHandler.php?action=HiscoresRequest", $(".searchpersonal").serialize(), function (data) {
            $(".scores").html(data);
            reloadPersonal();
        });
    }

    $(document).ready(function () {

        $("[data-type]").click(function () {
            type = $(this).data("type");
            changeScores();
            $("[data-type]").each(function () {
                $(this).removeClass("active");
            });
            $(this).addClass("active");
        });

        $("[data-skill]").click(function (e) {
            var skill = $(this).data("skill");
            e.preventDefault();
            $(".sub-head").html($(this).find("span").html() + " Highscores");
            setActiveSkill(skill);
            changeScores();
        });

        $(document).delegate(".hs-back", "click", function () {
            changeScores();
        });

        $(document).delegate("[data-username]", "click", function () {
            var username = $(this).data("username");
            $(".usernamesearch").val(username);
            searchUser();
        });

        $(".searchbtn").click(function (e) {
            e.preventDefault();
            searchUser();
        });

        $(".previous, .next").click(function () {
            if ($(this).hasClass("previous")) {
                changePage(-30);
            } else {
                changePage(30);
            }
        });

    });


</script>
