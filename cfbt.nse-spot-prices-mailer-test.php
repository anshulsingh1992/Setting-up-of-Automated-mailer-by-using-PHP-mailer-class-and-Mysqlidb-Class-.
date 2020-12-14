<?php
/*~ Setting up of Automated mailer by using PHP mailer class and Mysqlidb Class .
.---------------------------------------------------------------------------.
|
|   Authors: Anshul Singh |
| ------------------------------------------------------------------------- |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/
/*ini_set("error_reporting", E_ERROR);
ini_set("error_reporting", E_ALL);
ini_set("display_errors", 1);*/

include_once dirname(dirname(__FILE__)) .'/includes/common.php';
//include_once BASE_PATH.'/includes/simple_html_dom.php';
include_once BASE_PATH.'/includes/inc.common.nseit.php';
include_once BASE_PATH.'/includes/MysqliDb.php';
include_once BASE_PATH .'/includes/class.phpmailer.php';

$mysqli = new Mysqlidb (DB_HOST_INT, DB_USER_INT, DB_PASSWORD_INT, DB_NAME_INT);

DEFINE("SPOT_PRICES", "DB_name.table_name");

$message="
<!DOCTYPE html>
<html>
<head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
table, th, td {
  border-collapse: collapse;
  text-align: center;
  padding: 5px;
  font-family: 'Roboto', arial !important;
  font-size:12px;

}
th{
    background-color: #8FBC8F;
}

</style>
</head>
<body>
<table cellpadding='5' cellspacing='0' border='0' style='width:100% !important; border: 0px; border-collapse: collapse; text-align: left; color:#222; font-family: Roboto, arial;'>
  <tr>
      <td style='text-align:left; border: 0px !important;' >Dear Sir,</td>
  </tr>
  <tr>
      <td style='text-align:left; border: 0px !important;'>Please find the Spot price differences - NSE with MCX & BSE</td>
  </tr>
