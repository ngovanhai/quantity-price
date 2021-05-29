<style>
.alert.alert-danger {
    background-color: #f55145;
    color: #fff;
}
.alert.alert-success {
    background-color: #55b559;
    color: #fff;
}
.alert {
    border: 0;
    border-radius: 0;
    padding: 8px 10px !important;
    line-height: 20px;
    font-weight: 300;
    color: #fff;
}

.alert .alert-icon {
    display: block;
    float: left;
    margin-right: 1.071rem;
}

.alert b {
    font-weight: 500;
    font-size: 12px;
    text-transform: uppercase;
}
.alert.alert-warning {
    background-color: #ff9e0f;
    color: #fff;
}
</style>
<template>
    <div>  
        <!-- 15/08/2019 -->
        <b-container fluid>  
            <b-row>
                <b-col md="4" class="card">
                    <div style="display:flex;justify-content: space-between">
                        <div class="otQuantityTitleListProduct">Product List</div>
                        <button  type="button" class="btn btn-primary btn-sm" style="color: #fff!important;background-color: #5563c1!important;border-color: #0062cc!important;height:33px!important" data-toggle="modal" data-target="#exampleModalCenter">
                            Import excel
                        </button>
                    </div>
                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div style="width:34%!important" class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"   id="exampleModalLongTitle">Import excel</h5>
                                    <button type="button" @click="closeModal()"  class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div v-if="showNoti==true && countSuccessImport > 0" class="alert alert-success">
        <div  class="container">
            <div class="alert-icon">
                <i class="material-icons">check</i>
            </div>
            <b>Success Alert:</b> {{countSuccessImport}} fields have been imported successfully
        </div>
    </div>
    <!--
    <div v-if="showNoti==true && countSuccessImport == 0" class="alert alert-warning">
         <div class="container">
             <div class="alert-icon">
                <i class="material-icons">warning</i>
            </div>
             <b>Warning Alert:</b> No fields have been successfully imported, please check the fields in excel and try again!
        </div>
    </div>
    -->
        <div v-if="loading == true" style="display:flex; justify-content: center; flex-direction:column; align-items:center">
    <div class="spinner-border text-secondary" role="status">
  <span class="sr-only">Loading...</span>
