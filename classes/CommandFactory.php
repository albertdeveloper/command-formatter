<?php
include 'myClass.php';

class CommandFactory 
{
    public function initializeFileFormat($type)
    {
        if($type == 'json')
            return new JSON();
        elseif ($type == 'csv')
            return new CSV();
        
        echo 'Unsupported type';
    }
}