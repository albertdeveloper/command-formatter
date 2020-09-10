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
            file_put_contents($fileNameOnly . '.' . $toConvertFormat, json_encode($json));
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
                    
                    $yml .= "-\n";
                    $arr = array_combine($keys, $values);
                    
                    foreach ($arr as $key => $value) {
                        $yml .= "{$indent}{$key}: {$value}\n";
                    }
                }
            }
            fclose($file);
            
            file_put_contents($fileNameOnly . '.' . $toConvertFormat, $yml);
        }
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
                
                $yml .= "-\n";
                $arr = array_combine($header, $row);
                
                foreach ($arr as $key => $value) {
                    $yml .= "{$indent}{$key}: {$value}\n";
                }
            }
            file_put_contents($fileNameOnly . '.' . $toConvertFormat, $yml);
        }
    }
    
}

?>