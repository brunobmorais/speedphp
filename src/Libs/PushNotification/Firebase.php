<?php
namespace App\Libs\PushNotification;

class Firebase {

    public function send($registration_ids, $message) {
        $fields = array(
            'registration_ids' => $registration_ids,
            'notification' => $message,
        );
        return $this->sendPushNotification($fields);
    }

    /*
    * This function will make the actuall curl request to firebase server
    * and then the message is sent
    */
    private function sendPushNotification($fields) {

        define('FIREBASE_API_KEY', 'AAAADQJu17k:APA91bEsyfZoG_bVEhi-krgqABlEe-zB6HU1xJU2BtUeav_PlZYNKNoVdm-zkszZ3I-i-e7SnlrU3DCosEX3xxsAWiizROubY6cs_fNh9I1h0m6yHarfVKpnzOSSAUSl3o7Mal7IoKME');

        //firebase server url to send the curl request
        $url = 'https://fcm.googleapis.com/fcm/send';

        //building headers for the request
        $headers = array(
            'Authorization: key=' . FIREBASE_API_KEY,
            'Content-Type: application/json'
        );

        //Initializing curl to open a connection
        $ch = curl_init();

        //Setting the curl url
        curl_setopt($ch, CURLOPT_URL, $url);

        //setting the method as post
        curl_setopt($ch, CURLOPT_POST, true);

        //adding headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //disabling ssl support
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        //adding the fields in json format
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        //finally executing the curl request
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        //Now close the connection
        curl_close($ch);

        //and return the result
        return $result;
    }
}