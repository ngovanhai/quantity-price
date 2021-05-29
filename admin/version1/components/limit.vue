<template>
    <div> 
        <div class="info"> 
            <b><i class="fa fa-info-circle" aria-hidden="true"></i>  Rule for limit will applied for variant.</b><br/>
            "Bulk action" will support you add rule for collection, product, entire store.
            <br />
            <a @click="showAllRuleLimitFollowColleciton()" variant="primary" class="btn btn-primary" style="color:#fff;">Show all rule follow collection</a> 
        </div>
        <b-container fluid>   
            <b-row v-show="showListRuleFollow == true" style="margin-top:25px;">  
                    <b-col md="7">
                        <div class="card">
                             
                            <ul class="listRuleLimitFollow" v-if="listLimitFollowCollection.length > 0">
                                <li> <b-btn @click="showBulkAction=true" variant="primary">Bulk action</b-btn></li>
                                <li v-for="(ruleLimit,indexGroups) of listLimitFollowCollection" :key="indexGroups" class="row">
                                    <span class="col-md-4">{{ruleLimit.title_collection}}</span>
                                    <span class="col-md-4">Min:{{ruleLimit.min}} - Max:{{ruleLimit.max}} - Multiple:{{ruleLimit.multiple}}</span>
                                    <span  class="col-md-4"> 
                                        <a class="btn btn-info" @click.stop="showGroupCollection(ruleLimit)">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>  
                                        </a>
                                        <a class="btn btn-danger" @click.stop="deleteGroupCollection(ruleLimit.id_collection)">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i>  
                                        </a>
                                    </span>
                                </li>
                            </ul>
                            <div v-else>
                                <p>You have no rules for collection.</p>
                                <p>Add rule for collection by clicking in "Bulk action" button.</p>
                                <b-btn @click="showBulkAction=true" variant="primary">Bulk action</b-btn>
                            </div>
                        </div> 
                    </b-col> 
                    <b-col md="5" v-show="collectionsChoosen.length != 0"> 
                        <div class="card"> 
                            <h4 class="titleCard">Limit Purchase Default</h4>
                            <div class=""> 
                                <table class="table table-hover product_rule text-center">  
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label>Start/End Date</label>
                                                <input type="text" name="daterange1"  autocomplete="off" class="form-control daterange1" value="" placeholder="Choose start date and end date" /> 
                                            </td> 
                                            <td>
                                                <label>Multiple</label>
                                                <input type="number" min="1"   v-model="multiple" class="form-control" placeholder="Multiple">
                                            </td>  
                                        </tr>
                                    </tbody> 
                                    <tbody>
                                        <tr>
                                            <td>
                                                <label>Min limit purchase</label>
                                                <input type="number" min="1"   v-model="min" class="form-control" placeholder="Min limit purchase">
                                            </td>
                                            <td>
                                                <label>Max limit purchase</label>
                                                <input type="number" min="1"   v-model="max" class="form-control" placeholder="Max limit purchase">
                                            </td> 
                                        </tr>
                                    </tbody> 
                                    <tfoot>
                                        <td> 
                                            <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="listRuleLimitCustomerTag.length == 0" >  Add limit for customer tag</button>
                                        </td>
                                        <td>
                                            <button class="btn btn-default btn-save-product"   @click="saveLimitForBulkAction()" ><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</button> 
                                        </td>
                                    </tfoot> 
                                </table> 
                            </div>
                            <div  v-if="listRuleLimitCustomerTag.length > 0">
                                <div class="card cardLimit listRuleCustomertag" v-for="(item, index) in listRuleLimitCustomerTag" :key="index">
                                    <h6 class="titleCard">Limit Purchase For Cusstomer Tag </h6>
                                    <div class=""> 
                                        <table class="table table-hover product_rule">  
                                            <tbody>
                                                <tr> 
                                                    <td colspan="2">
                                                        <label>Enter Customer Tag</label>
                                                        <input type="text"   v-model="item.tag" class="form-control" :class="'tag_'+index" >
                                                        <small>Please enter a comma-separated list of tags</small>
                                                    </td>  
                                                </tr> 
                                                <tr>
                                                    <td>
                                                        <label>Min limit purchase</label>
                                                        <input type="number" min="1"  v-model="item.min" class="form-control minLimit" :class="'min_'+index" placeholder="Min limit purchase">
                                                        <small>If you set 0, this means unlimited</small>
                                                    </td>
                                                    <td>
                                                        <label>Max limit purchase</label>
                                                        <input type="number" min="1"  v-model="item.max" class="form-control maxLimit" :class="'max_'+index" placeholder="Max limit purchase">
                                                        <small>If you set 0, this means unlimited</small>
                                                    </td>
                                                    
                                                </tr>
                                            </tbody> 
                                            <tfoot>
                                                <td> 
                                                    <button class="btn outline-secondary "   @click="removeRuleLimitForCustomerTag(index)" >  Cancel</button>
                                                    <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="index == listRuleLimitCustomerTag.length - 1" >  Add limit for customer tag</button>
                                                
                                                </td>
                                                <td>
                                                    <button class="btn btn-default btn-save-product" @click="saveLimitForBulkAction()" v-if="index == listRuleLimitCustomerTag.length - 1" >  Save</button> 
                                                </td>
                                            </tfoot> 
                                        </table> 
                                    </div>
                                </div>
                            </div>
                        </div>  
                    </b-col> 
            </b-row>
            <b-row v-show="showListRuleFollow == false"> 
                <b-col md="7"> 
                    <div class="card"> 
                        <p><b><i class="fa fa-info-circle" aria-hidden="true"></i> Introduce:</b><br/>
                        If you create rule for collection then all products in that will discount according to that.</p>
                        <!-- START FILLTER VARIANT -->
                        <b-row>
                            <b-col md="3" class="my-1">
                                <b-form-group horizontal label="Has limit(s)" id="hasRule" class="mb-0">
                                    <b-form-checkbox
                                        v-model="status"
                                        value="1" @change="fillterHasRule()" 
                                        unchecked-value="0">
                                    </b-form-checkbox>
                                </b-form-group>
                            </b-col>
                            <b-col md="6" class="my-1 fillterCollection">
                                <b-form-input class="search_variant" v-model="filter" placeholder="Search variant ..." ></b-form-input>
                                <multiselect v-model="selectProduct" class="multiselect fillterCollection" @input="getvariantByProductID()" :custom-label="nameWithLang" :options="allProducts"  placeholder="Select product to continue" label="title" track-by="id"></multiselect>
                            </b-col> 
                            <b-col md="3" class="my-1">
                                <!-- BULK ACION cREATE RULE LIMIT  -->
                                <div>
                                    <div>
                                        <b-btn @click="showBulkAction=true" variant="primary">Bulk action</b-btn>
                                    </div> 
                                </div>
                                <!-- end BULK ACION -->
                            </b-col>
                        </b-row>
                        <!-- END FILLTER VARIANT -->
                        <div class="loading" v-if="items.length == 0 && selectProduct != 0">
                            <img src="admin/version1/images/loading.gif" alt="loading">
                        </div>
                        <b-table show-empty
                                stacked ="md"
                                :bordered ="true"
                                :hover ="true"
                                :items ="items"
                                :fields ="fields"
                                :current-page ="currentPage"
                                :per-page ="perPage" 
                                :sort-by.sync ="sortBy"
                                :sort-desc.sync ="sortDesc"
                                :sort-direction ="sortDirection" 
                                :filter ="filter" 
                                :striped ="true"
                                @filtered ="onFiltered" 
                                v-if="selectProduct != 0" 
                        >
                            <template slot="HEAD_actions" >
                                <a @click="selectAllVariant()" class="btn selectAll">Select All</a>
                            </template> 
                            <template slot="actions" slot-scope="row"> 
                                <b-form-checkbox
                                    v-model="allVariantChoosen"
                                    :value="row.item.id"> 
                                </b-form-checkbox>
                            </template>
                            <template slot="title" slot-scope="row">{{row.value}}</template>
                            <template slot="min" slot-scope="row">    
                                <p v-if="row.value != '' && row.item.max != '' ">Min:{{row.value}} - Max:{{row.item.max}}</p>
                            </template>
                            <template slot="action" slot-scope="row">  
                                <div v-if="row.item.hasLimit == true">
                                    <a class="btn btn-info" @click.stop="editLimitVariant(row.item.id)">
                                        <i class="fa fa-pencil" aria-hidden="true"></i> 
                                    </a>
                                    <a class="btn btn-danger" @click.stop="deleteLimitVariant(row.item.id)">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>  
                                    </a>
                                
                                </div>
                                <div v-else>
                                    <a class="btn btn-info" size="xs" @click.stop="addLimitVariant(row.item.id)">
                                        Add limit
                                    </a>
                                </div>
                            </template> 
                        </b-table> 
                    
                        <b-row  v-if="selectProduct != 0">
                            <b-col md="6" class="my-1">
                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                            </b-col>
                            <b-col md="6" class="my-1">
                                <b-form-group horizontal label="" class="mb-0">
                                    <b-form-select :options="pageOptions" v-model="perPage" />
                                </b-form-group>
                            </b-col>
                        </b-row>  
                        <div v-else style="text-align: center;padding-top: 10%;">
                            <img src="admin/version1/images/edit-property.png" style="width: 10%">
                            <p style="color:#353232; margin-top : 1rem; "><i class="fa fa-exclamation-circle"></i> Please choose a product to continue</p>
                        </div>
                    </div>
                </b-col>
                <b-col md="5" > 
                    <div class="card cardLimit">  
                        <h6 class="titleCard">Limit Purchase Default </h6>
                        <div class=" "> 
                            <table class="table table-hover product_rule">  
                                <tbody>
                                    <tr>
                                        <td>
                                            <label>Start/End Date</label>
                                            <input type="text" name="daterange"  autocomplete="off" class="form-control daterange" value="" placeholder="Choose start date and end date" /> 
                                        </td>
                                        <td>
                                            <label>Multipule</label>
                                            <input type="number" min="1" v-model="multiple" class="form-control" placeholder="Multipule">
                                        </td>
                                        
                                    </tr>
                                    <tr>
                                        <td>
                                            <label>Min limit purchase</label>
                                            <input type="number" min="1"  v-model="min" class="form-control minDefault" placeholder="Min limit purchase">
                                            <small>If you set 0, this means unlimited</small>
                                        </td>
                                        <td>
                                            <label>Max limit purchase</label>
                                            <input type="number" min="1"  v-model="max" class="form-control maxDefault" placeholder="Max limit purchase">
                                            <small>If you set 0, this means unlimited</small>
                                        </td>
                                        
                                    </tr>
                                </tbody> 
                                <tfoot>
                                    <td> 
                                        <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="listRuleLimitCustomerTag.length == 0" >  Add limit for customer tag</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-default"   @click="saveLimitForVariant()"   v-if="listRuleLimitCustomerTag.length == 0" >  Save</button>
                                    </td>
                                </tfoot> 
                            </table> 
                        </div> 
                    </div> 
                    <div  v-if="listRuleLimitCustomerTag.length > 0">
                        <div class="card cardLimit listRuleCustomertag" v-for="(item, index) in listRuleLimitCustomerTag" :key="index">
                            <h6 class="titleCard">Limit Purchase For Cusstomer Tag </h6>
                            <div class=""> 
                                <table class="table table-hover product_rule">  
                                    <tbody>
                                        <tr> 
                                            <td colspan="2">
                                                <label>Enter Customer Tag</label>
                                                <input type="text"   v-model="item.tag" class="form-control" :class="'tag_'+index" >
                                                <small>Please enter a comma-separated list of tags</small>
                                            </td>  
                                        </tr> 
                                        <tr>
                                            <td>
                                                <label>Min limit purchase</label>
                                                <input type="number" min="1"  v-model="item.min" class="form-control minLimit" :class="'min_'+index" placeholder="Min limit purchase">
                                                <small>If you set 0, this means unlimited</small>
                                            </td>
                                            <td>
                                                <label>Max limit purchase</label>
                                                <input type="number" min="1"  v-model="item.max" class="form-control maxLimit" :class="'max_'+index" placeholder="Max limit purchase">
                                                <small>If you set 0, this means unlimited</small>
                                            </td>
                                            
                                        </tr>
                                    </tbody> 
                                    <tfoot>
                                        <td> 
                                            <button class="btn outline-secondary "   @click="removeRuleLimitForCustomerTag(index)" >  Cancel</button>
                                            <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="index == listRuleLimitCustomerTag.length - 1" >  Add limit for customer tag</button>
                                           
                                        </td>
                                        <td>
                                             <button class="btn btn-default" @click="saveLimitForVariant()" v-if="index == listRuleLimitCustomerTag.length - 1" >  Save</button> 
                                        </td>
                                    </tfoot> 
                                </table> 
                            </div>
                        </div>
                    </div>
                </b-col>
            </b-row>
            <b-modal v-model="showBulkAction" title="Assign Products/Collections">
                <b-container fluid>
                    <b-row>
                        <b-col md="6" class="col-12" style="margin-top: 33px;">
                            <div>
                                <b-form-select id="exampleInput3"
                                        :options="optionsSelect"
                                        required
                                        v-model="selectedtypeBulkAction">
                                    <template slot="first">
                                    
                                    </template>
                                </b-form-select>
                            </div>
                            <div class="ot-pt-20" v-if="selectedtypeBulkAction == 0" >
                                <label for="inputLive" class="ot-text-bold">Assign for products</label>
                                <multiselect :options="allProducts"   v-model="productsChoosen" :multiple="true" label="title" track-by="title"  placeholder="Search">  </multiselect>
                            </div>
                            <div class="ot-pt-20" v-else-if="selectedtypeBulkAction == 1">
                                <label for="inputLive" class="ot-text-bold">Assign for collections</label>
                                <multiselect :options="allCollections"   v-model="collectionsChoosen" :multiple="true" label="title" track-by="title"  placeholder="Search">  </multiselect>
                            </div>
                        </b-col>
                        <b-col md="6" class="col-12">  
                            <b-row>
                                <b-col md="6" class="ot-pt-20-small col-6">
                                    <div class="text-center">
                                        <label>Start/End Date</label>
                                        <input type="text" name="daterange1"  autocomplete="off" class="form-control daterange1" value="" placeholder="Choose start date and end date" /> 
                                    </div>
                                </b-col>
                                <b-col md="6" class="ot-pt-20-small col-6">
                                    <div class="text-center" >
                                        <label>Multipule</label>
                                        <input type="number" min="1" v-model="multiple" class="form-control" placeholder="Multipule">
                                    </div>
                                </b-col> 
                                <b-col md="6" class="ot-pt-20-small col-6">
                                    <div class="text-center">
                                        <label for="inputLive" class="ot-text-bold">Min limit purchase</label>
                                    <input type="number" min="1"   v-model="min" class="form-control" placeholder="Min limit purchase">
                                    </div>
                                </b-col>
                                <b-col md="6" class="ot-pt-20-small col-6">
                                    <div class="text-center" >
                                        <label for="inputLive" class="ot-text-bold">Max limit purchase</label>
                                    <input type="number" min="1"   v-model="max" class="form-control" placeholder="Max limit purchase">
                                    </div>
                                </b-col>
                                <b-col md="12" class="ot-pt-20-small col-6"  style="margin-top:10px;">
                                    <div  v-if="listRuleLimitCustomerTag.length > 0">
                                        <div class="cardLimit listRuleCustomertag" v-for="(item, index) in listRuleLimitCustomerTag" :key="index">
                                            <h6 class="titleCard">Limit Purchase For Cusstomer Tag </h6>
                                            <div class=""> 
                                                <table class="table table-hover product_rule">  
                                                    <tbody>
                                                        <tr> 
                                                            <td colspan="2">
                                                                <label>Enter Customer Tag</label>
                                                                <input type="text"   v-model="item.tag" class="form-control" :class="'tag_'+index" >
                                                                <small>Please enter a comma-separated list of tags</small>
                                                            </td>  
                                                        </tr> 
                                                        <tr>
                                                            <td>
                                                                <label>Min limit purchase</label>
                                                                <input type="number" min="1"  v-model="item.min" class="form-control minLimit" :class="'min_'+index" placeholder="Min limit purchase">
                                                                <small>If you set 0, this means unlimited</small>
                                                            </td>
                                                            <td>
                                                                <label>Max limit purchase</label>
                                                                <input type="number" min="1"  v-model="item.max" class="form-control maxLimit" :class="'max_'+index" placeholder="Max limit purchase">
                                                                <small>If you set 0, this means unlimited</small>
                                                            </td>
                                                            
                                                        </tr>
                                                    </tbody> 
                                                    <tfoot>
                                                        <td> 
                                                            <button class="btn outline-secondary "   @click="removeRuleLimitForCustomerTag(index)" >  Cancel</button>
                                                        
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="index == listRuleLimitCustomerTag.length - 1" >  Add limit for customer tag</button>

                                                        </td>
                                                    </tfoot> 
                                                </table> 
                                            </div>
                                        </div>
                                    </div>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </b-container>
                <div slot="modal-footer" class="w-100">
                    <button class="btn btn-default btn-addTag"   @click="addRuleLimitForCustomerTag()" v-if="listRuleLimitCustomerTag.length == 0" >  Add limit for customer tag</button>

                    <button class="btn btn-default btn-save-product"  style="float:right;"  @click="saveLimitForBulkAction()"  >   <span id="ot-text-button"> Save</span></button> 
                </div>
            </b-modal>
        </b-container> 
    </div>
