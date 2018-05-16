<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';
use \Core\Parser AS Parser;
use Slim\App;
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);
$app->get('/getlist', function ($request, $response, $args) use ($app) {
    $PttParser = new Parser\Ptt\Main();
    $total_image_url = $PttParser->_Start();
    return $response->withJSON(
        $total_image_url,
        200,
        JSON_UNESCAPED_UNICODE
    );
});
$app->run();