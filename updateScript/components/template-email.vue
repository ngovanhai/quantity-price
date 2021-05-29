<template>   
    <div> 
        <b-table show-empty stacked="md" :items="items" :fields="fields" :current-page="currentPage" :per-page="perPage"  >
                 
        </b-table>
    </div>  
</template> 
 
  <script> 
 module.exports = {
 
  props: [],
  data: function() {
    return { 
        listTemplate:[],
        totalRows: 1,
        currentPage: 1,
        perPage: 5,
        pageOptions: [5, 10, 15],
        sortBy: null,
        sortDesc: false,
        sortDirection: 'asc',
        filter: null, 
        fields : [
            { key: 'id', label: 'ID','class': 'text-center' },
            { key: 'type', label: 'Type Email', sortable: true, sortDirection: 'desc' },
            { key: 'content', label: 'Email Content',  sortable: true,   'class': 'text-center ', sortDirection: 'desc' },
            { key: 'title', label: 'Email Title', sortable: true,   'class': 'text-center ', sortDirection: 'desc' }, 
        ],
    };
  },
  mounted: function() { 
        this.getListTemplateEmail();
  },
  components : {
     
  },
  methods: { 
        getListTemplateEmail: function(){
            $.ajax({
                url : 'process.php',
                type: 'GET',
                data: {action:'getListTemplateEmail',shop:shop},
                dataType: 'json'
            }).done(function (result) {
                self.listTemplate = result;  
            }).fail((error) => {
                console.log("fail getListTemplateEmail")
            });
        }
   }
};
</script>
<style>
     
</style>