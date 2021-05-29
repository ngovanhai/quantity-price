var scripts = document.getElementsByTagName('script');
var myScript = {};
for (var i = 0; i < scripts.length; i++) {
    var myScript1 = scripts[i];
    var src = myScript1.src;
    if (src.indexOf("custom-order.js") > -1) {
        myScript = myScript1;
    }
}
var temp = myScript.src.split("?");
var queryString = temp[temp.length - 1];
var params = parseQuery(queryString);

function parseQuery(query) {
    var Params = new Object();
    if (!query)
        return Params; // return empty object
    var Pairs = query.split(/[;&]/);
    for (var i = 0; i < Pairs.length; i++) {
        var KeyVal = Pairs[i].split('=');
        if (!KeyVal || KeyVal.length != 2)
            continue;
        var key = unescape(KeyVal[0]);
        var val = unescape(KeyVal[1]);
        val = val.replace(/\+/g, ' ');
        Params[key] = val;
    }
    return Params;
}
$("select[name='id'] option").each(function () {
    var that = this;
    if ($(that).html().indexOf("and above)") > -1) {
        $(that).remove();
    }
});
if ($("select[name='id'] option").length < 1) {
    //$("select[name='id']").hide();
} else if ($("select[name='id'] option").length == 1) {
    if ($("select[name='id'] option").html().indexOf("Default Title") > -1) {
        //$("select[name='id']").hide();
    }
}


//shop name
var shopName = params.shop;

