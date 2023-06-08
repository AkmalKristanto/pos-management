<?php
namespace App\Http\Helpers;

class PushNotif
{
    public static function send_notification_FCM($notification_id, $title, $message, $id, $type, $click_action, $data, $status) 
    {
        $accesstoken = env('FCM_KEY');
        // dd($accesstoken);
        $URL = 'https://fcm.googleapis.com/fcm/send';
        $url_icon = '';
        $img_url = '';
            $post_data = '{
                "to" : "' . $notification_id . '",
                "data" : '. json_encode($data) .',
                "notification" : {
                     "body" : "' . $message . '",
                     "title" : "' . $title . '",
                      "type" : "' . $type . '",
                     "id" : "' . $id . '",
                     "message" : "",
                    "icon" : "'.$url_icon.'",
                    "image" : "'.$img_url.'",
                    "sound" : "default",
                    "click_action" "'.$click_action.'",
                    },
     
              }';
              dd($post_data);
        $crl = curl_init();
     
        $headr = array();
        $headr[] = 'Content-type: application/json';
        $headr[] = 'Authorization: ' . $accesstoken;
        curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, false);
     
        curl_setopt($crl, CURLOPT_URL, $URL);
        curl_setopt($crl, CURLOPT_HTTPHEADER, $headr);
     
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        // dd($crl);
        $rest = curl_exec($crl);
        dd($rest);
        if ($rest === false) {
            // throw new Exception('Curl error: ' . curl_error($crl));
            //print_r('Curl error: ' . curl_error($crl));
            $result_noti = 0;
        } else {
     
            $result_noti = 1;
        }
     
        //curl_close($crl);
        //print_r($result_noti);die;
        return $result_noti;
    }
}