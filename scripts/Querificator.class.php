<?php 

/* This class generates simple math questions to be used as a CAPTCHA style
verification tools. 

March, 2012. 
Brandon Foltz
*/

class Querificator
{

	public function __constructor($iMode = 0)
	{
		//herp
	}
	
	/* This function generates a random addition, subtraction, or multiplication
	problem. The (string) question is placed in $sQuestion and the answer is
	returned (as a string to be compared with user input). */
	public function generateQuestion(&$sQuestion)
	{
		$operator = rand(0,2);
		
		$term1 = rand(0,10);
		$term2 = rand(0,10);
		
		if ($operator == 0)
		{
			$answer = $term1 + $term2;
			$sQuestion = strval($term1)." + ".strval($term2);
		}
		else if ($operator == 1)
		{
			$answer = $term1 - $term2;
			$sQuestion = strval($term1)." - ".strval($term2);
		}
		else if ($operator == 2)
		{
			$answer = $term1 * $term2;
			$sQuestion = strval($term1)." x ".strval($term2);
		}
			
		return strval($answer);
	}

}
