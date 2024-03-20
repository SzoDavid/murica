<?php

namespace murica_api;

require_once 'autoloader.php';

use murica_api\Controllers\AuthController;
use murica_api\Controllers\BaseController;
use murica_api\Controllers\Controller;
use murica_api\Controllers\ErrorController;

$BASE_URI = "/murica_api/";

$authController = new AuthController("auth/");
$errorController = new ErrorController("error/");

$controllers = [
    new BaseController(""),
    $authController,
    $errorController
];

$requestData = array();
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $requestData = $_POST;
        break;
    case 'GET':
        $requestData = $_GET;
        break;
    case 'PUT':
    case 'PATCH':
        parse_str(file_get_contents('php://input'), $requestData);

        //if the information received cannot be interpreted as an arrangement it is ignored.
        if (!is_array($requestData)) {
            $requestData = array();
        }

        break;
    default:
        //TODO: implement here any other type of request method that may arise.
        break;
}

if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}

$parsedURI = parse_url($_SERVER["REQUEST_URI"]);
$endpointName = str_replace($BASE_URI, "", $parsedURI["path"]);

if (empty($endpointName)) {
    $endpointName = "/";
}

header("Content-Type: application/json; charset=UTF-8");

$found = false;

/* @var $controller Controller */
foreach ($controllers as $controller) {
    $endpoints = $controller->getEndpoints();

    if (isset($endpoints[$endpointName])) {
        $auth = $authController->checkToken($requestData);

        if (!$auth) {
            $endpoint = $endpoints[$endpointName];

            echo $controller->$endpoint($requestData);
        }

        echo $auth;

        $found = true;
    }
}

if (!$found) {
    echo $errorController->error404(array("endpointName" => $endpointName));
}

