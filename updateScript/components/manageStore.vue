<template>
  <div>
    <b-container>
      <!-- User Interface controls -->

      <div class="wp_data" v-show="typeof storeEdit.shop == 'undefined'">
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group label-cols-sm="3" label="Filter" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Enter store name to search information"></b-form-input>
                <b-input-group-append>
                  <b-button :disabled="!filter" @click="filter = ''">Clear</b-button>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col> 
          <b-col md="6" class="my-1">
            <b-form-group label-cols-sm="3" label="Per page" class="mb-0">
              <b-form-select v-model="perPage" :options="pageOptions"></b-form-select>
            </b-form-group>
          </b-col> 
          <b-col md="6" class="my-1">
            <b-form-group
              label="Filter statue store"
              label-cols-sm="3"
              label-align-sm="right"
              label-size="sm"
              label-for="initialSortSelect"
              class="mb-0"
            >
              <b-form-select
                v-model="variableStatusStore"
                @change="filterStatusStore($event)"
                id="initialSortSelect"
                size="sm"
                :options="['All Store','Active','Uninstall','Deactive']"
              ></b-form-select>
            </b-form-group>
          </b-col>
        </b-row>

        <!-- Main table element -->
        <p>Total row: {{fields.length}} </p>
        <b-table
          show-empty
          stacked="md"
          :items="items"
          :fields="fields"
          :current-page="currentPage"
          :per-page="perPage"
          :filter="filter"
          @filtered="onFiltered"
        >
          <template slot="id" slot-scope="row">{{ row.value}}</template>
          <template slot="shop" slot-scope="row">
            <a :href="'https://'+row.value" target="_blank">{{ row.value}}</a>
          </template>

          <template slot="version" slot-scope="row">
            <p v-if="row.value == 1 && row.item.usePriceRule == 0">Draf Order Method</p>
            <p v-if="row.value == 1 && row.item.usePriceRule == 1">Price Rule Method</p>
            <p v-if="row.value == 0">Variant Method</p>
          </template>
          <template slot="installed_date" slot-scope="row">{{ row.value}}</template>
          <template slot="email_shop" slot-scope="row">{{ row.value}}</template>
          <template slot="enableApp" slot-scope="row">
            <p v-if="row.value == 1" class="status enable">Enable</p>
            <p v-else class="status disable">Disable</p>
          </template>
          <template slot="status" slot-scope="row">
            <p
              v-if="row.value === 'uninstall' || row.value === 'Uninstall'"
              class="status uninstall"
            >Uninstall</p>
            <p v-else-if="row.value === 'active'" class="status active">Active</p>
            <p v-else class="status deactive">Deactive</p>
          </template>
          <template slot="note" slot-scope="row">
                <a @click="showNoteStore(row.item.id)" style="cursor: pointer;color: #007bff;" :class="'textNote textNote_'+row.item.id" v-if="row.value != ''">{{row.value}}</a>
                <button @click="showNoteStore(row.item.id)" style="cursor: pointer;color: #007bff;" :class="'button btn textNote textNote_'+row.item.id" v-else>Add note</button>
                <div :class="'contentNote contentNote_'+row.item.id">
                    <textarea   cols="30" rows="5" v-model="row.value"> </textarea>
                    <b-button class="saveNote" @click="saveNoteStore(row.item.shop,row.item.id)" variant="dark">Save</b-button>
                </div>
            </template>
          <template slot="actions" slot-scope="row">
            <a
              size="sm"
              class="mr-1 btn btn-xanh"
              @click="sendEmail(row.item, row.index, $event.target)"
            >
              <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
            </a>
            <a
              size="sm"
              class="mr-1 btn btn-xanh"
              @click="addScriptToTheme(row.item, row.index, $event.target)"
            >
              <i class="fa fa-plus" aria-hidden="true"></i> 
            </a>
          </template> 
          <template slot="row-details" slot-scope="row">
            <b-card>
              <ul>
                <li v-for="(value, key) in row.item" :key="key">{{ key }}: {{ value }}</li>
              </ul>
            </b-card>
          </template>
        </b-table> 
        <b-row>
          <b-col md="6" class="my-1">
            <b-pagination
              v-model="currentPage"
              :total-rows="totalRows"
              :per-page="perPage"
              class="my-0"
            ></b-pagination>
          </b-col>
        </b-row>
      </div>
      <div v-show="typeof storeEdit.shop != 'undefined'">
        <h3>Edit Store: {{storeEdit.shop}}</h3>
        <div class="infoDataStore">
          <div class="row">
            <div class="col-md-12">
              <b-form-group label="Enable App">
                <b-form-radio-group
                  class="btnradios"
                  buttons
                  v-model="settings.enableApp"
                  :options="optionsEnableApp"
                  :name="'status'"
                  @change="changeEnableApp()"
                />
              </b-form-group>
            </div>
            <div class="col-md-12">
              <b-form-group label="Enable AjaxCart">
                <b-form-radio-group
                  class="btnradios"
                  buttons
                  v-model="settings.useAjaxCart"
                  :options="optionsUseAjaxcart"
                  :name="'status'"
                  @change="changeEnableApp()"
                />
              </b-form-group>
            </div>

            <div class="col-md-12">
              <b-form-group label="Tag for Customer">
                <b-form-radio-group
                  class="btnradios"
                  buttons
                  v-model="settings.type_tag_for_customer"
                  :options="optionsUse"
                  :name="'status'"
                  @change="changeEnableApp()"
                />
              </b-form-group>
            </div>

            <div class="col-md-12">
              <a @click="showDemoRuleLimit()">Show/Hide Variant ID Limit</a>
              <ul v-show="demoRuleLimit.length > 0">
                <li v-for="(element,key) in demoRuleLimit" :key="key">{{element.variant_id}}</li>
              </ul>
            </div>
            <div class="col-md-12">
              <label class="form-check-label" for="customCss">Custom Css</label>
              <b-form-textarea
                id="textarea1"
                v-model="settings.customCss"
                placeholder="Custom"
                :rows="4"
                :max-rows="8"
              ></b-form-textarea>
            </div>
          </div>
        </div>
        <div class="actionButton">
          <a class="button btn btncancleContentTemplateEmail" @click="cancelEdit()">Cancle</a>
          <a class="button btn btnSaveContentTemplateEmail btn-xanh" @click="saveSettings()">
            <i class="fa fa-floppy-o" aria-hidden="true"></i> Save
          </a>
        </div>
      </div> 

      
      <ul class="totalUser">
        <li class="totalUsing">
          <!-- listDisableShop -->
          <a>Install Yesterday</a>
          <i class="fa fa-user" aria-hidden="true"></i>
          {{storeListInstallYesterday.length}}/{{listAllShop.length}}
          <b-progress
            :value="storeListInstallYesterday.length"
            :max="listAllShop.length"
            class="mb-3"
          ></b-progress>
        </li>
        <li class="totalUninstall">
          <a>Uninstall App</a>
          <i class="fa fa-user-times" aria-hidden="true"></i>
          {{storeUninstall.length}}/{{listAllShop.length}}
          <b-progress :value="storeUninstall.length" :max="listAllShop.length" class="mb-3"></b-progress>
        </li>
        <li class="totalDeactive">
          <a>Deactive App</a>
          <i class="fa fa-user-o" aria-hidden="true"></i>
          {{storeDeactive.length}}/{{listAllShop.length}}
          <b-progress :value="storeDeactive.length" :max="listAllShop.length" class="mb-3"></b-progress>
        </li>
        <li class="totalDisableApp">
          <a>Disable App</a>
          <i class="fa fa-frown-o" aria-hidden="true"></i>
          {{listDisableShop.length}}/{{listAllShop.length}}
          <b-progress :value="listDisableShop.length" :max="listAllShop.length" class="mb-3"></b-progress>
        </li>
      </ul>

      <!-- <div class="wrapperChar">
        <h3>Merchant growth</h3>
        {{urlChart}}
        <img :src="urlChart" />
      </div> -->
    </b-container>
  </div>
