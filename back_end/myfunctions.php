<?php


//a function to prevent injection to database
function get_field($fieldValue){
	if( !isset($fieldValue) || $fieldValue == "" || $fieldValue == "NULL"){
		return NULL;
	}
	else{
		return $fieldValue;
	}
	
}

function insert_question($Qdata, $db){

//get_field function created in myfunctions.php     
$difficulty= get_field($Qdata['difficulty']);  
$type= get_field($Qdata['type']);
$funcName= get_field($Qdata['FuncName']);
$arg1= get_field($Qdata['arg1']);
$arg2= get_field($Qdata['arg2']);
$arg3= get_field($Qdata['arg3']);
$text= get_field($Qdata['text']);
$testCase1= get_field($Qdata['testCase1']);
$output1= get_field($Qdata['output1']);
$testCase2= get_field($Qdata['testCase2']);
$output2= get_field($Qdata['output2']);
$testCase3= get_field($Qdata['testCase3']);
$output3= get_field($Qdata['output3']);
$testCase4= get_field($Qdata['testCase4']);
$output4= get_field($Qdata['output4']);
$testCase5= get_field($Qdata['testCase5']);
$output5= get_field($Qdata['output5']);
$testCase6= get_field($Qdata['testCase6']);
$output6= get_field($Qdata['output6']);
$endOfFunc= get_field($Qdata['endOfFunc']);

//inserting information of the new question to test bank
$i= "insert into Qbank (Difficulty, Type, FunctionName, Argument_1, Argument_2, Argument_3, Qtext, TestCase_1, ExpectedOutput_1, TestCase_2, ExpectedOutput_2, TestCase_3, ExpectedOutput_3, TestCase_4, ExpectedOutput_4, TestCase_5, ExpectedOutput_5, TestCase_6, ExpectedOutput_6, endOfFunc)  VALUES('$difficulty', '$type', '$funcName', '$arg1' , '$arg2', '$arg3', '$text', '$testCase1', '$output1', '$testCase2', '$output2', '$testCase3', '$output3', '$testCase4', '$output4', '$testCase5', '$output5', '$testCase6', '$output6', '$endOfFunc')";   
$m= mysqli_query ( $db, $i );

if ($m == TRUE) {
	$response =array( "New question was inserted to Question Bank");
	   
	}
else{
	$response =array( mysqli_error($db) );
}

$json_response= json_encode($response);
echo $json_response;
}



function get_fields($db, $result, &$output){
	while ( $l= mysqli_fetch_array ($result, MYSQLI_ASSOC) )
	{
		$questionData= array();
			
		$QId= $l['QuestionID'];
		$difficulty= $l['Difficulty'];
		$type= $l['Type'];
		$Qtext= $l['Qtext'];
		$funcName= $l['FunctionName'];
	
		$questionData= array($QId, $difficulty, $type, $Qtext, $funcName);
			
		array_push($output, $questionData);
	}	
}


function display_Qbank($db){
	$output= array();
	$q= "SELECT * FROM Qbank order by QuestionID";
	$result= mysqli_query ( $db, $q );
	if($result == FALSE){
		$output= array( mysqli_error($db) );
		echo json_encode($output);
		exit();
	} 
	
	$num_questions= mysqli_num_rows($result);
	if ($num_questions == 0)
	{
		$output= array("Question bank is empty...");
	}
	else
	{	
		$output= array();
		get_fields($db, $result, $output);
		echo json_encode($output);	
	}
}


function filter_Qbank($db, $field, $filter){
	
	if ($field == "Qtext"){
		$q= "SELECT * FROM Qbank WHERE Qtext LIKE '%$filter%' order by QuestionID";
	}
	else{
		$q= "SELECT * FROM Qbank WHERE $field= '$filter' order by QuestionID";
	}	
	
	$result= mysqli_query($db, $q);
	
	if($result == TRUE){
		$output= array();
		get_fields($db, $result, $output);
		echo json_encode($output);	
	}
	else{
		echo json_encode(mysqli_error($db));
	}
}


