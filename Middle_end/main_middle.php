<?php

include("middle_functions.php");

//receiving data from front;QId and student answer
$input= file_get_contents('php://input');
parse_str($input, $data);
$task= $data["task"];

if ($task == "grade")
{
	$testName= $data["testName"];
	$ucid= $data["ucid"];
	$stdAnswerArray= $data["studentExam"];
	
	for( $k= 0; $k < count($stdAnswerArray); $k+=2)
	{
		$QId= $stdAnswerArray[$k];
		$stdAnswer= $stdAnswerArray[$k+1];
		
		//sending QId of the question to back to get grading information 
		$data=array("task"=>$task, "QId"=>$QId, "testName"=>$testName); 
		$queryData= http_build_query($data); 
		
		$QuestionInfo= passToBack($queryData);
		$QuestionInfo= json_decode($QuestionInfo);
	
		$finalGradeInfo= grade($stdAnswer, $QuestionInfo);
		
		$gradeData= array("task"=>"saveScore", "testName"=>$testName, "ucid"=>$ucid, "finalGrade"=>$finalGradeInfo);
		$gradeDataQuery= http_build_query($gradeData);
		
		$resFromBack= passToBack($gradeDataQuery);		
	}
	echo $resFromBack;
}
else
{
	$resFromBack= passToBack($input);
	echo $resFromBack;
}

?>
