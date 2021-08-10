
<!-- Moved the Crossword Option to the top to avoid repeating. It also instantizes only once but still affects all puzzles. Removed the puzzle option as
 it is not required. @kc9718us -->
<html>
<body>
			<div class="panel-heading">
							<div class="row">
								<div class="col-sm-12">
									<div align="center"><h2>Crossword Options</h2></div>
								</div>
							</div>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-sm-12" align="center">
									<div class="col-sm-6">
										<div class="row">
											<div class="col-sm-12">
												<h3>Puzzle Options</h3>
											</div>
										</div>
										<div align="left">
											<div class="row">
												<div class="col-sm-12" >
													<input type="checkbox" class="showSolutionCheckbox" onchange="solutionCheckboxChange()" checked> Show Solution
												</div>
											</div>
											<br>
											<div class="row">
												<div class="col-sm-12">
													<input type="checkbox" class="showBlankSquaresCheckbox" name="showBlankSquares" onchange="blankSquareCheckboxChange()"> Show blank squares
												</div>
											</div>
											<br>

										</div>
									</div>
									<div class="col-sm-6">
										<div class="row">
											<div class="col-sm-12">
												<h3>Look Options</h3>
											</div>
										</div>
										<div align="left" >
											<div class="row">
												<div class="col-sm-6" >
													<label>Blank Square Color</label>
												</div>
												<div class="col-sm-6" >
													<input type="text" class='blankSquareColor' name='blankSquareColor' value='#FFFFFF'/>
												</div>
											</div>
											<br>
											<div class="row">
												<div class="col-sm-6" >
													<label>Letter Square Color</label>
												</div>
												<div class="col-sm-6" >
													<input type="text" class='letterSquareColor' name='letterSquareColor' value='#EEEEEE'/>
												</div>
											</div>
											<br>
											<div class="row">
												<div class="col-sm-6" >
													<label>Letter Color</label>
												</div>
												<div class="col-sm-6" >
													<input type="text" class='letterColor' name='letterColor' value='#000000'/>
												</div>
											</div>
											<br>
											<div class="row">
												<div class="col-sm-6" >
													<label>Line Color</label>
												</div>
												<div class="col-sm-6" >
													<input type="text" class='lineColor' name='lineColor' value='#000000'/>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
