<?php

	/* Created by Stephen Schneider
	 * Class for generating a crossword puzzle
	 * Takes in a list of words and hints, grid width and height, and placement type to generate a solution.
	 * Based on the placement type the first word will get placed on a location.
	 * Algorithm will then attempt to keep placing words on the grid that fit until no more words can be placed.
	 * After a solution is generated a puzzle can be created by calling the generatePuzzle method.
	 * The score variable keeps track of how many words were placed so that the solution can be compared to other generated solutions.
	 * Also creates a fillin puzzle list where word placed locations are ordered by word length with length recorded.
	 */
class Crossword{
	private $wordList = [];
	private $unsortedWordList = [];
	private $hintList = [];
	private $unplacedWordList = [];
	
	private $placedWords = [];
	
	//private $placedWordListAcross = [];
	//private $placedWordListDown = [];
	
	private $wordsAndHintsList = [];
	private $puzzleNumbers = [];
	private $fillinList = [];
	private $skeletonHints = [];
	
	private $width;
	private $height;
	private $score = 0;
	
	private $solution = [];
	private $puzzle = [];
	private $placedLetters = [];
	
	private $firstWordSetting = 1;
	
	private $directions = Array("right", "down");
	
	private $wordProcessor;
	
	/*
	 * Creates a solution upon creation.
	 * A call to generatePuzzle() must be made to generate a puzzle based on the solution.
	*/
	public function __construct($width, $height, $wordList, $type){
		// Set starting values
		
		$this->width = $width;
		$this->height = $height;
		$this->firstWordSetting = $type;
		$this->wordProcessor = new wordProcessor(" ", "telugu");
		
		// Sets the word list and hint list and sort the words
		$this->setWordLists($wordList);

		// Adjust grid size based off longest word in case grid is too small
		$this->adjustGridSize();
		
		// Create an empty board
		$this->generateBoard($this->width, $this->height);
		
		// Create the solution
		//print_r("<br><br><br>");
		//print_r($this->wordList);
		//print_r("<br><br><br>");
		$this->generateSolution();
	}
	
	/*
	 * Set the wordList and hintList based off passed in word list
	 * wordItem[0] is the word, wordItem[1] is the hint
	 * Sort the wordList in order from best to worst words
	*/
	private function setWordLists($list){
		foreach($list as $wordItem){
			array_push($this->wordList, $wordItem[0]);
			array_push($this->hintList, $wordItem);
		}
		
		$this->unsortedWordList = $this->wordList;
		//$this->sortWordList();
	}
	
	/*
	 * Sort word list based off most placeable words.
	 * FUTURE: Currently sorting by length. Would be better if sorted by most number of intersections.
	 *         Might increase solution compuation time too.
	 *        *Note - Method adjustGridSize() assumes that longest word is on the top
	 *                If a change is made to this sort then adjustGridSize() will need to be modified to find longest word
	 */
	private function sortWordList(){
		usort($this->wordList, function($a, $b) {
			return $this->getWordLength($b) - $this->getWordLength($a);
		});
	}
	
	/*
	 * Adjust gridsize based off the longest word
	 */
	private function adjustGridSize(){
		if($this->getWordLength($this->wordList[0]) > $this->width){
			$this->width = $this->getWordLength($this->wordList[0]);
		}
		
		if($this->getWordLength($this->wordList[0]) > $this->height){
			$this->height = $this->getWordLength($this->wordList[0]);
		}
	}
	
	/*
	 * Generates an empty board based off width and height sizes
	 */
	private function generateBoard($width, $height){
		$this->solution = array_fill(0, $height, array_fill(0, $width, 0));
	}
	
	/*
	 * Creates a solution for the crossword puzzle.
	 * First places the first word (the one with most potential) and then continues to place the others.
	 * Scans through each placed letter on the grid trying to see if it has an intersection with current word.
	 * If it does, determine the starting cell for going down/across and see if the word can be placed.
	 * After a whole loop through the remaining words without placing anything then end placement.
	 */
	private function generateSolution(){
		// Create a temporary word list for solution generation
		$words = $this->wordList;
		
		$placeLocation = [];
		
		// Place first word
		$placeLocation = $this->placeFirstWord($words[0]);
		$this->placeWord($words[0], $placeLocation);
		
		// Remove first word from word list
		unset($words[0]);
		array_values($words);
	
		// start loop for placing words
		// loop is done once it goes through a whole iteration without adding a word
		$done = false;
		while(!$done){
			// Set done to true - will change to false if a word is placed
			$done = true;
			
			// Create a temp list to go through
			$placingWordList = $words;
			
			// Attempt to place each remaining word
			foreach($placingWordList as $key => $word){
				
				// Attempt to find a placeable location for the current word
				// If a location is found it is returned as [0] = row, [1] = column
				// If no location found then false is returned
				$placeLocation = $this->findOpenLocation($word);
				
				if($placeLocation != false){
					// Place the word in the found location
					$this->placeWord($word, $placeLocation);
					
					// Remove from word list
					unset($words[$key]);
					array_values($words);
					
					// Set done to false to continue through another loop
					$done = false;
				}
			}
		}
		
		$this->unplacedWordList = $words;
	}
	