</template> 
 
  <script>
module.exports = {
  props: [],
  data: function() {
    return {
      items: [],
      totalRows: 1,
      currentPage: 1,
      perPage: 5,
      settings: [],
      pageOptions: [5, 10, 15],
      sortBy: null,
      sortDesc: false,
      sortDirection: "asc",
      filter: null,
      storeEdit: [],
      urlChart:"",
      optionsEnableApp: [
        { text: "Enable App", value: 1 },
        { text: "Disable App", value: 0 }
      ],
      optionsUseAjaxcart: [
        { text: "Enable AjaxCart", value: 1 },
        { text: "Disable AjaxCart", value: 0 }
      ],
       optionsUse: [
        { text: "Enable", value: 1 },
        { text: "Disable", value: 0 }
      ],
      infoModal: {
        id: "info-modal",
        title: "",
        content: ""
      },
      fields: [
        { key: "id", label: "ID", class: "text-center" },
        { key: "shop", label: "Shop", sortable: true, sortDirection: "desc" },
        {
          key: "version",
          label: "Discount Methods",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "date_installed",
          label: "Installed date",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        { key: 'note', label: 'note', sortable: true  },
        {
          key: "email_shop",
          label: "Email",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "phone",
          label: "Phone",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "country",
          label: "Country",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "enableApp",
          label: "Enable App",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "status",
          label: "Status",
          sortable: true,
          class: "text-center ",
          sortDirection: "desc"
        },
        {
          key: "actions",
          label: "Actions",
          sortable: true,
          class: "text-center"
        }
      ],
      variableFilterStatusApp: "All",
      variableStatusStore: "All Store",
      listDisableShop: [],
      listEnableShop: [],
      listAllShop: [],
      storeActive: [],
      storeUninstall: [],
      storeDeactive: [],
      storeListInstallYesterday: [],
      tab: 1,
      demoRuleLimit: []
    };
  },
  mounted: function() {
    var self = this;
    this.totalRows = this.items.length;
    this.getListShop();
    this.getDataChar();
  },
  components: {},
  methods: {
    cancelEdit() {
      this.storeEdit = [];
    },
    saveNoteStore: function(shop,id){
        var seft = this;
        $.ajax({
            url: "process.php",
            data: {
                action: "saveNoteStore",
                shop: shop,
                note: $(".contentNote_"+id+ ' textarea').val(),
            },
            dataType: "JSON",
            type: "GET", 
        }).done((data) => { 
            $(".contentNote_"+id).hide();
            $(".textNote_"+id).show();
            seft.getListShop();
        })
    },
    showNoteStore: function(id){
        console.log($(".contentNote_"+id));
        $(".contentNote_"+id).show();
        $(".textNote_"+id).hide();
    },
    getDataChar(){ 
        let self = this;
        $.ajax({
            url: "process.php",
            type: "GET",
            data: {
                action: "getDataChar"
            },
            dataType: "json"
        }).done(function(result) {
            console.log("result",result)
            self.urlChart = "https://quickchart.io/chart?c={type:'bar',data:{labels:"+result.data.labels+",datasets:[{label:'Install',data:"+result.data.datasets[0]+"},{label:'Uninstall',data:"+result.data.datasets[0]+"}]}}"; 
        }).fail(error => {
            
        });
    },
    showDemoRuleLimit() {
      let self = this;
      if (self.demoRuleLimit.length == 0) {
        self.getDemoRuleLimitPurchase();
      } else {
        self.demoRuleLimit = [];
      }
    },
    changeEnableApp() {
      let self = this;
      $.ajax({
        url: "process.php",
        type: "GET",
        data: {
          action: "changeEnableApp",
          shop: self.settings.shop,
          enableApp: self.settings.enableApp
        },
        dataType: "json"
      })
        .done(function(result) {
          alert("Change settings successfully ");
          self.storeEdit = [];
        })
        .fail(error => {
          console.log("fail");
        });
    },
    getDemoRuleLimitPurchase() {
      let self = this;
      $.ajax({
        url: "process.php",
        type: "GET",
        data: { action: "getDemoRuleLimitPurchase", shop: self.settings.shop },
        dataType: "json"
      })
        .done(function(result) {
          self.demoRuleLimit = result;
        })
        .fail(error => {
          console.log("fail");
        });
    },
    saveSettings() {
      let self = this;
      $.ajax({
        url: "process.php",
        type: "POST",
        data: {
          action: "saveSettings",
          shop: self.settings.shop,
          settings: self.settings
        },
        dataType: "json"
      })
        .done(function(result) {
          alert("Save settings successfully ");
          self.storeEdit = [];
        })
        .fail(error => {
          console.log("fail");
        });
    },
    changeVersion(item) {
      let self = this;
      shop = item.shop;
      $.ajax({
        url: "process.php",
        type: "GET",
        data: { action: "changeVersion", shop: shop },
        dataType: "json"
      })
        .done(function(result) {
          self.items = result;
        })
        .fail(error => {
          console.log("fail");
        });
    },
    filterStatusApp(value) {
      if (value == "All") {
        this.items = this.listAllShop;
      }
      if (value == "Disable App") {
        this.items = this.listDisableShop;
      }
      if (value == "Enable App") {
        this.items = this.listEnableShop;
      }
    },
    filterStatusStore(value) {
      if (value == "All Store") {
        this.items = this.listAllShop;
      }
      if (value == "Active") {
        this.items = this.storeActive;
      }
      if (value == "Uninstall") {
        this.items = this.storeUninstall;
      }
      if (value == "Deactive") {
        this.items = this.storeDeactive;
      }
    },
    onFiltered(filteredItems) {
      // Trigger pagination to update the number of buttons/pages due to filtering
      this.totalRows = filteredItems.length;
      this.currentPage = 1;
    },
    sendEmail(item, index, button) {
      var self = this;
      $.ajax({
        url: "process.php",
        type: "GET",
        data: { action: "getSettings", shop: item.shop },
        dataType: "json"
      })
        .done(function(result) {
          console.log("result", result);
          self.settings = result;
          self.storeEdit = item;
        })
        .fail(error => {
          console.log("fail");
        });
    },
    resetInfoModal() {
      this.infoModal.title = "";
      this.infoModal.content = "";
    },
    getListShop: function() {
      let self = this;
      $.ajax({
        url: "process.php",
        type: "GET",
        data: { action: "getListShop" },
        dataType: "json"
      })
        .done(function(result) {
          self.items = result;
          self.listAllShop = result;
          for (let i = 0; i < result.length; i++) {
            if (result[i]["enableApp"] == "1") {
              self.listEnableShop.push(result[i]);
            } else {
              self.listDisableShop.push(result[i]);
            }
            if (window.today < result[i]["date_installed"]) {
              self.storeListInstallYesterday.push(result[i]);
            }
            if (result[i]["status"] == "active") {
              self.storeActive.push(result[i]);
            } else if (result[i]["status"] == "uninstall") {
              self.storeUninstall.push(result[i]);
            } else {
              self.storeDeactive.push(result[i]);
            }
          }
        })
        .fail(error => {
          console.log("fail");
        });
    },
    getAllShopInstallYesterday() {}
  }
};
</script>
<style>
.wrapperChar{
position: fixed;
    left: 0px;
    width: 300px;
    text-align: center;
    bottom: 10%;
}
.wrapperChar h3 {
    font-size: 13px;
    text-transform: uppercase;
    color: #8e8e8e;
}
.wrapperChar img{
    width:100%;
}

.contentNote{
    display:none;
}
.saveNote{
    display: block;
    margin-top: 10px;
    font-size: 15px;
    padding: 5px 5px;
    width: 100%;
}
.button.btn.textNote{
        display: block;
    margin-top: 10px;
    font-size: 15px;
    padding: 5px 5px;
    width: 100%;
    color: black !important;
}
</style>