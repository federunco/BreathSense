<?php
function sign ($key, $msg){
	return bin2hex(hash_hmac("sha256", $msg, $key, true));
}

function sha256 ($msg) {
	return bin2hex(hash("sha256", $msg, true));
}

function getSignatureKey($key, $dateStamp, $regionName, $serviceName){
    $kDate = hash_hmac("sha256", $dateStamp, "AWS4".$key, true);
    $kRegion = hash_hmac("sha256", $regionName, $kDate, true);
    $kService = hash_hmac("sha256", $serviceName, $kRegion, true);
    $kSigning = hash_hmac("sha256", "aws4_request", $kService, true);
    return $kSigning;
}

function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}


function createEndpoint($regionName, $awsIotEndpoint, $accessKey, $secretKey){
    $currentDateTime = new DateTime('UTC');
    $amzdate = $currentDateTime->format('Ymd\THis\Z');
    $dateStamp = $currentDateTime->format('Ymd');
    $service = 'iotdevicegateway';
    $region = $regionName;
    $secretKey = $secretKey;
    $accessKey = $accessKey;
    $algorithm = 'AWS4-HMAC-SHA256';
    $method = 'GET';
    $canonicalUri = '/mqtt';
    $host = $awsIotEndpoint;
    
    $credentialScope = $dateStamp . '/' . $region . '/' . $service . '/' . 'aws4_request';
    $canonicalQuerystring = 'X-Amz-Algorithm=AWS4-HMAC-SHA256';
    $canonicalQuerystring .= '&X-Amz-Credential=' . encodeURIComponent($accessKey . '/' . $credentialScope);
    $canonicalQuerystring .= '&X-Amz-Date=' . $amzdate;
    $canonicalQuerystring .= '&X-Amz-SignedHeaders=host';
    $canonicalHeaders = 'host:' . $host . "\n";
    $payloadHash = sha256('');
    $canonicalRequest = $method . "\n" . $canonicalUri . "\n" . $canonicalQuerystring . "\n" . $canonicalHeaders . "\nhost\n" . $payloadHash;
    $stringToSign = $algorithm . "\n" . $amzdate . "\n" .  $credentialScope . "\n" .  sha256($canonicalRequest);
    $signingKey = getSignatureKey($secretKey, $dateStamp, $region, $service);
    $signature = sign($signingKey, $stringToSign);
    $canonicalQuerystring .= '&X-Amz-Signature=' . $signature;
    return  "wss://" . $host . $canonicalUri . '?' . $canonicalQuerystring;
}

$finalEndpoint = createEndpoint(
        $AWS_IOT_REGION,
        $AWS_IOT_ENDPOINT, 
        $AWS_IAM_ACCESSKEY,
        $AWS_IAM_SECRETKEY
);
?>