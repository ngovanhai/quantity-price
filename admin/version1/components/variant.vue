<template>
    <div>  
        <!-- 15/08/2019 -->
        <b-container>
            <b-row>
                <b-col md="5" style="    padding: 0;"> 
                    <div class="card">
                        <div class="otQuantityTitleListVariant" v-if="items.length != 0">Variant list of <b>{{titleProductFilter}}</b></div>
                        <div class="otQuantityTitleListVariant" v-else>Variant List </div>
                        <div class="fillterVariantByCollection" v-if="items.length != 0">
                            <b-form-input class="search_variant"  v-model="filter" placeholder="Search variant ..." ></b-form-input> 
                            <a class="btn btn-info addRuleForAll" @click="selectAllVariant()" >Add rule for all variants</a>
                            <div class="clear-fix"></div>
                        </div>
                        <div class="loading" v-if="showloadding == true">
                            <img src="admin/version1/images/loading.gif" alt="loading">
                        </div>
                        <b-table show-empty
                                stacked="md"
                                :bordered="true"
                                :hover="true"
                                :items="items"
                                :fields="fields"
                                :current-page="currentPage"
                                :per-page="perPage"  
                                :sort-by.sync="sortBy"
                                :sort-desc.sync="sortDesc"
                                :sort-direction="sortDirection" 
                                :filter="filter" 
                                :striped="true"
                                class="otVariantList"
                                @filtered="onFiltered" 
                            v-if="items.length != 0" style="margin-top:5px;">
                            <template slot="HEAD_actions" slot-scope="head" >
                                <b-form-checkbox v-model="checkAll"  @click.native="selectAllVariantInPage()">  </b-form-checkbox> 
                            </template> 
                            <template slot="actions" slot-scope="row"> 
                                <b-form-checkbox
                                    v-model="allVariantChoosen" 
                                    :value="row.item.id" @change="addRuleVariantCheckbox(row.item.id,row.item.product_id,row.item.title,$event)"> 
                                </b-form-checkbox >
                            </template>
                            <template slot="title" slot-scope="row"> <a  @click="addRuleVariant(row.item.id,row.item.product_id,row.item.title)">{{row.value}}</a> </template>
                            <template slot="total_rules" slot-scope="row">    
                                {{row.value}}
                            </template>
                            <template slot="action" slot-scope="row">  
                                <div v-if="row.item.hasRule == true">
                                    <a  class="btn btn-info" @click.stop="editRuleVariant(row.item.id,row.item.title)" style="margin-bottom: 5px"> <i class="fa fa-pencil" aria-hidden="true"></i>    </a>
                                    <a class="btn btn-danger" @click.stop="deleteRuleVariant(row.item.id)" style="margin-bottom: 5px">  <i class="fa fa-trash-o" aria-hidden="true"></i>  </a> 
                                </div>
                                <div v-else>
                                    <a class="btn btn-info" size="xs" @click.stop="addRuleVariant(row.item.id,row.item.product_id,row.item.title)">
                                        Add rule
                                    </a>
                                </div>
                            </template> 
                        </b-table>   
                        <b-row v-if="items.length != 0">
                            <b-col md="9" class="my-1 col-8">
                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                            </b-col>
                            <b-col md="3" class="my-1 col-4">
                                <b-form-group horizontal label="" class="mb-0">
                                    <b-form-select :options="pageOptions" v-model="perPage" />
                                </b-form-group>
                            </b-col>
                        </b-row> 
                        <div v-if="showloadding == false && items.length == 0" style="text-align: center;padding-top: 10%;">
                            <img src="admin/version1/images/edit-property.png" style="width: 10%">
                            <p style="color:#353232; margin-top : 1rem; "><i class="fa fa-exclamation-circle"></i> Please choose a product by clicking in product title to add rule for variant</p>
                        </div> 
                    </div>
                </b-col>
                <b-col md="7"  style="" v-if="allVariantChoosen.length != 0">
                    <div class="card">
                        <div class="titleVariantChoosen" v-if="titleVariantChoose != null">
                            Add/Edit for variant: <b>{{titleVariantChoose}} </b>
                        </div>
                        <div class="product_rule card wrapperVariantRule"> 
                            <div class=" ">  
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Start date </label>
                                        <input type="date" class="form-control"  v-model="start_date" placeholder="Start date">
                                    </div>
                                    <div class="col-md-6">
                                        <label>End date</label>
                                        <input type="date" class="form-control" v-model="end_date" placeholder="End date">
                                    </div>
                                </div>
                                <div class="row" v-for="(group,indexGroups) of groups" :key="indexGroups">
                                    <div class="col-md-5">
                                        <label for="">Minimum Qty</label>
                                        <input type="number" min="1" v-model="group.number" :class="'defaultNumber_'+indexGroups" class="form-control ot-input-quantity" placeholder="Quantity">
                                    </div>
                                    <div class="col-md-5">
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
                                    </div>
                                    <div class="col-md-2">
                                        <a class="btn btn-delete deleteTies" style="margin-top: 31px;" @click="otTrashOffer(indexGroups)">  <i class="fa fa-trash-o"></i>   </a> 
                                    </div> 
                                </div>   
                                <div class="row"> 
                                    <div class="col-md-6"><a class="addTies btn-primary text-center" @click="otAddOffer">Add line</a></div> 
                                    <div class="col-md-6">
                                        <button class="btn btn-default"  style="float:left;margin-top: 2%;"  @click="saveRuleForVariant()" v-if="ruleForCustomerTag.length == 0" >   Save</button>
                                    </div> 
                                </div>
                                <div class="listCustomerTag" v-for="(element,index) in ruleForCustomerTag" :key="index" >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p> </p>
                                            <h5 class="titleAddRuleForCustomerTag"> Add new rule for customer tag{{": "+element.tag}} </h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div  class="col-md-5">
                                            <label>Enter Customer Tag</label>
                                            <input type="text" v-model="element.tag" class="form-control" :class="'tag_'+index" >
                                            <small>Please enter a comma-separated list of tags</small>
                                        </div>  
                                        <div  class="col-md-5">
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
                                        </div>
                                        <div class="col-md-2">
                                            <a class="btn btn-delete deleteTies"  style="margin-top: 31px;" @click="otTrashOfferCustomerTag(index)">  <i class="fa fa-trash-o"></i>   </a> 
                                        </div>
                                    </div> 
                                </div>
                                <div class="row" style="margin-top:10px;">
                                   
                                        <div class="col-md-6">
                                            <a class="btn btn-default btnAddCustomerTag" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                                        </div>   
                                        <div class="col-md-6">
                                            <button class="btn btn-default"  style="float:left;"  @click="saveRuleForVariant()" v-if="ruleForCustomerTag.length != 0" > Save</button>
                                        </div>
                                   
                                </div> 
                                <!-- <table class="table table-hover">   
                                    <tbody>
                                        <tr> 
                                            <td style="width:40%;">
                                                <label>Start date </label>
                                                <input type="date" class="form-control"  v-model="start_date" placeholder="Start date">
                                            </td> 
                                            <td style="width:40%;">
                                                <label>End date</label>
                                                <input type="date" class="form-control" v-model="end_date" placeholder="End date">
                                            </td>   
                                            <td  style="width:20%;"></td> 
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
                                                <a class="btn btn-delete deleteTies" style="margin-top: 31px;" @click="otTrashOffer(indexGroups)">  <i class="fa fa-trash-o"></i>   </a> 
                                            </td>
                                        </tr>  
                                        <tr> 
                                            <td  style="width:40%;"><a class="addTies btn-primary text-center" @click="otAddOffer">Add line</a></td> 
                                            <td  colspan="2">
                                                <button class="btn btn-default"  style="float:left;margin-top: 2%;"  @click="saveRuleForVariant()" v-if="idVariantEdit == null  && ruleForCustomerTag.length == 0" >   Save</button>
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
                                                <a class="btn btn-delete deleteTies"  style="margin-top: 31px;" @click="otTrashOfferCustomerTag(index)">  <i class="fa fa-trash-o"></i>   </a> 
                                            </td>
                                        </tr> 
                                    </tbody>
                                    <tfoot>
                                        <tr> 
                                            <td colspan="2">
                                                <a class="btn btn-default" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                                            </td>   
                                            <td>
                                                <button class="btn btn-default"  style="float:left;"  @click="saveRuleForVariant()" v-if="ruleForCustomerTag.length != 0" > Save</button>
                                            </td>
                                        </tr>
                                    </tfoot> 
                                </table>  -->
                            </div>
                        </div>  
                    </div> 
                </b-col> 
            </b-row>
        </b-container> 
    </div>
