<?php

/*
Yassmin Ali
CS490 Project
Alpha_backend
*/

include ( "account.php" );
include ("loginFunctions.php");
//connecting to database
$db = mysqli_connect($hostname, $username, $password, $project);
if (mysqli_connect_errno ())
{ echo "Failed to connect to MySQL: " . mysqli_connect_error ( ); 
  exit ();
}
mysqli_select_db ($db, $project);

//parsing input from request
$input_from_middle= file_get_contents('php://input');
parse_str($input_from_middle, $data);

//get_field function created in myfunctions.php     
$ucid= get_field($data['ucid']);  
$pass= get_field($data['password']);




//checking whether data exist on db 
if ((check_account($ucid, $pass, $db)) == false){
	$res= array('Incorrect UCID or password...');
	$json_response = json_encode($res);
	echo $json_response;
}
else{
	$res= array("$user");
	$json_response = json_encode($res);
	echo $json_response;
}

//closing mysqli connection
mysqli_close($db);

?>