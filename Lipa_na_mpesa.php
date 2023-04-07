<?php

function get_authorization_token(){

    $base_url=env("BASE_URL");
    $consumer_key=env("CONSUMER_KEY");
    $consumer_secret=env("CONSUMER_SECRET");
    
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



function mpesa_stk_push($BusinessShortCode='',$Password='',$Timestamp=0,$TransactionType='',$Amount='',$PartyA='',$PartyB='',$PhoneNumber='',$CallBackURL='',$AccountReference='',$TransactionDesc=''){
        
                $post_fieds = json_encode(array(
                        "BusinessShortCode"=>$BusinessShortCode,
                        "Password"=>$Password,
                        "Timestamp"=> $Timestamp,
                        "TransactionType"=> $TransactionType,
                        "Amount"=> $Amount,
                        "PartyA"=> $PartyA,
                        "PartyB"=> $PartyB,
                        "PhoneNumber"=> $PhoneNumber,
                        "CallBackURL"=> "https://www.paxcoins.com/callback.php",
                        "AccountReference"=> $AccountReference,
                        "TransactionDesc"=> $TransactionDesc 
                    
                   
                ));
                if($token = $this->get_authorization_token()){
                    $url = env("BASE_URL")."/mpesa/stkpush/v1/processrequest";
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>$post_fieds,
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer".$token,
                        "Content-Type: application/json"
                    ),
                    ));
                    $response = curl_exec($curl);
                    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    $err = curl_error($curl);
                    curl_close($curl);
                    if ($err) {
                        
                        return FALSE;
                    } else {                    
                        if($response){
                            if($file = json_decode($response)){ 

                                //store data in db
                                
                                print_r($file);
                               
                            }else{
                                return FALSE;
                            }
                            //print_r($file);die;
                        }else{
                            $error = $err?:'';
                            $code = $httpcode?:'';
                            return FALSE;
                        }
                    }
                }else{
                    return FALSE;
                }
            
        
}


?>