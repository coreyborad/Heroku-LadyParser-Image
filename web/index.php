<?php
require __DIR__ . '/../vendor/autoload.php';
use \Core\Parser AS Parser;
use Slim\App;
$app->get('/', function ($request, $response, $args) use ($app) {
    $PttParser = new Parser\Ptt\Main();
    $total_image_url = $PttParser->_Start();
    $newResponse = $response->withJson($total_image_url);
    $newResponse = $response->withHeader('Content-type', 'application/json');
    return $newResponse;
});
$app->run();