<?php
require('./classes/CommandFactory.php');
 
// initial check for the file
 if($argc == 1 && !isset($argv[1]))
 {
     echo 'must include a file path or an HTTP network location such as http://localhost/customers.json';
     exit;
 }
 
// validate file
 $actual_location = $argv[1];
 $file = basename($actual_location);
 
 $fileExtension =  pathinfo($file, PATHINFO_EXTENSION);
 $fileNameOnly = current(explode(".", $file));
  

 $readableExtension = array('csv','json','yaml');

 // check if extension is passed

 if(!in_array($fileExtension,$readableExtension))
 {
     echo 'Invalid file format only accept <b>'.implode(', ',$readableExtension).'</b>.';
     exit;
 }

 $checkIfFileFound = @fopen($actual_location, 'r');

 if(!$checkIfFileFound)
 {
    echo 'file '.$file. ' not found on '. $actual_location;
    exit;
 }
 
 print('Found '.$file.PHP_EOL);

 $number = 1;
 $availableExtension = array();
 print('Select File Format to convert to'.PHP_EOL);

 foreach($readableExtension as $extension)
 {
    if($extension != $fileExtension)
    {
    print($number.'. ' .strtoupper($extension).PHP_EOL);
    $availableExtension[] = $extension;
    $number++;    
    }
 }
 
 $readlinestr = 'Please specify a file format ';
 $toConvertFormat = readline($readlinestr);
  
 
 if(!in_array($toConvertFormat,$availableExtension))
 {
     print_r('Invalid file format.Exiting'.PHP_EOL);
     exit;
 }

 $commandFactory = new CommandFactory();
 $interpreter = $commandFactory->initializeFileFormat($fileExtension);
 $interpreter->process($actual_location,$fileNameOnly,$toConvertFormat);

?>
