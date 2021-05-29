<template>
    <div class=""> 
        <b-container fluid>
            <div class="info">
                Add rule for entire store
                This campaign will be used for all collections, products and product variants that don't have it own campaign. 
                The discount will be applied by priority below (higher is on the left)  <br>
                Priority of: <b>Product Variant</b> > <small>is higher than</small>> <b>Product</b> ><small>is higher than </small>>
                <b>Collection</b> ><small> is higher than </small>> <b>Entire</b>
            </div>  
            <div class="content-global">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <b-form-group >
                                <b-form-radio-group class="btnradios"
                                    buttons
                                    v-model="activeGroup"
                                    :options="activeOption" 
                                    :name="'status'"
                                    @change="changStatusGlobal()" /> 
                            </b-form-group>
                        </div> 
                        <div class="col-md-6">
                             <a class="btn btn-primary addTies" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                        </div>
                    </div> 
                    <div class="row">   
                        <div class="col-md-12">
                            <div class="table-responsive" style="margin: unset;max-height: 350px; overflow-y: scroll;">
                                <div class="loading" v-if="groups.length == 0">
                                    <img src="admin/version1/images/loading.gif" alt="loading">
                                </div>
                                <table class="table table-hover"> 
                                    <tbody>
                                        <tr> 
                                            <td>
                                                <label>Start date </label>
                                                <input type="date" class="form-control"  v-model="start_date" placeholder="Start date">
                                            </td> 
                                            <td>
                                                <label>End date</label>
                                                <input type="date" class="form-control" v-model="end_date" placeholder="End date">
                                            </td>   
                                        </tr> 
                                        <tr v-for="(group,indexGroups) of groups" :key="indexGroups">
                                            <td>
                                                <label for="">Minimum Qty</label>
                                                <input type="number" min="1" v-model="group.number" :class="'defaultNumber_'+indexGroups" class="form-control ot-input-quantity" placeholder="Quantity">
                                            </td>
                                            <td>
                                                <label for="">Discount Per Item</label>
                                                <div class="input-group"> 
                                                    <input type="text" class="form-control discount" :class="'defaultPrice_'+indexGroups" v-model="group.price" placeholder="Value">
                                                    <b-form-group>
                                                        <b-form-radio-group class="btnradios"
                                                            buttons
                                                            v-model  = "group.discountType"
                                                            :options = "options"
                                                            :name    = "'radiosBtnDefault'+indexGroups" /> 
                                                    </b-form-group>
                                                </div>
                                            </td>
                                            <td>
                                                <a class="btn btn-delete deleteTies"  style="" @click="otTrashOffer(indexGroups)">  <i class="fa fa-trash-o"></i>   </a> 
                                            </td>
                                        </tr> 
                                        <tr> 
                                            <td style="padding: 0px"><a class="addTies text-center btn-primary" @click="otAddOffer" >  Add line</a></td>
                                            <td></td>
                                            <td>
                                                <button class="btn btn-default"  style="float:left;"  @click="saveGlobal()"  v-if=" ruleForCustomerTag.length == 0">  Save</button>  
                                            </td>
                                        </tr> 
                                    </tbody> 
                                    <tbody v-for="(element,index) in ruleForCustomerTag" :key="index" class="listCustomerTag">
                                        <tr>
                                            <td cols="2">
                                                <p> </p>
                                                <h5 class="titleAddRuleForCustomerTag"> Add new rule for customer tag{{": "+element.tag}} </h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td >
                                                <label>Enter Customer Tag</label>
                                                <input type="text" v-model="element.tag" class="form-control" :class="'tag_'+index" >
                                                <small>Please enter a comma-separated list of tags</small>
                                            </td>  
                                            <td>
                                                <label for="">Discount Per Item</label>
                                                <div class="input-group"> 
                                                    <input type="text" class="form-control discount" :class="'price_'+index" v-model="element.price" placeholder="Value">
                                                    <b-form-group>
                                                        <b-form-radio-group class="btnradios"
                                                            buttons
                                                            v-model  = "element.discountType"
                                                            :options = "options"
                                                            :name    = "'radiosBtnDefault'+index" /> 
                                                    </b-form-group>
                                                </div>
                                            </td>
                                            <td>
                                                <a class="btn btn-delete deleteTies"  style="" @click="otTrashOfferCustomerTag(index)">  <i class="fa fa-trash-o"></i>   </a> 
                                            </td>
                                        </tr> 
                                        <tr> 
                                            <td style="padding: 0px"> </td>
                                            <td></td>
                                            <td>
                                                <button class="btn btn-default"  style="float:left;"  @click="saveGlobal()"  v-if=" ruleForCustomerTag.length != 0">  Save</button>  
                                            </td>
                                        </tr> 
                                    </tbody>
                                </table>  
                            </div> 
                        </div>
                    </div> 
                </div>  
            </div> 
       </b-container> 
    </div>
