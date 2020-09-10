<?php

interface CommandInterface
{
    public function process($actual_link, $fileNameOnly, $toConvertFormat);
    public function convertTo($actual_link, $fileNameOnly, $toConvertFormat);
}

class CSV implements CommandInterface
{
    public function process($actual_location, $fileNameOnly, $toConvertFormat)
    {
        $this->convertTo($actual_location, $fileNameOnly, $toConvertFormat);
    }
    
    public function convertTo($actual_location, $fileNameOnly, $toConvertFormat)
    {
        $fileFormat = $fileNameOnly . '.' . $toConvertFormat;
        
        if ($toConvertFormat == 'json') {
            if (!($fp = fopen($actual_location, 'r'))) {
                die("Can't open file...");
            }
            $key = fgetcsv($fp, "1024", ",");
            
            $json = array();
            while ($row = fgetcsv($fp, "1024", ",")) {
                $json[] = array_combine($key, $row);
            }
            fclose($fp);
            file_put_contents($fileFormat, json_encode($json));
        }
        
        if ($toConvertFormat == 'yaml') {
            $file = fopen($actual_location, 'r') or die('error');
            
            $yml    = '';
            $indent = str_repeat(' ', 4);
            $keys   = array();
            
            // set the key or get the heading as key
            while (($values = fgetcsv($file, 0, ',')) !== FALSE) {
                
                if (empty($keys)) {
                    $keys = $values;
                } else {
                    if (count($keys) !== count($values)) {
                        echo 'Not clean sheet on';
                        print_r($values);
                        exit;
                    }
                    
                    $yml .= "–\n";
                    $arr = array_combine($keys, $values);
                    
                    foreach ($arr as $key => $value) {
                        $yml .= "{$indent}{$key}: {$value}\n";
                    }
                }
            }
            fclose($file);
            
            file_put_contents($fileFormat, $yml);
            
        }
        echo $fileFormat . ' - created';
    }
}

class JSON implements CommandInterface
{
    public function process($actual_link, $fileNameOnly, $toConvertFormat)
    {
        $this->convertTo($actual_link, $fileNameOnly, $toConvertFormat);
    }
    
    function convertTo($jfilename, $fileNameOnly, $toConvertFormat)
    {
        $fileFormat = $fileNameOnly . '.' . $toConvertFormat;
        
        if ($toConvertFormat == 'csv') {
            $cfilename = $fileNameOnly . '.' . $toConvertFormat;
            if (($json = file_get_contents($jfilename)) == false)
                die('Error reading json file...');
            $data   = json_decode($json, true);
            $fp     = fopen($cfilename, 'w');
            $header = false;
            foreach ($data as $row) {
                if (empty($header)) {
                    $header = array_keys($row);
                    fputcsv($fp, $header);
                    $header = array_flip($header);
                }
                fputcsv($fp, array_merge($header, $row));
            }
            fclose($fp);
            
        }
        if ($toConvertFormat == 'yaml') {
            $cfilename = $fileNameOnly . '.' . $toConvertFormat;
            if (($json = file_get_contents($jfilename)) == false)
                die('Error reading json file...');
            $data   = json_decode($json, true);
            $fp     = fopen($cfilename, 'w');
            $header = false;
            $yml    = '';
            $indent = str_repeat(' ', 4);
            
            foreach ($data as $row) {
                if (empty($header)) {
                    $header = array_keys($row);
                }
                if (count($header) !== count($row)) {
                    echo 'Not clean sheet on';
                    print_r($values);
                    exit;
                }
                
                $yml .= "–\n";
                $arr = array_combine($header, $row);
                
                foreach ($arr as $key => $value) {
                    $yml .= "{$indent}{$key}: {$value}\n";
                }
            }
            
            file_put_contents($fileFormat, $yml);
        }
        
        echo $fileFormat . ' - created';
    }
}

class YAML implements CommandInterface
{
    public function process($actual_link, $fileNameOnly, $toConvertFormat)
    {
        $this->convertTo($actual_link, $fileNameOnly, $toConvertFormat);
    }
    public function convertTo($actual_link, $fileNameOnly, $toConvertFormat)
    {
        $fileFormat = $fileNameOnly . '.' . $toConvertFormat;
        $header     = false;
        
        $fp    = file_get_contents($actual_link);
        $datas = explode("–", $fp);
        $array = array();
        
        foreach ($datas as $key => $data) {
            if ($data !== '') {
                $parts = explode("\n", $data);
                foreach ($parts as $k => $yml) {
                    if (!empty($yml) && $yml !== '–') {
                        $convertToArray = explode(":", $yml);
                        
                        $convertToArray[0]               = trim($convertToArray[0]);
                        $convertToArray[1]               = trim($convertToArray[1]);
                        $array[$key][$convertToArray[0]] = $convertToArray[1];
                    }
                    
                }
            }
        }
        
        if ($toConvertFormat == 'json') {
            file_put_contents($fileFormat, json_encode($array));
        }
        
        if ($toConvertFormat == 'csv') {
            $data = json_decode(json_encode($array), true);
            $fp   = fopen($fileFormat, 'w');
            foreach ($data as $row) {
                if (empty($header)) {
                    $header = array_keys($row);
                    fputcsv($fp, $header);
                    $header = array_flip($header);
                }
                fputcsv($fp, array_merge($header, $row));
            }
            fclose($fp);
        }
        echo $fileFormat . ' - created';
        
    }
}
?>