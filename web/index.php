<?php
require __DIR__ . '/../vendor/autoload.php';
use Slim\App;
use Core\Parser;
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new \Slim\App();
$app->get('/parser/{parser_type}', function ($request, $response, $args) use ($app) {
    switch ($args['parser_type']) {
        case "Ptt":
            $PttParser = new Parser\Ptt\Main();
            $total_image_url = $PttParser->_Start();
            $new_response = new \Slim\Http\Response();
            return $new_response->withJSON(
                $total_image_url,
                200
            );
            break;
        case "UCar":
            $Parser = new Parser\UCar\Main();
            $Parser->_Start();
            $new_response = new \Slim\Http\Response();
            return $new_response->withJSON(
                [],
                200
            );
            break;
        case 2:
            echo "i equals 2";
            break;
    }
});
$app->get('/info/{parser_type}', function ($request, $response, $args) use ($app) {
    switch ($args['parser_type']) {
        case "UCar":
            $Parser = new Parser\UCar\Main();
            $result = $Parser->_Select();
            $new_response = new \Slim\Http\Response();
            return $new_response->withJSON(
                $result,
                200
            );
    }
});
$app->run();