<?php

	class CrosswordConnection{
		private $db;
		private $production;
		
		function __construct(){
			$production = false;// true in production
			$icsbinco = false;//icsbinco_puzzlem
			$username = "";
			$pass = "";
			if ($icsbinco) {
				$username = "icsbinco_puzzlem";
				$password = "Dh23HRENtf5N";
				$dbname = "icsbinco_puzzlemaster";
			} 
			else if ($production) {
				$username = "metroics_webuser";
				$password = "Dh23HRENtf5N";
				$dbname = "metroics_puzzle_master";
			} else {
				$username = "webuser";
				$password = "webby";
				$dbname = "master_puzzle";
			}
			
			$conn = NULL;
			
			try {
				$conn = new PDO("mysql:host=localhost;dbname=".$dbname, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				echo 'ERROR: ' . $e->getMessage();
			}
			
			$this->db = $conn;
		}
		
		public function addPuzzle($puzzleID, $puzzleNumbers, $title, $subtitle, 
				$showBlankSquares, $blankSquareColor, $letterSquareColor, $letterColor, 
				$lineColor, $wordsAcrossCount, $wordsDownCount, $puzzleType, $hints, 
				$p_puzzle, $solution){
					
			filter_var($puzzleID, FILTER_SANITIZE_STRING);
			filter_var($puzzleNumbers, FILTER_SANITIZE_STRING);
			filter_var($title, FILTER_SANITIZE_STRING);
			filter_var($subtitle, FILTER_SANITIZE_STRING);
			filter_var($showBlankSquares, FILTER_SANITIZE_STRING);
			filter_var($blankSquareColor, FILTER_SANITIZE_STRING);
			filter_var($letterSquareColor, FILTER_SANITIZE_STRING);
			filter_var($letterColor, FILTER_SANITIZE_STRING);
			filter_var($lineColor, FILTER_SANITIZE_STRING);
			filter_var($wordsAcrossCount, FILTER_SANITIZE_STRING);
			filter_var($wordsDownCount, FILTER_SANITIZE_STRING);
			filter_var($puzzleType, FILTER_SANITIZE_STRING);
			filter_var($hints, FILTER_SANITIZE_STRING);
			filter_var($p_puzzle, FILTER_SANITIZE_STRING);
			filter_var($solution, FILTER_SANITIZE_STRING);			

			$stmt = $this->db->prepare("CALL addNewCrossword(:p_puzzleID, :p_puzzleNumbers, :p_title, 
					:p_subtitle, :p_showBlankSquares, :p_blankSquarecolor, :p_letterSquareColor, 
					:p_letterColor, :p_lineColor, :p_wordsAcrossCount, :p_wordsDownCount, 
					:p_puzzleType, :p_hints, :p_puzzle, :p_solution, @out_ID)");
			
			$stmt->bindValue(':p_puzzleID',$puzzleID,PDO::PARAM_INT);
			$stmt->bindValue(':p_puzzleNumbers',$puzzleNumbers,PDO::PARAM_STR);
			$stmt->bindValue(':p_title',$title,PDO::PARAM_STR);
			$stmt->bindValue(':p_subtitle',$subtitle,PDO::PARAM_STR);
			$stmt->bindValue(':p_showBlankSquares',$showBlankSquares,PDO::PARAM_STR);
			$stmt->bindValue(':p_blankSquarecolor',$blankSquareColor,PDO::PARAM_STR);
			$stmt->bindValue(':p_letterSquareColor',$letterSquareColor,PDO::PARAM_STR);
			$stmt->bindValue(':p_letterColor',$letterColor,PDO::PARAM_STR);
			$stmt->bindValue(':p_lineColor',$lineColor,PDO::PARAM_STR);
			$stmt->bindValue(':p_wordsAcrossCount',$wordsAcrossCount,PDO::PARAM_INT);
			$stmt->bindValue(':p_wordsDownCount',$wordsDownCount,PDO::PARAM_INT);
			$stmt->bindValue(':p_puzzleType',$puzzleType,PDO::PARAM_INT);
			$stmt->bindValue(':p_hints',$hints,PDO::PARAM_STR);
			$stmt->bindValue(':p_puzzle',$p_puzzle,PDO::PARAM_STR);
			$stmt->bindValue(':p_solution',$solution,PDO::PARAM_STR);

			$stmt->execute();
			
			$return = $this->db->query('select @out_ID')->fetch(PDO::FETCH_ASSOC);
			return $return['@out_ID'];
		}
		
		public function getPuzzle($id){
			filter_var($id, FILTER_SANITIZE_STRING);
			$stmt = $this->db->prepare("CALL getCrosswordPuzzle(:p_id)");
			$stmt->bindValue(':p_id',$id,PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}
?>