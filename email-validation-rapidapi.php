<?php

/*
Plugin Name: Email Validation Rapid API
Description: Email Validation from Rapid API when user fill email in form it is register in wordpress, and verify them.
Version: 1.0
Author: Raghavendra Shukla
Author URI: https://raghavspn.wordpress.com/
*/




/*------------------ Check Email from RAPIDAPI ------------------*/
function custom_rapidapi_filter( $errors, $sanitized_user_login, $user_email ) {
    $curl = curl_init();
    $url = "https://mailcheck.p.rapidapi.com/?domain=".$user_email;
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "x-rapidapi-host: mailcheck.p.rapidapi.com",
        "x-rapidapi-key: 71d9bb5227msh06e2f77142d557bp17d56fjsne7974fe9e1fe"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $response = json_decode($response, true);
}
if($response->valid != 1){
    $reason = $response['reason'];
    if(empty($reason)){
        $reason = $response['text'];
    }
    $er='<strong>ERROR</strong>: '.$reason;
    $errors->add( 'demo_error',$er);  
}
return $errors;
}

add_filter( 'registration_errors', 'custom_rapidapi_filter', 10, 3 );





