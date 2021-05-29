<template>
  <div>
    <b-table
      show-empty
      stacked="md"
      :items="listJobEmail"
      :fields="fields"
      :current-page="currentPage"
      :per-page="perPage"
    >
       <template slot="actions" slot-scope="row">
        <a
          size="sm"
          class="mr-1 btn btn-xanh"
          @click="deleteJob(row.item, row.index, $event.target)"
        >
         <i class="fa fa-trash-o" aria-hidden="true"></i> 
        </a>
      </template>
     
    </b-table>
      

  </div>
</template> 
 
  <script>
module.exports = {
  props: [],
  data: function() {
    return {
      listJobEmail: [],
      totalRows: 1,
      currentPage: 1,
      perPage: 50,
      pageOptions: [50, 100],
      sortBy: null,
      sortDesc: false,
      sortDirection: "asc",
      filter: null,  
      fields: [
        { key: "id", label: "ID", class: "text-center" },
        {
          key: "shop",
          label: "shop",
          sortable: true,
          sortDirection: "desc"
        },
        {
          key: "start_date",
          label: "start_date",
          sortable: true,
          sortDirection: "desc"
        },
        {
          key: "end_date",
          label: "end_date",
          sortable: true,
          sortDirection: "desc"
        }
          ,
        {
          key: "status",
          label: "status",
          sortable: true,
          sortDirection: "desc"
        }
        ,
        {
          key: "email_type",
          label: "email_type",
          sortable: true,
          sortDirection: "desc"
        }
        ,
        {
          key: "start_date_report",
          label: "start_date_report",
          sortable: true,
          sortDirection: "desc"
        }
         ,
        {
          key: "end_date_report",
          label: "end_date_report",
          sortable: true,
          sortDirection: "desc"
        },
        { key: "actions", label: "Actions", sortable: true, class: " " }
      ]
    };
  },
  mounted: function() { 
    this.getListJobEmail(); 
  },
  components: {},
  methods: { 
    deleteJob:function(item, index, event){
        var sefl = this;
        console.log("item",item)
        $.ajax({
            url: "process.php",
            type: "GET",
            data: { action: "deleteJob",id:item.id,shop:item.shop },
            dataType: "json"
        })
        .done(function(result) { 
            sefl.getListJobEmail();
        })
        .fail(error => {
          console.log("fail getListTemplateEmail");
        });
    },
    getListJobEmail: function() {
        var sefl = this;
        $.ajax({
            url: "process.php",
            type: "GET",
            data: { action: "getListJobEmail" },
            dataType: "json"
        })
        .done(function(result) { 
          sefl.listJobEmail = result;
        })
        .fail(error => {
          console.log("fail getListTemplateEmail");
        });
    },
   
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