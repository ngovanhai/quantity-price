<template>
    <div> 
        <b-container fluid  class="bg_wrapper_collections "> 
            <b-row>
                <b-col md="7" > 
                    <div class="card">
                        <b-row>
                            <!-- START FILLTER -->
                            <b-col md="5" class="my-1">
                                <b-form-group horizontal label="Has rule" id="hasRule" class="mb-0">
                                    <b-form-checkbox
                                        v-model="statusFillterHasRule"
                                        value="1" @change="fillterHasRule()" 
                                        unchecked-value="0"> 
                                    </b-form-checkbox>
                                </b-form-group>
                            </b-col>
                            <b-col md="7" class="my-1">
                                <b-form-input v-model="filter" placeholder="Search collection ..." ></b-form-input>
                            </b-col>  
                            <!-- END FILLTER -->
                        </b-row> 
                        <div class="loading" v-if="items.length == 0  && statusFillterHasRule != '1'">
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
                                :filter="filter"
                                :sort-by.sync="sortBy"
                                :sort-desc.sync="sortDesc"
                                :sort-direction="sortDirection" 
                                :striped="true"
                                @filtered="onFiltered" 
                        >  
                            <!-- <template slot="HEAD_actions" slot-scope="head">
                                <a @click="selectAllCollection()" class="btn selectAll">Select All</a>
                            </template>  -->
                            <!-- <template slot="actions" slot-scope="row"> 
                                <b-form-checkbox
                                    v-model="collectionChoosen"
                                    :value="row.item.id"> 
                                </b-form-checkbox>
                            </template>  -->
                            <template slot="title" slot-scope="row"> 
                                <a style="cursor: pointer;" class="title_product">
                                    {{row.value}}
                                </a>
                            </template> 
                            <template slot="countProduct" slot-scope="row"> {{row.value}} </template>
                            <template slot="total_rules" slot-scope="row"> {{row.value}} </template>
                            <template slot="action" slot-scope="row">  
                                <div v-if="row.item.hasrule == true">
                                    <a class="btn btn-info" @click.stop="editRuleCollection(row.item.id,row.item.title)">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> 
                                    </a>
                                    <a class="btn btn-danger" @click.stop="deleteRuleCollection(row.item.id)">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>   
                                    </a> 
                                </div>
                                <div v-else>
                                    <a class="btn btn-info" size="xs" @click.stop="addRuleCollection(row.item.id,row.item.title)">
                                        Add rule
                                    </a>
                                </div>
                            </template> 
                        </b-table>  
                        <b-row>
                            <!-- START PAGINATION -->
                            <b-col md="9" class="my-1 col-8">
                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                            </b-col>
                            <b-col md="3" class="my-1 col-4">
                                <b-form-group horizontal label="" class="mb-0">
                                    <b-form-select :options="pageOptions" v-model="perPage" />
                                </b-form-group>
                            </b-col>
                            <!-- END PAGINATION -->
                        </b-row>
                    </div>  
                </b-col>
                <b-col md="4"> 
                    <div class="card">
                        <div class="imagesIntroduce"  style="margin-top:15px;">
                            <b><i class="fa fa-info-circle" aria-hidden="true"></i> Introduce:</b><br/>
                            If you create rule for collection then all products in it will be discounted according to that. 
                            <b-col md="12"  class="my-1">
                                <!-- ADD RULE FOR ENTIRE STORE -->
                                <b-btn @click="showCreateEntireStore = true" variant="info" style="height: calc(2.25rem + 2px); width: 100%;font-size:13px !important;">Add rule for entire store</b-btn>
                                <b-modal v-model="showCreateEntireStore" title="Add rule for entire store">
                                    
                                    <global-rule></global-rule>
                                    <div slot="modal-footer" class="w-100"> 
            
                                    </div>
                                </b-modal>
                            </b-col>
                        </div>
                    </div> 
                </b-col>
            </b-row>

            <!-- CREATE RULE  --> 
            <b-modal v-model="showModalCreateRuleForCollection" :title="'Rule for collection: ' + titleCollectionChoose">
                <b-container fluid>
                    <div class="table-responsive table_modal">  
                        <div class="collection_rule container"> 
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
                                            <button class="btn btn-default"  style="float:left;"  @click="saveRuleForCollection()"  v-if="idCollectionEdit == null && ruleForCustomerTag.length == 0"> Save</button>
                                            <button class="btn btn-default"  style="float:left;"  @click="updateRuleCollection()" v-if="idCollectionEdit != null && ruleForCustomerTag.length == 0"> Update</button>                              
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
                                </tbody>
                                <tfoot> </tfoot>
                            </table>
                        </div> 
                    </div> 

                    <!-- DESCRIPTION RULE THIS COLLECTION APPLIED -->
                    <!-- <div class="text-center intro_collection" v-show="showIntro == true"  v-if="idCollectionEdit == null && noticeBeforeAddRule != null">
                        <b> <i class="fa fa-info-circle" aria-hidden="true"></i>   Note:</b> Now,this collection is being applied global rule:
                        <div v-for="(group,indexGroups) of noticeBeforeAddRule" :key="indexGroups">
                            <div v-if="group.discountType == 'percent'"><span> Buy {{group.number}} </span> Discount <span> {{group.price}}%</div>
                            <div v-else><span>Buy {{group.number}} </span> Discount <span> €{{group.price}}</div>
                        </div> 
                    </div>   -->
                </b-container>
                <div slot="modal-footer" class="w-100"> 
                    <a class="btn btn-primary addTies" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                    <button class="btn btn-default"  style="float:right;"  @click="saveRuleForCollection()"  v-if="ruleForCustomerTag.length != 0"> Save</button>
                </div>
             </b-modal>
            <!-- END CREATE RULE   -->
        </b-container>
    </div>