</div>
<span style="color:#848484">Importing... please wait 1-3 minutes</span>
</div>
    <div v-if="errorValidate!==null" class="alert alert-warning">
         <div class="container">
             <div class="alert-icon">
                <i class="material-icons">warning</i>
            </div>
             <b>Warning Alert: </b> {{errorValidate}}!
        </div>
    </div>
     <div v-if="showNoti==true && countErrorImport > 0" class="alert alert-danger">
         <div class="container">
             <div class="alert-icon">
                <i class="material-icons">error_outline</i>
            </div>
             <b>Error Alert:</b> {{countErrorImport}} import fields failed
        </div>
    </div>
                                <div style="padding:30px" >
                                    <form v-if="loading == false" method="POST" enctype="multipart/form-data">
                                        <input type="file" id="inputName" name="file"  @change='fileChange($event)'>
                                    </form>
                                    <h1 style="font-size:18px;margin-top: 50px">
                                        Maximum 2000 lines in file can be import
                                    </h1>
                                    <p>We suggest you to split the file into smaller ones to make make the app import easier. 
                                    <br>Download our
                                    <a :href="urlTemplate" target="_blank">.xlsx template
                                    </a>
                                    to see an example of the required format. </p>
                                </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" @click="closeModal()" data-dismiss="modal">Close</button>
                                    <button id="buttonImport" type="sumit" :disabled="disableButtonImport" @click="postImportExcel()" name="importExcel" class="btn btn-primary" style="color: #fff!important;background-color: #5563c1!important;border-color: #0062cc!important;">Import</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="otWrapperProductList">
                        <div class="otFillterProduct">
                            <b-row> 
                                <b-col md="9">
                                    <b-row>
                                        <b-col md="5">
                                            <b-form-input v-model="filter" placeholder="Search for product name"></b-form-input>
                                        </b-col>
                                        <b-col md="7">
                                            <multiselect v-model="selectCollection" @input="changeFillterCollection()" :custom-label="nameWithLang" :options="allCollection"  placeholder="Filter By Collection" label="title" track-by="id"></multiselect>
                                        </b-col> 
                                    </b-row>
                                </b-col>
                                <b-col md="3">
                                    <b-form-group horizontal id="hasRule" class="mb-0">
                                        <b-form-checkbox
                                            v-model = "status"
                                            value   = "1" @change="fillterHasRule()" 
                                            unchecked-value = "0"> 
                                        </b-form-checkbox>
                                        <label class="labelHasRule" style="position: absolute;">Has rule</label>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </div>
                        <div class="otProductList">
                            <div class="loading" v-if="showLoadingProduct == true">
                                <img src="admin/version1/images/loading.gif" alt="loading">
                            </div>
                            <p>Total product: {{items.length}}</p>
                            <b-table show-empty
                                stacked         = "md"
                                :bordered       = "true"
                                :hover          = "true"
                                :items          = "items"
                                :fields         = "fields"
                                :current-page   = "currentPage"
                                :per-page       = "perPage" 
                                :sort-by.sync   = "sortBy"
                                :sort-desc.sync = "sortDesc"
                                :sort-direction = "sortDirection" 
                                :filter         = "filter" 
                                :striped        = "true"
                                @filtered       = "onFiltered" 
                            > 
                                <template slot="HEAD_actions" slot-scope="head"> 
                                    <b-form-checkbox v-model="checkAll"  @click.native="selectAllProduct()">  </b-form-checkbox>
                                </template> 
                                <template slot="actions" slot-scope="row"  > 
                                    <b-form-checkbox  @change="getvariantByProductIDCheckBox(row.item.id,row.item.title,$event)"
                                        v-model = "allProductChoosen"
                                        :value  = "row.item.id"> 
                                    </b-form-checkbox>
                                </template>
                                <template slot="title" slot-scope="row">
                                    <a style="cursor: pointer;"  @click="getvariantByProductID(row.item.id,row.item.title)" >
                                         {{row.value}}  
                                    </a>
                                </template>
                                <template slot="total_rules" slot-scope="row">
                                    <a style="cursor: pointer;" target="_blank"  :href="'https://'+shop+'/products/' + row.item.handle" >
                                         {{row.value}}  
                                    </a>
                                </template>
                                <template slot="action" slot-scope="row"> 
                                    <div v-if="row.item.hasrule == true">
                                        <a class="btn btn-info" @click.stop="editRuleProduct(row.item.id,row.item.title)">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>   
                                        </a>
                                        <a class="btn btn-danger" @click.stop="deleteRuleProduct(row.item.id,row.item.title,row.item.content_rule)">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i> 
                                        </a> 
                                    </div> 
                                    <div v-else>
                                        <a class="btn btn-info" size="xs" @click.stop="addRuleProduct(row.item.id,row.item.title)">
                                          Add rule 
                                        </a>
                                    </div>
                                </template> 
                            </b-table> 
                            <b-row>
                                <b-col md="6" class="my-1 col-6">
                                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                                </b-col>
                                <b-col md="6" class="my-1 col-6"> 
                                    <b-form-group horizontal label="" class="mb-0" style="float:right;width: 50%;">
                                        <b-form-select :options="pageOptions" v-model="perPage" />
                                    </b-form-group>
                                </b-col> 
                            </b-row>  
                        </div>
                    </div> 
                </b-col> 
                 <b-col md="6" v-if="showModalAddMultiRuleProduct == true && allProductChoosen.length != 0">
                    <div class="card"> 
                        <div class="container">
                            <div class="otQuantityTitleListVariant">Add Rules for <b> <span style="color:red;">{{allProductChoosen.length}}</span> Selected Products</b> </div>
                            <p> You can create discount rules for multiple products one time.<br> Otherwise you can click the product title to continue adding the rules to the variant. </p>   
                        </div> 
                        <div class="table-responsive" style="margin-top:5px; ">  
                            <div class="container">
                               <table class="table table-hover product_rule">  
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
                                            <td><a class="addTies btn-primary text-center" @click="otAddOffer" style="float: left;">Add line</a></td> 
                                            <td>
                                                <button class="btn btn-default"  style="float:left;"  @click="saveRuleForProduct()" v-if="idProductEdit == null && ruleForCustomerTag.length == 0" > Save</button>
                                                <button class="btn btn-default"  style="float:left;"  @click="updateRuleProduct()" v-if="idProductEdit != null && ruleForCustomerTag.length == 0"> Update</button> 
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
                                    <tfoot>
                                        <tr> 
                                            <td >
                                                <a class="btn btn-default" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                                            </td>   
                                            <td>
                                                <button class="btn btn-default"  style="float:left;"  @click="saveRuleForProduct()" v-if="ruleForCustomerTag.length != 0" > Save</button>
                                            </td>
                                        </tr>
                                    </tfoot> 
                                </table> 
                            </div> 
                        </div>  
                    </div> 
                </b-col>
                <b-col md="8" style="background:none;padding-top:0px;    padding-right: 0;" v-else>  
                    <variant-rule ref="childRef" :all-products="allProduct" :show-modal-create-rule-for-product="showModalCreateRuleForProduct" :title-product-filter = "titleProductFilter" :fillter-product="fillterProduct" ></variant-rule>                            
                </b-col>  
            </b-row>
        </b-container>
        <!-- modal create rule for 1 product  --> 
        <b-modal v-model="showModalCreateRuleForProduct" title="Add/Edit rule for product">
            <b-container fluid> 
                <div class="table-responsive addRuleForProduct  table_modal"> 
                    <table class="table table-hover product_rule">  
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
                                <td><a class="addTies btn-primary text-center" @click="otAddOffer" style="float: left;">Add line</a></td> 
                                <td>
                                    <button class="btn btn-default"  style="float:left;"  @click="saveRuleForProduct()" v-if="idProductEdit == null && ruleForCustomerTag.length == 0" > Save</button>
                                    <button class="btn btn-default"  style="float:left;"  @click="updateRuleProduct()" v-if="idProductEdit != null && ruleForCustomerTag.length == 0"> Update</button> 
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
                        <!-- <tfoot>
                            <tr> 
                                <td >
                                    <a class="btn btn-default" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                                </td>   
                                <td>
                                    <button class="btn btn-default"  style="float:left;"  @click="saveRuleForProduct()" v-if="ruleForCustomerTag.length != 0" > Save</button>
                                </td>
                            </tr>
                        </tfoot>  -->
                    </table> 
                </div> 
            </b-container>
            <div slot="modal-footer" class="w-100"> 
                <a class="btn btn-primary addTies" @click="otAddRuleForCustomerTag" style="float: left;">Add new rule for customer tag</a>
                <button class="btn btn-default"  style="float:right;"  @click="saveRuleForProduct()" v-if="ruleForCustomerTag.length != 0" > Save</button>
            </div>
        </b-modal> 
        <!-- end popup create rule --> 
       </div>
