<?php
	require("CrosswordConnection.php");
	require("CrosswordPuzzleMaker.php");
	require("word_processor.php");
	
	$id = $_GET["id"];
	
	$connection = new CrosswordConnection();
	$CrosswordSettings = $connection->getPuzzle($id);	
	
	//var_dump($CrosswordSettings);
	
	$puzzleID = $CrosswordSettings["puzzleID"];
	$puzzleNumbers = createGrid($CrosswordSettings["puzzleNumbers"]);
	$title = $CrosswordSettings["title"];
	$subtitle = $CrosswordSettings["subtitle"];
	$showBlankSquares = $CrosswordSettings["showBlankSquares"];
	$blankSquareColor = $CrosswordSettings["blankSquareColor"];
	$letterSquareColor = $CrosswordSettings["letterSquareColor"];
	$letterColor = $CrosswordSettings["letterColor"];
	$lineColor = $CrosswordSettings["lineColor"];
	$wordsAcrossCount = $CrosswordSettings["wordsAcrossCount"];
	$wordsDownCount = $CrosswordSettings["wordsDownCount"];
	$puzzleType = $CrosswordSettings["puzzleType"];
	$puzzle = createGrid($CrosswordSettings["puzzle"]);
	
	if($puzzleType != 3){
		$hints = createGrid($CrosswordSettings["hints"]);
	}
	else{
		$hints = createList($CrosswordSettings["hints"]);
	}

	//var_dump($puzzleNumbers);
	//var_dump($hints);
	function createList($encodedList){
		$array = [];
			
		$items = explode(",", $encodedList);
		
		$i = -1;
		foreach($items as $item){
			// First array is a blank array with explode - skip it
			if($i == -1){
				$i++;
				continue;
			}
			
			$array[$i] = $item;
			
			$i++;
		}
		
		return $array;
	}
	
	function createGrid($encodedArray){
		$array = [];
		
		$rows = explode("|", $encodedArray);
		$columns = null;
		
		$i = -1;
		foreach($rows as $row){
			// First array is a blank array with explode - skip it
			if($i == -1){
				$i++;
				continue;
			}
			
			$columns = explode(",", $row);
			
			$array[$i] = [];
			
			$skipFirst = true;
			foreach($columns as $col){
				// First array is a blank array with explode - skip it
				if($skipFirst){
					$skipFirst = false;
					continue;
				}
				array_push($array[$i], $col);
			}
			
			$i++;
		}
		
		return $array;
	}
	
	// Generates the word list for words paired with hints
	// Splits word from hint by taking the sides from the first comma, then trims extra space from each
	// Returns array in format word[i][0] = word, word[i][1] = hint
	function generateWordList($wordInput){
		$words = [];
		$wordLine = [];
		
		$lines = explode("\n", $wordInput);
		
		foreach($lines as $line){
			
			$word = strtolower(trim(strstr($line, ',', true)));
			$hint = trim(ltrim(strstr($line, ','), ','));
			

			if(!(empty($word) || empty($hint))){				
				$wordLine[0] = $word;
				$wordLine[1] = $hint;
				array_push($words, $wordLine);
			}
			
		}
		
		return $words;
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
	
	<style>	
		.jumbotron {
			background-image: url("silcHeader.png");
			-webkit-background-size: 100% 100%;
			-moz-background-size: 100% 100%;
			-o-background-size: 100% 100%;
			background-size: 100% 100%;
			height: 179px;
		}
		
		table.crossword tr td {
			width: 48px;
			height: 48px;
			font-size: 1.875em;
			vertical-align:middle;
			text-align: center;
		}
		
		table.puzzle tr td {
			width: 48px;
			height: 48px;
			font-size: 20px;
			vertical-align: top;
			text-align: left;
		}

		td.filled {
			border: 2px solid #000000;
			background-color: #EEEEEE;
		}
		
		td.unfilled {
			border: 0px solid #FFFFFF; 
			background-color: #FFFFFF;
			visibility: hidden;
		}    

        .wordhints{
            border: 2px solid #000000; 
            overflow:auto;
        }
        
        .wordhints .crosswordHintsBorderAcross{
            border-right: 2px dashed #000000; 
        }
		
		.wordhints .crosswordHintsBorderDown{
            border-left: 2px dashed #000000; 
        }
		
		.warningmessage {
            border: 2px solid red;
            color: red;
            overflow: auto;
        }
	</style>
</head>
<body>
    <div class="container-fluid">
        <div class="jumbotron" id="jumbos">
        </div>
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
                            <table id="grid" class="crossword puzzle crosswordPuzzle" <?php if($puzzleType == 3){echo('style="display: none"');}?>>
								<?php
									if($puzzleType != 3){
										// Print the crossword puzzle
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
									}
								?>
							</table>
                        </div>
						<div align="center">
                            <table id="grid" class="crossword puzzle skeletonPuzzle" <?php if($puzzleType != 3){echo('style="display: none"');}?>> 
								<?php
									if($puzzleType == 3){
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
									}
								?>
							</table>
                        </div>
						<br><br>
						<h2 align="center"> Hints <h2>
						<div align="center" class="wordhints crosswordHints" <?php if($puzzleType != 1){echo('style="display: none"');}?>>
							<div class="col-sm-6 crosswordHintsBorderAcross">
								<div class="row">
									<div class="col-sm-12">
										<h3>Across</h3>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12" style="text-align:left;">
										<?php
											if($puzzleType == 1){
												// Print hints going across for crossword puzzle
												foreach($hints as $placedWord) {
													if($placedWord[2] == "right"){
														echo("<h4>".$placedWord[0].") ".$placedWord[1]."</h4><br>");
														$wordsDownCount++;
													}
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
											if($puzzleType == 1){
												// Print hints going down for crossword puzzle
												foreach($hints as $placedWord) {
													if($placedWord[2] == "down"){
														echo("<h4>".$placedWord[0].") ".$placedWord[1]."</h4><br>");
														$wordsDownCount++;
													}
												}
											}
										?>                                        
									</div>
								</div>
							</div>
                        </div>
						<div align="center" class="wordhints fillinHints" <?php if($puzzleType != 2){echo('style="display: none"');}?>>
							<div class="col-sm-12">
								<div class="row">
									<div class="col-sm-12">
										<h3>Fill-In Words</h3>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12" style="text-align:left;">
										<?php
											if($puzzleType == 2){
												// Print hints going across for fillin puzzle
												// Print 4 categories per row

												$currentNum = null;
												$currentIteration = 0;
												
												foreach($hints as $hint) {
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
											}
										?>
									</div>
								</div>
							</div>
						</div>
						<div align="center" class="wordhints skeletonHints" <?php if($puzzleType != 3){echo('style="visibility: hidden"');}?>>
							<div class="col-sm-12">
								<div class="row">
									<div class="col-sm-12">
										<h3>Skeleton Characters</h3>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12" style="text-align:center;">
										<?php
											if($puzzleType == 3){
												foreach($hints as $char){
													echo(' '.$char.' ');
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
</body>
</html>
<script>
	$(".unfilled").css("background-color", "<?php echo($blankSquareColor); ?>");
	$(".filled").css("background-color", "<?php echo($letterSquareColor); ?>");
	$(".filled").css("color", "<?php echo($letterColor); ?>");
	$(".filled").css("border", "2px solid " + "<?php echo($lineColor); ?>");

	<?php
		if($showBlankSquares == "on"){
			echo('$(".unfilled").css("border", "2px solid " + "'.$lineColor.'");');
		}
		
		if($wordsAcrossCount >= $wordsDownCount){
			echo('$(".crosswordHintsBorderAcross").css("border-right", "2px solid #000000");');
			echo('$(".crosswordHintsBorderDown").css("border-left", "0px solid #000000");');
		}
		else{
			echo('$(".crosswordHintsBorderAcross").css("border-right", "0px solid #000000");');
			echo('$(".crosswordHintsBorderDown").css("border-left", "2px solid #000000");');
		}
	?>

</script>
</html>