</template>
 
<script>

    module.exports = {
        props: [],
        data: function() {
            return {  
                /*
                    groups       : list tier rule in content global rule
                    activeGroup  : default status global rule is active
                    activeOption : option status global rule (active, deactive)
                    options      : type rule in each tier rule (%,$) global have just 1 type rule : %
                    start_date, endate : manage schedule tier (Y,m,d)
                */
                groups       : [{price: null, number: null, discountType: 'percent'}],  
                activeGroup  : 'active',
                activeOption :  [ { text: 'Active',   value: 'active' },
                                  { text: 'Deactive', value: 'deactive' }
                                ], 
                options      : [ { text: '%', value: 'percent' }],
                start_date   : null,
                ruleForCustomerTag : [],
                end_date     : null 
            };
        },
        mounted: function() {
            let self = this;
            self.getGlobal();
        },
        methods : {
            otAddOffer: function () {
                let self = this;
                console.log(self.validBeforeAddOffer())
                if(self.validBeforeAddOffer() == true){
                    self.groups.push({price: null, number: null, discountType: 'percent'});
                }else{
                    ShopifyApp.flashError("Please enter all fields before continue !");  
                } 
            }, 
             otTrashOfferCustomerTag:function(index){
                let self = this;
                ShopifyApp.Modal.confirm({ 
                    title: "Delete rule",
                    message: "Do you want to delete rule ?",
                    okButton: "Confirm Remove",
                    cancelButton: "Cancel",
                    style: "danger" 
                }, function(res){
                    if(res){
                        self.ruleForCustomerTag.splice(index, 1);
                    } 
                }); 
            },
            validBeforeAddOffer:function(){
                var result = true;
                var self = this;
                $(".form-control").removeClass("error");
                console.log(self.groups)
                self.groups.forEach(function(element,index){ 
                    if(element.price == "" || element.number == "" || element.price == null || element.number == null ){
                        result =  false;
                        if(element.number == "" || element.number == null){
                            $(".defaultNumber_"+index).addClass("error");
                        }
                        if(element.price == "" || element.price == null){
                            $(".defaultPrice_"+index).addClass("error");
                        }  
                    }
                }) 
                return result;
            },
            validBeforeAddRuleForCustomerTag:function(){
                var result = true;
                var self = this;
                $(".form-control").removeClass("error");
                if(self.ruleForCustomerTag.length > 0){
                    self.ruleForCustomerTag.forEach(function(element,index){ 
                    if(element.tag == "" || element.price == ""){
                        if(element.tag == ""){
                            $(".tag_"+index).addClass("error");
                        }
                        if(element.price == ""){
                            $(".price_"+index).addClass("error");
                        } 
                        result =  false;
                    }
                }) 
                } 
                
                return result;
            },
            otAddRuleForCustomerTag:function(){
                let self = this;
                console.log("self.ruleForCustomerTag",self.ruleForCustomerTag)
                console.log(typeof self.ruleForCustomerTag)
                if(typeof self.ruleForCustomerTag != "array"){
                    self.ruleForCustomerTag = [];
                }
                if(self.validBeforeAddRuleForCustomerTag() ==  true){
                    self.ruleForCustomerTag.push({
                        "tag":"",
                        "price":"",
                        "discountType": 'percent'
                    });
                }else{
                    ShopifyApp.flashError("Please enter all fields before continue !");  
                }
                
            },
            otTrashOffer: function (index) {
                // delete 1 tied rule 
                let self = this ;
                ShopifyApp.Modal.confirm({ 
                    title       : "Delete rule",
                    message     : "Do you want to delete rule ?",
                    okButton    : "Confirm delete",
                    cancelButton: "Cancel",
                    style       : "danger"
                }, function(res){
                    if(res){
                        if(self.groups.length == 1){
                            // Neu xoa con 1 tied thì default 1 tied null khong xoa het
                            self.groups = [{price: null, number: null, discountType: 'percent'}];
                        }else{
                            self.groups.splice(index, 1);
                        }
                    } 
                }); 
            }, 
            changStatusGlobal:function(){
                // active or deactive global rule 
                let self = this  ;
                if (self.activeGroup == "active") { 
                    ShopifyApp.Modal.confirm({ 
                        title       : "Do you want to change status ?",
                        message     : "Global's rule deactived, all rule of product or collection or variant still works normally.",
                        okButton    : "Yes, I want",
                        cancelButton: "No", 
                    }, function(res){
                        if(res)  self.activeGroup = "deactive";  
                        self.saveGlobal();
                    });
                }else{
                    self.activeGroup = "active";  
                    self.saveGlobal();
                }
            }, 
            saveGlobal: function () {  
                // save or change status rule
                let self = this;
                var errorValid;
                errorValid = self.checkValidCreate();
                if (errorValid) {  
                    ShopifyApp.flashError(errorValid);
                } else {
                    self.$http.get('services_v2.php',{
                        params:{
                            action      : "saveGlobal",
                            shop        :shop,
                            group       :self.groups,
                            status      :self.activeGroup ,
                            start_date  :self.start_date,
                            end_date    :self.end_date,
                            ruleForCustomerTag    :self.ruleForCustomerTag,
                        }
                    }).then(response => {  
                        ShopifyApp.flashNotice("Save global rule success!");
                        self.getGlobal();
                    },response=>{
                        ShopifyApp.flashError("Save global rule error");
                    })
                }
            },
            getGlobal:function(){
                // get rule global
                let self = this;
                $.ajax({
                    url     : 'services_v2.php',
                    type    : 'GET',
                    data    : {action:'getGlobal',shop:shop},
                    dataType: 'json'
                }).done(function (result) {
                    if(result.content_rule  != null){
                        self.groups      = result.content_rule;
                        self.activeGroup = result.status;
                        self.start_date  = result.start_date;
                        self.end_date    = result.end_date;
                        self.ruleForCustomerTag    = result.ruleForCustomerTag;
                    } 
                }).fail((error) => {
                    ShopifyApp.flashError("Get global error!"); 
                });
            }, 
            checkValidCreate:function(){
                // check valid before save rule for global 
                let self = this;
                var error; 
                if (self.groups.length != 0) {
                    console.log(self.groups)
                    self.groups.forEach(function (element, index) {
                        if (element.price === null || element.number === null) {
                            return error = "Please check and enter the valid Quantity and Value of each Price Tier/Level.";  
                        }
                    })
                } else {
                   return error = "Please correct errors to continue";
                }
                if(self.start_date > self.end_date){ 
                    return error = "Please enter end date < start date !";
                }
                return error;
            },
           
        },
        components: { }
};
</script> 
<style src="admin/version1/styles/global.css?v="+window.version scoped></style>
 
