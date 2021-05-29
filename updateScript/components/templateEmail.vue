<template>
  <div>
    <b-table
      show-empty
      stacked="md"
      :items="listTemplate"
      :fields="fields"
      :current-page="currentPage"
      :per-page="perPage"
      v-show="showEditEmail == false"
    >
      <template slot="type" slot-scope="row">
        <p v-if="row.value == 1">Welcome email - when customer installed succesfully</p>
        <p v-if="row.value == 2">Warming email - app works well after installation</p>
        <p v-if="row.value == 3">Follow up / Performance emails - customer uses the app well</p>
        <p v-if="row.value == 4">Follow up / Performance emails - disabled confirmation</p>
        <p v-if="row.value == 5">Follow up / Performance emails - disable notification</p>
        <p v-if="row.value == 6">Uninstallation - same day as install</p>
        <p v-if="row.value == 7">Uninstallation - 2-3days from install</p>
        <p v-if="row.value == 8">Uninstallation - almost end of free trial ( sau khi trial 30 )</p>
        <p v-if="row.value == 9">Uninstallation - >1month (dùng được 1 tháng thì gỡ)</p>
        <p v-if="row.value == 10">Uninstallation - >2 months</p>
        <p v-if="row.value == 11">Uninstallation - >3months</p>
        <p v-if="row.value == 12">Uninstallation - other cases</p>
      </template>
      <template slot="actions" slot-scope="row">
        <a
          size="sm"
          class="mr-1 btn btn-xanh"
          @click="editEmailTemplate(row.item, row.index, $event.target)"
        >
          <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
        </a>
      </template>
    </b-table>
    <div v-show="showEditEmail == true">
      <b-card>
        <div class="form formSendEmail">
          <label>Title Email</label>
          <input
            class="form-control"
            type="text"
            placeholder="Title Email"
            v-model="emailEdit.title"
          />
          <label>Content Email</label>
          <textarea class="form-control editorBodyTemplate" name="editorBodyTemplate"></textarea>
          <div class="actionButton">
            <a
              class="button btn btncancleContentTemplateEmail"
              @click="cancleContentEmailTemplate()"
            >Cancle</a>
            <a
              class="button btn btnSaveContentTemplateEmail btn-xanh"
              @click="saveContentEmailTemplate()"
            ><i class="fa fa-floppy-o" aria-hidden="true"></i> Save</a>
          </div>
        </div>
      </b-card>
          <ul class="note">
        <li>Note</li>
        <li>{store_name}: Store name</li>
        <li>{data_chart} : Insert chart</li>
        <li>{data_rule}:  Insert orders that applied rules and most used rules </li>
    </ul>
    </div>

  </div>
</template> 
 
  <script>
module.exports = {
  props: [],
  data: function() {
    return {
      listTemplate: [],
      totalRows: 1,
      currentPage: 1,
      perPage: 50,
      pageOptions: [50, 100],
      sortBy: null,
      sortDesc: false,
      sortDirection: "asc",
      filter: null,
      showEditEmail: false,
      emailEdit: {
        title: "",
        content: ""
      },
      fields: [
        { key: "id", label: "ID", class: "text-center" },
        {
          key: "type",
          label: "Type Email",
          sortable: true,
          sortDirection: "desc"
        },
        {
          key: "title",
          label: "Email Title",
          sortable: true,
          class: " ",
          sortDirection: "desc"
        },
        { key: "actions", label: "Actions", sortable: true, class: " " }
      ]
    };
  },
  mounted: function() { 
    this.getListTemplateEmail();
    this.getFormCheckEditor();
  },
  components: {},
  methods: {
    getFormCheckEditor: function() {
      var seft = this;
      CKEDITOR.replace("editorBodyTemplate", {
        height: "500px",
        filebrowserBrowseUrl: "../ckfinder/ckfinder.html",
        filebrowserUploadUrl:
          "../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files"
      });
    }, 
    getListTemplateEmail: function() {
      var sefl = this;
      console.log("getListTemplateEmail");
      $.ajax({
        url: "process.php",
        type: "GET",
        data: { action: "getListTemplateEmail" },
        dataType: "json"
      })
        .done(function(result) {
          console.log("result", result);
          sefl.listTemplate = result;
        })
        .fail(error => {
          console.log("fail getListTemplateEmail");
        });
    },
    editEmailTemplate(item, index, button) {
      this.showEditEmail = true;
      CKEDITOR.instances["editorBodyTemplate"].setData(item.content);
      this.emailEdit = item;
      console.log(this.emailEdit);
    },
    cancleContentEmailTemplate: function() {
      this.showEditEmail = false;
    },
    saveContentEmailTemplate: function() {
      var sefl = this;
      sefl.emailEdit.content = CKEDITOR.instances[
        "editorBodyTemplate"
      ].getData();
      console.log("emailEdit", sefl.emailEdit);
      $.ajax({
        url: "process.php",
        type: "POST",
        data: { action: "saveContentEmailTemplate", emailEdit: sefl.emailEdit },
        dataType: "json"
      })
        .done(function(result) {
          sefl.getListTemplateEmail = result;
          sefl.showEditEmail = false;
        })
        .fail(error => {
          console.log("fail getListTemplateEmail");
        });
    }
  }
};
</script>
<style>
.actionButton {
    position: fixed;
    bottom: 0;
    background: white;
    box-shadow: 1px 1px black;
    width: 100%;
    left: 0;
    text-align: center;
}
.btncancleContentTemplateEmail{ 
    background: #00000038 !important;
}
.actionButton a {
    color: #ffff !important;
    padding: 11px 18px !important;
    font-size: 15px !important;
}
.note {
    position: fixed;
    left: 0;
    bottom: 20%;
    width: 300px;
    margin-left: 5px;
    border: 1px solid #7d7d7d47;
    margin-left: 5px;
    box-shadow: 2px 7px 8px #88888836;
    padding-left: 0px;
}
.note li{
    list-style: none;
    padding: 5px 5px;
}
.note li:first-child{
    text-align: center;
    font-weight: bold; 
    border-bottom: 1px solid #c1c1c1;
    padding: 5px 0px;
}
</style> 