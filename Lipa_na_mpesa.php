<?php

function get_authorization_token($base_url,$consumer_key,$consumer_secret){
    
    $encoded_key=base64_encode($consumer_key.":".$consumer_secret);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $base_url."/mpesa/stkpush/v1/processrequest",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ".$encoded_key,
            "Cache-Control: no-cache",
            "Content-Type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        // $this->ci->session->set_flashdata('error',$err);
        return FALSE;
    } else {
        $res = json_decode($response);
        if($res){
            return $res->access_token;
        }
    }
}

?>