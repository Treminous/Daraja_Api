<?php

function get_authorization_token(){

    //replace with config or environment variables;
    $base_url='';
    $consumer_key='';
    $consumer_secret='';
    $encoded_key=base64_encode($consumer_key.":".$consumer_secret);


    // send request
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
        

    //  initialize post fields
                $post_fieds = json_encode(array(
                        "BusinessShortCode"=>$BusinessShortCode,
                        "Password"=>$Password,
                        "Timestamp"=> $Timestamp,
                        "TransactionType"=> $TransactionType,
                        "Amount"=> $Amount,
                        "PartyA"=> $PartyA,
                        "PartyB"=> $PartyB,
                        "PhoneNumber"=> $PhoneNumber,
                        "CallBackURL"=> "https://www.paxcoins.com/mpesa/process_payments",
                        "AccountReference"=> $AccountReference,
                        "TransactionDesc"=> $TransactionDesc
                ));

                // if get token
                if($token = get_authorization_token()){
                    $url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
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



function process_payments()
{

// Callback data
$curl_post_data=file_get_contents("php://input");


   
// and IP Whitelisting
$whitelist = array(
'196.201.214.200',
'196.201.214.206',
'196.201.213.114',
'196.201.214.207',
'196.201.214.208',
'196.201.213.44',
'196.201.212.127',
'196.201.212.138',
'196.201.212.129',
'196.201.212.128',
'196.201.212.132',
'196.201.212.136',
'196.201.212.74',
'196.201.212.69',
'143.95.239.57',
'143.95.232.84',
'197.156.137.148',
'196.207.145.18'
);
if ((in_array($_SERVER['REMOTE_ADDR'], $whitelist))) {
 file_put_contents('mpesa_payment.txt',date('d M Y H:i:s')." ".$_SERVER['REMOTE_ADDR']." ".$curl_post_data."\n",FILE_APPEND);
 
 echo json_encode(array("rescode" =>0, "resmsg" => "Payment data received successfully."));

   //print_r($curl_post_data);
   $callbackData=json_decode($curl_post_data);
   
   
    $amount= 0;
    $mpesaReceiptNumber='';
    $transactionDate='';
    $phoneNumber='';
   
    //[{"Name":"Amount","Value":1},{"Name":"MpesaReceiptNumber","Value":"QJQ8MC5WM0"},{"Name":"TransactionDate","Value":20221026093735},{"Name":"PhoneNumber","Value":254700616558}]
     if(isset($callbackData->Body->stkCallback->CallbackMetadata->Item)){
        foreach($callbackData->Body->stkCallback->CallbackMetadata->Item as $itemIndex => $itemValue){
            if($itemValue->Name == 'Amount'){
                $amount = $itemValue->Value;
            } else if($itemValue->Name == 'MpesaReceiptNumber'){
                $mpesaReceiptNumber = $itemValue->Value;
            } else if($itemValue->Name == 'TransactionDate'){
                $transactionDate = $itemValue->Value;
            } else if($itemValue->Name == 'PhoneNumber'){
                $phoneNumber = $itemValue->Value;
            }
        }
    }
   
    $resultCode=$callbackData->Body->stkCallback->ResultCode;
    $resultDesc=$callbackData->Body->stkCallback->ResultDesc;
    $merchantRequestID=$callbackData->Body->stkCallback->MerchantRequestID;
    $checkoutRequestID=$callbackData->Body->stkCallback->CheckoutRequestID;
    $processing_status= ($resultCode==0)?1:0;
   
    $datetime = DateTime::createFromFormat('YmdHis', $transactionDate);
    // Getting the new formatted datetime
    $date= $datetime->format('Y-m-d H:i:s');
   
    //store payment information
    $order = null;
    $result = null;
    $payment_id=null;
   
    //fetch the db item
   
//check if payment exists by merchant_request_id


// print_r($sql);
// print_r($res);

// update data by merchant_request_id
   
        $result=[
           "payment_id"=>$payment_id,
            "result_desc"=>$resultDesc,
            "result_code"=>$resultCode,
            "merchant_request_id"=>$merchantRequestID,
            "checkout_request_id"=>$checkoutRequestID,
            "amount"=>$amount,
            "mpesa_receipt_number"=>$mpesaReceiptNumber,
            "date_of_transaction"=>$date,
            "phone_number"=>$phoneNumber,
            "processing_status"=>$processing_status
        ];
       
       //update the database
//         $res = $wpdb->update(
// $table_name,
// $result,
// array( 'merchant_request_id' =>$merchantRequestID)
// );

  // print_r($result);
   
   // update the order status to completed if processing status =1
   if($processing_status==1)
   {


  // send email

  send_email($email_address='',$message='');
  
   }
  $_SESSION['merchant_request_id'] =$merchantRequestID; 
}       
   
   
else
{
file_put_contents('mpesa_payment.txt',date('d M Y H:i:s')." ".$_SERVER['REMOTE_ADDR']." "."Blocked"." ".$curl_post_data."\n",FILE_APPEND);

echo json_encode(array("rescode" =>76,"developer_message"=>"fake payment record", "resmsg" => "Your IP address  has been blocked."));
}    
}


function send_email($email_address='',$message=''){


//  initialize post fields
                $post_fieds = json_encode(array(
                    
                        "From"=> "geofrey.ongidi@digitalvision.co.ke",
                        "To"=> $email_address,
                        "Subject"=> "Hello from Postmark",
                        "HtmlBody"=> $message,
                        "MessageStream"=> "notifications"
                    
                ));

                // if get token
                if($token = get_authorization_token()){
                    $url = "https://api.postmarkapp.com/email";
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
                        "Content-Type: application/json",
                        "Accept: application/json",
                        "X-Postmark-Server-Token: b1371069-335f-431b-8f45-88c22d7f1c47"
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