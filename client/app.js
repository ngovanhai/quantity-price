// v3
//debugger; 

if(typeof Shopify.designMode === "undefined"){
    shop = Shopify.shop;
    var page = __st.p;  
    $.ajaxSetup({
      cache: true
    }); 
    if(typeof window.moneyFormat != "undefined"){
        Shopify.money_format = window.moneyFormat;
        Shopify.money_with_currency_format = window.moneyFormat;
    
    }  
    var pathArray = window.location.pathname.split('/');
    var cart = $.inArray('cart', pathArray);
    var collection = $.inArray('collections', pathArray);
    var dataStore;
    
    if (typeof window.otQtyCheckExistFile === 'undefined') {
        otQuantityInit();
        window.otQtyCheckExistFile = false;
    } 
}else{
    showDesignMode();
}


async function otQuantityInit() {
    'use strict'
    let result = await checkExpire(); 
    let issetFormCart = false;
    dataStore = getDataStore();
    if(result.money_format != null){
        Shopify.money_format = result.money_format;
        Shopify.money_with_currency_format = result.money_with_currency_format;
    }   
    if(Shopify.shop == "andsup.myshopify.com"){
        Shopify.money_format = "¥{{amount_no_decimals}}";
        Shopify.money_with_currency_format = "¥{{amount_no_decimals}}";
    }
    if(Shopify.shop == "stories-flooring.myshopify.com"){
        Shopify.money_format = "£{{amount}}";
        Shopify.money_with_currency_format = "£{{amount}}";
    }
 

    if(Shopify.shop == "beemercy-2.myshopify.com"){
        debugger;
        Shopify.money_format = "£{{amount}}";
        Shopify.money_with_currency_format = "£{{amount}}";
    }
    let settings = result.settings;
    if (result.checkExpire === false && settings.enableApp == 1) {  
        window.usePriceRule = settings.usePriceRule;
        window.OTSettings = settings;
        window.settings = settings;
        let htmlcss = `    <style>  ${settings.customCss} </style> `
        $('body').append(htmlcss);
        // ------ Product page ------
        if (page == 'product') { 
            $.getScript(`${rootlinkQuantity}/client/quantityProductDetail.js?v=${result.version}`);
        }

        // ------ Collection page ------
        $.getScript(`${rootlinkQuantity}/client/collection.js?v=${result.version}`);

            // ------ Cart  page ------ 
        $.getScript(`${rootlinkQuantity}/client/ajaxCart.js?v=${result.version}`) ; 
        $.getScript(`${rootlinkQuantity}/client/limitOrder.js?v=${result.version}`) ; 
        if (cart > -1) {
            $.getScript(`${rootlinkQuantity}/client/quantityCart.js?v=${result.version}`);
        } 
    }
} 


// ------------ Get all rule to save file json cache in folder cache   ---------------
function getDataStore() {
    $.ajax({
        type: 'GET',
        data: { action: 'getDataStore', shop: shop },
        url: `${rootlinkQuantity}/quantity_break_v2.php`,
        dataType: 'json'
    }).done(result => {
        if (typeof result == "string") {
            result = JSON.parse(result);
        }
    })
} 

// ------------ Format money by class element   ---------------
function formatMoneyByClass(formatPrice){
    for (i = 0; i < $(`.${formatPrice}`).length; ++i) {
        $(`.${formatPrice}`)[i].innerText = Shopify.formatMoney($(`.${formatPrice}`)[i].innerText);
    }
}
 
// ------------ Check expire (run when return false)  ---------------
function checkExpire() {
    'use strict'
    return new Promise(resolve => {
        $.ajax({
            type: 'GET',
            data: { action: 'checkExpire', shop: shop },
            url: `${rootlinkQuantity}/quantity_break_v2.php`,
            dataType: 'json'
        }).done(result => {
            if (typeof result == "string") {
                result = JSON.parse(result);
            }
            resolve(result);
        })
    })
}

// ------------ Format money  ---------------

Shopify.formatMoney = function (cents, format) {
    if (typeof cents === 'string') {
        cents = cents.replace('.', '');
    }
    
    var value = '';
    var placeholderRegex = /\{\{\s*(\w+)\s*\}\}/;
    var formatString = (format || this.money_format);
    if(typeof format == "undefined" && Shopify.shop == "fest4all-dk.myshopify.com"){
        formatString = "{{amount_with_comma_separator}} Kr.";
    }
    if(format == ""){
        formatString = "$ {{amount}}";
    }
    function defaultOption(opt, def) {
        return (typeof opt == 'undefined' ? def : opt);
    }

    function formatWithDelimiters(number, precision, thousands, decimal) {
        precision = defaultOption(precision, 2);
        thousands = defaultOption(thousands, ',');
        decimal = defaultOption(decimal, '.');

        if (isNaN(number) || number == null) {
            return 0;
        }

        number = (number / 100.0).toFixed(precision);

        var parts = number.split('.'),
            dollars = parts[0].replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1' + thousands),
            cents = parts[1] ? (decimal + parts[1]) : '';

        return dollars + cents;
    }
    switch (formatString.match(placeholderRegex)[1]) {
        case 'amount':
            value = formatWithDelimiters(cents, 2);
            break;
        case 'amount_no_decimals':
            value = formatWithDelimiters(cents, 0);
            break;
        case 'amount_with_comma_separator':
            value = formatWithDelimiters(cents, 2, '.', ',');
            break;
        case 'amount_no_decimals_with_comma_separator':
            value = formatWithDelimiters(cents, 0, '.', ',');
            break;
    }
    return formatString.replace(placeholderRegex, value);
}
// ------------ End Format money  ---------------

 