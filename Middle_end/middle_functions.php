<?php

function passToBack($incoming){
	$url = "https://web.njit.edu/~yta5/CS490/beta/main_back.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $incoming);
	$response = curl_exec($ch);
	curl_close ($ch);
	return $response;
	
}

function runCode($fileName, $callFunc, $answer)
{
	file_put_contents("$fileName", $answer);   //create file and add answer
	file_put_contents("$fileName", $callFunc, FILE_APPEND);    //append the call to function based on the testcase
    $command = "python $fileName";
    $output= shell_exec($command);
    return $output;
}

function checkTestCase($functionName, $endOfFuncUsed, $testCaseArray, $fileName, $stdScore, $answer, &$comment, $assignedGrade)
{
	for ($i= 0; $i < count($testCaseArray); $i++)
    {
    	$testCase= ($testCaseArray[$i][0]);
    	$expectedOutput= ($testCaseArray[$i][1]);
    	$num= $i+1;
    	
    	if ($endOfFuncUsed != "print")
    	{
    	$callFunc= "\n\nprint($functionName($testCase))";
    	}
    	else
    	{
    	 	$callFunc= "\n\n$functionName($testCase)";
    	}	
    	if($testCase != NULL && $testCase != "undefined"){
    		$output= (runCode($fileName, $callFunc, $answer));
        	
        	//note: when we get output of running the code a \n is appended to it. therefore we appended \n to expectedOutput to make it equal in terms of format 
        	if($output == "$expectedOutput\n")
        	{        	
       			 $comment.="(-0) Great Job correct output for testcase $num<br>";
       		}
       		else
       		{
       			$stdScore-= $assignedGrade;
       			$comment.="(-$assignedGrade) InCorrect output for testcase $num: Your output is: $output ==> The expected output is: $expectedOutput<br>";
       		}
    	}
    }
    return $stdScore;
}


function grade($stdAnswer, $QuestionInfo)
{
	
	//question data received from backend
	$QID= $QuestionInfo[0];
	$functionName= $QuestionInfo[1];
	$Qtext= $QuestionInfo[2];
	$type= $QuestionInfo[3];
	$testCaseArray= $QuestionInfo[4];
	$endOfFunction= $QuestionInfo[5];
	$maxGrade=$QuestionInfo[6];
    $stdScore= $maxGrade;
    
    
	
    $currentDirectory = dirname(__FILE__);
	$fileName = "$currentDirectory/sampleFile.py";
	chmod("$fileName", 0777); 
	
	$comment= ""; 

    
    //Checking whether answer is correct
    //1.1 checking whether the function name matches what is in the database
    $correctedAnswer= $stdAnswer;
    $funcNameCheck= strstr($stdAnswer, $functionName);
    if(!$funcNameCheck)
    {  
        $pos=strpos($stdAnswer,"(");
        $wrongName=substr($stdAnswer,4,($pos-4));
        $correctedAnswer=str_replace($wrongName,$functionName,$stdAnswer);
        
        $stdScore-= 5;
        $comment.= "(-5) Incorrect function name.<br>";
    }
    else
    {
    	$comment.= "(-0) Great Job correct function name.<br>"; 
    }
        
    //1.2 checking for the use of : in the answer after function declaration
    $position= strpos($stdAnswer, ")"); 
    if($stdAnswer[$position+1] != ":" )
    {
    	$stdScore-= 3;
    	$comment.= "(-3) A colon is missing.<br>";
        $numReplacement= 1;
        $correctedAnswer=substr_replace( $correctedAnswer,":\n", $position+1, strlen($numReplacement));
    }
    else{
    	$comment.= "(-0) You used a colon for proper syntax.<br>";
    }
            
    //constraints checking      
    //need to ask: what if endOfFunc= print and user used return? how shoud we call the function? calling the function will differ in the two cases        
   // 2. checking for print/return
   	$endOfFuncUsed= $endOfFunction;
    $endFuncCheck=strstr($stdAnswer, $endOfFunction);
    if(!$endFuncCheck)
    {
        $stdScore-= 3;
        $comment.="(-3) You did not use $endOfFunction in your answer.<br>";
        if ( $endOfFunction == "return")
        {
        	$endOfFuncUsed= "print";
        }	
        else 
        {
        	$endOfFuncUsed= "return";
        }   
    }
    else
    {
    	$comment.= "(-0) Great Job You used $endOfFunction in your answer.<br>";
    }
    
    $testCasesScore= ($maxGrade - 5 - 3 - 3);
    
   //3. checking for using for or while loop
    if ($type == "for" || $type == "while")
    {
    	$testCasesScore-= 3;
    	
    	$found= strstr($stdAnswer, $type);
    	if(!$found)
    	{
    		$stdScore-= 3;
    		$comment.= "(-3)You did not use $type loop in your answer.<br>";
    	}
    	else
    	{
    		$comment.= "(-0) Great Job Correct use of $type loop in your answer.<br>";	
    	}
    } 
  	
  	//testing how many test cases we have so we can assign the grade properly
  	$testCaseNum= 0;
  	for ($i= 0; $i < count($testCaseArray); $i++)
    {
    	$testCase= ($testCaseArray[$i][0]);
  		if($testCase != NULL && $testCase != "undefined")
    	{
    		$testCaseNum+= 1;
    	}
    }
  	
  	$assignedGrade= $testCasesScore / $testCaseNum;
  	
    $stdScore= checkTestCase($functionName, $endOfFuncUsed, $testCaseArray, $fileName, $stdScore, $correctedAnswer, $comment, $assignedGrade, $assignedGrade);  
    $comment.= "<br>Student Final Score is: $stdScore.<br>";   
    
    $scoreInfo= array($QID, $Qtext, $stdAnswer, $comment, $stdScore);


	return $scoreInfo;
}

?>