</body>
</html>
<?php
	session_start();
	session_unset();
	require_once("CrosswordPuzzleMaker.php");
	require("word_processor.php");
	
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
		// Set starting variables gotten from post
		$title = $_POST["title"];
		$subtitle = $_POST["subtitle"];
		$height = $_POST["height"];
		$width = $_POST["width"];
		$puzzleType = $_POST["puzzletype"];
		$wordHintList = $_POST["wordInput"];
		$batch = $_POST['batchNumber'];
		
		// Set defaults if they weren't set on index page
		if($title == "" || $title == null){
			$title = "Crossword Puzzle";
		}
		
		if($subtitle == "" || $subtitle == null){
			$subtitle = "SILC Crossword";
		}
		
		if($height == 0 || $height == null){
			$height = 10;
		}
		else if($height > 100){
			$height = 100;
		}
		
		if($width == 0 || $width == null){
			$width = 10;
		}
		else if($width > 100){
			$width = 100;
		}

		//Added set defaults for batch @kc9718us
		if($batch == 0 || $batch == null){
			$batch = 3;
		}
		else if($batch > 10){
			$batch == 10;
		}
		
		// Create an array of words paired with hints
		// $words[i][0] is the word, $words[i][1] is the hint
		$words = generateWordList($wordHintList);
						//var_dump($words);

		//Variables for goto loops @kc9718us
		$round = 0;
		$wordCount = 0;
		$endRound = intdiv(count($words),$batch);

		//Loop to create multiple puzzles using goto. Upon less than the required number of batch, the loop will end itself. @kc9718us
		Beginning:
		if(count($words) < $batch){
			goto End;
		}else{
			$batchWords = generateBatch($words);

			//Checks to see if there are enough words in the array to make a puzzle. If not, remove the first word and try again. If 
			//there are no matches, ends the program and list all remaining words as unplaceable words. @kc9718us
			if(count($batchWords) != $batch){
				//Loop through the total number of words in the array for a match
				for($i = 0; $i<=count($words); $i++){
					$words = removeFirstWord($words);
					$batchWords = generateBatch($words);
					//If match is found, removes word from array and break out of loop
					if(count($batchWords) == $batch){
						$words = removeBatch($batchWords, $words);
						break;
					}else{
						goto End;
					}
				}

			}else{
				$words = removeBatch($batchWords, $words);
			}
		// Creates a few Crossword Puzzles and then keeps the one with the most placed words
		// Edited to accept batchWords instead of words to make Batch successful @kc9718us
		$crosswordMaker = new CrosswordPuzzleMaker($width, $height, $batchWords);
		}



		// Get puzzle/solution details from the Crossword Maker
		$solution = $crosswordMaker->getSolution();
		$puzzle = $crosswordMaker->getPuzzle();
		$puzzleNumbers = $crosswordMaker->getPuzzleNumbers();
		//var_dump($puzzleNumbers);
		$crosswordHints = getCrosswordHints($puzzleNumbers);
		//var_dump($crosswordHints);
		$fillinHints = $crosswordMaker->getFillInHints();		
		$unplacedWords = $crosswordMaker->getUnplacedWords();
		$skeletonHints = $crosswordMaker->getSkeletonHints();
		

		
		
		// Set count values to 0 - used for setting the middle divider between Across and Down hint lists
		// Depending on which value has the highest count determines if the border will appear on right/left side.
		$wordsAcrossCount = 0;
		$wordsDownCount = 0;
		
		$_SESSION["solution"] = $solution;
		$_SESSION["puzzle"] = $puzzle;
		$_SESSION["puzzleNumbers"] = $puzzleNumbers;
		$_SESSION["crosswordHints"] = $crosswordHints;
		$_SESSION["fillinHints"] = $fillinHints;
		$_SESSION["unplacedWords"] = $unplacedWords;
		$_SESSION["skeletonHints"] = $skeletonHints;
		$_SESSION["title"] = $title;
		$_SESSION["subtitle"] = $subtitle;
		$_SESSION["wordsAcrossCount"] = $wordsAcrossCount;
		$_SESSION["wordsDownCount"] = $wordsDownCount;
	}
	// If visiting for the first time by skipping the index page redirect them to it
	else{
		$url = "index.php";
		
		header("Location: ".$url);
		die();
	}
	
	// Generates the word list for words paired with hints
	// Splits word from hint by taking the sides from the first comma, then trims extra space from each
	// Returns array in format word[i][0] = word, word[i][1] = hint

	//Updated to not require hints for skeleton and fillin @kc9718us
	function generateWordList($wordInput){	
		$words = [];
		$wordLine = [];
		
		$lines = explode("\n", $wordInput);
		//var_dump($lines);
	
		//Pull puzzleType for creating wordlist
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
			// Set starting variables gotten from post
			$puzzleType = $_POST["puzzletype"];
		//Check for puzzle type, if crossword, proceed as normal
			if($puzzleType == "crossword"){ 
				foreach($lines as $line){
					$word = strstr($line, ',', true);
					//var_dump($word);
					$wordP = new wordProcessor($word, "telugu");
					$wordP->trim();
					//$wordP->toCaps();
				
					$word = $wordP->getWord();
	
					$hint = trim(ltrim(strstr($line, ','), ','));
	
					if(!(empty($word) || empty($hint))){				
					$wordLine[0] = $word;
					$wordLine[1] = $hint;
					array_push($words, $wordLine);
					
					}
	
				}
			}
		//If not skeleton or fillin, check for hint. If no hint, append hint
			else{
				foreach($lines as $line){
				//Check to see if there is a hint, if no hint, append 1 as hint
					$pos = strpos($line, ',');
					if($pos == false){
						$line .= ",1";
					}
					//Proceed as normal
	
					$word = strstr($line, ',', true);
					//var_dump($word);
	
					$wordP = new wordProcessor($word, "telugu");
					$wordP->trim();
					//$wordP->toCaps();
				
					$word = $wordP->getWord();
	
					$hint = trim(ltrim(strstr($line, ','), ','));
	
					if(!(empty($word) || empty($hint))){				
						$wordLine[0] = $word;
						$wordLine[1] = $hint;
						array_push($words, $wordLine);
					
					}
	
				}
			}
	
	
						//var_dump($word);
	//var_dump($hint);
	
	// If visiting for the first time by skipping the index page redirect them to it
		}else{
			$url = "index.php";
	
			header("Location: ".$url);
			die();
		}
		return $words;
		}
	
	// Number - Hint - Direction
	function getCrosswordHints($puzzleNumbers){
		$hints = [];
		$i = 0;
		foreach($puzzleNumbers as $placedWord) {
			$hints[$i] = [];
			
			array_push($hints[$i], $placedWord[5]);
			array_push($hints[$i], $placedWord[6]);
			array_push($hints[$i], $placedWord[3]);
			
			$i++;
		}
		
		return $hints;
	}

	/* Added functon generateBatch. The function takes the generatedWordList array and breaks it down even further depending on the batch count. The function
	returns an array based upon the number of the posted batch number. @kc9718us
	 */
	function generateBatch($wordArray){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
	
			// Set starting variables gotten from post
			$batch = $_POST['batchNumber'];

			$batchArray = [];
			//Sets count to 1 due to the first element of the array already in use
			$batchCount = 1;
			$num = 0;

			//Initializes and removes the first word of the array
			$initialWord = $wordArray[0]["0"];
			array_push($batchArray, $wordArray[0]);
			array_splice($wordArray,0,1);

			//Splits the first word to be used as a base for comparing characters from other words of the array
			$batchLetter = str_split($initialWord);

			//Goes through each word of the array to see if they contain similiar character to the first word.
			//Upon discovery, add the word to the new array and remove from the old array
			foreach($wordArray as $key){
				//If the number of words added to the new array matches the batch number, close the loop
				if($batchCount != $batch){
					$temp = str_split($key[0]);

					foreach($temp as $test){
						if(in_array($test,$batchLetter)){
							array_push($batchLetter, $temp);
							array_push($batchArray, $wordArray[$num]);
							unset($wordArray[$num]);
							$batchCount++;
							break;
						
						}
					}
					$num++;
				}else{
					break;
				}
			}
			//array_values($wordArray);


			return $batchArray;
			// If visiting for the first time by skipping the index page redirect them to it
		}else{
			$url = "index.php";
	
			header("Location: ".$url);
			die();
		}
		
		return $words;
	}

	/* Added function removeBatch. The function compares and removes the new batchArray words from the old wordArray.  It then returns the newly spliced old 
	wordArray. @kc9718us
	*/
	function removeBatch($batchArray, $wordArray){
		$count = 0;
		foreach($wordArray as $key){
			$temp = $key[0];
			foreach($batchArray as $value){
				if($temp == $value[0]){
					array_splice($wordArray,$count,1);
					//Remove count to keep index correct
					$count--;
					break;
				}
			}
			$count++;
		}
		return $wordArray;
	}

	/* Added function removeFirstWord. The function removes the first word of the array and places it at the end. The function helps support
	the generateBatch function due to the function using the first word as the initial source. @kc9718us
	*/
	function removeFirstWord($wordArray){
		$tempArray = $wordArray[0];
		array_splice($wordArray,0,1);
		array_push($wordArray, $tempArray);

		return $wordArray;
	}
