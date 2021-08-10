<?php
	require("CrosswordConnection.php");
	require_once $_SERVER['DOCUMENT_ROOT'] . '/puzzlemaster/bin/Puzzle.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/puzzlemaster/bin/functions.php';
	session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
	//var_dump($_POST);
	//var_dump($_SESSION);
	
	$puzzleID = "1";
	$puzzleNumbers = serializeArray($_SESSION["puzzleNumbers"]);
	$title = $_SESSION["title"];
	$subtitle = $_SESSION["subtitle"];
	$showBlankSquares = (isset($_POST["showBlankSquares"])) ? $_POST["showBlankSquares"] : "";
	$blankSquareColor = $_POST["blankSquareColor"];
	$letterSquareColor = $_POST["letterSquareColor"];
	$letterColor = $_POST["letterColor"];
	$lineColor = $_POST["lineColor"];
	$puzzleType = getPuzzleType($_POST["puzzleType"]);
	$puzzle = serializeArray($_SESSION["puzzle"]);
	$solution = serializeArray($_SESSION["solution"]);
	$wordsAcrossCount = $_SESSION["wordsAcrossCount"];
	$wordsDownCount = $_SESSION["wordsDownCount"];

	$batch = "3";
	
	if($puzzleType == 1){
		$hints = serializeArray($_SESSION["crosswordHints"]);
	}
	else if($puzzleType == 2){
		$hints = serializeArray($_SESSION["fillinHints"]);
	}
	else{
		$hints = serializeList($_SESSION["skeletonHints"]);
	}
	
	$puzzle_md = new Puzzle();
	$puzzle_md->puzzleName = 'Crossword - '.$title.' '.$subtitle;
	$puzzle_md->puzzleCreatorId = getPuzzleCreatorIDFromToken('V5hpE1hNVSnVtrNw');
	$newPuzzleId = $puzzle_md->persistNewPuzzle();
	
	$connection = new CrosswordConnection();
	$newID = $connection->addPuzzle($newPuzzleId, $puzzleNumbers, $title, $subtitle, 
				$showBlankSquares, $blankSquareColor, $letterSquareColor, $letterColor, 
				$lineColor, $wordsAcrossCount, $wordsDownCount, $puzzleType, $hints, 
				$puzzle, $solution);
	
	$url = "CrosswordView.php?id=".$newPuzzleId;
	
	header("Location: ".$url);
	die();
	
	function serializeList($list){
		$encodedList = "";
		
		foreach($list as $item){
			$encodedList .= ",".$item;		
		}
		
		//echo($encodedList."<br><br>");
		
		return $encodedList;
	}
	
	function serializeArray($array){
		$encodedArray = "";
		
		foreach($array as $row){
			$encodedArray .= "|";
			
			foreach($row as $col){
				$encodedArray .= ",".$col;
			}
			
		}
		
		//echo($encodedArray."<br><br>");
		
		return $encodedArray;
	}
	
	function getPuzzleType($displayMode){
		switch($displayMode){
			case "crossword":
				return 1;
				break;
			case "fillin":
				return 2;
				break;
			case "skeleton":
				return 3;
				break;
		}
	}
?>