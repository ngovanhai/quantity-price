<template> 
    <div class="wrapper_settings">  
        <b-container fluid>
            <div class="row">
                <!-- setting -->
                <div class="col-md-7" style="background: unset"> 
                     <!-- General Settings -->
                    <div class="card">
                        <p class="ot-title-tags"><i class="fa fa-cogs"></i> General Settings</p>
                        <div class="row">
                            <!-- enable app  -->
                            <div class="col-md-6 form-group">
                                <b-form-group >
                                    <b-form-radio-group class="btnradios"
                                        buttons
                                        v-model="settings.enableApp"
                                        :options="optionsEnableApp"
                                        :name="'status'"
                                        @change="changeEnableApp()"  /> 
                                </b-form-group> 

                                <b-form-group >
                                    <b-form-radio-group class="btnradios"
                                        buttons
                                        v-model="settings.usePriceRule"
                                        :options="optionsVersion"
                                        :name="'status'"
                                        @change="changeVersion()"  /> 
                                </b-form-group>  
                                <div>
                                    <p  style="margin-top:5px;" v-if="settings.usePriceRule == 1"><small>Click <b>use Draft Order Api</b> Api when you do not want discount box and customer can use 2 discounts. </small></p>
                                    <p style="margin-top:5px;" v-else><small>Click <b>use Price Rule Api</b> when you want the discount box to show up and customer can only use 1 discount.  </small></p>
                                </div>
                               
                            </div> 
                            <div class="col-md-6 form-group">
                                <button class="btn btn-primary" @click="reloadData()" style=" color:#6774c8 !important;">Sync data</button>
                                <p><small>if you dont find new product/collection/variant/customer, you can click "Sync data" to reload.</small></p>
                                <button class="btn btn-primary" @click="deleteAllRuleForVariant()" style=" color:#6774c8 !important;">Delete All Rule For Variant</button>
                             </div>
                        </div>  
                    </div> 
                     <!-- Quantity Break Settings  -->
                    <div class="card">
                         <p class="ot-title-tags"><i class="fa fa-cog"></i> Quantity Break Settings</p>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Layout</label>
                                 <b-form-select id="exampleInput3"
                                            :options="optionLayout"
                                            required
                                            v-model="settings.layout">
                                </b-form-select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Width</label>
                                <input class="form-control" v-model="settings.table_width">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Text size</label>
                                <input class="form-control" v-model="settings.table_text_size">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Heading Size</label>
                                <input class="form-control" v-model="settings.table_heading_size">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Border Size</label>
                                <input class="form-control" v-model="settings.table_border_size">
                            </div> 
                            <div class="col-md-3 form-group">
                                <label>Text color</label>
                                <input style="border: unset;" class="form-control" type="color" v-model="settings.table_text_color" >
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Heading color</label>
                                <input style="border: unset;" class="form-control" type="color" v-model="settings.table_heading_color">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Border color</label>
                                <input style="border: unset;" class="form-control" type="color" v-model="settings.table_border_color">
                            </div>
                            <div class="col-md-3" style="padding-top: 37px">
                                <b-form-checkbox id="checkbox4"
                                v-model="settings.show_heading"
                                value="1"
                                unchecked-value="0">
                                Show heading
                            </div>
                         </div>
                        <div class="row">
                            <div class="col-md-6"   v-if=" settings.layout =='table' ||  settings.layout =='table_horizontal'">
                                <b-form-group label="Show column layout table">
                                    <b-form-radio-group v-model="settings.showColumnLayoutTable"
                                                        :options="optionsShowColumnLayoutTable"
                                                        name="optionsShowColumnLayoutTable">
                                    </b-form-radio-group>
                                </b-form-group>
                            </div>
                            <div class="col-md-6">
                                <b-form-group label="Show price in product detail">
                                    <b-form-radio-group v-model="settings.show_percent"
                                                        :options="optionsShowPercent"
                                                        name="radioInline">
                                    </b-form-radio-group>
                                </b-form-group>
                            </div>
                         
                            <div class="col-md-6" v-if="settings.layout == 'table'  ||  settings.layout =='table_horizontal'" style="padding-top: 25px;">
                                 <b-form-checkbox id="checkbox2"
                                    v-model="settings.show_total_amount"
                                    value="1"
                                    unchecked-value="0">
                                    Show Total Amount column
                            </div>
                            <div class="col-md-6" style="padding-top: 25px;">
                                 <b-form-checkbox id="checkbox3"
                                    v-model="settings.showDiscountCode"
                                    value="1"
                                    unchecked-value="0">
                                    Show discount box in cart
                            </div>
                            <div class="col-md-3 "  v-if="settings.layout =='slide'">
                                <div class="form-group" style="margin-top: 20px">
                                    <b-form-checkbox id="checkbox3"
                                        v-model="settings.autoplay"
                                        value="1"
                                        unchecked-value="0">
                                        Autoplay
                                </div> 
                            </div>
                            <div class="col-md-3 form-group"  v-if="settings.layout =='slide'">
                                    <label>Color Price</label>
                                    <input style="border: unset;" class="form-control" type="color" v-model="settings.colorPrice" >
                            </div>
                            <div class="col-md-3 form-group" v-if="settings.autoplay=='1' && settings.layout =='slide'">
                                    <label>Speed</label>
                                    <input type="number" class="form-control" v-model="settings.speedSlide">
                            </div>
                            <div class="col-md-3 form-group"  >
                                    <label>Text after price </label>
                                    <input class="form-control" v-model="settings.textAfter">
                            </div> 
                            <div class="col-md-6 form-group">
                                <label>Quantity Break Heading</label>
                                <input class="form-control" v-model="settings.groups_heading">
                            </div>
                            <div class="col-md-6 form-group" v-if="settings.layout == 'table'  ||  settings.layout =='table_horizontal'">
                                <label>Quantity Break Table Heading</label>
                                <input class="form-control" v-model="settings.group_table_heading">
                            </div>
                            <div class="col-md-6 form-group"  v-if="settings.layout == 'slide'">
                                    <label>Number item</label>
                                    <input class="form-control" v-model="settings.numberItem">  
                            </div> 
                            <div class="col-md-6 form-group">
                                <label>Lable "and more"</label>
                                <input class="form-control" v-model="settings.table_text">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Label "Buy"</label>
                                <input class="form-control" v-model="settings.table_text1">
                                </div>
                          
                            <div class="col-md-6 form-group"  v-if="settings.layout == 'table'  ||  settings.layout =='table_horizontal' ||  settings.layout =='table_special_horizontal'">
                                <label>"Total amount" text</label>
                                <input class="form-control" v-model="settings.total_amount_text">
                            </div>
                            <div class="col-md-6 form-group" v-if="settings.showColumnLayoutTable == 0 || settings.layout == 'table'  ||  settings.layout =='table_horizontal'">
                                <label>Discount Price table heading</label>
                                <input class="form-control" v-model="settings.price_table_heading">
                            </div>
                            <div class="col-md-6 form-group"  v-if="settings.showColumnLayoutTable == 1 || settings.layout == 'table'  ||  settings.layout =='table_horizontal'">
                                <label>"Price Per Product" label</label>
                                <input class="form-control" v-model="settings.residual_text">
                            </div> 
                            <div class="col-md-6 form-group">
                                 <label for="">Label Price Collection</label> 
                                <input class="form-control"  type="text" v-model="settings.notificationInCollection">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Label in coupon</label>
                                <input class="form-control"  type="text" v-model="settings.labelCheckout">
                            </div> 
                            <div class="col-md-6 form-group">
                                 <label for="">Label notification in cart</label> 
                                <input class="form-control"  type="text" v-model="settings.notificationInCart">
                                <small>Please do not change the position as well as the value of 2 variables <b>%s</b> and <b>%d</b> will affect the results display</small>
                            </div>
                             <div class="col-md-6 form-group">
                                <label for="">Label notification multiple in cart</label> 
                                <input class="form-control"  type="text" v-model="settings.notificationMultiple">
                             </div>
                             
                        
                             <div class="col-md-6" style="padding-top: 30px;">
                                <b-form-checkbox id="checkbox1"
                                v-model="settings.use_tag"
                                value="1"
                                unchecked-value="0">
                                Enable tag for customer
                            </div> 
                            <div class="col-md-6 form-group" v-show="settings.use_tag == true">
                                <label>Type tag for customer</label>
                                 <b-form-select id="exampleInput3"
                                            :options="typeTagForCustomer"
                                            required
                                            v-model="settings.type_tag_for_customer">
                                </b-form-select>
                            </div>
                            <div class="col-md-12 form-group" v-show="settings.use_tag == true"> 
                                <label>Customer tag</label>
                                <input class="form-control" v-model="settings.customer_tag">
                            </div> 
                        </div> 
                        <div class="row">
                            <div class="col-md-12"> 
                                <button class="btn btn-default" @click="saveSettings()" style="margin:5px;float:right;">Save</button>
                            </div>
                        </div>
                    </div>
                    <!-- Limit Settings -->
                    <div class="card">  
                        <p class="ot-title-tags"><i class="fa fa-cog"></i> Limit Settings</p>
                        <div class="row">
                            <div class="col-md-6 form-group" style="padding-top: 16px;">
                                <b-form-checkbox id="limit_on_product"
                                    v-model="settings.limit_on_product"
                                    value="1"
                                    unchecked-value="0">
                                    Check limit on product page
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Min heading</label>
                                <input class="form-control" v-model="settings.min_table_heading">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Max heading</label>
                                <input class="form-control" v-model="settings.max_table_heading">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Limit headings</label>
                                <input class="form-control" v-model="settings.limits_heading">
                            </div> 
                            <div class="col-md-6 form-group">
                                <label>Minimum limit text</label>
                                <input class="form-control" v-model="settings.min_text"> 
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Maximum limit text</label>
                                <input class="form-control" v-model="settings.max_text">
                            </div>
                        </div>
                        <div class="row"> 
                            <div class="col-md-12 form-group">
                                <label for="">Custom Css:</label>
                                <b-form-textarea id="textarea1"
                                                v-model="settings.customCss"
                                                placeholder="Custom" 
                                                :rows="4"
                                                :max-rows="8">
                                </b-form-textarea> 
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-default" @click="saveSettings()" style="margin:5px;float:right;">Save</button>
                            </div>
                        </div> 
                    </div>   
                </div>
                <!-- preview  -->
                <div class="col-md-5 ot-preview">
                    <div class="card">
                         <p class="ot-title-tags">  Preview layout</p>
                        <h3 :style="{'color':settings.table_heading_color,'size':settings.table_heading_size}" v-show="settings.show_heading == true" class="quantity-break-header">{{settings.groups_heading}}</h3> 
                        <div v-show="settings.layout == 'table'">
                            <layout-table :settings="settings"></layout-table> 
                        </div> 
                        <div v-show="settings.layout == 'slide'">
                            <layout-slide :settings="settings"></layout-slide>
                        </div>
                        <div v-show="settings.layout == 'card'">
                            <layout-card :settings="settings"></layout-card>
                        </div>
                        <div v-show="settings.layout == 'addcart'"> 
                            <layout-addcart :settings="settings"></layout-addcart>
                        </div>
                        <div v-show="settings.layout == 'table_horizontal'">
                            <layout-table-horizontal :settings="settings"></layout-table-horizontal>
                        </div>
                        <div v-show="settings.layout == 'table_special'">
                            <layout-table-special :settings="settings"></layout-table-special>
                        </div>
                        <div v-show="settings.layout == 'table_special_horizontal'">
                            <layout-table-special-horizontal :settings="settings"></layout-table-special-horizontal>
                        </div>
                       <layout-limit :settings="settings"></layout-limit>
                    </div>
                </div>
            </div>
        </b-container> 
    <div> 