//replace shortcode
$("form[action='/cart/add']").each(function () {
    var that = $(this);
    //console.log(that.closest(".product-item").length);
    if (that.closest(".product-item").length < 1) {
        that.append("<div class='groupsTable' style='width:100%'></div>");
    }
});
//detect url
var pathArray = window.location.pathname.split('/');
var product = $.inArray('products', pathArray);
var cart = $.inArray('cart', pathArray);
var collectionPage = $.inArray('collections', pathArray);
var collection = pathArray[collectionPage + 1];
if (cart > -1) {
    if (typeof jsonCart != 'undefined') {
        var result = jsonCart;
        var items = result.items;
        for (var i = 0; i < items.length; i++) {
            items[i].product_description = '';
            items[i].product_title = '';
            items[i].title = '';
        }
        var data = {
            items: items,
            action: "updateCart",
            shop: shopName
        };
        if (typeof customerTags != "undefined") {
            data.customer_tag = customerTags;
        }
        $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (response) {
            if (typeof response == "string") {
                response = JSON.parse(response);
            }
            if (response.expired) {
                $(".loadingText").hide();
                $("[name=checkout]").attr("disabled", false);
                $('#additional-checkout-buttons, .additional-checkout-button').show();
            } else {
                response = response.result;
                if (response) {
                    if (response.length >= 1) {
                        var deleteList = [];
                        var addList = [];
                        for (var i = 0; i < items.length; i++) {
                            updateCart(response, items[i], deleteList, addList, items);
                        }
                        var deleteData = {
                            updates: {}
                        };
                        for (var i = 0; i < deleteList.length; i++) {
                            deleteData.updates[deleteList[i].id] = deleteList[i].quantity;
                        }
                        $.ajax({
                            type: "POST",
                            url: "/cart/update.js",
                            dataType: "json",
                            data: deleteData,
                            success: function (result) {
                                var addData = {
                                    updates: {}
                                };
                                for (var i = 0; i < addList.length; i++) {
                                    addData.updates[addList[i].id] = addList[i].quantity;
                                }
                                $.ajax({
                                    type: "POST",
                                    url: "/cart/update.js",
                                    dataType: "json",
                                    data: addData,
                                    success: function (result) {
                                        if(addData.length != 0 && deleteList != 0){ 
                                            location.reload();
                                        }  
                                    }
                                });
                            }
                        });
                    }
                    else {
                        var data = {};
                        data.shop = shopName;
                        data.action = "checkLimitOrder";
                        data["variants[]"] = [];
                        var temp = 0;
                        $("[name='updates[]']").each(function () {
                            var that = this;
                            var variantId = $(this).attr("data-id");
                            var variant = {
                                variantId: variantId,
                                quantity: $(that).val()
                            };
                            data["variants[" + temp + "]"] = variant;
                            temp = temp + 1;
                        });
                        $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
                            if (typeof result == "string") {
                                result = JSON.parse(result);
                            }
                            $(".loadingText").hide();
                            var limitVariants = result.limitVariant;
                            var changeLinkVariants = result.changeVriantlink;
                            var valid = true;
                            var data = {};
                            data.action = "getSettings";
                            data.shop = shopName;
                            $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
                                if (typeof result == "string") {
                                    result = JSON.parse(result);
                                }
                                result = result.settings;
                                var settings = result;
                                settings.table_width = settings.table_width.replace("px", "");
                                settings.table_width = settings.table_width.replace(" ", "");
                                if (settings.table_width.indexOf("%") == -1) {
                                    settings.table_width = settings.table_width + "px";
                                }
                                var styles = "<style>\
                                    .errorTooltip{\
                                        border: " + settings.limit_border_size + "px " + settings.limit_border_color + " solid;\
                                        color: " + settings.limit_text_color + ";\
                                        background: " + settings.limit_background + ";\
                                        font-size: " + settings.limit_text_size + "px;\
                                    }\
                                    .inputError {\
                                        border: " + settings.input_border_size + "px solid " + settings.input_border_color + " !important;\
                                    }\
                                    .priceGroupList, .productLimits {\
                                        border: " + settings.table_border_size + "px " + settings.table_border_color + " solid;\
                                        font-size: " + settings.table_text_size + "px;\
                                        color: " + settings.table_text_color + ";\
                                        width: " + settings.table_width + ";\
                                    }\
                                    .quantity-break-header, .limit-header {\
                                        font-size: " + settings.table_heading_size + "px;\
                                        color: " + settings.table_heading_color + ";\
                                        margin-bottom: 0px;\
                                    }\
                                    .priceGroupList {\
                                        margin-bottom: 25px;\
                                    }\\n\
                                    .selector-wrapper {\n\
                                        display: none;\n\
                                    }\
                                </style>";
                                $("body").append(styles);

                                $("[name='updates[]']").each(function () {
                                    var that = this;
                                    var variantId = $(this).attr("data-id");
                                    var quantity = parseInt($(this).val());
                                    limitVariants.forEach(function (item) {
                                        if (item.custom_variant) {
                                            item.variant_id = item.custom_variant.customVariant;
                                        }
                                        if (item.variant_id == variantId) {
                                            if (quantity < parseInt(item.min)) {
                                                valid = false;
                                                $("[name=checkout]").attr("disabled", true);
                                                $(".requiredNotification").addClass("error");
                                                $(that).addClass("inputError");
                                                $(that).parent().css("position", "relative");
                                                $(that).parent().append("<div class='errorTooltip'>" + settings.min_text + " <span class='ot-number'>" + item.min + "</span></div>");
                                                $(".requiredNotification").html("Some item in cart is not valid, pls check !");
                                            } else if (quantity > parseInt(item.max) && parseInt(item.max) != 0) {
                                                valid = false;
                                                $("[name=checkout]").attr("disabled", true);
                                                $(".requiredNotification").addClass("error");
                                                $(that).addClass("inputError");
                                                $(that).parent().css("position", "relative");
                                                $(that).parent().append("<div class='errorTooltip'>" + settings.max_text + " <span class='ot-number'>" + item.max + "</span></div>");
                                                $(".requiredNotification").html("Some item in cart is not valid, pls check !");
                                            }
                                        }
                                    });
                                });
                                if (valid) {
                                    $("a").each(function () {
                                        var elem = $(this);
                                        var href = elem.attr("href");
                                        if (href) {
                                            if (href.indexOf("variant=") > -1) {
                                                href = href.split("/");
                                                href = href[href.length - 1];
                                                href = href.split("=");
                                                var variantId = href[1];
                                                changeLinkVariants.forEach(function (item) {
                                                    if (item.customVariant == variantId) {
                                                        var newHref = elem.attr("href").replace(variantId, item.variantId);
                                                        elem.attr("href", newHref);
                                                    }
                                                });
                                            }
                                        }
                                    });
                                    $(".requiredNotification").removeClass("error");
                                    $(".inputError").removeClass("inputError");
                                    $("[name=checkout]").attr("disabled", false);
                                    $('#additional-checkout-buttons, .additional-checkout-button').show();
                                }
                            });
                        });
                    }
                }
                else {
                    $("[name=checkout]").attr("disabled", false);
                    $('#additional-checkout-buttons, .additional-checkout-button').show();
                    $(".loadingText").hide();
                }
            }
        });
    } else {
        $.ajax({
            type: 'GET',
            url: '/cart.js',
            cache: false,
            dataType: 'json',
            success: function (result) {
                var items = result.items;
                for (var i = 0; i < items.length; i++) {
                    items[i].product_description = '';
                    items[i].product_title = '';
                    items[i].title = '';
                }
                var data = {
                    items: items,
                    action: "updateCart",
                    shop: shopName
                };
                if (typeof customerTags != "undefined") {
                    data.customer_tag = customerTags;
                }
                $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (response) {
                    if (typeof response == "string") {
                        response = JSON.parse(response);
                    }
                    if (response.expired) {
                        $(".loadingText").hide();
                        $("[name=checkout]").attr("disabled", false);
                        $('#additional-checkout-buttons, .additional-checkout-button').show();
                    } else {
                        response = response.result;
                        if (response) {
                            if (response.length >= 1) {
                                var deleteList = [];
                                var addList = [];
                                for (var i = 0; i < items.length; i++) {
                                    updateCart(response, items[i], deleteList, addList, items);
                                }
                                var deleteData = {
                                    updates: {}
                                };
                                for (var i = 0; i < deleteList.length; i++) {
                                    deleteData.updates[deleteList[i].id] = deleteList[i].quantity;
                                }
                                //console.log("addList",addList);
                                //console.log("deleteData",deleteData);
                                
                                $.ajax({
                                    type: "POST",
                                    url: "/cart/update.js",
                                    dataType: "json",
                                    data: deleteData,
                                    success: function (result) {
                                        var addData = {
                                            updates: {}
                                        };
                                        for (var i = 0; i < addList.length; i++) {
                                            addData.updates[addList[i].id] = addList[i].quantity;
                                        }
                                        $.ajax({
                                            type: "POST",
                                            url: "/cart/update.js",
                                            dataType: "json",
                                            data: addData,
                                            success: function (result) {
                                                if(addData.length != 0 && deleteList != 0){
                                                    location.reload();
                                                } 
                                            }
                                        });
                                    }
                                });
                            }
                            else {
                                var data = {};
                                data.shop = shopName;
                                data.action = "checkLimitOrder";
                                data["variants[]"] = [];
                                var temp = 0;
                                $("[name='updates[]']").each(function () {
                                    var that = this;
                                    var variantId = $(this).attr("data-id");
                                    var variant = {
                                        variantId: variantId,
                                        quantity: $(that).val()
                                    };
                                    data["variants[" + temp + "]"] = variant;
                                    temp = temp + 1;
                                });
                                $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
                                    if (typeof result == "string") {
                                        result = JSON.parse(result);
                                    }
                                    $(".loadingText").hide();
                                    var limitVariants = result.limitVariant;
                                    var changeLinkVariants = result.changeVriantlink;
                                    var valid = true;
                                    var data = {};
                                    data.action = "getSettings";
                                    data.shop = shopName;
                                    $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
                                        if (typeof result == "string") {
                                            result = JSON.parse(result);
                                        }
                                        result = result.settings;
                                        var settings = result;
                                        settings.table_width = settings.table_width.replace("px", "");
                                        settings.table_width = settings.table_width.replace(" ", "");
                                        if (settings.table_width.indexOf("%") == -1) {
                                            settings.table_width = settings.table_width + "px";
                                        }
                                        var styles = "<style>\
                                            .errorTooltip{\
                                                border: " + settings.limit_border_size + "px " + settings.limit_border_color + " solid;\
                                                color: " + settings.limit_text_color + ";\
                                                background: " + settings.limit_background + ";\
                                                font-size: " + settings.limit_text_size + "px;\
                                            }\
                                            .inputError {\
                                                border: " + settings.input_border_size + "px solid " + settings.input_border_color + " !important;\
                                            }\
                                            .priceGroupList, .productLimits {\
                                                border: " + settings.table_border_size + "px " + settings.table_border_color + " solid;\
                                                font-size: " + settings.table_text_size + "px;\
                                                color: " + settings.table_text_color + ";\
                                                width: " + settings.table_width + ";\
                                            }\
                                            .quantity-break-header, .limit-header {\
                                                font-size: " + settings.table_heading_size + "px;\
                                                color: " + settings.table_heading_color + ";\
                                                margin-bottom: 0px;\
                                            }\
                                            .priceGroupList {\
                                                margin-bottom: 25px;\
                                            }\\n\
                                            .selector-wrapper {\n\
                                                display: none;\n\
                                            }\
                                        </style>";
                                        $("body").append(styles);

                                        $("[name='updates[]']").each(function () {
                                            var that = this;
                                            var variantId = $(this).attr("data-id");
                                            var quantity = $(this).val();
                                            limitVariants.forEach(function (item) {
                                                if (item.custom_variant) {
                                                    item.variant_id = item.custom_variant.customVariant;
                                                }
                                                if (item.variant_id == variantId) {
                                                    if (quantity < parseInt(item.min)) {
                                                        valid = false;
                                                        $("[name=checkout]").attr("disabled", true);
                                                        $(".requiredNotification").addClass("error");
                                                        $(that).addClass("inputError");
                                                        $(that).parent().css("position", "relative");
                                                        $(that).parent().append("<div class='errorTooltip'>" + settings.min_text + " <span class='ot-number'>" + item.min + "</span></div>");
                                                        $(".requiredNotification").html("Some item in cart is not valid, pls check !");
                                                    } else if (quantity > parseInt(item.max) && parseInt(item.max) != 0) {
                                                        valid = false;
                                                        $("[name=checkout]").attr("disabled", true);
                                                        $(".requiredNotification").addClass("error");
                                                        $(that).addClass("inputError");
                                                        $(that).parent().css("position", "relative");
                                                        $(that).parent().append("<div class='errorTooltip'>" + settings.max_text + " <span class='ot-number'>" + item.max + "</span></div>");
                                                        $(".requiredNotification").html("Some item in cart is not valid, pls check !");
                                                    }
                                                }
                                            });
                                        });
                                    });
                                    if (valid) {
                                        $("a").each(function () {
                                            var elem = $(this);
                                            var href = elem.attr("href");
                                            if (href) {
                                                if (href.indexOf("variant=") > -1) {
                                                    href = href.split("/");
                                                    href = href[href.length - 1];
                                                    href = href.split("=");
                                                    var variantId = href[1];
                                                    changeLinkVariants.forEach(function (item) {
                                                        if (item.customVariant == variantId) {
                                                            var newHref = elem.attr("href").replace(variantId, item.variantId);
                                                            elem.attr("href", newHref);
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                        $(".requiredNotification").removeClass("error");
                                        $(".inputError").removeClass("inputError");
                                        $("[name=checkout]").attr("disabled", false);
                                        $('#additional-checkout-buttons, .additional-checkout-button').show();
                                    }
                                });
                            }
                        }
                        else {
                            $("[name=checkout]").attr("disabled", false);
                            $('#additional-checkout-buttons, .additional-checkout-button').show();
                            $(".loadingText").hide();
                        }
                    }
                });
            }
        });
    }
    //} else if (product > -1) {
} else {
    var tableWrap = $(".groupsTable");
    var productHandle = pathArray[product + 1];
    var fullUrl = window.location.href;
    var variantId = "";
    if (fullUrl.indexOf("variant=") > -1) {
        var fullUrlParts = fullUrl.split("variant=");
        variantId = fullUrlParts["1"];
    }
    else if ($("select[name='id']")) {
        variantId = $("select[name='id']").val();
    }
    getSettings(variantId, tableWrap, productHandle);
}
function updateCart(response, item, deleteList, addList, items) {
    var customVariantId = 0;
    var cartQuantity = item.quantity;
    var quantity = 0;
    for (var j = 0; j < response.length; j++) {
        if (response[j].variant_id == item.variant_id) {
            for (var k = 0; k < items.length; k++) {
                if (items[k].variant_id == response[j].id) {
                    cartQuantity = cartQuantity + items[k].quantity;
                    break;
                }
            }
            var deleteData = {
                id: response[j].variant_id,
                quantity: 0
            };
            var updateData = {
                id: response[j].id,
                quantity: response[j].item_quantity
            };
            deleteList.push(deleteData);
            if (response[j].id != 0) {
                addList.push(updateData);
            }
        }
    }
}
function changeVariant(variantId, tableWrap, settings) {
    tableWrap.html("Loading ...");
    var data = {};
    data.shop = shopName;
    data.action = "getListPriceGroups";
    data.variantId = variantId;
    var text1 = $("form[action='/cart/add'] [type='submit']").html();

    if (typeof text1 == "undefined") {
        text1 = $("form[action='/cart/add'] button").html();
    }

    if (text1.indexOf("unavailable") > -1) {
        tableWrap.empty();
    } else {
        $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
            tableWrap.empty();
            if (result.groupsList.length > 0) {
                if (typeof shopCurrency == "undefined") {
                    shopCurrency = "";
                }
                var row = "";
                var amount_title = "";
                result.groupsList.forEach(function (item) {
                    var percent = 0;
                    var total_amount = "";
                    var total = "";

                    if (settings.show_total_amount == 1) {
                        amount_title = "<th>" + settings.total_amount_text + "</th>";
                        total_amount = parseFloat(item.number) * parseFloat(item.price);
                        total = parseFloat(total_amount).toFixed(2);
                        total = "<td>" + shopCurrency + total + "</td>";
                    }

                    if (settings.show_percent == 1) {
                        if (Number.isInteger(parseFloat(item.percent))) {
                            percent = parseInt(parseFloat(item.percent));
                        } else {
                            percent = parseFloat(item.percent);
                        }
                        row = row + "<tr><td>" + settings.table_text1 + " " + item.number + " " + settings.table_text + "</td><td>" + percent + "%</td>" + total + "</tr>";
                    } else {
                        row = row + "<tr><td>" + settings.table_text1 + " " + item.number + " " + settings.table_text + "</td><td>" + shopCurrency + parseFloat(item.price).toFixed(2) + "</td>" + total + "</tr>";
                    }
                });
                var groupsTable = "";
                if (settings.show_total_amount == 1) {
                    amount_title = "<th>" + settings.total_amount_text + "</th>";
                }
                settings.show_heading == 1 ? groupsTable = "<h3 class='quantity-break-header'>" + settings.groups_heading + "</h3><table class='priceGroupList'><tr><th>" + settings.group_table_heading + "</th><th>" + settings.price_table_heading + "</th>" + amount_title + "</tr></table>" : groupsTable = "<table class='priceGroupList'><tr><th>" + settings.group_table_heading + "</th><th>" + settings.price_table_heading + "</th>" + amount_title + "</tr></table>";
                tableWrap.append(groupsTable);
                $(".priceGroupList").append(row);
            }
            if (result.limits.length > 0) {
                $(".errorTooltip").remove();
                var row = "";
                result.limits.forEach(function (item) {
                    var qtyBox = $("[name='quantity']");
                    if (item.min == 0) {
                        item.min = "No limit";
                    } else {
                        if (qtyBox.length && settings.limit_on_product == 1) {
                            if (parseInt(qtyBox.val()) < item.min) {
                                qtyBox.closest('form').find('[name="add"]').attr("disabled", true);
                                qtyBox.addClass("inputError");
                                qtyBox.parent().css("position", "relative");
                                qtyBox.parent().append("<div class='errorTooltip'>" + settings.min_text + " <span class='ot-number'>" + item.min + "</span></div>");
                            } else {
                                $('.errorTooltip').remove();
                                qtyBox.closest('form').find('[name="add"]').removeAttr("disabled");
                                qtyBox.removeClass("inputError");
                            }
                            qtyBox.unbind('change').change(function () {
                                if (parseInt(qtyBox.val()) < item.min) {
                                    qtyBox.closest('form').find('[name="add"]').attr("disabled", true);
                                    qtyBox.addClass("inputError");
                                    qtyBox.parent().css("position", "relative");
                                    qtyBox.parent().append("<div class='errorTooltip'>" + settings.min_text + " <span class='ot-number'>" + item.min + "</span></div>");
                                } else {
                                    $('.errorTooltip').remove();
                                    qtyBox.closest('form').find('[name="add"]').removeAttr("disabled");
                                    qtyBox.removeClass("inputError");
                                }
                            });
                        }
                    }
                    if (item.max == 0) {
                        item.max = "No limit";
                    } else {
                        if (qtyBox.length && settings.limit_on_product == 1) {
                            if (parseInt(qtyBox.val()) > item.max) {
                                qtyBox.closest('form').find('[name="add"]').attr("disabled", true);
                                qtyBox.addClass("inputError");
                                qtyBox.parent().css("position", "relative");
                                qtyBox.parent().append("<div class='errorTooltip'>" + settings.max_text + " <span class='ot-number'>" + item.max + "</span></div>");
                            } else {
                                $('.errorTooltip').remove();
                                qtyBox.closest('form').find('[name="add"]').removeAttr("disabled");
                                qtyBox.removeClass("inputError");
                            }
                            qtyBox.unbind('change').change(function () {
                                if (parseInt(qtyBox.val()) > item.max) {
                                    qtyBox.closest('form').find('[name="add"]').attr("disabled", true);
                                    qtyBox.addClass("inputError");
                                    qtyBox.parent().css("position", "relative");
                                    qtyBox.parent().append("<div class='errorTooltip'>" + settings.max_text + " <span class='ot-number'>" + item.max + "</span></div>");
                                } else {
                                    $('.errorTooltip').remove();
                                    qtyBox.closest('form').find('[name="add"]').removeAttr("disabled");
                                    qtyBox.removeClass("inputError");
                                }
                            });
                        }
                    }
                    row = row + "<tr><td>" + item.min + "</td><td>" + item.max + "</td></tr>";
                });
                var limitsTable = "";
                settings.show_heading == 1 ? limitsTable = "<h3 class='limit-header'>" + settings.limits_heading + "</h3><table class='productLimits'><tr><th>" + settings.min_table_heading + "</th><th>" + settings.max_table_heading + "</th></tr></table>" : limitsTable = "<table class='productLimits'><tr><th>" + settings.min_table_heading + "</th><th>" + settings.max_table_heading + "</th></tr></table>";
                tableWrap.append(limitsTable);
                $(".productLimits").append(row);
            } else {
                var qtyBox = $("[name='quantity']");
                $('.errorTooltip').remove();
                qtyBox.closest('form').find('[name="add"]').removeAttr("disabled");
                qtyBox.removeClass("inputError");
            }
        });
    }
}
function getSettings(variantId, tableWrap, productHandle) {
    var data = {};
    data.action = "getSettings";
    data.shop = shopName;
    $.post("https://apps.omegatheme.com/group-price-attribute/custom-order.php", data, function (result) {
        if (typeof result == "string") {
            result = JSON.parse(result);
        }
        if (result.expired) {
            $(".loadingText").hide();
            $("[name=checkout]").attr("disabled", false);
            $('#additional-checkout-buttons, .additional-checkout-button').show();
        } else {
            result = result.settings;
            result.use_tag = parseInt(result.use_tag);
            if (result.use_tag && result.customer_tag) {
                if (typeof customerTags != "undefined") {
                    if (customerTags.indexOf(result.customer_tag) > -1) {
                        var settings = result;
                        settings.table_width = settings.table_width.replace("px", "");
                        settings.table_width = settings.table_width.replace(" ", "");
                        if (settings.table_width.indexOf("%") == -1) {
                            settings.table_width = settings.table_width + "px";
                        }
                        var styles = "<style>\
                            .errorTooltip{\
                                border: " + settings.limit_border_size + "px " + settings.limit_border_color + " solid;\
                                color: " + settings.limit_text_color + ";\
                                background: " + settings.limit_background + ";\
                                font-size: " + settings.limit_text_size + "px;\
                            }\
                            .inputError {\
                                border: " + settings.input_border_size + "px solid " + settings.input_border_color + " !important;\
                            }\
                            .priceGroupList, .productLimits {\
                                border: " + settings.table_border_size + "px " + settings.table_border_color + " solid;\
                                font-size: " + settings.table_text_size + "px;\
                                color: " + settings.table_text_color + ";\
                                width: " + settings.table_width + ";\
                            }\
                            .quantity-break-header, .limit-header {\
                                font-size: " + settings.table_heading_size + "px;\
                                color: " + settings.table_heading_color + ";\
                                margin-bottom: 0px;\
                            }\
                            .priceGroupList {\
                                margin-bottom: 25px;\
                            }\\n\
                            .selector-wrapper {\n\
                                display: none;\n\
                            }\
                        </style>";
                        $("body").append(styles);
                        var select = $("[name='id']");
                        select.change(function () {
                            changeVariant($(this).val(), tableWrap, settings);
                        });
                        if (variantId) {
                            changeVariant(variantId, tableWrap, settings);
                        } else {
                            $.getJSON("/products/" + productHandle + ".js", function (product) {
                                var variantId = product.variants[0].id;
                                changeVariant(variantId, tableWrap, settings);
                            });
                        }
                        $(".selector-wrapper select").change(function () {
                            changeVariant(select.val(), tableWrap, settings);
                        });
                        $(".radio-wrapper label").click(function () {
                            setTimeout(function () {
                                changeVariant(select.val(), tableWrap, settings);
                            }, 0);
                            //            changeVariant(select.val(), tableWrap, settings);
                        });
                    }
                }
            } else {
                var settings = result;
                settings.table_width = settings.table_width.replace("px", "");
                settings.table_width = settings.table_width.replace(" ", "");
                if (settings.table_width.indexOf("%") == -1) {
                    settings.table_width = settings.table_width + "px";
                }
                var styles = "<style>\
                    .errorTooltip{\
                        border: " + settings.limit_border_size + "px " + settings.limit_border_color + " solid;\
                        color: " + settings.limit_text_color + ";\
                        background: " + settings.limit_background + ";\
                        font-size: " + settings.limit_text_size + "px;\
                    }\
                    .inputError {\
                        border: " + settings.input_border_size + "px solid " + settings.input_border_color + " !important;\
                    }\
                    .priceGroupList, .productLimits {\
                        border: " + settings.table_border_size + "px " + settings.table_border_color + " solid;\
                        font-size: " + settings.table_text_size + "px;\
                        color: " + settings.table_text_color + ";\
                        width: " + settings.table_width + ";\
                    }\
                    .quantity-break-header, .limit-header {\
                        font-size: " + settings.table_heading_size + "px;\
                        color: " + settings.table_heading_color + ";\
                        margin-bottom: 0px;\
                    }\
                    .priceGroupList {\
                        margin-bottom: 25px;\
                    }\\n\
                    .selector-wrapper {\n\
                        display: none;\n\
                    }\
                </style>";
                $("body").append(styles);
                var select = $("[name='id']");
                select.change(function () {
                    changeVariant($(this).val(), tableWrap, settings);
                });
                if (variantId) {
                    changeVariant(variantId, tableWrap, settings);
                } else {
                    $.getJSON("/products/" + productHandle + ".js", function (product) {
                        var variantId = product.variants[0].id;
                        changeVariant(variantId, tableWrap, settings);
                    });
                }
                $(".selector-wrapper select").change(function () {
                    changeVariant(select.val(), tableWrap, settings);
                });
                $(".radio-wrapper label").click(function () {
                    setTimeout(function () {
                        changeVariant(select.val(), tableWrap, settings);
                    }, 0);
                    //            changeVariant(select.val(), tableWrap, settings);
                });
            }

        }
    });
}