	/*
	 * Places first word based off the firstWordSetting passed in on Crossword initialization.
	 * 0 = place first word at cell (0, 0) on the grid going across
	 * 1 = place first word at cell (0, 0) on the grid going down
	 * 2 = place first word on the last row going across at last available position
	 * 3 = place first word in random direction in a random cell
	 */
	private function placeFirstWord($word){
		$placeLocation = [];
		$length = $this->getWordLength($word);
		
		// Place word going across at spot 0, 0
		if($this->firstWordSetting == 0){
			$row = 0;
			$col = 0;
			$direction = "right";
		}
		else if($this->firstWordSetting == 1){
			$row = 0;
			$col = 0;
			$direction = "down";
		}
		else if($this->firstWordSetting == 2){
			$row = $this->height - 1;
			$col = $this->width - $length;
			$direction = "right";
		}
		else{
			$direction = rand(0, 1);
			
			if($direction == 0){
				$row = rand(0, $this->height - 1);
				$col = rand(0, $this->width - $length);
				$direction = "right";
				
			}
			else{
				$direction = "down";
				$row = rand(0, $this->height - $length);
				$col = rand(0, $this->width - 1);
			}
		}
		
		$placeLocation[0] = $row;
		$placeLocation[1] = $col;
		$placeLocation[2] = $direction;

		return $placeLocation;
	}
	
	/*
	 * Takes an input word, splits into letters, and then checks each letter placed on the grid to the letters of the word.
	 * If there is a match, then check to see if the word can be placed from a given starting position.
	 * First loop checks across, the second loop checks down.
	 * If the word can be placed, then return the starting position with [0] as row, [1] as column, [2] as direction.
	 * Else the word can't be placed on the grid then return false.
	 */
	private function findOpenLocation($word){
		// Get length and split word into letters
		$wordLength = $this->getWordLength($word);
		$wordLetters = $this->splitWord($word);
		
		// Iterate through list of all placed letters and check to see if they exist in the word
		foreach($this->placedLetters as $loc){
			$placedLetter = $loc[0];
			$row = $loc[1];
			$col = $loc[2];
			
			// Create an array of each time the letter is found in the word
			$foundLetters = [];
			$foundLetters = array_keys($wordLetters, $placedLetter);
			
			
			if(count($foundLetters) !== 0){
				// Check if word can be placed for each time the letter appears in the word
				// Ex: 'p' in Apple will be checked in the 1 and 2 positions
				foreach($foundLetters as $letterPosition){
					
					// Loop for each direction in the directions list (right, down)
					foreach($this->directions as $dir){
						
						// Get starting cell position based on direction and letter position in the word
						$startingCell = $this->getStartCell($letterPosition, $row, $col, $dir);				
							
						// If cell is not out of bounds, continue
						if(!$this->ifOutOfBounds($startingCell)){
							
							// Check if there's an intersection with another word
							// If no intersection, set to true, and loop for each letter in the word to see if it can be placed
							$checkingWordPlacement = !($this->checkIfWordIntersection($word, $startingCell, $dir));
							
							// Loop for each letter in the word and see if it can be placed on the grid.
							// If that letter can't be placed then break the loop
							$i = 0;
							while(($checkingWordPlacement) && ($i < $wordLength)){
								$checkingWordPlacement = $this->checkWordPlacement($i, $wordLetters, $startingCell, $dir);
								$i++;
							}
							
							// If the letters can be placed, check to see if the word can be placed
							if($checkingWordPlacement){
								
								// Check to see if the word has already been placed in that location. If so then don't place.
								// Example being if the same word is passed in twice then prevent it from being placed in the same location
								$ifDuplicate = $this->checkIfDuplicateWord($wordLetters, $startingCell, $dir);
								
								// Word can be placed - return array of row, col, and direction values
								if(!$ifDuplicate){
									$startingCell[2] = $dir;
									return $startingCell;
								}
							}
						}
					}
				}
			}	
		}
		// Word cannot be placed - return false
		return false;
	}
	
