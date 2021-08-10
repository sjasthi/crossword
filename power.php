<?php

header("Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation");
header("Content-Disposition: attachment; filename=test.ppt");

require_once 'vendor/autoload.php';

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
//change
$objPHPPowerPoint = new PhpPresentation();

use PhpOffice\PhpPresentation\Shape\Drawing\Base64;

if($_REQUEST['powerCross'] != "") {

  $img1 = $_REQUEST['powerCross'];

} else {

  $img1 = $_REQUEST['powerPuzzle'];
}


$img2 = $_REQUEST['powerSolution'];

// Create slide
$currentSlide = $objPHPPowerPoint->getActiveSlide();

//Create text for Puzzle Slide
$textPuzzle = $currentSlide->createRichTextShape()
      ->setHeight(100)
      ->setWidth(300)
      ->setOffsetX(0)
      ->setOffsetY(0);
$textPuzzle->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
$textRun = $textPuzzle->createTextRun('Puzzle');
$textRun->getFont()->setBold(true)
                   ->setSize(40)
                   ->setColor( new Color( 'FFE06B20' ) );


//Create image for Puzzle Slide
$puzzleImage = new Base64();
$puzzleImage->setName('Image')
    ->setDescription('puzzle')
    ->setData($img1)
    ->setResizeProportional(false)
    ->setHeight(400)
    ->setWidth(500)
    ->setOffsetX(200)
    ->setOffsetY(200);
$currentSlide->addShape($puzzleImage);

// Create slide for Solution
$currentSlide2 = $objPHPPowerPoint->createSlide();

// Create text for Slide for Solution
$textSolution = $currentSlide2->createRichTextShape()
      ->setHeight(100)
      ->setWidth(300)
      ->setOffsetX(0)
      ->setOffsetY(0);
$textSolution->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
$textRun = $textSolution->createTextRun('Solution');
$textRun->getFont()->setBold(true)
                   ->setSize(40)
                   ->setColor( new Color( 'FFE06B20' ) );

// Create image for Slide for Solution
$puzzleSolution = new Base64();
$puzzleSolution->setName('Image')
    ->setDescription('solution')
    ->setData($img2)
    ->setResizeProportional(false)
    ->setHeight(400)
    ->setWidth(500)
    ->setOffsetX(200)
    ->setOffsetY(200);
$currentSlide2->addShape($puzzleSolution);

//Download
$oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
$oWriterPPTX->save('php://output');