</template>
<script>  
    module.exports = {
        // bien truyen tu component cha
        props: ["allProducts","fillterProduct","titleProductFilter"],
        data: function() {
            return { 
                items:[],
                currentPage: 1, 
                perPage: 10,
                showloadding:false, 
                pageOptions: [5,10, 20,50,100],  
                sortBy: null,
                titleVariantChoose: null,
                sortDesc: false, 
                checkAll:false,
                sortDirection: 'asc',
                totalRows:null,
                showAddRuleForvariant:false,
                productFillter:null,
                filter: null,
                min:null,
                max:null,
                start_date:null,
                end_date:null,
                groups: [{price: null, number: null, discountType: 'percent'}], 
                options: [  
                    { text: '%', value: 'percent' },
                    { text: window.money_format, value: 'price' },
                ],
                status: 'not_accepted',  
                fields: [
                    { key: 'actions', label: ``,'class': 'text-center selectVariants' },
                    { key: 'title', label: 'Variant', sortable: true, sortDirection: 'desc' },
                    { key: 'total_rules', label: 'Rule(s)',   'class': 'text-center' },
                    { key: 'action', label: 'Actions','class': 'text-center actions' }
                ],
                idVariantEditLimit: null, 
                variantsHasRule:[],
                allVariantChoosen:[],
                idVariantEdit:null, 
                allVariants:[],
                resultFilterItems:[],
                drafCheck:[],
                fillterProductOld: null,
                selectProduct: [],
                ruleForCustomerTag:[],
                showModalCreateRuleForVariant:false,
            };
        },
        
        mounted: function() {
            let self = this   
            var interval_obj = setInterval(function(){  
                if(self.fillterProduct != null && self.fillterProductOld != self.fillterProduct){
                    self.fillterProductOld = self.fillterProduct
                    self.getvariantByProductID(); 
                }else if(self.fillterProduct == false){
                    self.allVariants = []
                    self.items = []
                    self.showloadding = false; 
                }
            }, 1000);
        },
        methods : { 
                validBeforeAddRuleForCustomerTag:function(){
                    var result = true;
                    var self = this;
                    $(".form-control").removeClass("error");
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
                    return result;
                },
                otAddRuleForCustomerTag:function(){
                    let self = this;
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
                changeFillterProduct:function(){
                    let self = this  
                    if(self.selectProduct){
                        self.fillterProductOld = self.selectProduct.id
                        self.getvariantByProductID();
                    } 
                }, 
                // saveLimitForVariant: function(){
                //     var self = this 
                //     let valid = self.validBeforeSaveLimit();
                //     if(valid == true){
                //         $.ajax({
                //             url: 'services_v2.php',
                //             type: 'POST',
                //             data: {
                //                     action:'saveLimitForVariant',
                //                     shop:shop,
                //                     allVariantChoosen:self.allVariantChoosen,
                //                     min:self.min,
                //                     max:self.max
                //                 },
                //             dataType: 'json'
                //         }).done(function (result) {
                //             self.min= null;
                //             self.max= null;
                //             ShopifyApp.flashNotice("Save rule successfully!"); 
                //         }).fail((error) => {
                //             ShopifyApp.flashError("Save limit for variant error!");  
                //         });
                //     }else{
                //         ShopifyApp.flashError(valid);  
                //     }
                    
                // },
                // getLimitVariant:function(id){
                //     let self = this 
                //     self.allVariantChoosen.push(id)
                //     $.ajax({
                //         url: 'services_v2.php',
                //         type: 'GET',
                //         data: {action: 'getLimitByVariantID',shop:shop,idVariant :id},
                //         dataType: 'json'
                //     }).done(function (result) {
                //         if(result.length != 0){
                //             self.idVariantEditLimit = id
                //             self.min = result.min
                //             self.max = result.max 
                //         }else{
                //             self.idVariantEditLimit = null
                //         }
                        
                //     }).fail((error) => {
                //         ShopifyApp.flashNotice("Get limit by product id error!");  
                //     }); 
                // },
                // updateLimitVariant:function(){
                //     let self = this 
                //     $.ajax({
                //         url: 'services_v2.php',
                //         type: 'GET',
                //         data: {
                //             action: 'updateLimitVariant',
                //             shop:shop,
                //             idVariant :self.idVariantEditLimit,
                //             min: self.min,
                //             max: self.max,
                //         },
                //         dataType: 'json'
                //     }).done(function (result) {
                //         ShopifyApp.flashNotice("Update limit successfully!"); 
                //         self.idVariantEditLimit = null
                //     }).fail((error) => {
                //         ShopifyApp.flashError("Update rule for variant error!");  
                //     }); 
                // },
                // validBeforeSaveLimit(){
                //     var self = this  
                //     var valid = true;
                //     if(self.allVariantChoosen.length == 0){
                //         valid = "Please choose variant before save limit!";
                //     }
                //     if(self.min == null || self.max == null || parseInt(self.min) > parseInt(self.max) ){
                //         valid = "Min limit and Max limit not empty and max larger min"
                //     }
                //     return valid
                // },
                otAddOffer: function () {
                     let self = this;
                    if(self.validBeforeAddOffer() == true){
                        self.groups.push({price: null, number: null, discountType: 'percent'});
                    }else{
                        ShopifyApp.flashError("Please enter all fields before continue !");  
                    } 
                }, 
                validBeforeAddOffer:function(){
                    var result = true;
                    var self = this;
                    $(".form-control").removeClass("error");
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
                otTrashOffer: function (index) {
                    let self = this 
                    ShopifyApp.Modal.confirm({ 
                        title: "Delete rule",
                        message: "The campaign will be stop immediately. Do you want to delete rule ?",
                        okButton: "Confirm Remove",
                        cancelButton: "Cancel",
                        style: "danger"
                    }, function(res){
                        if(res){
                        if(self.groups.length == 1){
                                self.groups = [{price: null, number: null, discountType: 'percent'}]
                            }else{
                                self.groups.splice(index, 1);
                            }
                        } 
                    }); 
                }, 
                nameWithLang ({title}) {
                    return `${title}`
                },
                onFiltered (filteredItems) {
                    let self = this
                    // Trigger pagination to update the number of buttons/pages due to filtering
                    self.totalRows = filteredItems.length
                    self.resultFilterItems = filteredItems
                    self.currentPage = 1
                }, 
                selectAllVariantInPage(){
                    var self = this
                    self.checkAll = !self.checkAll 
                    self.drafCheck = []
                    self.allVariantChoosen = []
                    let start = (self.currentPage -1) * self.perPage
                    
                    if(self.checkAll == true){  
                        for(var i = start; i <  (start+self.perPage) ; i++){ 
                            if(self.items.length > i){
                                self.allVariantChoosen.push(self.items[i]['id']);
                                self.drafCheck.push(self.items[i]['id']);
                            } 
                        }
                    }else{ 
                        self.allVariantChoosen = []
                    }
                    
                    self.titleVariantChoose = self.allVariantChoosen.length + " variants";
                },
                selectAllVariant(){
                    var self = this
                    self.checkAll = !self.checkAll 
                    self.drafCheck = [] 
                    if(self.checkAll == true){ 
                        for(var i=0; i < self.items.length ; i++){
                            self.allVariantChoosen.push(self.items[i].id);
                            self.drafCheck.push(self.items[i]['id']);
                        }
                    }else{ 
                        self.allVariantChoosen = []
                    }
                    self.titleVariantChoose = self.allVariantChoosen.length + " variants";
                },
                saveRuleForVariant: function(){
                    var self = this 
                    let valid = self.validBeforeSave();
                    if(valid == true){
                        $.ajax({
                            url: 'services_v2.php',
                            type: 'POST',
                            data: {
                                action:'saveRuleForVariant',
                                shop:shop,
                                groups:self.groups,
                                allVariantChoosen:self.allVariantChoosen,
                                start_date:self.start_date,
                                end_date:self.end_date,
                                ruleForCustomerTag:self.ruleForCustomerTag,
                            },
                            dataType: 'json'
                        }).done(function (result) {
                            ShopifyApp.flashNotice("Save rule successfully!");  
                            self.status =  'not_accepted'
                            self.getvariantByProductID(); 
                            self.ruleForCustomerTag = [];
                            self.groups = [];
                            self.allVariantChoosen = [];
                            self.checkAll = false;
                            self.showModalCreateRuleForVariant = false;
                        }).fail((error) => {
                            console.log("fail")
                        });
                    }else{
                        ShopifyApp.flashError(valid);  
                    }
                    
                },
                validBeforeSave(){
                    var self = this  
                    var valid = true;
                    if(self.allVariantChoosen.length == 0){
                        valid = "Please choose variant before save rule!";
                    }  
                    if(self.groups.length == 0){
                        valid = "Please enter rule(s) value before save rule!";
                    }
                    let checkDuplicate = 0
                    for(let i =0; i < self.groups.length; i++){
                        if(self.groups[i]['price'] == null || self.groups[i]['number'] == null){
                            valid = "Please check and enter the valid Quantity and Value of each Price Tier/Level!";
                        }
                            if(checkDuplicate == self.groups[i]['number']){
                            valid = "Duplicate number in rule !";
                        }else{
                            checkDuplicate = self.groups[i]['number']
                        }
                    }
                    if(self.start_date > self.end_date){
                        valid = "Please enter end date < start date !";
                    }
                    return valid
                },
                editRuleVariant:function(id,titleVariantChoose){
                    let self = this 
                    self.showModalCreateRuleForVariant = true
                    self.showAddRuleForvariant = true;
                    self.drafCheck= []
                    self.drafCheck.push(id)
                    self.allVariantChoosen = []
                    self.allVariantChoosen.push(id)
                    self.checkAll = false
                    self.titleVariantChoose = titleVariantChoose
                    $.ajax({
                        url: 'services_v2.php',
                        type: 'GET',
                        data: {action: 'getRuleByVariantID',shop:shop,idVariant :id},
                        dataType: 'json'
                    }).done(function (result) {
                        self.groups = result.content_rule
                        self.idVariantEdit = result.variant_id  
                        self.ruleForCustomerTag = result.ruleForCustomerTag  
                        
                    }).fail((error) => {
                        ShopifyApp.flashError("Get rule by variant id error!");  
                    }); 
                }, 
                updateRuleVariant:function(){
                    let self = this 
                    let valid = self.validBeforeUpdate();
                    if(valid == true){
                        $.ajax({
                            url: 'services_v2.php',
                            type: 'GET',
                            data: {action: 'updateRuleVariant',shop:shop,idVariant :self.idVariantEdit,groups:self.groups,start_date:self.start_date,end_date:self.end_date},
                            dataType: 'json'
                        }).done(function (result) {
                            ShopifyApp.flashNotice("Update rule successfully!"); 
                            self.status =  'not_accepted'
                            self.getvariantByProductID();
                            self.allVariantChoosen = []
                            self.idVariantEdit = null
                            self.showModalCreateRuleForVariant = false
                        }).fail((error) => {
                            ShopifyApp.flashError("Update rule for variant error!"); 
                        }); 
                    }else{
                        ShopifyApp.flashError(valid);  
                    }
                },
                deleteRuleVariant:function(id){
                    let self = this 
                    ShopifyApp.Modal.confirm({ 
                        title: "Delete rule",
                        message: "Do you want to delete rule ?",
                        okButton: "Delete",
                        cancelButton: "No",
                        style: "danger"
                    }, function(res){
                        if(res){
                            $.ajax({
                                url: 'services_v2.php',
                                type: 'GET',
                                data: {action: 'deleteRuleVariant',shop:shop,idVariant :id},
                                dataType: 'json'
                            }).done(function (result) {
                                ShopifyApp.flashNotice("Delete rule successfully!"); 
                                self.status = 'not_accepted'
                                self.getvariantByProductID();
                            }).fail((error) => {
                                ShopifyApp.flashError("Delete rule error!");  
                            });
                        } 
                    }); 
                },
                addRuleVariantCheckbox:function(id,idProduct,titleVariantChoose,$event){
                    let self = this;   
                    let checkExisProduct = self.drafCheck.indexOf(id);
                    if($event != null){
                        self.drafCheck.push($event); 
                        self.allVariantChoosen.push($event);
                        
                    } else{
                        self.checkAll = false;
                        self.drafCheck.splice(checkExisProduct,1)
                        for(let i=0; i< self.items.length; i++){
                            if(self.items[i]['id'] == self.drafCheck[0]){
                                self.titleVariantChoose = self.items[i]['title'] 
                            }
                        }
                        self.allVariantChoosen.splice(checkExisProduct,1) 
                    }
                    for(let i = 0;i < self.items.length;i++){
                        if(self.drafCheck.indexOf(self.items[i]['id']) != -1 || $event == self.items[i]['id']){ 
                            self.items[i]['_rowVariant'] = 'success'
                        }else{
                            self.items[i]['_rowVariant'] = ''
                        }
                    } 
                    if(self.drafCheck.length > 1){
                        self.titleVariantChoose = self.drafCheck.length + " variants"; 
                    }   
                },
                addRuleVariant:function(id,idProduct,titleVariantChoose){
                    var self = this 
                    self.allVariantChoosen = []
                    self.showAddRuleForvariant = true;
                    self.drafCheck = []
                    let checkExisProduct = self.drafCheck.indexOf(id);
                    if(checkExisProduct == -1){
                        self.drafCheck.push(id); 
                        self.allVariantChoosen.push(id);
                    }else{
                        self.drafCheck.splice(checkExisProduct,1) 
                        self.allVariantChoosen.splice(checkExisProduct,1)
                    }
                
                    self.idVariantEdit = null
                    self.checkAll = false;
                    if(self.drafCheck.length == 1){
                        self.titleVariantChoose = titleVariantChoose 
                    } else if(self.drafCheck.length > 1){
                        self.titleVariantChoose = self.allVariantChoosen.length + " variants"; 
                    } 
                    // self.getLimitVariant(id); 
                    self.titleVariantChoose = titleVariantChoose
                    self.showModalCreateRuleForVariant = true; 
                }, 
                getvariantByProductID:function(){
                    let self = this
                    self.variantsHasRule = []
                    self.items = []
                    self.showloadding = true;
                    self.allVariants = []
                    $.ajax({
                        url: 'services_v2.php',
                        type: 'GET',
                        data: {action: 'getVariantByProductID',shop:shop,idProduct :self.fillterProductOld},
                        dataType: 'json'
                    }).done(function (result) {
                        self.items = result.variants
                        self.totalRows = self.items.length
                        self.allVariants = result.variants
                        for(let i = 0; i < result.variants.length ;i++){ 
                            if(result.variants[i]['hasRule'] === true){  
                                self.variantsHasRule.push(result.variants[i]);
                            } 
                        }  
                        self.showloadding = false;
                    }).fail((error) => {
                        
                    }); 
                },
                fillterHasRule:function(){
                    let self = this   
                    if(self.status === 'accepted'){
                        self.items = self.allVariants 
                    }else{
                        self.items = self.variantsHasRule
                    }
                }, 
                validBeforeUpdate(){
                    var self = this  
                    var valid = true; 
                    if(self.groups.length == 0){
                        valid = "Please enter rule(s) value before save rule!";
                    }
                    let checkDuplicate = 0
                    for(let i =0; i < self.groups.length; i++){
                        if(self.groups[i]['price'] == null || self.groups[i]['number'] == null){
                            valid = "Please check and enter the valid Quantity and Value of each Price Tier/Level.  !";
                        }
                        if(checkDuplicate == self.groups[i]['number']){
                            valid = "Duplicate number in rule !";
                        }else{
                            checkDuplicate = self.groups[i]['number']
                        }
                    }
                    if(self.start_date > self.end_date){
                        valid = "Please enter end date < start date !";
                    }
                    return valid
                }
                
        }, 
        components: {
                Multiselect: window.VueMultiselect.default,
        }
    };
</script>
<style  scoped src="admin/version1/styles/variant.css?v=13">
</style>
 <style lang="css">
.wrapperVariantRule table{
    overflow: hidden;
    display: block;
}
.wrapperVariantRule table tr td{
   width: 30%;
}
.wrapperVariantRule table tbody{
   display: block;
}
.listCustomerTag{
    font-size: 13px;
}
.wrapperVariantRule .btnAddCustomerTag{
 font-size: 11px;
}
.wrapperVariantRule label{
    font-size: 12px;
}

 </style>