	/*
	 * Method for getting the starting cell based off the word, position of found letter in the word,
	 * the location of the already placed letter, and the direction.
	 * Returns startingPosition as [0] row and [1] col.
	 */
	private function getStartCell($pos, $row, $col, $dir){
		$startingPosition = [];
		if($dir == "right"){
			$startingPosition[0] = $row;
			$startingPosition[1] = $col - $pos;
		}
		else{
			$startingPosition[0] = $row - $pos;
			$startingPosition[1] = $col;
		}
		
		return $startingPosition;
	}
				
	private function ifOutOfBounds($cellPos){
		if($cellPos[0] < 0 || $cellPos[1] < 0){
			return true;
		}
		
		return false;
	}	
	
	/* 
	 * Checks to see if there will be an intersection with another word based on the starting location and direction.
	 * An intersection would include going in the same direction as another word and either riding on it or clashing into it.
	 * Example: Prevent the word Dog from being placed before the word Good in the same direction (d o g o o d)
	 * If across, then check the space to the right and left of the start/end positions
	 * If down, then check the up and down spots of the start/end positions
	 * Return true if there's an intersection, false if no intersection
	 */
	private function checkIfWordIntersection($word, $startingCell, $dir){
		$length = $this->getWordLength($word);
		
		if($dir == "right"){
			// Letter before start location
			$letter1 = $this->getLetterLeft($startingCell[0], $startingCell[1]);
			// Letter after end location
			$letter2 = $this->getLetter($startingCell[0], $startingCell[1] + $length);
		}
		else{
			// Letter before start location
			$letter1 = $this->getLetterUp($startingCell[0], $startingCell[1]);
			// Letter after end location
			$letter2 = $this->getLetter(($startingCell[0] + $length), $startingCell[1]);
		}
		
		// Return true if letter1 or letter2 is not 0 or null
		return !(($letter1 == "0" || is_null($letter1)) && ($letter2 == "0" || is_null($letter2)));
	}
	
	/*
	 * Method for checking to see if a given letter can be placed in a given cell.
	 * Logic is handled based off the direction, start location and letter position in word, 
	 * and what is happening in adjacent cells.
	 * Returns true if the letter can be placed or false if it can't.
	 */
	private function checkWordPlacement($pos, $wordLetters, $startingPosition, $dir){
		$cell = Array();
		$canBeUsed = false;

		// Set current placement cell based on position of the letter
		if($dir == "right"){
			$cell[0] = $startingPosition[0];
			$cell[1] = $startingPosition[1] + $pos;
		}
		else{
			$cell[0] = $startingPosition[0] + $pos;
			$cell[1] = $startingPosition[1];
		}
		
		// Get the current letter in the placement cell
		$cellLetter = $this->getLetter($cell[0], $cell[1]);
		$wordLetter = $wordLetters[$pos];	
		
		// If the letter is the same then the cell can be used
		if($cellLetter === $wordLetter){
			$canBeUsed = true;
		}
		// If letters aren't the same then the cell can't be used
		else if($cellLetter != "0"){
			$canBeUsed = false;
		}
		else{
			// Check if the cell can be used based off whether it is the first, middle, or last letter being placed
			// Start of word
			if($pos === 0){
				if($cellLetter == "0"){
					$canBeUsed = $this->checkOpenStartLetter($cell[0], $cell[1], $dir);
				}
			}
			// Middle of the word
			else if($pos !== (count($wordLetters) - 1)){
				if($cellLetter == "0"){
					$canBeUsed = $this->checkOpenMiddleLetter($cell[0], $cell[1], $dir);
				}
			}
			// End of word
			else{
				if($cellLetter == "0"){
					$canBeUsed = $this->checkOpenEndLetter($cell[0], $cell[1], $dir);
				}
			}
		}
		
		return $canBeUsed;	
	}
	