</table><br>
<table cellpadding='5' cellspacing='0' border='1' style='width:100%; border: 1px solid black; border-collapse: collapse; text-align: center; color:#222; font-family: Roboto, arial;'>
  <tr style='background-color: #8fbc8f; color:#000;'>
    <th rowspan='2'>Symbol</th>
    <th rowspan='2'>Date</th>
    <th colspan='2'>NSE</th>
    <th colspan='2'>MCX</th>
    <th colspan='2'>BSE</th>
    <th rowspan='2'>Absolute Difference in Session I  between NSE & MCX(in Rs.)</th>
    <th rowspan='2'>Absolute Difference in Session II  between NSE & MCX(in Rs.)</th>
    <th rowspan='2'>Absolute Difference in Session I  between NSE & BSE(in Rs.)</th>
    <th rowspan='2'>Absolute Difference in Session II  between NSE & BSE(in Rs.)</th>
    <th rowspan='2'>Difference in Session I  between NSE & MCX(in %)</th>
    <th rowspan='2'>Difference in Session II  between NSE & MCX(in %)</th>
    <th rowspan='2'>Difference in Session I  between NSE & BSE(in %)</th>
    <th rowspan='2'>Difference in Session II  between NSE & BSE(in %)</th>
 </tr>
 <tr style='background-color: #8fbc8f; color:#111;'>
    <th>Session<br> I</th>
    <th>Session II</th>
    <th>Session<br> I</th>
    <th>Session II</th>
    <th>Session<br> I</th>
    <th>Session II</th>

 </tr>";

    $t=1607452200;
    //$t=strtotime(date(Ymd));
    $mysqli->where("post_date",$t);
    $mysqli->where("status",1);
    $spot_prices_raw = $mysqli->get("db_name.table_name");

    if(isset($spot_prices_raw) && count($spot_prices_raw) > 1){

          //$data= array();
          foreach ($spot_prices_raw as $key => $value) {
           $data[$value["raw_date"]][$value["commodity"]][$value["source"]][$value["session"]]=$value["price"];
          }

          foreach ($data as $keydate => $valuedate) {
            foreach ($valuedate as $commodity_symbol => $commodity_value) {

                    $optHtml = "";
                    $optHtml .= '<tr>';
                    $optHtml .= '<td style="width:80px;">'. ucfirst(strtolower($commodity_symbol)).'</td>';
                    $optHtml .= '<td style="width:80px;">'. $keydate .'</td>';
                    $commodity_source_name = ["nse","mcx","bse"];
                    foreach ($commodity_source_name as $source_key => $source_data) {

                        $session_one_value = $commodity_value[$source_data][1];
                        $session_two_value = $commodity_value[$source_data][2];
                        $optHtml .= '<td>'.  number_format($session_one_value,2) .'</td>';
                        $optHtml .= '<td>'.  number_format($session_two_value,2) .'</td>';
                    }

                    $nse_mcx_first_diff = number_format($commodity_value['nse'][1] - $commodity_value['mcx'][1],2);
                    $nse_mcx_second_diff = number_format($commodity_value['nse'][2] - $commodity_value['mcx'][2],2);
                    $nse_bse_first_diff = number_format($commodity_value['nse'][1] - $commodity_value['bse'][1],2);
                    $nse_bse_second_diff = number_format($commodity_value['nse'][2] - $commodity_value['bse'][2],2);
                    $optHtml .= '<td>'. $nse_mcx_first_diff .'</td>';
                    $optHtml .= '<td>'. $nse_mcx_second_diff .'</td>';
                    $optHtml .= '<td>'. $nse_bse_first_diff .'</td>';
                    $optHtml .= '<td>'. $nse_bse_second_diff .'</td>';

                    $optHtml .= '<td>'. round((1-$commodity_value['mcx'][1]/$commodity_value['nse'][1])*100,2).'%</td>';
                    $optHtml .= '<td>'. round((1-$commodity_value['mcx'][2]/$commodity_value['nse'][2])*100,2).'%</td>';
                    $optHtml .= '<td>'. round((1-$commodity_value['bse'][1]/$commodity_value['nse'][1])*100,2).'%</td>';
                    $optHtml .= '<td>'. round((1-$commodity_value['bse'][2]/$commodity_value['nse'][2])*100,2).'%</td>';
                    $optHtml .= '</tr>';

                    $message.=$optHtml;
            }
          }

     $message.="</table>
        <br><br>
        <table cellpadding='5' cellspacing='0' border='0' style='width:100% !important; border: 0px; border-collapse: collapse; text-align: left; color:#222; font-family: Roboto, arial;'>
        <tr>
            <td style='text-align:left; border: 0px !important;' >Warm regards</td>
        </tr>
      </table>
      </body>
      </html>";



        //echo $message; exit;

        $receivers = "anshul.singh@pinstorm.com";
        
        $subject = "Spot price differences - NSE with MCX & BSE";
        //following function do first id of mailers TO and other are CC
        echo phpmailerCC($receivers, $subject, $message);

    }   //main if check data
    else {
      echo "Noo data found";
    }



  /**
   * Send mail using PHPMailer class
   * @param  array $emailid     List of email ids
   * @param  String $subject    Mail subject
   * @param  [String] $msg      Mailer body
   * @param  [String] $attachment File path
   * @return [String]             Success or Error message
   */
  function phpmailerCC($emailid,$subject,$msg,$attachment=null){


        $mail = new PHPMailer; // create a new object
        $mail->IsSMTP(); // enable SMTP
        $mail->Mailer = PHPMAILER_PROTOCOL;
        $mail->SMTPDebug = 0; // debugging: 0 =  off errors, 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true; // authentication enabled
        $mail->SMTPSecure = PHPMAILER_SSL; // secure transfer enabled REQUIRED for GMail
        $mail->Host = PHPMAILER_HOST;
        $mail->Port = PHPMAILER_PORT; // or 587
        $mail->IsHTML(true);
        $mail->Username = PHPMAILER_EMAILID;
        $mail->Password = PHPMAILER_EMAILID_PASS;
        $mail->SetFrom(PHPMAILER_FROM, PHPMAILER_FROM_NAME, true);

        $mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
        $mail->MsgHTML($msg);
        //~ $attachment = ($_REQUEST['attachment'])?$_REQUEST['attachment']:'contents.html';
        //$attachment = 'contents.html';

        if(isset($attachment) && !empty($attachment)):
            foreach(explode(",", $attachment) as $attachKey => $attachVal):
               $mail->AddAttachment($attachVal);
            endforeach;
        endif;
        $a  = "";
        foreach(explode(",", $emailid) as $mailKey => $mailVal):

          if($mailKey == 0){
              $mail->AddAddress($mailVal);
          }else{
              $mail->AddCC($mailVal);
          }

        endforeach;


        if(!$mail->Send())
          $mailResult = "Mailer Error: " . $mail->ErrorInfo;
        else
          $mailResult = "E-mail has been sent";

        return $mailResult;
    }

?>