</template>
 
<script>  
    module.exports = {
        // bien truyen tu component cha
        props : ["allProducts"],
        data  : function() {
            return {   
                /*
                    min,max  : min/max limit purchase 
                    fields,sortBy,sortDesc: custom column table variants list 
                    currentPage, perPage, pageOptions custom paginiton data table
                    fillter :key search title collection in data table
                    items : list variants by product id
                    checkAll: choose all variant to add limit
                    status : fillter variants has rule limit (0: variant has not rule, 1: variant has rule)
                    showBulkAction : show popup add limit for product, collection, entire store
                    selectProduct : fillter variant by selectProduct
                    selectedtypeBulkAction : type bulk action add rule limit (0: assign for product , 1 assign for collection)
                    allCollections : list collection get from shopify 
                    collectionsChoosen,productsChoosen : collection/product chosen add rule limit (bulk action)
                    idVariantEdit : variant edit limit rule.
                    allVariantChoosen : add multi limit for variant
                    allVariants : all variant get by selectProduct
                    variantsHasRule : variant has rule limit
                */
                min                 : "",
                max                 : "",  
                items               : [],
                currentPage         : 1,
                perPage             : 10, 
                sortBy              : null,
                sortDesc            : false,
                sortDirection       : 'asc',
                totalRows           : null,
                filter              : null, 
                status              : '0', 
                checkAll            : false,  
                showBulkAction      : false,  
                selectProduct       : 0, 
                selectedtypeBulkAction : 0,  
                allCollections      : [],
                collectionsChoosen  : [],
                productsChoosen     : [], 
                idVariantEdit       : null, 
                allVariantChoosen   : [], 
                allVariants         : [], 
                variantsHasRule     : [],
                fields: [
                    { key: 'actions', label: ``,'class': 'text-center selectVariants' },
                    { key: 'title',   label: 'Variant', sortable: true, sortDirection: 'desc' },
                    { key: 'min',     label: 'Limit Default(s)',   'class': 'text-center' },
                    { key: 'multiple',     label: 'Multiple',   'class': 'text-center' },
                    { key: 'action',  label: 'Actions','class': 'text-center actions' }
                ],
                optionsSelect: [ 
                    { text: 'Products',     value: 0 },
                    { text: 'Collections',  value: 1 },
                    // { text: 'All Products', value: 2 },
                ], 
                pageOptions: [ 10, 20,50 ],
                listLimitFollowCollection : [],
                showListRuleFollow: false,
                listRuleLimitCustomerTag:[],
                date:"",
                multiple:"", 
            };
        },
        mounted: function() {
            let self = this;
            self.getAllCollection(); 
            $('input[name="daterange"]').daterangepicker({
                timePicker: true,  
                autoupdateinput: false,
                // startDate: moment().startOf('hour'),
                // endDate: moment().startOf('hour').add(32, 'hour'),
                locale: {
                  format: 'MM/DD/YYYY hh:mm'
                }
            }); 
            $(".daterange").val("");
             $('input[name="daterange1"]').daterangepicker({
                timePicker: true,  
                autoupdateinput: false,
                // startDate: moment().startOf('hour'),
                // endDate: moment().startOf('hour').add(32, 'hour'),
                locale: {
                  format: 'MM/DD/YYYY hh:mm'
                }
            }); 
            $(".daterange1").val("");
            
            $(".daterange1,.daterange").on('cancel.daterangepicker', function(ev, picker) {
                console.log("cancel")
                $(this).val('');
            });
         }, 
        methods : {  
            nameWithLang ({title,sku}) {
                //custome name display in multi select 
                if(sku != ""){
                    return `${title} `+`(${sku})`;
                }else{
                    return `${title}`;
                }
                
            },
            removeRuleLimitForCustomerTag(key){
                this.listRuleLimitCustomerTag.splice(key, 1); // xóa 1 phần tử từ vị trí 2 
            },
            addRuleLimitForCustomerTag(){ 
                $(".form-control").removeClass("error");
                let data = { 
                    min:"",
                    max:"",
                    tag:""
                }
                var self = this; 
                if(self.checkValidRuleLimitCustomerTag() == true && self.min != "" && self.max != ""){
                    self.listRuleLimitCustomerTag.push(data);
                }else{ 
                    if(self.max == "" || self.min == ""){
                        if(self.min == ""){
                            $(".minDefault").addClass("error");
                        }
                        if(self.max == ""){
                            $(".maxDefault").addClass("error");
                        }
                    }
                    ShopifyApp.flashError("Please enter all fields before continue !");  
                }
                 
            }, 
            checkValidRuleLimitCustomerTag(){ 
                var result = true;
                var self = this;
                self.listRuleLimitCustomerTag.forEach(function(element,index){ 
                    if(element.min == "" || element.max == "" || element.tag == ""){
                        if(element.min == ""){
                            $(".min_"+index).addClass("error");
                        }
                        if(element.max == ""){
                            $(".max_"+index).addClass("error");
                        }
                        if(element.tag == ""){
                            $(".tag_"+index).addClass("error");
                        }
                        result =  false;
                    }
                }) 
                return result;
            },
            showGroupCollection:function(RuleByCollection){  
                let self = this;
                self.min = RuleByCollection.min;
                self.max = RuleByCollection.max;
                self.multiple = RuleByCollection.multiple;
                self.listRuleLimitCustomerTag = RuleByCollection.limitforCustomerTag;
                RuleByCollection['id'] = RuleByCollection['id_collection'];
                RuleByCollection['title'] = RuleByCollection['title_collection'];
                self.selected = 1; 
                self.selectedtypeBulkAction = 1; 
                
                self.collectionsChoosen.push(RuleByCollection);
            },
            deleteGroupCollection:function(idCollection){
                let self = this ;
                ShopifyApp.Modal.confirm({ 
                    title: "Delete limit",
                    message: "Do you want to delete limit ?",
                    okButton: "Delete",
                    cancelButton: "No",
                    style: "danger"
                }, function(res){
                    if(res){
                        $.ajax({
                            url: 'services_v2.php',
                            type: 'GET',
                            data: {action: 'deleteGroupCollection',shop:shop,idCollection :idCollection},
                            dataType: 'json'
                        }).done(function (result) {
                            ShopifyApp.flashNotice("Delete rule successfully!");
                            self.showListRuleFollow  = false;
                            self.showListRuleFollow = !self.showListRuleFollow;
                            self.showAllRuleLimitFollowColleciton();  
                        }).fail((error) => {
                            ShopifyApp.flashError("Delete rule error!");  
                        }); 
                    } 
                }); 
            },
            showAllRuleLimitFollowColleciton: function(){
                var self = this; 
                self.showListRuleFollow = !self.showListRuleFollow;
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: {action: 'showAllRuleLimitFollowColleciton',shop:shop},
                    dataType: 'json'
                }).done(function (result) {
                    console.log(result)
                    self.listLimitFollowCollection = result;
                }).fail((error) => {
                    ShopifyApp.flashError("get all limit in addLimitVariant error!");   
                }); 
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
                    self.allCollections = response ;
                })  
            },
            onFiltered (filteredItems) {
                let self = this;
                // Trigger pagination to update the number of buttons/pages due to filtering
                self.totalRows = filteredItems.length;
                self.currentPage = 1;
            }, 
            selectAllVariant(){
                // select all variant to add limit for multi variants
                var self = this;
                self.checkAll = !self.checkAll; 
                if(self.checkAll == true){ 
                    for(var i=0; i < self.items.length ; i++){
                        self.allVariantChoosen.push(self.items[i].id);
                    }
                }else{ 
                    self.allVariantChoosen = [];
                }
            },
            saveLimitForVariant: function(){
                // Save limit for variant  
                var self = this; 
                let valid = self.validBeforeSave();   
                self.date = $(".daterange").val();
                if(valid == true){
                    $.ajax({
                        url : 'services_v2.php',
                        type: 'POST',
                        data: {
                            action :'saveLimitForVariant',
                            shop   :shop, 
                            min    :self.min, 
                            max    :self.max,
                            multiple :self.multiple,
                            date :self.date,
                            allVariantChoosen:self.allVariantChoosen,
                            listRuleLimitCustomerTag:self.listRuleLimitCustomerTag
                        },
                        dataType   : 'json'
                    }).done(function (result) { 
                        self.status =  '0';
                        self.resetValueAfterSave();
                        self.getvariantByProductID();
                        ShopifyApp.flashNotice("Save rule successfully!"); 
                    }).fail((error) => {
                        ShopifyApp.flashError("Save limit for variant error!");  
                    });
                }else{
                    ShopifyApp.flashError(valid);  
                } 
            },
            saveLimitForBulkAction: function(){ 
                // save rule limit for bulk action 
                var self = this;
                let valid = self.validBeforeSaveProducts();
                self.date = $(".daterange1").val();
                if(valid === true){
                    $("#ot-text-button,.btn-save-product").text("Saving...");
                    $(".btn-save-product").attr("disabled","disabled");
                    $.ajax({
                        url : "services_v2.php",
                        data: {
                            action  : "saveLimitForProducts",
                            shop    : shop,
                            min     : self.min,
                            max     : self.max,
                            multiple :self.multiple,
                            date :self.date,
                            selected: self.selectedtypeBulkAction,
                            productsChoosen   : self.productsChoosen,
                            collectionsChoosen: self.collectionsChoosen,
                            listRuleLimitCustomerTag:self.listRuleLimitCustomerTag
                        },
                        dataType : "JSON",
                        type     : "POST"
                    }).done(function(){ 
                        $('.btn-save-product').prop('disabled', false);
                        $("#ot-text-button,btn-save-product").text("Save");
                        self.status =  '0';
                        if(self.selectProduct.id != null){
                            self.getvariantByProductID(); 
                        } 
                        self.collectionsChoosen = [];
                        self.productsChoosen = []; 
                        self.showBulkAction = false;
                        self.resetValueAfterSave();
                        self.showListRuleFollow = !self.showListRuleFollow;
                        self.showAllRuleLimitFollowColleciton();  
                        ShopifyApp.flashNotice("Save limit successfully!"); 
                    })
                }else{
                    $('.btn-save-product').prop('disabled', false);
                    $("#ot-text-button").text("Save");
                    ShopifyApp.flashError(valid);  
                }
            },
            validBeforeSaveProducts: function(){
                // valid add bulk limit purchase
                var self = this;
                var valid = true; 
                if(self.selectedtypeBulkAction == null){
                    valid = "Please select an option";
                }else{
                    if(self.selectedtypeBulkAction == 0 && self.productsChoosen == ""){
                        valid = "Please assign products";
                    }
                    if(self.selectedtypeBulkAction == 1 && self.collectionsChoosen == ""){
                        valid = "Please assign collections";
                    }
                }
                return valid;
            }, 
            validBeforeSave(){
                // check valid when add rule limit for varian
                var self = this; 
                var valid = true;
                $(".form-control").removeClass("error");
                if(self.allVariantChoosen.length == 0){
                    valid = "Please choose variant before save limit!";
                }
                if(self.max == "" || self.min == ""){
                    if(self.min == ""){
                        $(".minDefault").addClass("error");
                    }
                    if(self.max == ""){
                        $(".maxDefault").addClass("error");
                    }
                    valid = "Please choose variant before save limit!";
                }
                if(self.listRuleLimitCustomerTag.length > 0){ 
                    self.listRuleLimitCustomerTag.forEach(function(element,index){
                        if(element.min == "" || element.max == ""){
                            valid = "Please enter all fields before continue !"; 
                            if(element.min == ""){
                                $(".min_"+index).addClass("error");
                            }
                            if(element.max == ""){
                                $(".max"+index).addClass("error");
                            }
                            if(element.tag == ""){
                                $(".tag"+index).addClass("error");
                            }
                        }
                    })
                }
                return valid;
            },
            editLimitVariant:function(id){
                // get rule limit for variant id
                let self = this; 
                self.allVariantChoosen = [];
                self.allVariantChoosen.push(id);
                self.idVariantEdit = id; 
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action: 'getLimitByVariantID',shop:shop,idVariant :id},
                    dataType: 'json'
                }).done(function (result) {
                    self.min = result.min ;
                    self.max = result.max ;
                    self.multiple = result.multiple ;
                    self.date = result.start_date + "-" + result.end_date ;
                    self.listRuleLimitCustomerTag = result.limitforCustomerTag;
                    if(result.start_date != null && result.start_date != null){
                        $(".daterange").val(self.date);
                    } 
                }).fail((error) => {
                    ShopifyApp.flashNotice("Get limit by variant id error!");  
                }); 
            },
            updateLimitVariant:function(){
                // update rule limit purchase by idVariantEdit
                let self = this; 
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {
                        action      : 'updateLimitVariant',
                        shop        : shop,
                        idVariant   : self.idVariantEdit,
                        min         : self.min,
                        max         : self.max,
                    },
                    dataType: 'json'
                }).done(function (result) { 
                    self.status =  '0';
                    self.getvariantByProductID();
                    self.allVariantChoosen = [];
                    self.idVariantEdit = null;
                   
                    self.min = null;
                    self.max = null;
                    ShopifyApp.flashNotice("Update limit successfully!"); 
                }).fail((error) => {
                    ShopifyApp.flashError("Update rule for variant error!");  
                }); 
            },
            deleteLimitVariant:function(id){ 
                // delete rule limit purchase by variant id
                let self = this;
                ShopifyApp.Modal.confirm({ 
                    title   : "Delete limit",
                    message : "Do you want to delete limit ?",
                    okButton: "Delete",
                    cancelButton: "No",
                    style   : "danger"
                }, function(res){
                    if(res){
                        $.ajax({
                            url : 'services_v2.php',
                            type: 'GET',
                            data: {action: 'deleteLimitVariant',shop:shop,idVariant :id},
                            dataType: 'json'
                        }).done(function (result) {
                            ShopifyApp.flashNotice("Delete rule successfully!"); 
                            self.status = '0';
                            self.getvariantByProductID();
                        }).fail((error) => {
                            ShopifyApp.flashError("Delete rule error!");  
                        }); 
                    } 
                }); 
                
            }, 
            addLimitVariant:function(id){
                // add limit for variant
                var self = this;
                self.allVariantChoosen = [];
                self.allVariantChoosen.push(id);
                self.idVariantEdit = null;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action: 'getLimitVariantBeforeAdd',shop:shop,idVariant :id},
                    dataType: 'json'
                }).done(function (result) {
                    console.log("result",result);
                    self.min = result.min;
                    self.max = result.max;
                }).fail((error) => {
                    ShopifyApp.flashError("get limit in addLimitVariant error!");   
                }); 
            }, 
            getvariantByProductID:function(){
                // get all variant limit by product selectProduct.id 
                let self = this;
                self.variantsHasRule = [];
                self.items = [];
                self.allVariants = [];
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action: 'getVariantLimitByProductID',shop:shop,idProduct :self.selectProduct.id},
                    dataType: 'json'
                }).done(function (result) {
                    self.items       = result.variants;
                    self.totalRows   = self.items.length;
                    self.allVariants = result.variants;
                    for(let i = 0; i < result.variants.length ;i++){ 
                        if(result.variants[i]['hasLimit'] === true){  
                            self.variantsHasRule.push(result.variants[i]);
                        } 
                    } 
                }).fail((error) => {
                        ShopifyApp.flashError("Get variant limit by product  error!");  
                }); 
            },
            fillterHasRule:function(){
                // fillter has rule limit 
                let self = this;   
                if(self.status === '1'){
                    self.items = self.allVariants; 
                }else{
                    self.items = self.variantsHasRule;
                }
            }, 
            resetValueAfterSave:function(){
                var self = this;
                self.min = null;
                self.max = null;
                self.listRuleLimitCustomerTag = [];
                self.date ="";
                self.multiple ="";
                $(".daterange").val("");
            }
        },
        components: { Multiselect: window.VueMultiselect.default  }
    };
</script>
<style  scoped src="admin/version1/styles/limit.css?v=333"> </style>

<style>
.cardLimit .btn-default{
    float: initial;
}
.cardLimit{
    margin-bottom: 10px;
}
.outline-secondary {
    color: #000000 !important;
    border-color: #6c757d !important;
}
.listRuleCustomertag .btn-default{
    float: initial;
    margin-right: 0 !important;
}
.cardLimit .btn-addTag{
    font-size: 13px;
    /* margin-top: 10px; */
}

</style>
 
