<?php
require __DIR__ . '/../vendor/autoload.php';
use \Core\Parser AS Parser;
//Obj
$PttParser = new Parser\Ptt\Main();
$total_image_url = $PttParser->_Start();
var_dump($total_image_url);
//****** Write to json ******/
// $fp = fopen(__DIR__.'/img_result.json', 'w');
// fwrite($fp, json_encode($total_image_url));
// fclose($fp);
