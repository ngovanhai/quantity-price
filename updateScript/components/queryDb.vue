<template>
  <div>
    <b-container>  
        <b-form-textarea
            id="textarea1"
            v-model="queryDB"
            placeholder="query"
            :rows="4"
            :max-rows="8"
        ></b-form-textarea>
        <a class="button btn btnSaveExecQuery btn-xanh" @click="execQuery('get')">
            <i class="fa fa-floppy-o" aria-hidden="true"></i> GET
        </a>
        <a class="button btn btnSaveExecQuery btn-xanh" @click="execQuery('update')">
            <i class="fa fa-floppy-o" aria-hidden="true"></i> Update
        </a>
        
        <div class="wpResult">
            <p v-if="typeof resultQuery == 'array'">Total result : {{resultQuery.length}}</p>
            <div v-else>{{resultQuery}}</div>
            <table class="table"  v-if="typeof resultQuery == 'array'">
                <tr v-for="item in resultQuery" :key="item.id">
                   <td>{{item}}</td>
                </tr>
            </table> 
        </div> 
    </b-container>
  </div>
</template> 
 
<script>
module.exports = {
    props: [],
    data: function() {
        return {
            queryDB: "",
            resultQuery:[]
        };
    },
    mounted: function() { 
    },
    components: {},
    methods: {
        execQuery: function(typeQuery){
            var self = this;
            $.ajax({
                url: "process.php",
                type: "GET",
                data: {
                    action: "execQuery",
                    queryDB: this.queryDB,
                    typeQuery: typeQuery
                },
                dataType: "json"
            }).done(function(result) {
                self.resultQuery = result; 
            }).fail(error => {
                self.resultQuery = error;  
            });
        }
    }
};
</script>
<style> 
.btnSaveExecQuery{
    margin-top: 10px;
}
.wpResult{
    margin-top: 15px;
}
</style>