?>


<!-- Added codes to ensure puzzle has right number of batch words. In the case of unplaced words being found, removes the first element of the
array to the end and tries again for as many times as there are words in the array. If no solution is found, end and place the words as unplaced words. -->
<?php
	if(count($unplacedWords)>1){
		removeFirstWord($batchWords);
		foreach($batchWords as $keys){
			array_push($words, $keys);
		}
		$wordCount++;
		if($wordCount != count($words)){
			goto Beginning;
		}else{
			goto End;
		}
	}

?>

<!-- Gutted out most of the unrequired codes and kept the codes for skeleton puzzles only. @kc9718us -->

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN''http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
	
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    
    <!-- Spectrum -->
    <link rel="stylesheet" type="text/css" href="spectrum.css">
    <script type="text/javascript" src="spectrum.js"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale = 1">
    
    <title>Crossword Puzzle</title>
	
	<link rel="stylesheet" type="text/css" href="crosswordstyle.css">
</head>
<body>
	<form action="CrosswordSave.php" method="post">
		<div class="container-fluid">
			<div class="jumbotron" id="jumbos">
			</div>
			<div class="col-sm-12"><input type="submit" name="submit" class="btn btn-primary btn-lg" value="Generate"></div>
			<div align="center" class="warningmessage" 
				<?php
					// Display warning message only if there are unplaced words
					if(sizeof($unplacedWords) == 0){
						echo('style="display: none;"');
					}
				?>>
				<div class="col-sm-12">
					<!-- Changed warning to space limitation @kc9718us -->
					<h3> Warning - The following words could not be placed on the puzzle due to space</h3>
					<?php
						// Print unplaced words
						foreach($unplacedWords as $word) {
							echo("<h4>".$word."</h3>");
							
						}
					?>
				</div>	
			</div>

			<br>
			<div class="panel">
				<div class="panel-group">
					<div class="panel panel-primary">
						
						<div class="panel-heading">
							<div class="row">
								<div class="col-sm-12">
									<div align="center"><h2>Crossword Puzzle</h2></div>
								</div>
							</div>
						</div>
						<div class="panel-body">
							<div align="center">
								<h3><?php echo($title);?></h3>
							</div>
							<div align="center">
								<h4><?php echo($subtitle);?></h4>
							</div>
							<div align="center">
								<table id="grid" class="crossword puzzle skeletonPuzzle">
									<?php
										// Print the crossword puzzle
										foreach ($puzzle as $key => $row) 
										{		
											echo'<tr>';
											foreach ($row as $k => $val){
												if($val != "0"){
													echo'<td class="filled">&nbsp;&nbsp;&nbsp;&nbsp;</td>
													';
												}
												else{
													echo'<td class="unfilled"> &nbsp;&nbsp;&nbsp;&nbsp; </td>
													';
												}
											}
											echo'</tr>';
										}
									?>
								</table>
							</div>
							<br><br>

							<div align="center" class="wordhints skeletonHints">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12">
											<h3>Skeleton Characters</h3>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12" style="text-align:center;">
											<?php
												foreach($skeletonHints as $char){
													echo(' '.$char.' ');
												}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>

						<br>
						<div class="panel panel-primary solutionSection">
							<div class="panel-heading ">
								<div class="row">
									<div class="col-sm-12">
										<div align="center"><h2>Crossword Solution</h2></div>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div align="center">
									<h3><?php echo($title);?></h3>
								</div>
								<div align="center">
									<h4><?php echo($subtitle);?></h4>
								</div>
								<div align="center">
									<table id="grid" class="crossword">
										<?php
											// Display the solution
											foreach ($solution as $key => $row) 
											{		
												echo'<tr>';
												foreach ($row as $k => $val){
													if($val != "0"){
														echo'<td class="filled">'.$val.'</td>
														';
													}
													else{
														echo'<td class="unfilled"> &nbsp;&nbsp;&nbsp;&nbsp; </td>
														';
													}
												}
												echo'</tr>';
											}
										?>
									</table>
								</div>
								<br><br>
								<h2 align="center"> Words <h2>
								<div align="center" class="wordhints">
									<div class="col-sm-6 crosswordHintsBorderAcross">
										<div class="row">
											<div class="col-sm-12">
												<h3>Across</h3>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-12" style="text-align:left;">
												<?php
													// Display the solution words going across
													foreach($puzzleNumbers as $placedLocation) {
														if($placedLocation[3] == "right"){
															echo("<h4>".$placedLocation[5].") ".$placedLocation[0]."</h4><br>");
														}
													}
												?>
											</div>
										</div>
									</div>
									<div class="col-sm-6 crosswordHintsBorderDown">
										<div class="row">
											<div class="col-sm-12">
												<h3>Down</h3>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-12" style="text-align:left;">
												<?php
													// Display the solution words going down
													foreach($puzzleNumbers as $placedLocation) {
														if($placedLocation[3] == "down"){
															echo("<h4>".$placedLocation[5].") ".$placedLocation[0]."</h4><br>");
														}
													}
												?>                                        
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</body>
</html>
<?php
//Added codes for goto to loop the puzzle to the top to rerun to the codes to create puzzles. @kc9718us
while($round <= $endRound){
	$round++;
	$wordCount = 0;
	goto Beginning;
}
//End point for goto loop to finish codes
End:
?> 
<!-- Moved unplaced word warning here. Changed a few codes to use $words array instead of unplacedword. @kc9718us -->
<html>
	<body>
	<div align="center" class="warningmessage" 
				<?php
					// Display warning message only if there are unplaced words
					if(count($words) == 0){
						echo('style="display: none;"');
					}
				?>>
				<div class="col-sm-12">
					<h3> Warning - The following words could not be placed </h3>
					<?php
						// Print unplaced words
						foreach($words as $word) {
							echo("<h4>".$word[0]."</h3>");
							
						}
					?>
				</div>	
			</div>
			<br>
			<br>
	</body>
