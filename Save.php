<?php
    include_once('Connection.php');
    require("word_processor.php");

    $wordInput = $_POST['wordInput'];
    $puzzleType = $_POST['puzzletype'];

    $words = generateWordList($wordInput);

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


    if(is_array($words)){
        $dataArr = array();
        foreach($words as $row){
		echo '<br Answer >'.$row[0].'<br>';
		echo '<br Hint >'.$row[1].'<br>';

        $sql = "INSERT INTO crosswords VALUES ('$row[0]', '$row[1];) ";
        $result = mysqli_query($conn, $sql);
		}
    }

    //header("Location: ../index.php?save=success");
    echo '<script>alert("Answers and Clues saved")</script>';
?>
    