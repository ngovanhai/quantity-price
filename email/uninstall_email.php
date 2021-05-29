<?php
ini_set('display_errors', TRUE);
error_reporting(E_ALL);  
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';  
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; 
 
 

// $settings = getSettingsEmail($shop,$shopInfo); 
// sendMail($settings);
function sendMail($settings){
    $mail = new PHPMailer(true); 
    $mail->ErrorInfo;
    try {
        //Server settings
        $mail->CharSet = "utf-8";
        $mail->SMTPDebug = 0; 
        $mail->isSMTP();
        
        $mail->Host = $settings['smtp']; 
        $mail->SMTPAuth = true;
        // $mail->SMTPDebug = 2;
        $mail->Username = $settings['username'];  
        $mail->Password = $settings['password'];        
        $mail->SMTPSecure = $settings['encryption'];
        $mail->Port = (int)$settings['port'];   
        $mail->setfrom('contact@omegatheme.com', "Quantity Notification");
        $mail->addAddress("kaylee@omegatheme.com", "Quantity Price Breaks was uninstalled");
       
        //Content
        $mail->isHTML(true);
        $mail->Subject = $settings['title'];
        $mail->Body = $settings['body'];
        $mail->send();
        return true;
    } catch (Exception $e) {
        return 'Message could not be sent. Mailer Error: '. $mail->ErrorInfo;
    }
}

  
function getSettingsEmail($shop,$shopInfo){
    $settingsEmail = [];
    $settingsEmail['smtp'] = "smtp.gmail.com";
    $settingsEmail['port'] = 465;
    $settingsEmail['encryption'] = "ssl";
    $settingsEmail['username'] = "contact@omegatheme.com";
    $settingsEmail['password'] = "xipat100";
    $settingsEmail['title'] = $shop."was uninstalled Quantity Price Breaks App";

    $country =$shopInfo['country_name'];
    $customer_email =$shopInfo['customer_email'];
    $email =$shopInfo['email'];
    $zip =$shopInfo['zip'];
    $province =$shopInfo['province'];
    $timezone =$shopInfo['timezone'];
    $myshopify_domain =$shopInfo['myshopify_domain'];
    $shop_owner =$shopInfo['shop_owner'];
    $phone =$shopInfo['phone'];

    $settingsEmail['body'] = '
    <div>
    <table align="center" border="0" width="100%">
      <tbody>
        <tr>
          <td align="center" bgcolor="#245f37" valign="top" width="100%" style="padding: 25px 0px;   ">
            <table border="0" style=" background: black; background-color: #fff; background-repeat: no-repeat; 	width: 580px; height: 485px; padding: 0px 30px; padding-bottom: 15px; ">
              <tbody>
                <tr>
                  <td style=" padding: 0; text-align: center; height: 75px; font-size: 24px;  color: #fff; font-weight: 700; ">
                       Quantity Breaks Price
                  </td>
                </tr> 
                <tr>
                  <td style=" text-align: left; background: #fff; border-radius: 10px; border: none;  padding: 0 25px; ">
                    <p style="margin-top: 50px;">Hi Kaylee,</p></br>
                    <p>The store '.$shop.' was uninstalled - </p>'.date('Y-m-d H:i:s').'
                    <p>Store details</p>
                    </br>
                    <p style="margin-bottom: 0;"><b>Shop Owner:</b> '.$shop_owner.'</p> 
                    <p style="margin-bottom: 0;"><b>Customer Email:</b> '.$customer_email.'</p>
                    <p style="margin-bottom: 0;"><b>Phone:</b> '.$phone.'</p>
                    <p style="margin-bottom: 0;"><b>Domain:</b> '.$myshopify_domain.'</p>
                    <p style="margin-bottom: 0;"><b>Email:</b> '.$email.'</p>
                    <p style="margin-bottom: 0;"><b>Zipcode:</b> '.$zip.'</p>
                    <p style="margin-bottom: 0;"><b>Province:</b> '.$province.'</p>
                    <p style="margin-bottom: 0;"><b>Timezone:</b> '.$timezone.'</p>  
                    <p style="margin-bottom: 0;"><b>Country:</b>  '.$country.'</p>
                 
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
    ';
    return $settingsEmail;
}