	/*
	 * Method for checking if a words first letter can be placed in a given cell based off the words direction if it's an open placement.
	 * Returns false if letter can't be placed or true if it can.
	 */
	private function checkOpenStartLetter($row, $col, $dir){
		// Check up, left, and down for null or 0 values
		if($dir == "right"){
			$spaceLetter = $this->getLetterUp($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterDown($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterLeft($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		// Check Up, Left, and Right for null or 0 values
		else{
			$spaceLetter = $this->getLetterUp($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterRight($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterLeft($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		
		return true;
	}
	
	/*
	 * Method for checking if a words middle letters can be placed in a given cell based off the words direction if it's an open placement.
	 * Returns false if letter can't be placed or true if it can.
	 */
	private function checkOpenMiddleLetter($row, $col, $dir){
		// Check up and down for null or 0 values
		if($dir == "right"){
			$spaceLetter = $this->getLetterUp($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterDown($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		// Check left and right for for null or 0 values
		else{
			$spaceLetter = $this->getLetterRight($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterLeft($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		
		return true;
	}
	
	/*
	 * Method for checking if a word's last letter can be placed in a given cell based off the words direction if it's an open placement.
	 * Returns false if letter can't be placed or true if it can.
	 */
	private function checkOpenEndLetter($row, $col, $dir){
		// Check up, down, and right for null or 0 values
		if($dir == "right"){
			$spaceLetter = $this->getLetterUp($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterDown($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterRight($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		// Check down, left, and right for null or 0 values
		else{
			$spaceLetter = $this->getLetterDown($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterRight($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
			
			$spaceLetter = $this->getLetterLeft($row, $col);
			
			if($spaceLetter != 0 || $spaceLetter != null){
				return false;
			}
		}
		
		return true;
	}
	
	/*
	 * Checks if there an idential word exists at the start section going in that direction.
     * Returns true if there is, false if there isn't	 
	 */
	private function checkIfDuplicateWord($wordLetters, $startingCell, $dir){
		$i = 0;
		
		foreach($wordLetters as $letter){
			if($dir == "right"){
				if($letter !== ($this->solution[$startingCell[0]][$startingCell[1]+$i])){
					return false;
				}
			}
			else{
				if($letter !== ($this->solution[$startingCell[0]+$i][$startingCell[1]])){
					return false;
				}
			}

			$i++;
		}

		return true;
	}
	
	/*
	 * Creates a puzzle based off the generated solution.
	 * Must be called after the initial creation of the Crossword class.
	 * Creates a list of across and down words with their hints and assigns each a number.
	 * Numbers are then placed on the puzzle grid.
	 * If two words start at the same cell, but different directions, then they will both have the same number.
	 */
	public function generatePuzzle(){
		// Copy list of across and down words
		// $listAcross = $this->placedWordListAcross;
		// $listDown = $this->placedWordListDown;
		
		$wordList = $this->placedWords;
		
		// Sort the words by their placement on the grid
		// Words on higher rows and lowest columns take priority
		// $listAcross = $this->sortPlacedWords($listAcross);
		// $listDown = $this->sortPlacedWords($listDown);
		$wordList = $this->sortPlacedWords($wordList);
		
		// Start looping for each word going across and assign it a number
		// If that word's starting position also contains a word going down then assign the number also to the down word.
		// When assigning down words do not repeate a number already used for a down word assigned during across loop.
		$numberCount = 1;
		
		
		for($i = 0, $size = count($wordList); $i < $size; $i++){
			$currentWord = $wordList[$i];
			
			if(isset($currentWord[5])){
				continue;
			}
			
			if($i < (count($wordList)) - 1){
				$nextWord = $wordList[$i+1];

				if($currentWord[1] == $nextWord[1] && $currentWord[2] == $nextWord[2]){
					$wordList[$i+1][5] = $numberCount;
					$wordList[$i+1][6] = $this->getHintForPuzzleList($nextWord[0]);
				}
			}
			
			$wordList[$i][5] = $numberCount;
			$wordList[$i][6] = $this->getHintForPuzzleList($currentWord[0]);
			$numberCount++;
		}
		$this->puzzleNumbers = $wordList;
		
		/*
		$downNumbersUsed = [];
		
		// Assign a number to each word going across
		foreach($listAcross as &$wordPlacement){
			
			// Check to see if there's a word going down at start position
			$wordDown = $this->findAtLocation($listDown, $wordPlacement);
			
			// Assign the wordPlacement the puzzle number
			$wordPlacement[3] = $numberCount;
			
			// Create a puzzle record: puzzle number, row, column, direction, word, hint
			$puzzleNum = [];
			$puzzleNum[0] = $numberCount;
			$puzzleNum[1] = $wordPlacement[1];
			$puzzleNum[2] = $wordPlacement[2];
			$puzzleNum[3] = "right";
			$puzzleNum[4] = $wordPlacement[0];
			$puzzleNum[5] = $this->getHintForPuzzleList($wordPlacement[0]);
			
			// Add word to puzzle list
			array_push($this->puzzleNumbers, $puzzleNum);

			// If a word also exists down then create another record, but with the down word details
			if($wordDown >= 0){
				$puzzleNum[4] = $listDown[$wordDown][0];
				$puzzleNum[3] = "down";
				$puzzleNum[5] = $this->getHintForPuzzleList($listDown[$wordDown][0]);
				
				array_push($this->puzzleNumbers, $puzzleNum);
				
				// Set value so that word can be skipped during down loop
				$listDown[$wordDown][3] = $numberCount;
				
				// Add value to array that is used during down loop
				array_push($downNumbersUsed, $numberCount);
			}
			
			$numberCount++;
		}
		
		// Loop for each word going down
		foreach($listDown as &$wordPlacement){
			// If word hasn't been numbered yet then number it
			if(!isset($wordPlacement[3])){
				
				// Increment the numberCount until a new down number is found
				while(in_array($numberCount, $downNumbersUsed)){
					$numberCount++;
				}
				
				// Assign puzzle number to wordPlacement
				$wordPlacement[3] = $numberCount;
				
				// Create a puzzle record: puzzle number, row, column, direction, word, hint
				// TODO - Redo this ordering to make more sense 
				$puzzleNum = [];
				$puzzleNum[0] = $numberCount;
				$puzzleNum[1] = $wordPlacement[1];
				$puzzleNum[2] = $wordPlacement[2];
				$puzzleNum[3] = "down";
				$puzzleNum[4] = $wordPlacement[0];
				$puzzleNum[5] = $this->getHintForPuzzleList($wordPlacement[0]);
				
				array_push($this->puzzleNumbers, $puzzleNum);
				
				$numberCount++;
			}	
		}*/
		
		// Sort puzzle words so they display in order by puzzle number
		//asort($this->puzzleNumbers);
	
		// Create the puzzle board based off assigned puzzle numbers
		$this->generatePuzzleBoard();
		
		// Set the puzzle words for fill in configuration - sorts words by length
		$this->setFillInPuzzle();
		
		// Reduce the grid size for empty grid rows/columns at the beginning/end of solution
		// Must be done last since placements rely on full grid
		$this->reduceGridSize();
		
		// Generate character list for Skeleton puzzle
		$this->generateSkeletonHints();
	}
	
	/*
	 * Sort word list based off the location of the placed word.
	 * Priority should be row then column.
	 * Example: Word in cell (0, 0) should be sorted to top, while (width, length) should be at the bottom
	 * Keeps puzzle numbers in order based off their placement on the grid.
	 * Returns the sorted list.
	 */
	private function sortPlacedWords($placedWordList){
		
		// Custom sort to sort the words
		usort($placedWordList, function($a, $b) {
			// row a > row b
			if($a[1] > $b[1]){
				return 1;
			}
			// row a == row b
			else if($a[1] == $b[1]){
				// col a > col b
				if($a[2] > $b[2]){
					return 1;
				}
				// col a == col b
				else if($a[2] == $b[2]){
					if($b[3] == "right"){
						return 1;
					}
					else{
						return -1;
					}
				}
				// col a < col b
				else{
					return -1;
				}
			}
			// row a < row b
			else{
				return -1;
			}
		});
		
		return $placedWordList;
	}
	
	/*
	 * Searches for a word based off starting location passed in searchWordPlacement
	 * If the starting location exists in the passed in word list, then return that array value
	 * This is used when creating puzzle numbers for the across word list, but then looking up if there
	 * is also a word in the down list at the same location so the number can be assigned to both.
	 * Return -1 if no word is found, otherwise return index of found word.
	 */
	private function findAtLocation($placedWordList, $searchWordPlacement){
		$i = 0;
		
		foreach($placedWordList as $word){
			if($searchWordPlacement[1] == $word[1] && $searchWordPlacement[2] == $word[2]){
				return $i;
			}
			
			$i++;
		}
		
		return -1;
	}
	
	/*
	 * Lookup method for getting the hints needed for the puzzle list.
	 * Returns the given words hint and unsets it from the unsortedWordList.
	 * Must unset the word in case the same word is used multiple times, but with different hints.
	 */
	private function getHintForPuzzleList($word){
		$pos = array_search($word, $this->unsortedWordList);
		$hint = $this->hintList[$pos];
		
		// Unset the word from the lists incase there is a duplicate word with different hints
		unset($this->hintList[$pos]);
		array_values($this->hintList);
		unset($this->unsortedWordList[$pos]);
		array_values($this->unsortedWordList);
		
		return $hint[1];
	}
	
	/*
	 * Generates a puzzle board based off the puzzle numbers created in generatePuzzle() method.
	 * First converts each letter in the placed grid to a blank value.
	 * Then places the puzzle numbers at each starting location.
	 */
	private function generatePuzzleBoard(){
		$this->puzzle = $this->solution;
		
		$i = 0;
		$j = 0;
		
		foreach($this->puzzle as &$row){
			foreach($row as &$col){
				if($col != "0"){
					$col = " ";
				}
			}
		}
		
		foreach($this->puzzleNumbers as $placedLocation){
			$this->puzzle[$placedLocation[1]][$placedLocation[2]] = $placedLocation[5];
		}
	}

	
	/*
	 * Places word in passed in placeLocation: [0] row, [1] col, [2] dir.
	 * Add each placed letter tot he placedLetters list.
	 * Add each placed word into either the placed lists for across or down words.
	 * Increment score when a word is added.
	 */
	private function placeWord($word, $placeLocation){
		$row = $placeLocation[0];
		$col = $placeLocation[1];
		$dir = $placeLocation[2];
		
		// Create placedWord array to be added to placedWord list
		$length = $this->getWordLength($word);
		$letters = $this->splitWord($word);
		
		$placedWord = [];
		$placedWord[0] = $word;
		$placedWord[1] = $row;
		$placedWord[2] = $col;
		$placedWord[4] = $length;
		
		
		$addedLetter = [];
		
		// For each letter place it on the grid and then add it to placed letters array
		if($dir == "right"){
			for($i = 0; $i < $length; $i++){
				if($this->solution[$row][$col + $i] == "0"){
					$addedLetter[0] = $letters[$i];
					$addedLetter[1] = $row;
					$addedLetter[2] = $col + $i;
					
					array_push($this->placedLetters, $addedLetter);
				}
				$this->solution[$row][$col + $i] = $letters[$i];
			}
			
			$placedWord[3] = "right";
			
			array_push($this->placedWords, $placedWord);
		}
		else{
			for($i = 0; $i < $length; $i++){
				if($this->solution[$row + $i][$col] == "0"){
					$addedLetter[0] = $letters[$i];
					$addedLetter[1] = $row + $i;
					$addedLetter[2] = $col;
					
					array_push($this->placedLetters, $addedLetter);
				}
				$this->solution[$row + $i][$col] = $letters[$i];
			}
			$placedWord[3] = "down";
			array_push($this->placedWords, $placedWord);
		}
		
		// Increment score to keep track of how many words have been placed on the grid
		$this->score++;
	}
	
	/*
	 * Removes any columns or rows that appear at the beginning/end of the generated puzzle and solution
	 * This removes a lot of empty space from large puzzles
	 * Must be called after puzzle has been completed since many values are based on absolute positioning on the grid
	 * Loops go from top/bottom to find empty rows/columns.  If empty, then unset it. If not empty than break loop
	 * since there should be no more empties that direction.
	 */
	private function reduceGridSize(){
		// Delete columns first - otherwise issue happens where columns don't get deleted
		// Fix would be in the count(array_unique()) line, but was easier to just remove columns first.
		
		// Delete blank columns on right side
		for($i = $this->width - 1; $i >= 0; $i--){
			$column = $this->getColumn($i);
			
			if((count(array_unique($column)) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0"){
				$this->removeColumn($i);
			}
			else{
				break;
			}
		}
		
		// Delete blank columns on left side
		// Get reference to column size first since values will be unset
		$columnCount = count($this->solution[0]);
		
		for($i = 0; $i < $columnCount; $i++){
			$column = $this->getColumn($i);
			
			if((count(array_unique($column)) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0"){
				$this->removeColumn($i);
			}
			else{
				break;
			}
		}
		
		// Delete the blank rows on top - only delete if whole row is blank (0)
		for($i = 0; $i <= $this->height - 1; $i++){
			if((count(array_unique($this->solution[$i])) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0"){
				$this->removeRow($i);
				
				// De-increment since array keys are reset
				$i--;
			}
			else{
				break;
			}
		}
		
		// Delete the blank rows on bottom - only delete if whole row is blank (0)
		for($i = count($this->solution) - 1; $i >= 0; $i--){
			
			if((count(array_unique($this->solution[$i])) == 1) && array_values(array_unique($this->solution[$i]))[0] == "0"){
				$this->removeRow($i);
			}
			else{
				break;
			}
		}
	}
	
	private function generateSkeletonHints(){
		$characterList = [];
		
		foreach($this->puzzleNumbers as $placedWord){
			$chars = $this->splitWord($placedWord[0]);
			
			foreach($chars as $char){
				array_push($characterList, $char);
			}
		}
		
		shuffle($characterList);
		//$characterList = array_unique($characterList);
		
		$this->skeletonHints = $characterList;
	}
	
	private function getColumn($col){
		$column = [];

		for($i = 0; $i < count($this->solution); $i++){
			array_push($column, $this->solution[$i][$col]);
		}
		
		return $column;
	}
	
	/*
	 * Removes the row by unsetting it and then re-indexes
	 */
	private function removeRow($i){
		unset($this->solution[$i]);
		unset($this->puzzle[$i]);
		$this->solution = array_values($this->solution);
		$this->puzzle = array_values($this->puzzle);
	}
	
	/*
	 * Removes the column from the input position by unsetting all of that column's values in each row and then re-indexes
	 */
	private function removeColumn($col){
		for($i = 0; $i < count($this->solution); $i++){
			unset($this->solution[$i][$col]);
			unset($this->puzzle[$i][$col]);
			$this->solution = array_values($this->solution);
			$this->puzzle = array_values($this->puzzle);
		}
		
	}
	
	/*
	 * Creates the fillin puzzle hints by ordering the hint list by word length and then adds the word's length to spot [6] of placed words
	 */
	private function setFillInPuzzle(){
		$hintList = $this->puzzleNumbers;
		//var_dump($hintList);
		$fillinList = [];
		
		usort($hintList, function($a, $b) {
			return $b[4] - $a[4];
		});
		
		$i = 0;
		foreach($hintList as $hint){
			$fillinList[$i] = [];
			array_push($fillinList[$i], $hintList[$i][0]);
			array_push($fillinList[$i], $this->wordProcessor->setWord($hintList[$i][4], "telugu"));
			$i++;
		}
		
		//var_dump($fillinList);
		$this->fillinList = $fillinList;
	}
	
	/*** Functions for cell values ***/
	
	private function getLetterUp($row, $col){
		if($row != 0){
			return $this->solution[$row - 1][$col];
		}
		else{
			return null;
		}
	}
	
	private function getLetterDown($row, $col){
		if($row < ($this->height - 1)){
			return $this->solution[$row + 1][$col];
		}
		else{
			return null;
		}
	}
	
	private function getLetterLeft($row, $col){
		if($col != 0){
			return $this->solution[$row][$col - 1];
		}
		else{
			return null;
		}
	}
	
	private function getLetterRight($row, $col){
		
		if($col < ($this->width - 1)){
			return $this->solution[$row][$col + 1];
		}
		else{
			return null;
		}
	}
	
	private function getLetter($row, $col){
		if($row >= 0 && $row < $this->height && $col >=0 && $col < $this->width){
			return $this->solution[$row][$col];
		}
		else{
			return null;
		}
	}
	
	
	
	/*** Getter functions ***/
	
	public function getSolution(){
		return $this->solution;
	}
	
	public function getPuzzle(){
		return $this->puzzle;
	}
	
	public function getUnplacedWords(){
		return $this->unplacedWordList;
	}
	
	public function getPuzzleNumbers(){
		return $this->puzzleNumbers;
	}
	
	public function getScore(){
		return $this->score;
	}
	
	public function getFillInHints(){
		return $this->fillinList;
	}
	
	public function getSkeletonHints(){
		return $this->skeletonHints;
	}
	
	/*** Word Processor Functions ***/
	private function getWordLength($word){
		$this->wordProcessor->setWord($word, "telugu");
		
		return $this->wordProcessor->getLength();
	}
	
	private function splitWord($word){
		$this->wordProcessor->setWord($word, "telugu");
		
		return $this->wordProcessor->getLogicalChars();
	}
}