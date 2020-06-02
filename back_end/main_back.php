<?php

include ( "account.php" );
include ("myfunctions.php");

//connecting to database
$db = mysqli_connect($hostname, $username, $password, $project);
if (mysqli_connect_errno ())
{ echo "Failed to connect to MySQL: " . mysqli_connect_error ( ); 
  exit ();
}
mysqli_select_db ($db, $project);

$input_from_middle= file_get_contents('php://input');
parse_str($input_from_middle, $data);

$choice= $data['task'];
    
if ($choice == 'viewQues')
{
	display_Qbank($db);
	
}
else if($choice == 'addQues')
{
	insert_question($data,$db);  
}
else if($choice == 'createExam')
{
	$tableName= get_field($data['tableName']);
	$Qarray= get_field($data['questions']);
	createExam($Qarray, $db, $tableName);	
}
else if ($choice == 'takeExam'){
	
	$tableName= get_field($data['tableName']);
	display_exam($db, $tableName);
}
else if($choice == 'grade')
{
	$testName= get_field($data['testName']);
	$QId= get_field($data['QId']);
	
	getGradingInfo($QId, $db, $testName);
}
else if( $choice == 'saveScore')
{
	$ucid= get_field($data['ucid']);
	//$scoreData is an array of arrays that holds each question's ($QId, $answer, $comment, $score)
	$scoreData= get_field($data['finalGrade']);
	$testName= get_field($data['testName']);
	
	addScore($db, $testName, $scoreData, $ucid);
}
else if ( $choice == 'viewScore' ){
	$user= get_field($data['user']);
	$ucid= get_field($data['ucid']);
	$testName= get_field($data['testName']);
	
	viewScore($db, $ucid, $testName, $user);
}
else if ( $choice == 'updateScore' ){
	//updatedScore is an array that holds (QID, comment, score)
	$updatedScore= get_field($data['updatedScore']);
	$ucid= get_field($data['ucid']);
	$testName= get_field($data['testName']);
	
	editScore ($db, $ucid, $testName, $updatedScore);
}
else if ($choice == 'getExam'){
	getExamNames($db);
}
else if ($choice == 'releaseScore'){
	$ucid= get_field($data['ucid']);
	$testName= get_field($data['testName']);
	releaseScore($db, $testName, $ucid);
}
else if($choice == 'filter'){
	
	$field= get_field($data['field']);
	$filter= get_field($data['filter']);

	filter_Qbank($db, $field, $filter);
	
}

?>