</template>
<script> 
    module.exports = { 
        props: [],
        data: function() {
            return { 
                /*
                    statusFillterHasRule  : fillter collection has rule (0: collection has not rule, 1: collection has rule)
                    showCreateEntireStore : custum show or hide modal create rule for entire store
                    fields,sortBy,sortDesc: custom column table collection list
                    start_date, endate    : manage schedule tier (Y,m,d) 
                    showIntro             : show/hide description rule this collection applied
                    filter                : key search title collection in data table
                    checkAll              : select all collection to add rule for all collection in per page
                    groups                : ties rule in content rule for collection
                    options               : type for each rule (% : percent or $ : price)
                    items                 : collection list to show data table (fillted has rule)
                    allCollection         : collection list get from shopify 
                    collectionChoosen     : collection selected to add or edit rule for multi collection
                    idCollectionEdit      : collection chosen edit 
                    collectionHasRule     : all collection has rule 
                    collectionNotRule     : all collection has not rule
                    showModalCreateRuleForCollection : show/hide modal create rule for collection
                    noticeBeforeAddRule   : text notice rule before edit rule for collection (rule of collection chosen applied)
                    currentPage, perPage, pageOptions custom paginiton data table
                */
                statusFillterHasRule    : '0', 
                showCreateEntireStore   : false, 
                titleCollectionChoose   : null,
                noticeBeforeAddRule     : null,
                showModalCreateRuleForCollection :false,   
                currentPage     : 1, 
                perPage         : 5,
                totalRows       : null, 
                sortDesc        : false,
                sortDirection   : 'asc',
                sortBy          : null,
                filter          : null,  
                start_date      : null,
                end_date        : null, 
                showIntro       : false,  
                checkAll        : false, 
                items           : [],
                allCollection   : [], 
                collectionChoosen: [], 
                idCollectionEdit : null,
                collectionHasRule: [], 
                collectionNotRule: [], 
                ruleForCustomerTag:[],
                fields: [
                    { key: 'title',   label: 'Collection', sortable: true,'class': 'titleColletion', sortDirection: 'desc' },
                    // { key: 'countProduct', label: 'Products',  'class': 'text-center countProduct' }, 
                    { key: 'total_rules',  label: 'Rule(s)',   'class': 'text-center ruleCollection' },
                    { key: 'action', label: 'Actions','class': 'text-center actions' }
                ],
                options: [  
                    { text: '%', value: 'percent' },
                    { text: window.money_format, value: 'price' },
                ],
                groups          : [{price: null, number: null, discountType: 'percent'}], 
                pageOptions     : [ 5, 10, 20,50 ],
            };
        }, 
        mounted: function() {
            let self = this;  
         self.getCountCollection("custom");
        },
        methods : {
             getCountCollection: function(type){ 
                var self = this;
                var page1 = 0;
                var countCollection;
                var index1 = 1;
                var since_idcollection = 0;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action:'getCountCollection',shop:shop,type:type},
                    dataType: 'json'
                }).done(function (result) { 
                    countCollection = result;
                    page1 = Math.ceil(countCollection/250);
                    initGetAllCollection();
                })   
                function initGetAllCollection(){ 
                    if(index1 <= page1){
                        $.ajax({
                            url : 'services_v2.php',
                            type: 'GET',
                            data: {action:'getCollectionPerPage',since_id:since_idcollection,shop:shop,type:type},
                            dataType: 'json'
                        }).done(function (response) {     
                            console.log("response.length",response.length)
                            if(response.length != 0){ 
                                self.allCollection =  self.allCollection.concat(response); 
                                self.items = self.items.concat(response) ; 
                                self.allCollection.sort(); 
                                if(typeof response[response.length - 1]['id'] != "undefined"){
                                    index1 = index1+1;
                                    since_idcollection = response[response.length - 1]['id']; 
                                    initGetAllCollection(); 
                                } 
                                if(type == "smart" && response.length < 250){
                                    self.collectionHasRule = [];
                                    self.collectionNotRule = [];
                                    for(let i = 0; i < self.allCollection.length ;i++){
                                        if(self.allCollection[i]['hasrule'] === true){
                                            self.collectionHasRule.push(self.allCollection[i]);
                                        }else{
                                            self.collectionNotRule.push(self.allCollection[i]);
                                        }
                                    } 
                                    
                                    self.totalRows = self.items.length;
                                    self.statusFillterHasRule = '0' ;
                                } 
                            }
                            if(type == "custom" && response.length < 250) {
                                self.getCountCollection("smart");
                            }
                         })  
                    } 
                } 
            },  
             otAddOffer: function () {
                let self = this;
                console.log(self.validBeforeAddOffer())
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
                let self = this;  
                ShopifyApp.Modal.confirm({ 
                    title: "Delete rule",
                    message: "Do you want to delete rule ?",
                    okButton: "Confirm Remove",
                    cancelButton: "Cancel",
                    style: "danger"
                }, function(res){
                    if(res){
                    if(self.groups.length == 1){
                            self.groups = [{price: null, number: null, discountType: 'percent'}];
                        }else{
                            self.groups.splice(index, 1);
                        }
                    } 
                }); 
            },   
            fillterHasRule:function(){
                // fillter all collection has rule
                let self = this  ;
                if(self.statusFillterHasRule === '1'){
                    self.items = self.allCollection ;
                }else{ 
                    self.items = self.collectionHasRule;
                }
            },
            getAllCollection: function(){
                // get all collection from shopify 
                let self = this;
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: {action:'getAllCollection',shop:shop},
                    dataType: 'json'
                }).done(function (response) { 
                    self.allCollection = response   
                    self.collectionHasRule = [];
                    self.collectionNotRule = [];
                    for(let i = 0; i < self.allCollection.length ;i++){
                        if(self.allCollection[i]['hasrule'] === true){
                            self.collectionHasRule.push(self.allCollection[i]);
                        }else{
                            self.collectionNotRule.push(self.allCollection[i]);
                        }
                    } 
                    self.items = self.allCollection ;
                    self.totalRows = self.items.length;
                    self.statusFillterHasRule = '0' ;
                })  
            },
            deleteRuleCollection:function(id){
                // delete rule collection by collection id
                let self = this ;
                ShopifyApp.Modal.confirm({ 
                    title   : "Do you want to delete rule ?",
                    message : "All product in this collection will don't apply this rule.",
                    okButton: "Delete",
                    cancelButton: "No",
                    style: "danger"
                }, function(res){
                    if(res){
                        $.ajax({
                            url  : 'services_v2.php',
                            type : 'GET',
                            data : {action: 'deleteRuleCollection',shop:shop,idCollection :id},
                            dataType: 'json'
                        }).done(function (result) { 
                            ShopifyApp.flashNotice("Delete offer success!");
                            self.statusFillterHasRule = '0';
                            self.getAllCollection();
                        }).fail((error) => {
                            ShopifyApp.flashError("Delete offer error!"); 
                        }); 
                    } 
                }); 
            },
            editRuleCollection:function(id,title){
                // get rule of collection edit 
                let self = this ;
                self.showModalCreateRuleForCollection = true;
                self.titleCollectionChoose = title;
                self.collectionChoosen = [];
                $.ajax({
                    url  : 'services_v2.php',
                    type : 'GET',
                    data : {action: 'getRuleByCollectionID',shop:shop,idCollection :id},
                    dataType: 'json'
                }).done(function (result) {
                    self.groups = result.content_rule;
                    self.idCollectionEdit = result.collection_id;
                    self.collectionChoosen.push(result.collection_id);
                    self.ruleForCustomerTag = result.ruleForCustomerTag;
                }).fail((error) => {
                    ShopifyApp.flashError("get by collection id error!"); 
                }); 
            },
            updateRuleCollection:function(){
                // update rule of collection edit
                let self = this ;
                let valid = self.validBeforeUpdate();
                if(valid == true){
                    $.ajax({
                        url: 'services_v2.php',
                        type: 'GET',
                        data: {action: 'updateRuleCollection',shop:shop,idCollection :self.idCollectionEdit,groups:self.groups,start_date:self.start_date,end_date:self.end_date},
                        dataType: 'json'
                    }).done(function (result) {
                        ShopifyApp.flashNotice("Update rule success!"); 
                        self.statusFillterHasRule =  '0';
                        self.getAllCollection();
                        self.idCollectionEdit = null;
                        self.showModalCreateRuleForCollection = false;
                    }).fail((error) => {
                        ShopifyApp.flashError("Update rule for collection error!"); 
                    }); 
                }else{
                    ShopifyApp.flashError(valid);  
                }
            },
            selectAllCollection(){
                // select all collection to add/edit rule for multi collection
                var self = this;
                self.checkAll = !self.checkAll;
                if(self.checkAll == true){
                    for(var i=0; i < self.allCollection.length ; i++){
                        self.collectionChoosen.push(self.allCollection[i].id);
                    }
                }else{
                    self.collectionChoosen = [];
                }
            }, 
            saveRuleForCollection: function(){
                var self = this ;
                let valid = self.validBeforeSave();
                if(valid == true){ 
                    $.ajax({
                        url  : 'services_v2.php',
                        type : 'GET',
                        data : {
                            action:'saveRuleForCollection',
                            shop:shop,groups:self.groups,
                            collectionChoosen:self.collectionChoosen,
                            start_date:self.start_date,
                            end_date:self.end_date,
                            ruleForCustomerTag:self.ruleForCustomerTag
                        },
                        dataType: 'json'
                    }).done(function (result) { 
                        ShopifyApp.flashNotice("Save rule for collection successfully!"); 
                        self.statusFillterHasRule =  '0';
                        self.getAllCollection();
                        self.items = self.allCollection ;
                        self.showModalCreateRuleForCollection = false;
                    }).fail((error) => {
                        ShopifyApp.flashError("Save rule for collection error!"); 
                    });
                }else{
                    ShopifyApp.flashError(valid);
                }
            },
            validBeforeSave(){
                // check valid rule for collection 
                var self  = this;  
                var valid = true;
                if(self.collectionChoosen.length == 0){
                    valid = "Please choose collection before save rule!";
                }  
                if(self.groups.length == 0){
                    valid = "Please enter rule(s) value before save rule!";
                }
                for(let i =0; i < self.groups.length; i++){
                    if(self.groups[i]['price'] == null || self.groups[i]['number'] == null){
                        valid = "Please check and enter the valid Quantity and Value of each Price Tier/Level!"; break;
                    }
                } 
                if(self.start_date > self.end_date){
                    valid = "Please enter end date < start date !";
                }
                return valid;
            },
            addRuleCollection:function(id,title){
                // add rule for collection d
                var self = this ;
                self.titleCollectionChoose = title;
                self.collectionChoosen = [];
                self.collectionChoosen.push(id);
                self.idCollectionEdit = null;
                self.showModalCreateRuleForCollection = true;
                $.ajax({
                    url  : 'services_v2.php',
                    type : 'GET',
                    data : {action: 'getRuleCollectionBeforeAdd',shop:shop,idCollection :id},
                    dataType: 'json'
                }).done(function (result) {
                    self.groups = [{price: null, number: null, discountType: 'percent'}];
                    self.noticeBeforeAddRule = result ;
                    if(result.length != 0){
                        self.showIntro = true;
                    } 
                }).fail((error) => { 
                    ShopifyApp.flashError("get by collection id error!"); 
                }); 
            }, 
            onFiltered (filteredItems) {
                let self = this;
                // Trigger pagination to update the number of buttons/pages due to filtering
                self.totalRows = filteredItems.length;
                self.currentPage = 1;
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
            validBeforeUpdate(){
                // validition before save/update rule for collection
                var self = this;  
                var valid = true; 
                if(self.groups.length == 0){
                    valid = "Please enter rule(s) value before save rule!";
                }
                for(let i =0; i < self.groups.length; i++){
                    if(self.groups[i]['price'] == null || self.groups[i]['number'] == null){
                        valid = "Please check and enter the valid Quantity and Value of each Price Tier/Level.  !";
                    }
                }
                if(self.start_date > self.end_date){
                    valid = "Please enter end date < start date !";
                }
                return valid;
            }
        },
        components: { 
            'global-rule': httpVueLoader(`admin/version1/components/global.vue?v=${window.version}`), 
        }
    };
</script>
<style  scoped src="admin/version1/styles/collection.css?v=15"> </style>
 
