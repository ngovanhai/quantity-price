
 new Vue({
    el: '#wp_list_script',
    components: { 
        'manage-store': httpVueLoader(`components/manageStore.vue?v=${window.v}`), 
        'template-email': httpVueLoader(`components/templateEmail.vue?v=${window.v}`), 
        'job-sendemail': httpVueLoader(`components/jobSendEmail.vue?v=${window.v}`), 
        'query-db': httpVueLoader(`components/queryDb.vue?v=${window.v}`), 
      },
    data : function () {
        return {  
          tab:1
        }
    },  
    mounted: function() {            
         
    },  
    methods : {  
        
    }, 
})
 