</template> 


  <script> 
 module.exports = {
  // bien truyen tu component cha
  props: [],
  data: function() {
    return {
      // khai bao bien
        settings:{},
        defaultColor: '#FF0000',
        optionLayout:[
            { text: 'Slide', value: "slide" },
            { text: 'Table', value: "table" },
            { text: 'Table Horizontal', value: "table_horizontal" },
            { text: 'Special Horizontal Table', value: "table_special_horizontal" },
            { text: 'Card', value: "card" }, 
            { text: 'Line Layout', value: "line" },  
            { text: 'Table add to cart', value: "addcart" },  
            { text: 'Show min/max in table', value: "table_special" }, 
        ],
        optionsEnableApp: [
            { text: 'Enable App', value: 1 },
            { text: 'Disable App', value: 0}
        ],
        optionsVersion: [
            { text: 'Use Draftorder Api', value: 1 },
            { text: 'Use Price Rule Api', value: 0}
        ],
        optionsShowPercent: [
            { text: 'Show percent', value: '1' },
            { text: 'Show price', value: '0' },
        ],
        optionsShowColumnLayoutTable: [
            { text: 'Show column discount per product', value: '0' },
            { text: 'Show price per product', value: '1' },
        ],
        typeTagForCustomer: [
            { text:"Enable all customer have tag ",value:1},
            { text:"Disable all customer have tag ",value:0}
        ],
    };
  },
  mounted: function() {
        var self = this
        self.getSetting();
        self.getDiscountCode(); 
        
  },
  methods : {
      changeEnableApp: function(){
        var self = this 
        if(self.settings.enableApp == 1){
                ShopifyApp.Modal.confirm({ 
                    title: "Please confirm Disable App",
                    message: "All created campaigns will be pause until you enable app again. ",
                    okButton: "Yes, i want",
                    cancelButton: "No",
                     
                }, function(res){
                    if(res){
                        self.settings.enableApp = 0;
                        self.saveSettings();
                    }else{
                        self.settings.enableApp = 1;
                    }
                });
        }else{
            self.settings.enableApp = 1;
            self.saveSettings();
        }
           
      },
      changeVersion:function(){
            var self = this 
            var data = {};
            data.action = "changeVersion";
            data.shop = shop;
            data.usePriceRule = self.settings.usePriceRule;
            $.ajax({
                url: 'services_v2.php',
                type: 'GET',
                data: data,
                dataType: 'json'
            }).done(function (result) {
                   self.getSetting()
                ShopifyApp.flashNotice("Change api successfully!"); 
                
            }).fail((error) => {
                ShopifyApp.flashNotice("Save settings error!");  
            });
      },
      deleteAllRuleForVariant:function(){
            var self = this 
            var data = {};
            data.action = "deleteAllRuleForVariant";
            data.shop = shop;
            $.ajax({
                url: 'services_v2.php',
                type: 'GET',
                data: data,
                dataType: 'json'
            }).done(function (result) {
                ShopifyApp.flashNotice("Delete  successfully!"); 
                
            }).fail((error) => {
                ShopifyApp.flashNotice("Delete  error!");  
            });
      },
      saveSettings: function (){
        var self = this 
        var data = {};
        data.action = "saveSettings";
        data.shop = shop;
        data.settings = self.settings;
        $.ajax({
            url: 'services_v2.php',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (result) {
            ShopifyApp.flashNotice("Save settings successfully!"); 
         }).fail((error) => {
            ShopifyApp.flashNotice("Save settings error!");  
        });
      },
      getSetting: function(){
        var self = this
        $.ajax({
            url: 'services_v2.php',
            type: 'GET',
            data: {action: 'getSettings',shop:shop},
            dataType: 'json'
        }).done(function (result) {
            if (typeof result == "string") {
                result = JSON.parse(result);
            }  
            self.settings = result   
        }).fail((error) => {
            ShopifyApp.flashNotice("Get settings error!");   
        });
      },
      reloadData: function(){
        var self = this
        $.ajax({
            url: 'services_v2.php',
            type: 'GET',
            data: {action: 'reloadData',shop:shop},
            dataType: 'json'
        }).done(function (result) {
             ShopifyApp.flashNotice("Reload data successfully!"); 
        }).fail((error) => {
            ShopifyApp.flashNotice("Get settings error!");   
        });
      },
      getDiscountCode:function(){
            var self = this
            $.ajax({
                url: 'services_v2.php',
                type: 'GET',
                data: {action: 'getTotalDiscountCode',shop:shop},
                dataType: 'json'
            }).done(function (result) {
                if(result != false){
                    if (typeof result == "string") {
                        result = JSON.parse(result);
                    }    
                    self.getDiscountCodeSaveToDB(0);  
                } 
            }).fail((error) => {
                ShopifyApp.flashNotice("Get settings error!");   
            });
      },
      getDiscountCodeSaveToDB(since_id){
            var self = this
            $.ajax({
                url: 'services_v2.php',
                type: 'GET',
                data: {action: 'getDiscountCodeSaveToDB',shop:shop,since_id:since_id}, 
                dataType: 'json'
            }).done(function (result) { 
                console.log("result getDiscountCodeSaveToDB:",result)
                if(result != false){
                    console.log("khong tao moi")
                    self.getDiscountCodeSaveToDB(result);
                } 
            }).fail((error) => {
                ShopifyApp.flashNotice("Get settings error!");   
            });
      },
      updatePlan:function(){
          window.location.href = "https://windy.omegatheme.com/group-price-attribute/services_v2.php?action=getProductPerPage&since_id=4326595723377&shop=test-quantity.myshopify.com"
      }
      
  },
  components: { 
      'layout-table': httpVueLoader(`admin/version1/components/layout/table.vue?v=${window.version}`), 
      'layout-slide': httpVueLoader(`admin/version1/components/layout/slide.vue?v=${window.version}`), 
      'layout-card' : httpVueLoader(`admin/version1/components/layout/card.vue?v=${window.version}`), 
      'layout-table-special' : httpVueLoader(`admin/version1/components/layout/table-special.vue?v=${window.version}`), 
      'layout-addcart' : httpVueLoader(`admin/version1/components/layout/addcart.vue?v=${window.version}`), 
      'layout-table-horizontal': httpVueLoader(`admin/version1/components/layout/table-horizontal.vue?v=${window.version}`), 
      'layout-table-special-horizontal': httpVueLoader(`admin/version1/components/layout/layout-table-special-horizontal.vue?v=${window.version}`), 
      'layout-limit': httpVueLoader(`admin/version1/components/layout/limit.vue?v=${window.version}`),
   }
};
</script>
<style scoped src="admin/version1/styles/settings.css?v=14"> </style>

 
