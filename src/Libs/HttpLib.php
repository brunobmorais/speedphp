<?php
namespace App\Libs;


class HttpLib
{

    public function __construct()
    {
    }

    /********** REST ******************/
    /**
     * Realiza uma requisição GET
     * @access private
     * @param $url string
     * @return array
     */
    public static function get($url,$header = array('Content-Type: application/json')) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response['body'] = curl_exec($ch);
        $response['info'] = curl_getinfo($ch);

        curl_close($ch);

        return $response;

    }

    /**
     * Realiza uma requisição POST
     * @access private
     * @param $url string
     * @param $data array
     * @return array
     */
    public static function post($url, $data = [],$header = array('Content-Type: application/json')) {

        if (in_array( 'Content-Type: application/json',$header ))
            $data = json_encode($data);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $curl_response = curl_exec($curl);

        $response['info'] = curl_getinfo($curl);
        $response['body'] = $curl_response;

        curl_close($curl);

        return $response;
    }

    /**
     * Realiza uma requisição PUT
     * @access private
     * @param $url string
     * @param $data array
     * @return array
     *    array(
     *   'Content-Type: application/json',
     *   'Authorization: Bearer ' . $token)
     */
    public static function put($url, $data = [], $header = array('Content-Type: application/json')) {

        if (in_array( 'Content-Type: application/json',$header ))
            $data = json_encode($data);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $curl_response = curl_exec($curl);
        $response['info'] = curl_getinfo($curl);
        $response['body'] = $curl_response;
        curl_close($curl);

        return $response;
    }

    /**
     * Realiza uma requisição PUT
     * @access private
     * @param $url string
     * @param $data array
     * @return array
     */
    public static function delete($url, $data = [], $header = array('Content-Type: application/json')) {

        if (in_array( 'Content-Type: application/json',$header ))
            $data = json_encode($data);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl,CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $curl_response = curl_exec($curl);
        $response['info'] = curl_getinfo($curl);
        $response['body'] = $curl_response;
        curl_close($curl);

        return $response;
    }

}