function createExam($Qarray, $db, $tableName){
	$createTable= "CREATE TABLE $tableName (Qindex INT(11) AUTO_INCREMENT, QID INT(11), FunctionName VARCHAR(255), Type VARCHAR(255), Argument_1 VARCHAR(255), Argument_2 VARCHAR(255), Argument_3 VARCHAR(255), Qtext MEDIUMTEXT, TestCase_1 VARCHAR(1000), ExpectedOutput_1 VARCHAR(1000), TestCase_2 VARCHAR(1000), ExpectedOutput_2 VARCHAR(1000), TestCase_3 VARCHAR(1000) DEFAULT 'NULL', ExpectedOutput_3 VARCHAR(1000) DEFAULT 'NULL' , TestCase_4 VARCHAR(1000) DEFAULT 'NULL', ExpectedOutput_4 VARCHAR(1000) DEFAULT 'NULL', TestCase_5 VARCHAR(1000) DEFAULT 'NULL', ExpectedOutput_5 VARCHAR(1000) DEFAULT 'NULL', TestCase_6 VARCHAR(1000) DEFAULT 'NULL', ExpectedOutput_6 VARCHAR(1000) DEFAULT 'NULL', endOfFunc VARCHAR(255), maxScore int(11), PRIMARY KEY (Qindex), FOREIGN KEY (QID) REFERENCES Qbank(QuestionID));";
	$createResult= mysqli_query ( $db, $createTable );
	
	if ($createResult == TRUE) 
	{
		$response =array( "New Exam is created...");
		$appendExam= "INSERT INTO examNames (examName) VALUES ('$tableName')";
		$appendResult= mysqli_query ( $db, $appendExam );
		
		if ( $appendResult == FALSE){
			$response =mysqli_error($db);
			exit;
		}
		
		
		for( $i= 0; $i < count($Qarray); $i++)
		{
			$QId= $Qarray[$i][0];
			$maxScore= $Qarray[$i][1];
			$addQues= "INSERT INTO $tableName (QID, FunctionName, Type, Argument_1, Argument_2, Argument_3, Qtext, TestCase_1, ExpectedOutput_1, TestCase_2, ExpectedOutput_2, TestCase_3, ExpectedOutput_3, TestCase_4, ExpectedOutput_4, TestCase_5, ExpectedOutput_5, TestCase_6, ExpectedOutput_6, endOfFunc) SELECT QuestionID, functionName, Type, Argument_1, Argument_2, Argument_2, Qtext, TestCase_1, ExpectedOutput_1, TestCase_2, ExpectedOutput_2, TestCase_3, ExpectedOutput_3, TestCase_4, ExpectedOutput_4, TestCase_5, ExpectedOutput_5, TestCase_6, ExpectedOutput_6, endOfFunc FROM Qbank WHERE QuestionID= '$QId'"; 
			$addResult= mysqli_query ( $db, $addQues );
		
			if($addResult == TRUE)
			{
				$response= array( "New questions are inserted to $tableName...");
				
				$addMaxScore= "UPDATE $tableName SET maxScore= $maxScore WHERE QID= '$QId'";
				$addMax= mysqli_query ( $db, $addMaxScore );
				
				if($addMax == TRUE){
					$response= array( "New questions are inserted to $tableName...");
				}
				else{
					$response= mysqli_error($db);
				}
			}
			else
			{
				$response =array( mysqli_error($db) );
			}
		}
	}
	else
	{
		$response = mysqli_error($db);
	}
	
	echo json_encode ($response);
}


function display_exam($db, $tableName){	
	$q= "SELECT * FROM $tableName";
	$result= mysqli_query ( $db, $q );
	//$num_questions= mysqli_num_rows($result);
	
	if ($result == FALSE)
	{
		$response =array( mysqli_error($db) );
		echo json_encode($response);
		exit();	
	}
	else
	{
		$examArray= array();
		while ( $l= mysqli_fetch_array ($result, MYSQLI_ASSOC) )
		{
			$question= array();
			$text= $l["Qtext"];
			$QId= $l["QID"];
			$maxScore= $l["maxScore"];
			$question= array($QId, $text, $maxScore);
			array_push($examArray,$question);
		}
	}
	echo json_encode($examArray);
}


function getGradingInfo($QId, $db, $testName){
	
	$q= "SELECT * FROM $testName WHERE QID= '$QId'";
	$result= mysqli_query ( $db, $q );
	
	if ($result == FALSE){
		$Info= array (mysqli_error($db) );
		echo json_encode($Info);
		exit();
	}  
	else
	{
		$l= mysqli_fetch_array ($result, MYSQLI_ASSOC); 
		
	    $QID= $l["QID"];   
	    $funcName= $l["FunctionName"];
		$Qtext= $l["Qtext"];
		$type=$l["Type"];
		$TestCase1= $l["TestCase_1"];
		$output1= $l["ExpectedOutput_1"];
		$TestCase2= $l["TestCase_2"];
		$output2= $l["ExpectedOutput_2"];
		$TestCase3= $l["TestCase_3"];
		$output3= $l["ExpectedOutput_3"];
		$TestCase4= $l["TestCase_4"];
		$output4= $l["ExpectedOutput_4"];
		$TestCase5= $l["TestCase_5"];
		$output5= $l["ExpectedOutput_5"];
		$TestCase6= $l["TestCase_6"];
		$output6= $l["ExpectedOutput_6"];
		$endOfFunc= $l["endOfFunc"];
		$maxScore= $l["maxScore"];
		
		 
		$testCasesArray= array(array($TestCase1, $output1), array($TestCase2, $output2), array($TestCase3, $output3), array($TestCase4, $output4), array($TestCase5, $output5), array($TestCase6, $output6));	
		$Info= array($QID, $funcName, $Qtext, $type, $testCasesArray, $endOfFunc, $maxScore );
	}
	echo json_encode($Info);
}



