 <template>
    <div> 
        <b-container fluid>  
            <p>This feature just only for store install since 25/10/2019. If you install before 25/10/2019 click in <a target="_blank" :href="'https://'+shop+'/admin/oauth/request_grant?client_id=aeca27f78916a0b6c0916b621906aaf3&redirect_uri=https://apps.omegatheme.com/group-price-attribute/addPermisson.php&scope=read_themes,write_themes,read_script_tags,write_script_tags,read_customers,write_draft_orders,read_draft_orders,write_price_rules,write_products,read_products,read_orders'" >here</a> to active feature.</p>
            <div class="card"> 
                <div class="campaignsperFormence">
                    <h5>Campaigns performance</h5> 
                    <ul class="listFillterTracking">
                        <li  v-for="(contentFillter,key) of fillterDate" :key="key" class="contentFillter" :class="'contentFillter_'+contentFillter.value" @click="fillterOrderByDate(contentFillter.value)"> {{contentFillter.label}} </li> 
                    </ul>  
                    <p v-if="start_date != null">{{start_date}} to {{end_date}}</p>
                    <ul class="reportTracking">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="listTracking">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="valueTracking">{{listOrderApplyRuleStatistic.length}} / {{listOrderStatistic.length}}</p> 
                                            <p>Order applied rule</p> 
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <i class="fa fa-file-text-o" aria-hidden="true" style="font-size: 50px;"></i>
                                        </div>  
                                    </div>
                                    <div class="row">
                                        <div class="panel-footer">
                                            Order applied rule / Total order
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  class="col-md-4"> 
                                <div class="listTracking">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="valueTracking">${{totalDiscount}}</p> 
                                            <p>Total discount price</p>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <i class="fa fa-tags" aria-hidden="true"></i>
                                        </div>  
                                    </div>
                                    <div class="row">
                                        <div class="panel-footer" > Total discount price applied the rule  </div>
                                    </div>
                                </div> 
                            </div>
                            <div  class="col-md-4"> 
                                <div class="listTracking">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <p class="valueTracking">{{countRule}}</p>
                                            <p>Campaign runing</p>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <i class="fa fa-pause" aria-hidden="true"></i>
                                        </div>  
                                    </div>
                                    <div class="row">
                                        <div class="panel-footer"> Total campaign running </div>
                                    </div> 
                                </div> 
                            </div> 
                        </div>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mostCampaign">
                            <h5>Top most applied campaign</h5>
                            <table  class="table"> 
                                <tr v-for="(listMostAppliedRule,key) of listMostAppliedRule" :key="key">
                                    <td>
                                        <div class="statsticIDRule">#{{listMostAppliedRule.id_rule}}</div>
                                    </td>
                                    <td>
                                        <div class="statsticContentRule"> 
                                            <a @click="showDetailContentRule(listMostAppliedRule.id_rule)" style="color: rgb(85, 99, 193);">Detail content rule</a> 
                                            <div class="detailContentRule" :class="'detailContentRule_'+listMostAppliedRule.id_rule">
                                                <ul class="">
                                                    <li  v-for="(content_rule,key) of listMostAppliedRule.content_rule" :key="key">Buy {{content_rule.number}} discount {{content_rule.number}}</li>
                                                </ul>
                                            </div>
                                        </div> 
                                    </td>
                                    <td>
                                        <div  class="statsticTotalDiscountOrderByRule"><i class="fa fa-tags" aria-hidden="true"></i> Total discount: ${{Math.ceil(listMostAppliedRule.total_discount)}}</div>
                                    </td>
                                    <td>
                                        <div class="statsticCountOrderByRule">{{listMostAppliedRule.number_record}} orders</div> 
                                    </td>
                                    <td> <button class="btn btn-default" @click="showListOrder(listMostAppliedRule.id_rule)">View all order</button></td>
                                </tr> 
                            </table>
                        </div> 
                    </div>
                    <div class="col-md-12">
                        <div class="mostCampaign">
                            <div>
                                <apexchart width="100%" type="line" :options="options" :series="series"></apexchart>
                            </div>
                        </div> 
                    </div>
                </div>   
            </div> 
       </b-container>  
       <b-modal v-model="showModalListOrder" title="List order">
            <b-container fluid> 
                <div class="listOrder">  
                    <table class="table">
                        <tr v-for="(orderByRule,key) of listOrderByRule" :key="key">
                            <td><span class="otmarker"></span></td>
                            <td><a :href="'https://'+orderByRule.shop+'/admin/orders/'+orderByRule.id_order" target="_blank">#{{orderByRule.id_order}}</a></td>
                            <td>{{orderByRule.customer}}</td>
                            <td>{{orderByRule.create_date}}</td> 
                            <td> - ${{orderByRule.discount_price}}</td> 
                            <td>${{orderByRule.total_price}}</td>
                        </tr>
                    </table>
                </div> 
            </b-container>
            <div slot="modal-footer" class="w-100"> </div>
        </b-modal> 
    </div>
