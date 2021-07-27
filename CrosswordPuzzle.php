<?php
	session_start();
	session_unset();
	require("CrosswordPuzzleMaker.php");
	require("word_processor.php");

	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// Set starting variables gotten from post
		$title = $_POST["title"];
		$subtitle = $_POST["subtitle"];
		$height = $_POST["height"];
		$width = $_POST["width"];
		$puzzleType = $_POST["puzzletype"];
		$wordHintList = $_POST["wordInput"];

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

		// Create an array of words paired with hints
		// $words[i][0] is the word, $words[i][1] is the hint
		$words = generateWordList($wordHintList);
						//var_dump($words);

		//(HERE*) Creates a few Crossword Puzzles and then keeps the one with the most placed words
		$crosswordMaker = new CrosswordPuzzleMaker($width, $height, $words);

		//(HERE*) Get puzzle/solution details from the Crossword Maker
		$solution = $crosswordMaker->getSolution();
		$puzzle = $crosswordMaker->getPuzzle();

		//(HERE*)
		$puzzleNumbers = $crosswordMaker->getPuzzleNumbers();
		//var_dump($puzzleNumbers);
		$crosswordHints = getCrosswordHints($puzzleNumbers);
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
	function generateWordList($wordInput){

		$words = [];
		$wordLine = [];

		$lines = explode("\n", $wordInput);
		//var_dump($lines);
		foreach($lines as $line){
			$word = strstr($line, ',', true);
			//var_dump($word);
			$wordP = new wordProcessor($word, "telugu");
			$wordP->trim();
			//$wordP->toCaps();

			$word = $wordP->getWord();
			$hint = trim(ltrim(strstr($line, ','), ','));
						//var_dump($word);
//var_dump($hint);

			if(!(empty($word) || empty($hint))){
				$wordLine[0] = $word;
				$wordLine[1] = $hint;
				array_push($words, $wordLine);

			}

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
?>
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

	<!-- HTML Canvas -->
    <script src="html2canvas.min.js"></script>

    <style>

    </style>
</head>
<body>
	<form action="power.php" method="post" id="theform">
		<input type="hidden" name="powerCross" id="powerCross">
		<input type="hidden" name="powerPuzzle" id="powerPuzzle">
		<input type="hidden" name="powerSolution" id="powerSolution">
		//HERE* adding a power point button
		<button type="button" id="sub" class="btn btn-primary" style="position: absolute; top: 550px; right: 25px;">PowerPoint</button>
	</form>
	<form action="CrosswordSave.php" method="post">
		<div class="container-fluid">
			<div class="jumbotron" id="jumbos">
			</div>
			<!-- <div class="col-sm-12"><input type="submit" name="submit" class="btn btn-primary btn-lg" value="Generate"></div> -->
			<div align="center" class="warningmessage"
				<?php
					// Display warning message only if there are unplaced words
					if(sizeof($unplacedWords) == 0){
						echo('style="display: none;"');
					}
				?>>
				<!-- //(HERE*)Replace the warning sign because the new generation of other puzzles should take care of this problem. -->
				<div class="col-sm-12">
					<h3> Warning - The following words could not be placed </h3>
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
									<div align="center"><h2>Crossword Puzzle</h1></div>
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
								<div class="canvasCross" style="display: inline-block; padding: 30px;">
								<table id="grid" class="crossword puzzle crosswordPuzzle">
									<?php
										//(HERE**) Print the crossword puzzle
										foreach ($puzzle as $key => $row)
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
							</div>
							<div align="center">
								<div class="canvasPuzzle" style="display: inline-block; padding: 30px;">
									<table id="grid" class="crossword puzzle skeletonPuzzle example">
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
							</div>
							<br><br>
							<h2 align="center"> Hints <h2>
							<div align="center" class="wordhints crosswordHints">
								<div class="col-sm-6 crosswordHintsBorderAcross">
									<div class="row">
										<div class="col-sm-12">
											<h3>Across</h3>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12" style="text-align:left;">
											<?php
												// Print hints going across for crossword puzzle
												foreach($crosswordHints as $hint) {
													if($hint[2] == "right"){
														echo("<h4>".$hint[0].") ".$hint[1]."</h4><br>");
														$wordsAcrossCount++;
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
												// Print hints going down for crossword puzzle
												foreach($crosswordHints as $hint) {
													if($hint[2] == "down"){
														echo("<h4>".$hint[0].") ".$hint[1]."</h4><br>");
														$wordsDownCount++;
													}
												}
											?>
										</div>
									</div>
								</div>
							</div>
							<div align="center" class="wordhints fillinHints">
								<div class="col-sm-12">
									<div class="row">
										<div class="col-sm-12">
											<h3>Fill-In Words</h3>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12" style="text-align:left;">
											<?php

												// Print hints going across for fillin puzzle
												// Print 4 categories per row

												$currentNum = null;
												$currentIteration = 0;

												foreach($fillinHints as $hint) {
													if($currentNum != $hint[1]){
														$currentNum = $hint[1];

														// If 4th iteration then start new row
														if($currentIteration % 4 == 0){
															// If first time looping, start first row
															if($currentIteration == 0){
																echo('<div class="row">');
															}
															// Close previous length div and previous row, start new row
															else{
																echo('</div></div><div class="row">');
															}
															// Start new length div
															echo('<div class="col-sm-3"><h3><u>'.$currentNum.' Length Words</u></h3>');
														}
														// Close previous length div, start new one
														else{
															echo('</div><div class="col-sm-3"><h3><u>'.$currentNum.' Length Words</u></h3>');
														}

														$currentIteration++;
													}

													// Place word
													echo("<h4>".$hint[0]."</h4>");
												}

												// Close current row and length divs
												echo('</div></div>');
											?>
										</div>
									</div>
								</div>
							</div>
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
						<!-- (HERE!) move this to the Top -->
						<h1></h1>
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
											<div class="row">
												<div class="col-sm-3">
													<select class="form-control" id="puzzletype" name="puzzleType" onchange="puzzleHintsChange()">
														<option value="crossword" <?php if($puzzleType == "crossword"){echo('selected="selected"');} ?>>Crossword</option>
														<option value="fillin" <?php if($puzzleType == "fillin"){echo('selected="selected"');} ?>>Fill-In</option>
														<option value="skeleton" <?php if($puzzleType == "skeleton"){echo('selected="selected"');} ?>>Skeleton</option>
													</select>
												</div>
											</div>
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
									<div class="canvasSolution" style="display: inline-block; padding: 30px;">
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
	//HERE** changed here
	$(document).ready(function() {

		$("#sub").click(function(event) {

			event.preventDefault;

			var canvasCross = $(".canvasCross");
			var canvasPuzzle = $(".canvasPuzzle");
			var canvasSolution = $(".canvasSolution")

			window.scrollTo(0,0);

			if($(".skeletonHints").is(":visible")) {

				html2canvas(canvasPuzzle[0]).then(function(canvas){

				$("#powerPuzzle").val(canvas.toDataURL('image/jpeg'));

			})

			} else {

				html2canvas(canvasCross[0]).then(function(canvas){

				$("#powerCross").val(canvas.toDataURL('image/jpeg'));

			})

			}

			html2canvas(canvasSolution[0]).then(function(canvas){

				$("#powerSolution").val(canvas.toDataURL('image/jpeg'));

				if(canvas) {

					$("#theform").submit();
					alert("Power Point File Donwloaded");
				}

			})

		})

	})
</script>
</html>