function addScore($db, $testName, $scoreData, $ucid)
{
	$checkTable= " SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'yta5' AND table_name = scores ";
	$checkTableResult= mysqli_query ( $db, $checkTable );
	
	if ( $checkTableResult == 0 ){
		$createTable= "CREATE TABLE scores (stdID VARCHAR(30) COLLATE latin1_swedish_ci, test VARCHAR (255), QID INT(11), Qtext MEDIUMTEXT, answer MEDIUMTEXT, comment MEDIUMTEXT, score INT(11), isReleased VARCHAR(10) DEFAULT 'NO',PRIMARY KEY (stdID, QID, test), FOREIGN KEY (QID) REFERENCES Qbank(QID), FOREIGN KEY (stdID) REFERENCES Students (UCID));";
		$createResult= mysqli_query ( $db, $createTable );
		
		if ($createResult == TRUE){
			$response= "scores table is created...";	
		}
		else{
			$response= array( mysqli_error($db) );	
		}
	}
	
	$QID= $scoreData[0];
	$Qtext= $scoreData[1];
	$Qtext= mysqli_real_escape_string($db, $Qtext);
	$answer= $scoreData[2];
	$answer= mysqli_real_escape_string($db, $answer);
	$comment= $scoreData[3];
	$comment= mysqli_real_escape_string($db, $comment);
	$score= $scoreData[4];
		
	$q= "INSERT INTO scores (stdID, test, QID, Qtext, answer, comment, score) VALUES ('$ucid', '$testName', '$QID', '$Qtext', '$answer', '$comment', '$score')";
	$result= mysqli_query ( $db, $q );
	
	if ($result == TRUE){
		$response= "Your Exam was successfully submitted!<br>";	
	}
	else{
		$response= array( mysqli_error($db) );	
	}	  
		
	echo json_encode($response);
}


function releaseScore($db, $testName, $ucid){
	$value= 'YES';
	$release= "UPDATE scores SET isReleased= '$value' WHERE test= '$testName' AND stdID= '$ucid' ";
	$result= mysqli_query ( $db, $release );
				
	if($result == TRUE){
		$response= array( "Score is released");
	}
	else{
		$response= array( mysqli_error($db) );
	}
	
	echo json_encode($response);
}


function viewScore($db, $ucid, $testName, $user){
	
	$q= "SELECT * FROM scores WHERE stdID='$ucid' AND test= '$testName'";
	$result= mysqli_query($db, $q);
	
	if($result == FALSE){
		$Info=( mysqli_error($db) );
		echo json_encode($Info);
		exit();
	} 
	
	$num= mysqli_num_rows($result);
	if ($num == 0)
	{
		$Info= json_encode( "The information does not exist..<br>You did not take $testName exam...");
	}
	else
	{	
		$Info= array();
		while ( $l= mysqli_fetch_array ($result, MYSQLI_ASSOC) )
		{	
			if ($user == 'student'){
				$released= $l["isReleased"];
				if ( $released == 'NO'){
					echo json_encode("Score is not released yet..");
					exit();
				}
			}
			$row= array();
			$stdID= $l["stdID"];
			$QID= $l["QID"];
			$comment= $l["comment"];
			$score= $l["score"];
			$answer= $l["answer"];
			$Qtext= $l["Qtext"];
			
			array_push($row, $stdID, $QID, $comment, $score, $answer, $Qtext);
			array_push($Info, $row);
			
		}
	}	

	echo json_encode($Info);
}

    
//a function to update the score and comment in scores table
function editScore ($db, $ucid, $testName, $updatedScore){
	
	for ( $i= 0; $i < count($updatedScore); $i++)
	{
		$QId= ($updatedScore[$i][0]);
		$comment= ($updatedScore[$i][1]);
		$comment= mysqli_real_escape_string($db, $comment);
		$score= ($updatedScore[$i][2]);
		
		$query= "UPDATE scores SET comment= '$comment', score= '$score' WHERE QID= '$QId' AND test= '$testName' AND stdID= '$ucid' ";
		$queryResult= mysqli_query ( $db, $query );
				
		if($queryResult == TRUE)
		{
			$response= array( "The update is successful...");
		}
		else
		{
			$response= array( mysqli_error($db) );	
		}
	}		
	echo json_encode($response);
}


function getExamNames($db){
	
	$q= "SELECT * FROM examNames";
	$result= mysqli_query($db, $q) or die (mysqli_error($db) );
	$examNames= array();
	
	while ( $l= mysqli_fetch_array ($result, MYSQLI_ASSOC) )
	{	
		$name= $l["examName"];	
		array_push($examNames, $name);
	}
	
	echo json_encode($examNames);
}

    
function delete_table($db, $tableName){
$q1= "DROP TABLE $tableName";
$result1= mysqli_query($db, $q1);

if ($result1 == FALSE)
{
	echo json_encode( mysqli_error($db) );
} 
else
{
	$q2= "DELETE FROM examNames WHERE examName= '$tableName'";
	$result2= mysqli_query($db, $q2);
	if ($result2 == FALSE)
	{
		echo json_encode( mysqli_error($db) );
		exit();
	} 
	
	echo json_encode("$tableName is removed!");
}

}
?>




