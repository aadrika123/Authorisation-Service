<?php

use Illuminate\Support\Facades\Config;

if (!function_exists('SMSJHGOVT')) {
    function SMSJHGOVT($mobileno, $message, $templateid = null)
    {
        if (strlen($mobileno) == 10 && is_numeric($mobileno) && $templateid != NULL) {
            $username      = Config::get("constants.SMS_USER_NAME");               #_username of the department
            $password      = Config::get("constants.SMS_PASSWORD");                #_password of the department
            $senderid      = Config::get("constants.SMS_SENDER_ID");               #_senderid of the deparment
            $deptSecureKey = Config::get("constants.SMS_SECURE_KEY");              #_departmentsecure key for encryption of message...
            $url           = Config::get("constants.SMS_URL");
            $message       = $message;                                                #_message content
            $encryp_password = sha1(trim($password));

            $key = hash('sha512', trim($username) . trim($senderid) . trim($message) . trim($deptSecureKey));
            $data = array(
                "username"       => trim($username),
                "password"       => trim($encryp_password),
                "senderid"       => trim($senderid),
                "content"        => trim($message),
                "mobileno"       => trim($mobileno),
                "key"            => trim($key),
                "templateid"     => $templateid,
                "smsservicetype" => "singlemsg",
            );

            $fields = '';
            foreach ($data as $key => $value) {
                $fields .= $key . '=' . urlencode($value) . '&';
            }
            rtrim($fields, '&');
            $post = curl_init();
            // curl_setopt($post, CURLOPT_SSLVERSION, 5); // uncomment for systems supporting TLSv1.1 only
            curl_setopt($post, CURLOPT_SSLVERSION, 6); // use for systems supporting TLSv1.2 or comment the line
            curl_setopt($post, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($post, CURLOPT_URL, $url);
            curl_setopt($post, CURLOPT_POST, count($data));
            curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($post); //result from mobile seva server
            curl_close($post);
            $response = ['response' => true, 'status' => 'success', 'msg' => 1];
            if (strpos($result, '402,MsgID') !== false) {
                $response = ['response' => true, 'status' => 'success', 'msg' => $result];
            } else {
                $response = ['response' => false, 'status' => 'failure', 'msg' => $result];
            }

            // print_r($response);
            return $response;
        } else {
            if ($templateid == NULL)
                $response = ['response' => false, 'status' => 'failure', 'msg' => 'Template Id is required'];
            else
                $response = ['response' => false, 'status' => 'failure', 'msg' => 'Invalid Mobile No.'];
            return $response;
        }
    }
}
if (!function_exists('send_sms')) {
    function send_sms($mobile, $message, $templateid)
    {
        if (Config::get("constants.SMS_TEST")) {
            // $mobile = "9153975142";                 #_office mobile no
            // $mobile = "7631035473";                 #_deepankar sir
            // $mobile = "8797770238";                 #_mrinal sir
            $mobile = "8906128883";                    #_rohan sir
        }
       return $res=SMSJHGOVT($mobile, $message, $templateid);
        print_var($message);
        // $res=SMSJHGOVT($mobile, $message, $templateid);
        return []; //$res;
    }
}