<?php

namespace murica_api;

require_once 'autoloader.php';

use Exception;
use murica_api\Controllers\AuthController;
use murica_api\Controllers\BaseController;
use murica_api\Controllers\Controller;
use murica_api\Controllers\ErrorController;
use murica_api\Controllers\UserController;
use murica_api\Exceptions\QueryException;
use murica_bl\Exceptions\MuricaException;
use murica_bl_impl\DataSource\Factories\DataSourceFactory;
use murica_bl_impl\Services\ConfigService\ConfigService;
use murica_bl_impl\Services\TokenService\ArrayTokenService;

//ini_set('display_errors',0);
header('Content-Type: application/json; charset=UTF-8');

$errorController = new ErrorController('error');

$tokenService = new ArrayTokenService();
try {
    $configService = new ConfigService(__DIR__ . '/configs.json');
    $dataSource = (new DataSourceFactory($configService))->createDataSource();
    $userDao = $dataSource->createUserDao();
} catch (MuricaException $ex) {
    exit($errorController->internalServerError(['errorMessage' => $ex->getTraceMessages()]));
} catch (Exception $ex) {
    exit($errorController->internalServerError(['errorMessage' => $ex->getMessage()]));
}

$controllers = [
    new BaseController('', $userDao),
    new AuthController('auth', $tokenService, $userDao),
    new UserController('users', $userDao, $configService),
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

if (isset($_SERVER['HTTP_X_API_KEY'])) {
    $requestData['token'] = $_SERVER['HTTP_X_API_KEY'];
}

$parsedURI = parse_url($_SERVER['REQUEST_URI']);
$endpointName = str_replace($configService->getBaseUri(), '', $parsedURI['path']);

if (empty($endpointName)) {
    $endpointName = '/';
}

/* @var $controller Controller */
foreach ($controllers as $controller) {
    $endpoints = $controller->getEndpoints();

    if (!isset($endpoints[$endpointName])) {
        continue;
    }

    $endpoint = $endpoints[$endpointName];

    if (!isset($controller->getPublicEndpoints()[$endpoint])
        && !(isset($requestData['token']) && $tokenService->verifyToken($requestData['token']))) {

        echo json_encode($errorController->unauthorized(null));
        exit;
    }

    try {
        echo json_encode($controller->$endpoint($requestData));
    } catch (QueryException $ex) {
        exit(json_encode($errorController->notFound(['resource' => $ex->getMessage()])));
    } catch (MuricaException $ex) {
        exit(json_encode($errorController->internalServerError(['errorMessage' => $ex->getTraceMessages()])));
    } catch (Exception $ex) {
        exit(json_encode($errorController->internalServerError(['errorMessage' => $ex->getMessage()])));
    }

    exit;
}

echo json_encode($errorController->notFound(['endpoint' => $endpointName]));
