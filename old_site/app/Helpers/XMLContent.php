<?php
namespace App\Helpers;

use Exception;

class XMLContent {
    
public function randomString($length = 6) {
    $str = "";
    $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}

public function sendSMS2($mobileNo, $message, $LoggedCompSmsID, $LoggedCompSmsPassword, $tID, $sendName = 'SPCASE') {
    if (strlen($mobileNo) > 0 && strlen($message) > 0) {
        //$smsURL = "http://www.modsms.gurukrupaenterprise.com/pushsms.php?username=" . $LoggedCompSmsID . "&password=" . $LoggedCompSmsPassword . "&sender=" . $sendName . "&to=" . $mobileNo . "&message=" . urlencode($message) . "&priority=11";
        
        $smsURL = "http://modsms.gurukrupaenterprise.com/pushsms.php?username=" . $LoggedCompSmsID . "&api_password=" . $LoggedCompSmsPassword . "&sender=SPCASE&to=" . $mobileNo . "&message=" . urlencode($message) . "&priority=11&e_id=1201160740393312730&t_id=" .$tID;
        
        //$smsURL = "http://modsms.gurukrupaenterprise.com/pushsms.php?username=" . $LoggedCompSmsID . "&api_password=" . $LoggedCompSmsPassword . "&sender=" . $sendName . "&to=" . $mobileNo . "&message=" . urlencode($message) . "&priority=11";
        //$response = file_get_contents($smsURL);

        //curl_setopt_array($ch, array(
        //        CURLOPT_URL => $smsURL,
        //    CURLOPT_RETURNTRANSFER => true
        //));
//http://modsms.gurukrupaenterprise.com/pushsms.php?username=spectacase&api_password=2b9e7d4ktfzbph78z&sender=SPCASE&to=9909340404&message=Welcome%20in%20Spectacase%20app%20,%20your%20OTP%20for%20registration%20is%20654321&priority=11&e_id=1201160740393312730&t_id=1207161959862498511
        //$output = curl_exec($ch);
        
        try {
            $ch = curl_init();

            // Check if initialization had gone wrong*    
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $smsURL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
       
            //curl_setopt ( $ch, CURLOPT_HEADER, 0 );

       
            // Check the return value of curl_exec(), too
//             if ($content === false) {
//                 throw new Exception(curl_error($ch), curl_errno($ch));
//             }

            /* Process $content here */

            // Close curl handle
            curl_close($ch);
            return $content;
        } catch(Exception $e) {
            return "Error " . $e->getCode() . " - " . $e->getMessage();
        }
        
//        // create a new cURL resource
//        $ch = curl_init ();
//        // // set URL and other appropriate options
//    	curl_setopt ( $ch, CURLOPT_URL, $smsURL );
//        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
//        // // grab URL and pass it to the browser
//        $output = curl_exec ( $ch );
//        // // close cURL resource, and free up system resources
//        curl_close ( $ch );
//
//        return $output;
    }
    return false;
}

public function sendWhatsappMessage($mobileNo, $message, $InstanceID) {
    if (strlen($mobileNo) > 0 && strlen($message) > 0) {
        $smsURL = "http://whatsapp.vinayakinfosoft.com/api/sendText?token=" . $InstanceID . "&phone=" . $mobileNo . "&message=". urlencode($message);
        $response = file_get_contents($smsURL);

        // create a new cURL resource
        // $ch = curl_init ();
        // // set URL and other appropriate options
        // curl_setopt ( $ch, CURLOPT_URL, $smsURL );
        // //curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        // // grab URL and pass it to the browser
        // $isSMSSend = curl_exec ( $ch );
        // // close cURL resource, and free up system resources
        // curl_close ( $ch );

        return $smsURL;
    }
    return false;
}
}

?>