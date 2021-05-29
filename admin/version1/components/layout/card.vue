<template>   
    <div class="">
       <ul class="layoutCard">
            <li class="listDiscount" :style="{'border':settings.table_border_size+'px solid '+settings.table_border_color}">
                <div class="col-left">{{settings.table_text1}} <span><b>3</b></span> {{settings.table_text}}</div>
                <div class="col-right">
                    <div class="totalAmount">€30</div>
                    <div class="pricePerProduct">€10 {{settings.textAfter}}</div>
                </div>
                <div class="discountPercentProduct">30%</div>
            </li>
            <li class="listDiscount"  :style="{'border':settings.table_border_size+'px solid '+settings.table_border_color}">
                <div class="col-left">{{settings.table_text1}} <span>5</span> {{settings.table_text}}</div>
                <div class="col-right">
                    <div class="totalAmount">€25</div>
                    <div class="pricePerProduct">€5 {{settings.textAfter}}</div>
                </div>
                <div class="discountPercentProduct">50%</div>
            </li> 
             
        </ul>  
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
 