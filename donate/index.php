<?php
define("_VALID_PHP", true);
define("login-protect", true);
define("TITLE", "Donate");
require_once($_SERVER ['DOCUMENT_ROOT'] . "/lib/init.php");
if (!$user->exists() || !$user->isLoggedIn()) {
    return;
}
$user->getShopManager()->configure();
?>
<div class="left-content">
    <div class='sub-page-header'>
        <h5 class="sub-head"><?= SITE_NAME ?> Shop</h5>
    </div>
    <div class='container-one shop'>
        <div class="shop_left">
            <h4 class="key"><i class="fa fa-check-circle" style="color: green;"></i> = Perks you own</h4>
            <?php $user->getModule("ShopModule")->loadShop(); ?>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div class="right-content">
    <div class="shop_right">
        <?php $user->getModule("ShopModule")->loadCart(); ?>
    </div>
    <div class='sidebar-box'>
        <div class='sbox-head'>Donation Guide & FAQ</div>
        <div class='sbox-content' stlye="padding:20px;">
            <div class='shop-faq'>
                <p>The <?= SITE_NAME ?> donation system allows for the purchase of perks (in-game enhancement) which
                    last for the lifetime of their account. Perks can be purchased with <?= SITE_NAME ?> Credits (earned
                    from voting & other activities) or can be purchased with real world currency.<br></p>
                <p><br>When purchasing perks with real world currency, players can earn a donator rank by donating a
                    lifetime total of the following:</p><br><br>
                <ul>
                    <li><span class="username" style="color: #006600;"><img style='margin-right: 1px; margin-top: 1px;'
                                                                            src='/lib/images/icons/regular.png'>Regular Donator</span>
                        - $25 Lifetime Total
                        <ul>
                            <li>Donator Zone (bank, furnace, fairy ring, ect)</li>
                        </ul>
                    </li>
                    <li><span class="username" style="color: #990000;"><img style='margin-right: 1px; margin-top: 1px;'
                                                                            src='/lib/images/icons/extreme.png'>Extreme Donator</span>
                        - $50 Lifetime Total
                        <ul>
                            <li>Extreme Donator Zone (resources)</li>
                            <li>Ability to change donator icon.</li>
                        </ul>
                    </li>
                </ul>
                <h3>Donation FAQ</h3>
                <p class="shop-faq-header">Can I donate for in-game items?</p>
                <p class="quote">The only available things for purchase are located on this page. <?= SITE_NAME ?> staff
                    does not individually sell to players.</p>
                <p class="shop-faq-header">Do you accept rsgp or rs accounts?</p>
                <p class="quote"><?= SITE_NAME ?> does not accept rs gold or accounts as a form of payment or
                    donation.</p>
                <p class="shop-faq-header">Can I donate for someone else?</p>
                <p class="quote">Yes, you may enter a giftee username under the shopping card and proceed as you
                    normally pay.</p>
                <p class="shop-faq-header">When/how do I recieve my perks after purchase?</p>
                <p class="quote">The perks are automatically credited towards your account within a few minutes of
                    buying. Re-log in-game for them to take effect.</p>
            </div>
        </div>
    </div>
    <div style="margin-left:60px; padding:10px;">
        <?php TemplateManager::displayAd(160, 600); ?>
    </div>
</div>
<form id="finalize" action="/lib/misc/paypal/paypal.php" method="post" style="display: none;">
    <input type="hidden" id="data" name="data" value="null">
    <input type="hidden" id="giftee" name="giftee" value="">
</form>
<?php $sys->displayStruct("footer") ?>
<script>
    $(document).ready(function () {
        var cart = [];
        var isCredits = false;
        var purchased = false;
        var loading = false;

        $(document).delegate(".select", "click", function () {
            if (purchased) {
                return;
            }
            var product = parseInt($(this).data("id"));
            var status = $(this).data("status");
            if (status == 0) {
                cart.push(product);
                $(this).val("Remove");
            } else if (status == 1) {
                $(this).val("Add to Cart");
                for (var i = 0; i < cart.length; i++) {
                    if (cart[i] == product) {
                        cart.splice(i, 1);
                    }
                }
            }
            updateCart();
            $(this).data("status", status == 0 ? 1 : 0);
        });

        $(".checkout").click(function (e) {
            if (loading || purchased) {
                return;
            }
            e.preventDefault();
            if (cart.length < 1) {
                sendError(".error", "Sorry, your cart is empty.");
                return;
            }
            if ($(".giftee").val() != "") {
                if (!confirm("You have entered a giftee username of '" + $(".giftee").val() + "'. Do you wish to proceed?")) {
                    return;
                }
            }
            var arr = "";
            for (var i = 0; i < cart.length; i++) {
                arr += (cart[i]) + (i != cart.length - 1 ? "," : "");
            }
            loading = true;
            if (isCredits) {
                $(".checkout").val("Loading....");
                $.post("/lib/sys/ActionHandler.php?action=ModuleAction&name=ShopModule&modAction=checkout", {
                    arr: arr,
                    giftee: $(".giftee").val()
                }, function (data) {
                    if (data == "SUCCESS") {
                        $(".cart-list").html("Purchase Success! Reload page to shop again.");
                        $(".checkout").val("Purchase Success!");
                        purchased = true;
                    } else {
                        sendError(".error", data);
                        loading = false;
                        $(".checkout").val("Checkout");
                    }
                });
                return;
            }
            $("#data").val(arr);
            if ($(".giftee").val() != "") {
                $("#giftee").val($(".giftee").val());
            }
            $("#finalize").submit();
        });

        $(".currency").click(function () {
            if (purchased) {
                return;
            }
            var type = this.className == "btn currency usd" ? 0 : 1;
            if (type == 0 && isCredits == false || type == 1 && isCredits) {
                return;
            }
            $(".item").each(function () {
                var credits = $(this).data("credits");
                var usd = $(this).data("usd");
                switch (type) {
                    case 0:
                        isCredits = false;
                        $(this).find("#price").html("$" + usd);
                        break;
                    case 1:
                        isCredits = true;
                        $(this).find("#price").html(credits + " Credits");
                        break;
                }
                updateCart();
            });
        });


        function updateCart() {
            var html = "";
            var total = 0;
            for (var i = 0; i < cart.length; i++) {
                var id = cart[i];
                var item = $("[data-id='" + id + "']").closest(".item")
                var name = item.find("h4").html();
                var credits = parseInt(item.data("credits"));
                var usd = parseInt(item.data("usd"));
                total += isCredits ? credits : usd;
                html += name + "<br>";
            }
            if (html != "") {
                html += "<br><br>-------------------</br> Total:  " + (isCredits ? (total + " Credits") : ("$" + total));
            } else {
                html = "<strong>No items in cart. Add an item to continue.</strong>";
            }
            $(".cart-list").html(html);
        }

    });
</script>