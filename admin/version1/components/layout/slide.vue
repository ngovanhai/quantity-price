<template>   
    <div class="quantitySlide">
        <div class="listDiscount">
            <p>Buy 2 +</p> 
            <p v-if="settings.show_percent == 1">
                <span>20%</span> {{settings.textAfter}}
            </p>
            <p v-else><span>€20</span> {{settings.textAfter}}</p>
        </div>
        <div class="listDiscount">
            <p>Buy 3 +</p>
            <p v-if="settings.show_percent == 1">
                <span>30%</span> {{settings.textAfter}}
            </p>
            <p v-else><span>€30</span> {{settings.textAfter}}</p>
        </div>
        <div class="listDiscount">
            <p>Buy 4 +</p>
            <p v-if="settings.show_percent == 1">
                <span>40%</span> {{settings.textAfter}}
            </p>
            <p v-else><span>€40</span> {{settings.textAfter}}</p>
        </div>
        <div class="listDiscount">
            <p>Buy 5 +</p>
            <p v-if="settings.show_percent == 1">
                <span>50%</span> {{settings.textAfter}}
            </p>
            <p v-else><span>€50</span> {{settings.textAfter}}</p>
        </div>
        <div class="listDiscount">
            <p>Buy 6+</p>
            <p v-if="settings.show_percent == 1">
                <span>60%</span> {{settings.textAfter}}
            </p>
            <p v-else><span>€60</span> {{settings.textAfter}}</p>
        </div> 
</div>
</template> 
 <script src="admin/version1/lib/jquery.min.js"> </script>
  <script> 
 module.exports = {
 
  props: ["settings"],
  data: function() {
    return { 
    };
  },
  mounted: function() {
        var self = this 
        self.loadEffect();
         $('.quantitySlide').hide();
  },
  methods : {
     loadEffect: function() {
            var self = this; 
            $.getScript("admin/version1/lib/slick.min.js").done(function (script, textStatus) {  
                $('.quantitySlide').slick({
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    arrows: true,
                    dots: false,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3,
                                infinite: true,
                                dots: true
                            }
                        },
                        {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        } 
                    ]
                });
                $('.quantitySlide').show();
            }); 
       },
  },
  components: { 
    
   }
};
</script>
 <style src="admin/version1/lib/slick-theme.css">  </style>
<style src="admin/version1/lib/slick.css">  </style>