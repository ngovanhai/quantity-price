<!-- https://dev.omegatheme.com/group-price-attribute/cron/update_version.php?shop=sajaro-invitations.myshopify.com -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> 
<?php
  $shop = $_GET['shop']; 
?>
<script>
    innsertProductInSub();
    function innsertProductInSub(){
        $.ajax({
            url: 'convert_db.php',
            type: 'GET',
            data: {shop:'<?php echo $shop ?>',action:'getProduct'}
        }).done((result) => {  
            $('#step2').addClass('btn-primary');
            for(var i=1; i <= result ; i++){
                updateNewDB(i)
            } 
        });
    } 
    function updateNewDB(pages){
        console.log("a")
        $.ajax({
            url: 'convert_db.php',
            type: 'GET',
            data: {shop:'<?php echo $shop ?>',action:'updateNewDB',pages:pages}
        }).done((result) => {
            $('#step3').addClass('btn-primary'); 
            $('.loading').hide();
            $('.done').show();
        });
    }
</script>
<style>
    body{  
    margin-top:40px; 
} 
.stepwizard-step p {
    margin-top: 10px;
}

.stepwizard-row {
    display: table-row;
}

.stepwizard {
    display: table;
    width: 100%;
    position: relative;
}

.stepwizard-step button[disabled] {
    opacity: 1 !important;
    filter: alpha(opacity=100) !important;
}

.stepwizard-row:before {
    top: 14px;
    bottom: 0;
    position: absolute;
    content: " ";
    width: 100%;
    height: 1px;
    background-color: #ccc;
    z-order: 0;

}

.stepwizard-step {
    display: table-cell;
    text-align: center;
    position: relative;
}

.btn-circle {
    width: 30px;
    height: 30px;
    text-align: center !important;
    padding: 6px 0 !important;
    font-size: 12px !important;
    line-height: 1.428571429 !important;
    border-radius: 50% !important;
    opacity: 1 !important;
    opacity: .65;
}
</style>
<body>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>  
<div class="container" style="margin-top: 10%;text-align: center;">
<div class="stepwizard">
    <div class="stepwizard-row setup-panel">
        <div class="stepwizard-step">
            <a href="#step-1" id="step1" type="button" class="btn btn-primary btn-circle">1</a>
            <p>Step 1: Backup your databse in old version </p>
        </div>
        <div class="stepwizard-step">
            <a href="#step-2" id="step2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
            <p>Step 2: Convert your database to new version</p>
        </div>
        <div class="stepwizard-step">
            <a href="#step-3" id="step3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
            <p>Step 3: Please uninstall app and repeat install</p>
        </div>
    </div>
</div>
<form role="form"> 
    <img class="loading" src="https://framednetwork.com/wp-content/themes/videotouch/images/loader.gif" alt="">
    <img style="display:none; width: 4%; margin-top: 5%;" class="done" src="https://www.shareicon.net/data/256x256/2017/02/24/879486_green_512x512.png" alt="">
</form>
</div> 
 
 