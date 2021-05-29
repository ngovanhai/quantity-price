<template>   
    <table class="priceGroupList" :style="{'border':settings.table_border_size+'px solid '+settings.table_border_color,'color':settings.table_text_color,'font-size':settings.table_text_size}" v-show="settings.layout == 'table'" >
        <tbody>
            <tr>
                <th>{{settings.group_table_heading}}</th>
                <th v-show="settings.showColumnLayoutTable == 0" >{{settings.price_table_heading}}</th>  
                <th v-show="settings.showColumnLayoutTable == 1" >{{settings.residual_text}}</th>    
                <th v-show="settings.show_total_amount == true" >{{settings.total_amount_text}}</th>       
            </tr>   
            <tr>
                <td>{{settings.table_text1}} 5 {{settings.table_text}}</td>       
                <td  v-show="settings.showColumnLayoutTable == 0">
                    <div v-if="settings.show_percent == 1">10%</div>
                    <div v-else>€10</div>
                </td>       
                <td v-show="settings.showColumnLayoutTable == 1" >
                    <div v-if="settings.show_percent == 1">90%</div>
                    <div v-else>€90</div>    
                </td>  
                <td v-show="settings.show_total_amount == true" >€450</td>  
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
 