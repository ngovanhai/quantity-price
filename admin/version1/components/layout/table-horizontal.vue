<template>   
    <table class="priceGroupList" :style="{'border-width':settings.table_border_size+'px solid '+settings.table_border_color,'color':settings.table_text_color,'font-size':settings.table_text_size}"  >
        <tbody>
            <tr>
                <td>{{settings.group_table_heading}}</td>
                <td>{{settings.table_text1}} 5 {{settings.table_text}}</td>  
                <td>{{settings.table_text1}} 10 {{settings.table_text}}</td>  
            </tr>   
            <tr v-show="settings.showColumnLayoutTable == 0">
                <td>{{settings.price_table_heading}}</td>
                <td><div v-if="settings.show_percent == 1">10%</div>
                    <div v-else>€10</div></td>  
                <td><div v-if="settings.show_percent == 1">20%</div>
                    <div v-else>€20</div> </td>  
            </tr> 

            <tr v-show="settings.showColumnLayoutTable == 1">
                <td>{{settings.price_table_heading}}</td>
                <td><div v-if="settings.show_percent == 1">90%</div>
                    <div v-else>€90</div></td>  
                <td><div v-if="settings.show_percent == 1">80%</div>
                    <div v-else>€80</div> </td>  
            </tr> 
        
        </tbody>
    </table>   
</template> 
 
  <script> 
 module.exports = {
 
  props: ["settings"],
  data: function() {
    return { 
    };
  },
  mounted: function() {
        var self = this 
  },
  methods : {
     
  },
  components: { 
    
   }
};
</script>
 