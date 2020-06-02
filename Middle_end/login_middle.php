<?php


$input= file_get_contents('php://input');
parse_str($input, $data);
    
$ucid= $data['ucid'];
$pw= $data['pw'];

$data_to_back= array("ucid"=> $ucid, "password"=>$pw);
$queryData= http_build_query($data_to_back);
    
$url = "https://web.njit.edu/~yta5/CS490/beta/login.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $queryData);
$response = curl_exec($ch);
curl_close ($ch);
	
echo $response;
?>
