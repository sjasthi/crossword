
<?php
    include_once('Connection.php');
?>


<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN''http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

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
	</style>
</head>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<body>
    <form target="_blank" action="CrosswordPuzzle.php" method="post" class="form-horizontal">
        <div class="container-fluid">
            <div class="jumbotron" id="jumbos">
            </div>
            <div class="panel">
                <div class="panel-group">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div align="center"><h2>Crossword Puzzle Maker</h2></div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-1" style="text-align: left;">Title</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="title" name="title" value="Title">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-1" style="text-align: left;">Subtitle</label>
                                <div class="col-sm-9">
                                    <input class="form-control" id="subtitle" name="subtitle" value="Subtitle">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-1" style="text-align: left;">Grid Height</label>
                                <div class="col-sm-1">
                                    <input class="form-control" id="height" name="height" value="10">
                                </div>
                                <label class="control-label col-sm-1" style="text-align: left;">Grid Width</label>
                                <div class="col-sm-1">
                                    <input class="form-control" id="width" name="width" value="10">
                                </div>
                                <label class="control-label col-sm-5" style="text-align: left;">*Height and Width will adjust based on input</label>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-1" style="text-align: left;">Puzzle Mode</label>
                                <div class="col-sm-3">
                                    <select class="form-control" id="puzzletype" name="puzzletype" onchange="sizeChange(this.value);">
                                        <option value="crossword" selected="selected">Crossword</option>
                                        <option value="fillin" >Fill-In</option>
										<option value="skeleton" >Skeleton</option>
                                    </select>
                                </div>
                            </div>
                            <!--Added input box for batch numbers. Diabled by default but will enable upon skeleton option chosen. @kc9718us  -->
                            <div class ="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-1" sytle="text-align: left;">Batch Number</label>
                                <div class="col-sm-1">
                                    <input class="form-control" id="batchNumber" name="batchNumber" disabled="true" value="3">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <label class="control-label col-sm-9" style="text-align: left;">Input (Enter the Word and Clue separated by a comma, each pair on a separate line)
                                </label>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-10">
                                    <textarea class="form-control" rows="10" id="input" name="wordInput"></textarea>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="charLabel" style="color:red;font-size:14px" name="charName" value="">
                                        <?php
                                            // If there is a warning message after input validation display message to user
                                            if(isset($warningMessage)){
                                                echo($warningMessage);
                                            }
                                        ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                                <div class="row">
                                    <div class="text-center">
                                        <input type="submit" name="submit" class="btn btn-primary btn-lg" value="Generate">

                                        <!-- Added Save button. @kc9718us -->
                                        <input type="submit"id="save" name="save" formaction = "Save.php" class="btn btn-primary btn-lg" value="Save"> 

                                        <!-- Added Batch HTML and Batch PPT button. Diasabled by default but will enable upont skeleton option chosen. @kc9718us -->
                                        <input type="submit" id="batch" name="batch" formaction = "SkeletonPuzzle.php" disabled="true" class="btn btn-primary btn-lg" value="Batch HTML">
                                    
                                        <input type="submit" id="ppt" name="ppt" formaction = "SkeletonPPT.php" disabled = "true" class="btn btn-primary btn-lg" value="Batch PPT">

                                        <input type="submit" id="list" name="list" formaction = "list.php" class="btn btn-primary btn-lg" value="List">
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

<!-- Script for enabling and disabling batch features upon puzzle type selections. @kc9718us -->
<script>
$(document).ready(function () {
$('#puzzletype').change(function () {
    
    selectVal = $('#puzzletype').val();
    //console.log(selectVal);

   
    if (selectVal == 'skeleton') {
       
       $('#batch').removeAttr('disabled','disabled');
       $('#ppt').removeAttr('disabled','disabled');
       $('#batchNumber').removeAttr('disabled','disabled');
    }
    if(selectVal != 'skeleton'){
       
      $('#batch').attr('disabled','disabled');
      $('#ppt').attr('disabled','disabled');
      $('#batchNumber').attr('disabled','disabled');

    }
  })
  
});
</script>



