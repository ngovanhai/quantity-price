<?php 
ini_set('display_errors', TRUE);
error_reporting(E_ALL); 
use sandeepshetty\shopify_api;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; 
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require '../../vendor/autoload.php';
require '../../conn-shopify.php';

if (isset($_GET['action'])) { 
    if ($_GET['action'] == "sendReview") {
        $shop = $_GET['shop'];
        if(isset($_GET['comment'])){
            $comment = $_GET['comment']; 
        }else{
            $comment = "";
        }
        $star_value = $_GET['star_value'];
        $select_settings = $db->query("SELECT * FROM tbl_appsettings WHERE id = $appId");
        $app_settings = $select_settings->fetch_object();

        $shop_data = $db->query("select * from tbl_usersettings where store_name = '" . $shop . "' and app_id = $appId");
        $shop_data = $shop_data->fetch_object();
        $appName = $app_settings->app_name;
//        $shopify = shopify_api\client(
//                $shop, $shop_data->access_token, $app_settings->api_key, $app_settings->shared_secret
//        );
//        $customer = $shopify('GET', '/admin/shop.json');
//        $email = $customer['email']; 
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = 'email-smtp.eu-west-1.amazonaws.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'AKIARCFWEUWYGTXY2YW5';
            $mail->Password = 'BOA4fMjB6YbKUSOesW2omFn9Inw3MI48Cj83l8qHRf1O';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->setFrom('do_not_reply@omegatheme.com', 'Omegatheme Support');
            $mail->addAddress('contact@omegatheme.com', 'Facebook Reviews Customer');
            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Here is customer rating';
            $mail->Body = "<table style='width: 100%;'>
                            <thead style='background: #d54937; color: #fff; text-align: center; font-size: 15px;'>
                                <tr>
                                    <td style='padding: 12px;'>Rating</td>
                                    <td>App</td>
                                    <td>Comment</td>
                                    <td>Shop</td>
                                </tr>
                            </thead>
                            <tbody style=' text-align: center;'>
                                <tr>
                                    <td style=' padding: 12px;'>{$star_value}</td>
                                    <td style=' padding: 12px;'>{$appName}</td>
                                    <td>{$comment}</td>
                                    <td>{$shop}</td>
                                </tr>
                            </tbody>
                         </table>";
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}
