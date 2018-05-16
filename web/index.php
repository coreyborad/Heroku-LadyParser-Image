<?php
require __DIR__ . '/../vendor/autoload.php';
use \Core\Parser AS Parser;
use Slim\App;
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App();
$app->get('/getlist', function ($request, $response, $args) use ($app) {
    $PttParser = new Parser\Ptt\Main();
    $total_image_url = $PttParser->_Start();
    $new_response = new \Slim\Http\Response();
    return $new_response->withJSON(
        $total_image_url,
        200
    );
});
$app->run();