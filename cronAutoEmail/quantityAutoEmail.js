var request = require('request');
var mysql = require("mysql");
const fs = require('fs'); 
const QuickChart = require('quickchart-js');
const myChart = new QuickChart();

const nodemailer = require('nodemailer');
const user = "contact@omegatheme.com";
const password = "xipat100";
const jobPerRequest = 5;
var jobs  = [];
var emailTemplate = [];

var infoShop = [];
// config


// DEV
// var appID = 51;
// var dbpool = mysql.createPool({
//     host: 'localhost',
//     user: 'root',
//     password: '',
//     database: 'shopify_trong',
// });

// LIVE
var appID = 5;
var dbpool = mysql.createPool({ 
    host: '10.12.82.13',
    user: 'shopify',
    password: 'h1yw5ovS78iYaGRX',
    database: 'shopify',
    connectionLimit: 15,
	charset : 'utf8mb4'
}); 

const transport = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 465,
    auth: {
       user: user,
       pass: password
    }
}); 

// end config 


// status = 0: job chua chay
// status = 1: job dang  chay
// status = 2: job chay thanh cong
// status = 3: job loi




dbpool.getConnection(function (err, connection) { 
    try{
      getEmailTemplate(connection);
      setInterval(function(){
        getJob(connection);
      },5000)
      
      setInterval(function(){
        initJob(connection);
      },3000)
    }catch(error){
        writeErrorLog(error)
    }
    connection.release();
});

  
function initJob(connection){  
    if(jobs.length > 0){
      console.log(`dang chay ${jobs.length} job`)
      for (const [key, job] of Object.entries(jobs)) { 
        if (job.status == 0) {
            job.status = 1;
            processJob(connection,job); 
        }
      }
    }else{
      console.log("chua co job nao.");
    }
}

async function processJob(connection,job){ 
   updateStatusJob(connection,job.id,1)  
  var templateEmailOfJob = await  getEmailTemplateByID(job.email_type); 
  if(typeof infoShop[job.shop] == "undefined"){
    var shop_info = await getShopInfo(connection,job.shop);
  }else{
    var shop_info = infoShop[job.shop] ;
  } 
  if(shop_info == null){
    updateStatusJob(connection,job.id,3)  
  }else{
    if(job.email_type == 3 || job.email_type == 5){
      var htmlInfoMostUsedRule  = await getInfoMostUsedRule(job.start_date_report,job.end_date_report,job.shop,connection);
      var totalOrder = await getTotalOrder(job.start_date_report,job.end_date_report,job.shop,connection);
      var totalOrderAppliedRule = await getTotalOrderAppliedRule(job.start_date_report,job.end_date_report,job.shop,connection); 
      var htmlChart = await getHtmlChart(totalOrder,totalOrderAppliedRule);
      templateEmailOfJob.content = templateEmailOfJob.content.replace(/{data_chart}/g,htmlChart);
      templateEmailOfJob.content = templateEmailOfJob.content.replace(/{data_rule}/g,htmlInfoMostUsedRule);
    }
    templateEmailOfJob.content = templateEmailOfJob.content.replace(/{store_name}/g,shop_info.name_shop);
    templateEmailOfJob.title = templateEmailOfJob.title.replace(/{store_name}/g,shop_info.name_shop);
    sendEmail(shop_info.email_shop,templateEmailOfJob.title,templateEmailOfJob.content,job.id,connection);
    updateStatusJob(connection,job.id,2) 
  } 
}

function getHtmlChart(totalOrder,totalOrderAppliedRule){
  return new Promise(resolve=> {
    var dateChart = [];
    var totalOrderChart = [];
    var totalOrderAppliedChart = [];
    for (const [key, perOrder] of Object.entries(totalOrder)) {  
      dateChart.push(perOrder.create_date);
      totalOrderChart.push(perOrder.number_record);
      for (const [key, perAppiedRule] of Object.entries(totalOrderAppliedRule)) {  
          if(perAppiedRule.create_date == perOrder.create_date){
            totalOrderAppliedChart.push(perAppiedRule.number_record);
            break;
          }
          if(key == totalOrderAppliedRule.length - 1){
            totalOrderAppliedChart.push(0);
          }
      }
    }  
    if(totalOrderChart.length != 0){
      let nameFile = (new Date()).getTime();
      myChart
        .setConfig({
          type: 'bar',
          data: { labels: (dateChart), datasets: [{label:"Total Order",data:(totalOrderChart)},{label:"Total Order discount",data:(totalOrderAppliedChart)}] },
        }).setWidth(800).setHeight(400)
        .setBackgroundColor('transparent');
      const chartImageUrl = myChart.getUrl(); 
      resolve(` <div style="text-align:center;"> <img src="${chartImageUrl}" width="800px" /></div>`);
    }else{
      resolve(` `)
    } 
  })
}

