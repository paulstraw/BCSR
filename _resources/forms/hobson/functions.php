<?php

//trim return string to get specific value
function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
};

//return interger
function begins_with($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

function sendData() {
    global $return, $data_contact, $url_curl, $userpwd, $content, $to_error , $headers, $modify;
    
    // Pass JSON to web server
    $ch = curl_init($url_curl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    if ($modify == 'True') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($content))); //prep data for put by including content length
    }
    else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    };
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($modify == 'True') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");     
    }
    else {
        curl_setopt($ch, CURLOPT_POST, 1);
    };
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    curl_close($ch);
    var_dump($return);
    
    /*
    $ch = curl_init($url_curl);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $return = curl_exec($ch);
    curl_close($ch);
    var_dump($return);
    */
    errorCheck();
};
function errorCheck() { 
    //check for error
    
    global $return, $data_contact, $url_curl, $userpwd, $content, $to_error , $headers, $modify, $url_contacts, $entityID;
    
    $status = 'default';
    $status = get_string_between($return, 'status":"', '"');    
    $errorMessage = get_string_between($return, 'message":"', '"');
    if ($status !== "ok") {
        if (begins_with($errorMessage, "A duplicate record has been found")) {
            $modify = 'True';
            //if contacts table
            if ($url_curl == $url_contacts) {
                $entityID = filter_var($errorMessage, FILTER_SANITIZE_NUMBER_INT);
            };
            $url_curl = $url_curl . '/' . $entityID;
            sendData();
        }
        else {
        //set email variables
        $subject ='Error: Hobson Radius Form';
        $message = 'There has been an error on the Hobson Radious Form Submission
            
            Status: ' . $status .
            '
            ***Modify: ' . $modify .
            '
            ***Error: ' . $errorMessage .
            '
            ***url:' . $url_curl .
            '
            ***entityID:' . $entityID .
            
            '
            ***error return:' . $return  . 
            '
            
            data send:' . print_r( $data_contact, true ) 
        ;
        //send email
        mail ( $to_error , $subject , $message, $headers );
        };
    };
}

?>
