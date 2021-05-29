<template>   
    <div> 
        <table class="priceGroupList layoutAddToCart" :style="{'border':settings.table_border_size+'px solid '+settings.table_border_color,'color':settings.table_text_color,'font-size':settings.table_text_size}">
            <tbody> 
                <tr>
                    <td>{{settings.table_text1}} 2 {{settings.table_text}} 
                        <span v-if="settings.show_percent == 1">10% {{settings.textAfter}}</span>
                        <span v-else>€10 {{settings.table_text}}</span>
                    </td>  
                    <td><button class="btn btn-default">Add to cart</button></td>  
                </tr>    
                <tr>
                    <td>{{settings.table_text1}} 4 {{settings.table_text}} 
                        <span v-if="settings.show_percent == 1">20% {{settings.textAfter}}</span>
                        <span v-else>€20 {{settings.table_text}}</span>
                    </td>     
                    <td><button class="btn btn-default">Add to cart</button></td>  
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
<style>
    .layoutAddToCart .btn-default{
        padding: 7px 10px;
        font-size: 12px;
    } 
    .layoutAddToCart tr td,.productLimits tr td,.productLimits tr th,.priceGroupList tr td,.priceGroupList tr th{
        border: 1px solid #d1d2d4  !important;
        font-size: 15px !important;
    }
    .layoutAddToCart,.priceGroupList{
        margin-bottom: 15px;
    }
</style>