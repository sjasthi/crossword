<?php

	/* Created by Stephen Schneider
	 * CrosswordPuzzleMaker makes multiple instances of the crossword puzzle.
	 * If one version outperforms another (by placing more words) then use that crossword instead.
	 * 0 = place first word at cell (0, 0) on the grid going across
	 * 1 = place first word at cell (0, 0) on the grid going down
	 * 2 = place first word on the last row going across at last available position
	 * 3 = place first word in random direction in a random cell
	 */
class CrosswordPuzzleMaker{
	
	// Final crossword to be generated into a puzzle
	private $crossword;
	private $wordList;
	private $wordProcessor;
	
	public function __construct($width, $height, $words){
		require("Crossword.php");
		
		$startTime = time();
		$endTime = $startTime + 4;
		
		$this->wordProcessor = new wordProcessor(" ", "telugu");
		
		$this->wordList = $words;
		
		$this->setWordIntersections();
		
		$this->sortWordList();
		
				//var_dump($this->wordList);

		
		foreach($this->wordList as $word){
			//print_r($word[0]." - Intersections: ".$word[2]."<br>");
		}
		
		//print_r("<br><br><br>");

		// Create first puzzle
		$puzzle1 = new Crossword($width, $height, $this->wordList, 0);
		$this->crossword = $puzzle1;
		
		// For the following puzzles, if they score higher than the first one, set them as the final crossword
		$puzzle2 = new Crossword($width, $height, $this->wordList, 1);
		if($puzzle2->getScore() > $this->crossword->getScore()){
			$this->crossword = $puzzle2;
		}
		
		$puzzle3 = new Crossword($width, $height, $this->wordList, 2);
		if($puzzle3->getScore() > $this->crossword->getScore()){
			$this->crossword = $puzzle3;
		}
		
		$puzzleCount = 0;
		$currentTime = time();
		while($currentTime < $endTime){
			$puzzle4 = new Crossword($width, $height, $this->wordList, 3);
			if($puzzle4->getScore() > $this->crossword->getScore()){
				$this->crossword = $puzzle4;
			}
			
			$currentTime = time();
			$puzzleCount++;
		}
		
		//print_r("count: ".$puzzleCount);
		//$this->crossword = $puzzle4;
		
		// Generate puzzle based on best crossword
		$this->crossword->generatePuzzle();
	}
	
	private function setWordIntersections(){
		$i = 0;
		
		$words = $this->wordList;
		foreach($words as &$word){
			
			$charList = $this->splitWord($word[0]);
			$intersections = 0;
			//print_r("<br><br><br>");
			
			//print_r($charList);
			
			$j = 0;
			foreach($this->wordList as $interWord){
				if($i != $j){
					$intersections += $this->getIntersectionCount($charList, $interWord[0], $i);
					//$intersections += $this->getWordLength($word);
					
					$word[2] = $intersections;
				}
				else{
					//print_r("<br>word is same");
				}
				$j++;
			}
			
			$i++;
		}
		
		//print_r("<br><br><br>");
		$this->wordList = $words;
	}
	
	private function getIntersectionCount($charList, $interWord, $i){
		$totalCount = 0;
		
		$interChars = $this->splitWord($interWord);
		
		foreach($charList as $char){
			foreach($interChars as $innerChar){
				if($this->compareToWord($char, $innerChar) == 0){
					$totalCount++;
				}
			}
			
			//$count = substr_count($interWord, $char);
			
			/*if($count != 0){
				$totalCount += $count;
			}*/
		}
		
		//print_r("<br>".$totalCount);
		
		return $totalCount;
	}
	
	private function sortWordList(){
		usort($this->wordList, function($a, $b) {
			return $b[2] - $a[2];
		});
	}
	
	public function getSolution(){
		return $this->crossword->getSolution();
	}
	
	public function getPuzzle(){
		return $this->crossword->getPuzzle();
	}
	
	public function getPuzzleNumbers(){
		return $this->crossword->getPuzzleNumbers();
	}
	
	public function getUnplacedWords(){
		return $this->crossword->getUnplacedWords();
	}
	
	public function getFillInHints(){
		return $this->crossword->getFillInHints();
	}
	
	public function getSkeletonHints(){
		return $this->crossword->getSkeletonHints();
	}
	
	/* Debug method to test many random puzzles at once to make sure none fail
	public function puzzleGenerationTest($width, $height, $words){
		for($i = 0; $i < 100; $i++){
			$puzzle3 = new Crossword($width, $height, $words, 2);
		}
	}*/
	
	/*** Word Processor Functions ***/
	private function getWordLength($word){
		$this->wordProcessor->setWord($word, "telugu");
		
		return $this->wordProcessor->getLength();
	}
	
	private function splitWord($word){
		$this->wordProcessor->setWord($word, "telugu");
		
		return $this->wordProcessor->getLogicalChars();
	}
	
	private function compareToWord($word, $compareWord){
		$this->wordProcessor->setWord($word, "telugu");
		
		return $this->wordProcessor->compareTo($compareWord);
	}
}
?>