</html>


<script>
	// Set default spectrum elements
	$(".blankSquareColor").spectrum({
		color: "#FFFFFF",
		change: function(color) {
			$(".unfilled").css("background-color", color.toHexString());
			$(".blankSquareColor").val(color.toHexString());
		}
	});

	$(".letterSquareColor").spectrum({
		color: "#EEEEEE",
		change: function(color) {
			$(".filled").css("background-color", color.toHexString());
			$(".letterSquareColor").val(color.toHexString());
		}
	});
	
	$(".letterColor").spectrum({
		color: "#000000",
		change: function(color) {
			$(".filled").css("color", color.toHexString());
			$(".letterColor").val(color.toHexString());
		}
	});
	
	$(".lineColor").spectrum({
		color: "#000000",
		change: function(color) {
			$(".filled").css("border", "2px solid " + color.toHexString());
			
			// Only change hidden lines if they're showing - need to remain white for copy and pasting to word if hidden
			if($(".unfilled").css("visibility") === "visible"){
				$(".unfilled").css("border", "2px solid " + color.toHexString());
			}
			
			$(".lineColor").val(color.toHexString());
		}
	});

	// Set the way the middle border in the across/down hints section works
	// The border needs to be positioned on the side with the most words since the border does not fill the whole space otherwise
	<?php
		if($wordsAcrossCount >= $wordsDownCount){
			echo('$(".crosswordHintsBorderAcross").css("border-right", "2px solid #000000");');
			echo('$(".crosswordHintsBorderDown").css("border-left", "0px solid #000000");');
		}
		else{
			echo('$(".crosswordHintsBorderAcross").css("border-right", "0px solid #000000");');
			echo('$(".crosswordHintsBorderDown").css("border-left", "2px solid #000000");');
		}
		
		if($puzzleType == "crossword"){
			echo('$(".crosswordHints").show();');
			echo('$(".fillinHints").hide();');
			echo('$(".skeletonHints").hide();');
			
			echo('$(".crosswordPuzzle").show();');
			echo('$(".skeletonPuzzle").hide();');
		}
		else if($puzzleType == "fillin"){
			echo('$(".crosswordHints").hide();');
			echo('$(".fillinHints").show();');
			echo('$(".skeletonHints").hide();');
			
			echo('$(".crosswordPuzzle").show();');
			echo('$(".skeletonPuzzle").hide();');
		}
		else{
			echo('$(".crosswordHints").hide();');
			echo('$(".fillinHints").hide();');
			echo('$(".skeletonHints").show();');
			
			echo('$(".crosswordPuzzle").hide();');
			echo('$(".skeletonPuzzle").show();');
		}
	?>

	$(".crossword").css("border", "2px solid " + $(".lineColor").spectrum('get').toHexString());
	
	// Updates the solution section to hidden/visable on check box update
	function solutionCheckboxChange(){
		if($('.showSolutionCheckbox').is(":checked")){  
			$(".solutionSection").show();
		}
		else{
			$(".solutionSection").hide();
		}
	}
	
	// Updates the solution section to hidden/visable on check box update
	function blankSquareCheckboxChange(){
		if($('.showBlankSquaresCheckbox').is(":checked")){  
			$(".unfilled").css("visibility", "visible");
			$(".unfilled").css("border", "2px solid " + $(".lineColor").spectrum('get').toHexString());

		}
		else{
			$(".unfilled").css("visibility", "hidden");
			$(".unfilled").css("border", "0px solid #FFFFFF"); //+ $(".lineColor").spectrum('get').toHexString());
		}
	}
	
	// Updates puzzle to show solution or fill-in puzzle hints
	function puzzleHintsChange(){
		if($('#puzzletype').val() == "crossword"){  
			$(".crosswordHints").show();
			$(".fillinHints").hide();
			$(".skeletonHints").hide();
			
			$(".crosswordPuzzle").show();
			$(".skeletonPuzzle").hide();
		}
		else if($('#puzzletype').val() == "fillin"){  
			$(".crosswordHints").hide();
			$(".fillinHints").show();
			$(".skeletonHints").hide();
			
			$(".crosswordPuzzle").show();
			$(".skeletonPuzzle").hide();
		}
		else{
			$(".crosswordHints").hide();
			$(".fillinHints").hide();
			$(".skeletonHints").show();
			
			$(".crosswordPuzzle").hide();
			$(".skeletonPuzzle").show();
		}
	}
</script>
</html>
