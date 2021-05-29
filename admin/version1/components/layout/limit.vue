<template>   
    <div>
        <h3 :style="{'color':settings.table_heading_color}" v-show="settings.show_heading == true" class="limit-header">{{settings.limits_heading}}</h3> 
        <table class="productLimits" :style="{'border':settings.table_border_size+'px solid '+settings.table_border_color,'color':settings.table_text_color,'font-size':settings.table_text_size}" >
            <tbody>
                <tr>
                    <th>{{settings.min_table_heading}}</th>
                    <th>{{settings.max_table_heading}}</th>           
                </tr>   
                <tr>        
                    <td>2</td>      
                    <td>4</td>  
                </tr> 
            </tbody> 
        </table> 
    </div>
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
 