</template>
<script>    
    module.exports = {
        props: ["fillterCollection"], 
        data: function() {
            return { 
                /* 
                    allproduct: all product in store from shopify 
                    items: list product to data table
                    fields,sortBy,sortDesc: custom column table collection list.
                    filter: fillter product name in table
                    start_date, endate    : manage schedule tier (Y,m,d) 
                    productHasRule : all product has rule when fillter has rule
                    allProductChoosen : all product chosen when add multi rule for product
                    idProductEdit : id of product edit, when click add edit rule of product
                    titleProductChoose: title collection chosen to edit rule
                    fillterCollectionOld: chua collection da tung fillter  in data table
                    showLoadingProduct: show loading when get product from shopify 
                    fillterProduct: get all variant by fillterProduct;
                    selectCollection: get product by id collection in datatable
                    allCollection: get all collection from shopify 
                    resultFilterItems: when fillter item, click select all, all product in per page will chosen not all product all page.
                    status: fillter product has rule 
                    showModalAddMultiRuleProduct: show popup add rule for multi product
                    showModalCreateRuleForProduct : show modal add rule for one product
                */
            //    statusRowFileExit:true,
            //    index:1,
            //    dataExcel:'none',
               loading:false,
               showNoti : false,
               countSuccessImport : 0,
                countErrorImport : 0,
                errorValidate:null,
               files:null,
               disableButtonImport:true,
                urlTemplate : window.rootlink + '/TeamplateQuantity.xlsx',
                allProduct      : [],
                items           : [],  
                currentPage     : 1,
                perPage         : 10,
                sortBy          : null,
                sortDesc        : false,
                checkAll        : false,
                sortDirection   : 'asc',
                totalRows       : null,  
                filter          : null, 
                shop            : window.shop,
                titleProductFilter:null, 
                start_date      : null,
                end_date        : null, 
                productHasRule  : [], 
                allProductChoosen:[], 
                idProductEdit   : null, 
                fillterCollectionOld: null, 
                showLoadingProduct: true, 
                fillterProduct: null, 
                selectCollection: [], 
                allCollection   : [],   
                titleProductChoose : null, 

                drafCheck        : [], 
                resultFilterItems: [], 
                status          : '0',   
                showModalAddMultiRuleProduct: false,
                showModalCreateRuleForProduct: false, 

                pageOptions  : [5,10,20,50,100 ],   
                groups  : [{price: null, number: null, discountType: 'percent'}], 
                options : [  
                    { text: '%', value: 'percent' },
                    { text: window.money_format, value: 'price' },
                ], 
                ruleForCustomerTag:[],
                fields : [
                    { key: 'actions', label: ``,'class': 'text-center selectColletion' },
                    { key: 'title',   label: 'Product', sortable: true, sortDirection: 'desc' },
                    { key: 'total_rules', label: 'Rule(s)',   'class': 'text-center ' },
                    { key: 'action', label: 'Actions','class': 'text-center actions' }
                ],
            };
        }, 
        mounted: function() {
            let self = this;   
            self.getAllProduct();
            self.getCountCollection("custom");

        },
        watch: {
            index(){
                console.log(2323);
            }
        },
        methods : {
            fileChange(e){
                this.files = e;
                if(this.files.target.files.length>0){
                    this.showNoti = false;
                    this.disableButtonImport = false;
                    this.countSuccessImport = 0;
                    this.countErrorImport = 0;
                    this.errorValidate = null;
                }
                else{
                    this.disableButtonImport = true;
                }
            },
             closeModal:function(){
                 this.files = null;
                  this.showNoti = false;
                    this.disableButtonImport = false;
                    this.countSuccessImport = 0;
                    this.countErrorImport = 0;
                    this.errorValidate = null;
                    this.loading = false;
             },
            //  postImportExcel:function(){
            //      this.disableButtonImport = true;
            //      this.loading = true;
            //      self = this;
            //      if(this.statusRowFileExit){
            //             const fd = new FormData();
            //             fd.append("importFile", this.files.target.files[0]);
            //             fd.append("shop", shop);
            //             fd.append("action", "importExcel");
            //             fd.append("index", self.index);
            //             fd.append("dataExcel", self.dataExcel);
            //             $.ajax({
            //                 url: 'services_v2.php',
            //                 type: 'POST',
            //                 data:fd,
            //                 contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            //                 processData: false, // NEEDED, DON'T OMIT THIS
            //                 dataType: 'json',
            //                 cache: false,
            //             }).done(function (result) {
            //                 console.log(result);
                           
            //             }).fail((error) => {
            //                 self.loading = false;
            //                 document.getElementById("inputName").value = "";
            //                 self.files = null;
            //                 ShopifyApp.flashError("Cannot import, Please check type file and all fields before continue !");
            //             });
            //             }else{
            //                 ShopifyApp.flashError("Not selected file yet !");
            //             }
                
            //  },
             postImportExcel:function(){
                //  document.getElementById("inputName").value = "";
                 this.disableButtonImport = true;
                 this.loading = true;
                 self = this;
                 if(this.files!==null){
                        const fd = new FormData();
                        fd.append("importFile", this.files.target.files[0]);
                        fd.append("shop", shop);
                        fd.append("action", "importExcel");
                        $.ajax({
                            url: 'services_v2.php',
                            type: 'POST',
                            data:fd,
                            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
                            processData: false, // NEEDED, DON'T OMIT THIS
                            dataType: 'json',
                            cache: false,
                        }).done(function (result) {
                            self.loading = false;
                            self.showNoti = true;
                            self.countSuccessImport = result.success;
                            self.countErrorImport = result.error;
                            self.errorValidate = result.validate;
                            ShopifyApp.flashNotice("Import excel successfully!");
                            self.files = null;
                            self.disableButtonImport = true;
                            self.changeFillterCollection();
                        }).fail((error) => {
                            self.loading = false;
                            self.files = null;
                            ShopifyApp.flashError("Cannot import, Please check type file and all fields before continue !");
                        });
                        }else{
                            ShopifyApp.flashError("Not selected file yet !");
                        }
                },
            getCountCollection: function(type){ 
                var self = this;
                var page = 0;
                var countCollection;
                var index = 1;
                var since_id = 0;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action:'getCountCollection',shop:shop,type:type},
                    dataType: 'json'
                }).done(function (result) { 
                    countCollection = result;
                    page = Math.ceil(countCollection/250);
                    initGetAllCollection();
                })   
                function initGetAllCollection(){ 
                    if(index <= page){
                        $.ajax({
                            url : 'services_v2.php',
                            type: 'GET',
                            data: {action:'getCollectionPerPage',since_id:since_id,shop:shop,type:type},
                            dataType: 'json'
                        }).done(function (response) {     
                            self.allCollection =  self.allCollection.concat(response);  
                            self.allCollection.sort(); 
                            if(typeof response[response.length - 1]['id'] != "undefined"){
                                index = index+1;
                                since_id = response[response.length - 1]['id']; 
                                initGetAllCollection(); 
                            }  
                            if(type == "custom" && response.length < 250) {
                                self.getCountCollection("smart");
                            }
                         })  
                    } 
                } 
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
            changeFillterCollection:function(){
                // filter product by collection id
                let self = this;    
                if(self.selectCollection != null && self.selectCollection.length != 0){
                    self.fillterCollectionOld = self.selectCollection.id;
                    self.getProductByCollection(self.fillterCollectionOld);
                }else{ 
                    self.fillterCollectionOld = null; 
                    self.getAllProduct();
                }
            },
            nameWithLang ({title}) {
                // track by name product (multi select)
                return `${title}`;
            },
        
            getAllCollection: function(){
                // get all collection from shopify 
                let self = this;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action:'getAllCollection',shop:shop},
                    dataType: 'json'
                }).done(function (response) { 
                    self.allCollection = response;    
                })  
            },
            otAddOffer: function () {
                // add tierd in content_rule
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
            otTrashOffer: function (index) {
                // trash tierd in content_rule
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
            getProductByCollection(id){
                // get product by collection id
                let self = this;
                self.showLoadingProduct = true;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action:'getProductByCollection',shop:shop,collectionID:id},
                    dataType: 'json'
                }).done(function (result) {
                    self.items = result;
                    self.productHasRule = [];
                    for(let i = 0; i < result.length ;i++){
                        if(result[i]['hasrule'] === true){ 
                            self.productHasRule.push(result[i]);
                        } 
                    } 
                    self.showLoadingProduct = false;
                
                })
            },
            getvariantByProductIDCheckBox(id,title,$event){ 
                // get variant by product id
                let self = this;    
                // check product da ton tai trong mang chua
                let checkExisProduct = self.drafCheck.indexOf(id); 
                if($event != null){
                    self.drafCheck.push($event); 
                } else{
                    self.checkAll = false;
                    self.drafCheck.splice(checkExisProduct,1)
                } 
                if(self.drafCheck.length > 1){
                    self.showModalAddMultiRuleProduct = true;
                }else if(self.drafCheck.length == 1){
                    self.showModalAddMultiRuleProduct = false ;
                    self.fillterProduct = id;
                    for(let i=0; i< self.items.length; i++){
                        if(self.items[i]['id'] == self.drafCheck[0]){
                            self.titleProductFilter = self.items[i]['title'];
                        }
                    } 
                } else{
                    self.fillterProduct = false;
                    self.showModalAddMultiRuleProduct = false;
                } 
            },
            getvariantByProductID(id,title){
                // get variant by product id
                let self = this; 
                self.allProductChoosen = [] ;
                self.allProductChoosen.push(id); 
                let checkExisProduct = self.drafCheck.indexOf(id);
                self.drafCheck = [];
                self.drafCheck.push(id); 
                self.titleProductFilter =  title;
                self.fillterProduct = id ;
                self.showModalAddMultiRuleProduct = false; 
            },
            getAllProduct: function(){
                // get all product shopify queue
                // lay tong so product de chia so lan lay
                let self = this;
                self.allProduct = [];
                self.items = [];
                self.productHasRule = [];
                var page;
                var countProduct;
                var index = 1;
                var since_id = 0;
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action:'getCountProduct',shop:shop},
                    dataType: 'json'
                }).done(function (result) { 
                    countProduct = result;
                    page = Math.ceil(countProduct/150);
                    initGetAllProduct();
                })   
                function initGetAllProduct(){ 
                    console.log("index",index);
                    console.log("page",page);
                    console.log("since_id",since_id);
                    if(index <= page){
                        $.ajax({
                            url : 'services_v2.php',
                            type: 'GET',
                            data: {action:'getProductPerPage',since_id:since_id,shop:shop},
                            dataType: 'json'
                        }).done(function (response) {     
                            self.allProduct =  self.allProduct.concat(response); 
                            self.$emit("get-products",response);
                            self.items =  self.items.concat(response);
                            self.items.sort();
                            self.allProduct.sort(); 
                            self.totalRows = self.items.length;
                            for(let i = 0; i < response.length ;i++){
                                if(response[i]['hasrule'] === true){ 
                                    self.productHasRule.push(response[i]);
                                } 
                            }  
                            if(response.length != 0){
                                if(typeof response[response.length - 1]['id'] != "undefined"){
                                    index = index+1;
                                    since_id = response[response.length - 1]['id'];
                                    initGetAllProduct(); 
                                } 
                            }  
                            console.log("response.length",response.length)
                            if(response.length == 0 && index <= page){
                                console.log("retry"); 
                                initGetAllProduct(); 
                            }
                            self.showLoadingProduct = false; 
                         })  
                    }else{
                     }
                } 
            },  
            editRuleProduct:function(id,title){
                let self = this ;
                self.checkAll = [];
                self.allProductChoosen = [];
                self.allProductChoosen.push(id);
                self.checkAll.push(id);
                self.titleProductChoose = title; 
                $.ajax({
                    url : 'services_v2.php',
                    type: 'GET',
                    data: {action: 'getRuleByProductID',shop:shop,idProduct :id},
                    dataType: 'json'
                }).done(function (result) {
                    self.showModalCreateRuleForProduct = true;
                    self.groups = result.content_rule ;
                    self.start_date = result.start_date;
                    self.end_date = result.end_date;
                    self.idProductEdit = result.product_id  ;
                    self.ruleForCustomerTag = result.ruleForCustomerTag  ;
                }).fail((error) => {
                    ShopifyApp.flashError("get rule by product id error!"); 
                }); 
            },
            updateRuleProduct:function(){
                let self = this ;
                // 0: save, 1: update
                let valid = self.validBeforeSave(1);
                if(valid == true){
                    $.ajax({
                        url : 'services_v2.php',
                        type: 'GET',
                        data: {
                            action: 'updateRuleProduct',
                            shop:shop,
                            idProduct :self.idProductEdit,
                            groups:self.groups,
                            start_date:self.start_date,
                            end_date:self.end_date,
                            ruleForCustomerTag:self.ruleForCustomerTag, 
                        },
                        dataType: 'json'
                    }).done(function (result) { 
                        self.showModalCreateRuleForProduct = false; 
                        self.changeFillterCollection();  
                        self.idProductEdit = null;
                        self.status =  '0';
                        self.checkAll = [];
                        self.allProductChoosen = [];
                        ShopifyApp.flashNotice("Update rule successfully!"); 
                    }).fail((error) => {
                        ShopifyApp.flashError("Update rule for product error!");  
                    }) ; 
                }else{
                    ShopifyApp.flashError(valid);  
                }
            },
            deleteRuleProduct:function(id,title,content_rule){
                let self = this ;
                let message = "Rule of "+title+":  ";
                for(let i = 0; i < content_rule.length; i++){
                    message += "Buy " + content_rule[i]["number"] + " discount "; 
                    if(content_rule[i]["discountType"] == "percent"){
                        message += content_rule[i]["price"] + "% " ;
                    }else{
                        message += content_rule[i]["price"] + "$ ";
                    }
                }
                message += ". The campaign will be stop immediately.";
                ShopifyApp.Modal.confirm({ 
                    title   : "Please confirm, do you want to delete this rule",
                    message : message,
                    okButton: "Confirm Remove",
                    cancelButton: "Cancel",
                    style   : "danger"
                }, function(res){
                    if(res){
                        $.ajax({
                            url : 'services_v2.php',
                            type: 'GET',
                            data: {action: 'deleteRuleProduct',shop:shop,idProduct :id},
                            dataType: 'json'
                        }).done(function (result) { 
                            self.changeFillterCollection();  
                            self.status =  '0';
                            ShopifyApp.flashNotice("Delete offer successfully!");
                        }).fail((error) => {
                            ShopifyApp.flashError("Delete offer error!"); 
                        }); 
                    } 
                }); 
            },
            fillterHasRule:function(){
                let self = this ;
                if(self.status === '1'){
                    self.items = self.allProduct; 
                }else{
                    self.items = self.productHasRule;
                }
            }, 
            addRuleProduct:function(id=null){
                var self = this  ;
                self.idProductEdit = null;
                self.checkAll = [];
                self.allProductChoosen = [];
                if(id != null){
                    self.allProductChoosen.push(id);
                }
                if(self.allProductChoosen.length != 0){
                    self.groups = [{price: null, number: null, discountType: 'percent'}];
                    self.start_date = null;
                    self.end_date = null;
                    self.showModalCreateRuleForProduct = true;
                }else{
                    ShopifyApp.flashError("Please select at least 1 product to continue !"); 
                } 
            }, 
            selectAllProduct(){
                var self = this;
                self.checkAll = !self.checkAll ; 
                self.allProductChoosen = [];
                self.drafCheck = [];
                let start = (self.currentPage -1) * self.perPage;
                if(self.checkAll == true){ 
                    for(var i = start; i <  (start+self.perPage) ; i++){ 
                        if(self.resultFilterItems.length > i){
                            self.allProductChoosen.push(self.resultFilterItems[i]['id']);
                            self.drafCheck.push(self.resultFilterItems[i]['id']);
                        }
                        self.showModalAddMultiRuleProduct = true;
                    }
                }
                for(let i = 0;i < self.items.length;i++){
                        if(self.drafCheck.indexOf(self.items[i]['id']) != -1){ 
                            self.items[i]['_rowVariant'] = 'success';
                        }else{
                            self.items[i]['_rowVariant'] = '';
                    }
                }
            }, 
            onFiltered (filteredItems) {
                let self = this;
                // Trigger pagination to update the number of buttons/pages due to filtering
                self.totalRows = filteredItems.length;
                self.resultFilterItems = filteredItems;
                self.currentPage = 1;
            }, 
            saveRuleForProduct: function(){
                var self = this  ;
                // 0 : save, 1: update
                let valid = self.validBeforeSave(0);
                console.log(self.ruleForCustomerTag)
                if(valid == true){ 
                    $.ajax({
                        url : 'services_v2.php',
                        type: 'POST',
                        data: {
                            action:'saveRuleForProduct',
                            shop:shop,
                            groups:self.groups,
                            allProductChoosen:self.allProductChoosen,
                            start_date:self.start_date,
                            end_date:self.end_date,
                            ruleForCustomerTag:self.ruleForCustomerTag,
                        },
                        dataType: 'json'
                    }).done(function (result) {
                        ShopifyApp.flashNotice("Update offers successfully!"); 
                        self.status =  '0';
                        self.changeFillterCollection();  
                        self.showModalCreateRuleForProduct = false;
                        self.checkAll = [];
                        self.allProductChoosen = [];
                        self.fillterProduct = null;
                    }).fail((error) => {
                        console.log("fail");
                    });
                }else{
                    ShopifyApp.flashError(valid);  
                }
            }, 
            validBeforeSave(typeCheck){
                var self = this  ;
                var valid = true; 
                if(self.groups.length == 0){
                    valid = "Please enter rule(s) value before save rule!";
                }
                let checkDuplicate = 0;
                for(let i =0; i < self.groups.length; i++){
                    if(self.groups[i]['price'] == null || self.groups[i]['number'] == null){   
                        valid = "Please check and enter the valid Quantity and Value of each Price Tier/Level.  !";
                    }
                    if(checkDuplicate == self.groups[i]['number']){
                        valid = "Duplicate number in rule !";
                    }else{
                        checkDuplicate = self.groups[i]['number'];
                    }
                }
                if(typeCheck == 0){
                    if(self.allProductChoosen.length == 0){
                        valid = "Please select at least 1 product to continue !";
                    }
                } 
                if(self.start_date > self.end_date){
                    valid = "Please enter end date < start date !";
                }
                return valid;
            },
        },
        components: {
            Multiselect: window.VueMultiselect.default,
            'variant-rule' : httpVueLoader(`admin/version1/components/variant.vue?v=${window.version}`),
         }
    };
</script>
<style  scoped src="admin/version1/styles/product.css?v=10"> </style>