</template>
 
<script> 
    const shop = window.shop;
    module.exports = {
        props: [],
      
        data: function() {
            return { 
                listOrderStatistic : [],
                listOrderApplyRuleStatistic : [],
                totalPrice : 0,
                totalDiscount : 0,
                countRule: 0,
                listMostAppliedRule : [],
                showModalListOrder: false,
                listOrderByRule:[], 
                fillterDate : [
                   { 'value' : 1, 'label' : "Today" },
                   { 'value' : 2, 'label' : "Yesterday" },
                   { 'value' : 3, 'label' : "Week" },
                   { 'value' : 4, 'label' : "Month" },
                   { 'value' : 5, 'label' : "Last Month" },
                ],
                valueFillterDate: 0,
                start_date: null,
                end_date:null,
                dataShowChartAllOrder:[] ,
                dataShowChartOrderAppliedRule:[] ,
                options: {
                    chart: {
                        id: 'vuechart-example', 
                    },
                    xaxis: {
                        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
                    }
                },
                series: [
                    {
                        name: 'All Order',
                        data: []
                    },
                    {
                        name: 'Order applied rule',
                        data: []
                    }
                ],
                shop:shop
                 
            };
        },
        mounted: function() {
            let self = this   
            self.getStatistic();
            self.getAllRule();
            self.getCountOrderByRule();
            self.getDataToShowChart();  
            self.installWebhook();  
        },
        methods : {   
            installWebhook:function(){
                let self = this  
                var data = {};
                data.action = "installWebhook";
                data.shop = shop;    
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) { 
                }).fail((error) => {
                    console.log("error",error);
                });
            },
            getDataToShowChart:function(){
                let self = this  
                var data = {};
                data.action = "getDataToShowChart";
                data.shop = shop;    
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) { 
                    let dateChart = [];
                    let valueAllOrder = [];
                    let valueApplyRule = [];
                    result.orderAppliedRule.forEach(function(element) {
                        dateChart.push(element[0])
                        valueAllOrder.push(element[1])
                    }); 
                    result.orderAppliedRule.forEach(function(element) {
                      valueApplyRule.push(element[1])
                    }); 
                    self.options =  {
                        xaxis :{
                            categories: dateChart
                        }
                    };
                    self.series[0] =  {
                        data : valueAllOrder
                    };
                    self.series[1] =  {
                        data :valueApplyRule
                    };
                     
                }).fail((error) => {
                    console.log("error",error);
                });
            },
            getStatistic : function(){
                let self = this; 
                var data = {};
                data.action = "getStatistic";
                data.shop = shop;  
                data.valueFillterDate = self.valueFillterDate
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) {
                    self.listOrderStatistic = result; 
                    self.listOrderApplyRuleStatistic = [];
                    self.totalDiscount = 0;
                    for(let i =0; i < result.length ; i++){ 
                        self.totalPrice = parseFloat(self.totalPrice) + parseFloat(result[i]['total_price']);
                        self.totalDiscount = Math.ceil(parseFloat(self.totalDiscount) + parseFloat(result[i]['discount_price']),2);
                        if(result[i]['id_rule'] != null){
                            self.listOrderApplyRuleStatistic.push(result[i]);
                        }
                    }
                }).fail((error) => {
                    console.log("error",error);
                });
            }, 
            getAllRule: function(){
                let self = this; 
                var data = {};
                data.action = "getAllRule";
                data.shop = shop;  
                data.valueFillterDate = self.valueFillterDate
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) {
                     self.countRule = result;
                }).fail((error) => {
                    console.log("error",error);
                });
            },
            getCountOrderByRule : function(){
                let self = this; 
                var data = {};
                data.action = "getCountOrderByRule";
                data.shop = shop;  
                data.valueFillterDate = self.valueFillterDate
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) {
                    self.listMostAppliedRule = result;
                }).fail((error) => {
                    console.log("error",error);
                });
            },
            fillterOrderByDate: function(fillterDate){
                let self = this ;
                self.getStartEndDate(fillterDate); 
                $(".contentFillter").removeClass("active");
                $(".contentFillter_"+fillterDate).addClass("active"); 
                self.valueFillterDate = fillterDate
                self.getStatistic();
                self.getAllRule();
                self.getCountOrderByRule(); 
            },
            getStartEndDate: function(fillterDate){
                let self = this ;
                var current_datetime = new Date();  
                let formatted_date = current_datetime.getDate() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear();
                 
                if(fillterDate == 1){
                    // today
                    self.start_date = current_datetime.getDate() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear();
                    self.end_date   = current_datetime.getDate() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear(); 
                }
                if(fillterDate == 2){
                    // yesterday 
                    self.start_date = (current_datetime.getDate() - 1)  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear();
                    self.end_date   = (current_datetime.getDate() - 1)  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear(); 
                }
                if(fillterDate == 3){
                    // week
					if((current_datetime.getDate() - 7) < 0){
						self.start_date = (30 + current_datetime.getDate() - 7)  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear();
						self.end_date   = current_datetime.getDate()  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear(); 
					}else{
						self.start_date = (current_datetime.getDate() - 7)  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear();
						self.end_date   = current_datetime.getDate()  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear(); 
					} 
                }
                if(fillterDate == 4){
                    // month 
                    self.start_date = current_datetime.getDate()  + "-" +  current_datetime.getMonth()  + "-" + current_datetime.getFullYear();
                    self.end_date   = current_datetime.getDate()  + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getFullYear(); 
                }
                if(fillterDate == 5){
                    // last month
                    self.start_date = (current_datetime.getDate())  + "-" + (current_datetime.getMonth()) + "-" + current_datetime.getFullYear();
                    self.end_date   = (current_datetime.getDate())  + "-" + (current_datetime.getMonth() - 1) + "-" + current_datetime.getFullYear();
                }
            }, 
            showDetailContentRule: function(idRule){ 
                let self = this;  
                $(".detailContentRule").hide();
                $(".detailContentRule_" + idRule).show();
            },
            showListOrder: function(idRule){
                let self = this;  
                self.showModalListOrder = true; 
                var data = {};
                data.action = "showListOrder";
                data.id = idRule;
                data.shop = shop;  
                $.ajax({
                    url: 'services_v2.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json'
                }).done(function (result) { 
                    self.listOrderByRule = result; 
                }).fail((error) => {
                    console.log("error",error);
                });
            }
        },
        components: {  apexchart: VueApexCharts, }
    }; 
  
