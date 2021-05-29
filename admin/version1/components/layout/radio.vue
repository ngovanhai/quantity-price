<template>   
     
   <div class="quantity-radio">
        <label><input type="radio" class="ot_quantity_layout_radio" name="quantity_radio" value="2" checked="">
            <span>{{settings.table_text}} 2 </span>    
            <span class="formatPrice money">{{settings.table_text1}}  €3,60</span> 
        </label><br><label><input type="radio" class="ot_quantity_layout_radio" name="quantity_radio" value="3">
            <span>{{settings.table_text}}  3</span>    
            <span class="formatPrice money">{{settings.table_text1}} €3,15</span> 
        </label><br><label><input type="radio" class="ot_quantity_layout_radio" name="quantity_radio" value="4">
            <span>{{settings.table_text}} 4</span>    
            <span class="formatPrice money">{{settings.table_text1}} €2,70</span> 
        </label><br> 
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