function getTotalOrder(startDate,endDate,shop,connection){
  return new Promise(resolve=>{
    let query = `SELECT create_date,id_rule, COUNT(*) AS number_record FROM quantity_statistic WHERE shop = '${shop}' AND ('${getFormatDate(startDate)}' < create_date < '${getFormatDate(endDate)}' )  GROUP BY create_date HAVING number_record > -1`;
    try{ 
      execQuery(connection, query,function (orders) { 
        for (const [key, order] of Object.entries(orders)) {  
          order.create_date =  order.create_date.getDate()+"/"+order.create_date.getMonth();
        } 
        resolve(orders);
      })
    }catch(error){
      writeErrorLog(error);
    }   
    // resolve();
  })
}
function getTotalOrderAppliedRule(startDate,endDate,shop,connection){
  return new Promise(resolve=>{
    let query = `SELECT create_date,id_rule, COUNT(*) AS number_record FROM quantity_statistic WHERE shop = '${shop}' AND ('${getFormatDate(startDate)}' < create_date < '${getFormatDate(endDate)}' )  AND id_rule IS NOT NULL GROUP BY create_date HAVING number_record > -1`;
    try{ 
      execQuery(connection, query,async function (orders) { 
        for (const [key, order] of Object.entries(orders)) {  
          order.create_date =  order.create_date.getDate()+"/"+order.create_date.getMonth();
        }
        resolve(orders);
      })
    }catch(error){
      writeErrorLog(error);
    }   
    // resolve();
  })
} 
function getInfoMostUsedRule(startDate,endDate,shop,connection){
  return new Promise(resolve=>{
    let query = `SELECT statistic.id_rule,statistic.type_rule,COUNT(*) as count FROM quantity_statistic as statistic WHERE statistic.shop = '${shop}' AND ('${getFormatDate(startDate)}' < statistic.create_date < '${getFormatDate(endDate)}' ) AND statistic.id_rule IS NOT NULL GROUP BY statistic.id_rule ORDER BY count DESC limit 2 `;
    try{ 
      execQuery(connection, query,async function (mostUsedRule) {  
        if(mostUsedRule.length > 0){
          var html = ` <ul> ` ;
          for (const [key, rule] of Object.entries(mostUsedRule)) { 
            let detailRule = await getDetailRuleByID(rule.type_rule); 
            html += `<li>Rule on ${detailRule} (#${rule.id_rule}) was used the most (${rule.count} number)</li> `;
          }
          html  += `</ul>`;
          resolve(html);
        }else{
          resolve("");
        }
      })
    }catch(error){
      writeErrorLog(error);
    }   
    // resolve();
  })
  
} 
function getDetailRuleByID(type){
  return new Promise(resolve => {
    if(type == 0){
      // rule for variant 
      var tableRule = "variant";
    }
    if(type == 1){
      // rule for product 
      var tableRule = "products";
    }
    if(type == 2){ 
      // rule for collection 
      var tableRule = "variant";
    }
    if(type == 3){
      // rule for global 
      var tableRule = "global";
    }
    resolve(tableRule);
    
  })
  

}
function getShopInfo(connection,shop){
  return new Promise(resolve => {
      var query = `Select * from shop_installed where shop = "${shop.replace(" ", "")}" AND app_id = `+appID;
      execQuery(connection, query, function (shopinfo) {  
          if (shopinfo.length == 0) {
              resolve(null);
          }else{
              resolve(shopinfo[0]);
          }
      })  
  })
} 
function getEmailTemplateByID(id){
  return new Promise(resolve => {
    for (const [key, template] of Object.entries(emailTemplate)) { 
      if (template.id == id) {
        resolve(template);
      }
      if(key == emailTemplate.length){
        resolve(null);
      }
    } 
    
  })
  
} 
function getEmailTemplate(connection){
  var query = `SELECT * FROM quantity_template_email` ;
    execQuery(connection, query, function (respone) {  
      emailTemplate = respone;
  })   
}
 
function updateStatusJob(connection, id, status) {
  var currenTime = new Date();
  var queryUpdateDone = `UPDATE quantity_email_jobs SET status = ${status},last_update = '${currenTime}'  WHERE id = ${id}`;
  execQuery(connection, queryUpdateDone, function (status, respone) { });
}

function getJob(connection){
    const nDate = new Date().toLocaleString('en-US', {
      timeZone: "UTC"
    });
    var date = new Date(nDate);
    date = getFormatDate(date);
    dbpool.getConnection(function (err, connection) {
        if (err) {
            throw err;
            return false;
        }  
        var query = `SELECT * FROM quantity_email_jobs where status = 0 AND start_date <= '${date}' LIMIT ${jobPerRequest}` ; 
        execQuery(connection, query, function (responeJOB) {  
            if (responeJOB.length != 0) {
              jobs = jobs.concat(responeJOB);
            } 
        })  
        connection.release();
    });
} 
function execQuery(connection, query, callback) { 
  connection.query(query, function (err, rows) {
    if (err) {
      if (typeof callback === 'function') {
        callback(err);
      }
    }
    else {
      if (typeof callback === 'function') {
        callback(rows);
      }
    }
  });
} 
function getFormatDate(date){
  var d = new Date(date);
  var day = d.getDate();
  var month = d.getMonth() + 1;
  var year = d.getFullYear();
  var hours = d.getHours();
  var minutes = d.getMinutes();
  var seconds = d.getSeconds();
  return year+'-'+month+'-'+day+' '+hours+':'+minutes+':'+seconds;
} 
function sendEmail(to,subject,html,id,connection){
    const message = {
        from: user,  
        to: to,        
        subject: subject,
        html: html
    }; 
    try{
      transport.sendMail(message, function(err, info) {
        console.log("Done send email");
        updateStatusJob(connection, id, 2);
      });
    }catch(error){
      updateStatusJob(connection, id, 3);
      writeErrorLog(error)
    }
    
}

function writeErrorLog(err){
  console.log("error:",err);
    // let nameFile = (new Date()).getTime(); 
    // fs.writeFile(`logs/email${nameFile}.json`, JSON.stringify(err), function (err) {
    //   console.log("error:",err);
    // } );
}