</script>
<style scoped>
    .detailContentRule{ 
        display: none;
    }
    .detailContentRule ul{
        padding-left: 5px;
    }
    .detailContentRule ul li{
        list-style-type: none;
    }
    .panel-footer {
        background: #3f51b5;
        color: #fff;
        padding:5px 15px;
        position: absolute;
        width: 100%;
        left: 0;
    }
    .valueTracking {
        font-weight: bold;
        font-size: 37px;
        margin-bottom: 0px;
    }
    .reportTracking li{
        text-align: left;
    }
    .listTracking i{
        margin-top: 15px;

        font-size: 50px;
    }
    .reportTracking{
        border:none;
    }
    .listTracking{
        padding: 19px 22px; 
        box-shadow: rgba(0, 0, 0, 0.15) 0px 1px 2px 0px;
        -webkit-transition: box-shadow 0.2s ease-in;
        -o-transition: box-shadow 0.2s ease-in;
        transition: box-shadow 0.2s ease-in;
        position: relative;
    }
    .listTracking:hover {
        box-shadow: 0 3px 12px 0 rgba(0, 0, 0, 0.15);
    }
    .nopadding{
        padding:0px;
    }
    .otmarker {
        -webkit-box-flex: 0;
        -webkit-flex: none;
        -ms-flex: none;
        flex: none; 
        margin-right: 10px;
        vertical-align: middle;
        border-radius: 50%;
        border: 3px solid #ebeef0;
        background-color: #c3cfd8;
        position: absolute;
        left: -7px;
        width: 17px;
        height: 17px;
        z-index: 999;
        top: 10px;
    }
    .topOrder{
        padding-left: 0px;
    }
    .mostCampaign ,.campaignsperFormence{
        padding-left: 0px;
        width: 90%;
        margin: auto;
    }
    .mostCampaign{
        margin-top: 20px;
    }
    .mostCampaign tr{ 
        border: 1px solid #dfe3e8;
        border-radius: 5px;
        box-shadow: 0px 0px 4px #dfe3e869;
        list-style-type: none;
     
    }
    .mostCampaign tr td{
        padding: 15px 15px;
    }
    .listOrder{
         position: relative;
    }
    .listOrder table::after {
        content: ' ';
        position: absolute;
        top: 5px;
        bottom: 18px;
        left: 20;
        width: 3px;
        background: #e3e6e9;
    }
    .listOrder table {
        margin-left: 15px;
        padding: 10px 0px;
    }
    .listOrder table tr td{
        position: relative;
        list-style-type: none;
        padding-left: 15px;
        margin-bottom: 15px;
    }
     
    .listOrder table tr .ui-feed__marker{
        position: relative;
    } 
    .statsticIDRule{
        color: #5563c1;
        font-weight: 500;
    }
    .statsticContentRule span{
        display: block; 
    }
    .campaignsperFormence{
        display: flex;
        flex-direction: column;
    }
</style>
 
 
