<?php

namespace murica_api;

require_once 'autoloader.php';

$pw = "Esther@Server030522";

//use Exception;
//use murica_api\Controllers\AuthController;
//use murica_api\Controllers\BaseController;
//use murica_api\Controllers\Controller;
//use murica_api\Controllers\ErrorController;
//use murica_bl_impl\DataSource\Factories\DataSourceFactory;
//use murica_bl_impl\Services\ConfigService\ConfigService;
//use murica_bl_impl\Services\TokenService\ArrayTokenService;
//
//$BASE_URI = '/murica_api/';
//
//$errorController = new ErrorController('error/');
//
//$tokenService = new ArrayTokenService();
//try {
//    $configService = new ConfigService(__DIR__ . '/configs.json');
//    $dataSource = (new DataSourceFactory($configService))->createDataSource();
//    $userDao = $dataSource->createUserDao();
//} catch (Exception $ex) {
//    exit($errorController->internalServerError(['errorMessage' => $ex->getMessage()]));
//}
//
//$controllers = [
//    new BaseController('', $userDao),
//    new AuthController('auth/', $tokenService, $userDao),
//    $errorController
//];
//
//$requestData = array();
//switch ($_SERVER['REQUEST_METHOD']) {
//    case 'POST':
//        $requestData = $_POST;
//        break;
//    case 'GET':
//        $requestData = $_GET;
//        break;
//    case 'PUT':
//    case 'PATCH':
//        parse_str(file_get_contents('php://input'), $requestData);
//
//        //if the information received cannot be interpreted as an arrangement it is ignored.
//        if (!is_array($requestData)) {
//            $requestData = array();
//        }
//
//        break;
//    default:
//        //TODO: implement here any other type of request method that may arise.
//        break;
//}
//
//if (isset($_SERVER['HTTP_X_API_KEY'])) {
//    $requestData['token'] = $_SERVER['HTTP_X_API_KEY'];
//}
//
//$parsedURI = parse_url($_SERVER['REQUEST_URI']);
//$endpointName = str_replace($BASE_URI, '', $parsedURI['path']);
//
//if (empty($endpointName)) {
//    $endpointName = '/';
//}
//
//header('Content-Type: application/json; charset=UTF-8');
//
///* @var $controller Controller */
//foreach ($controllers as $controller) {
//    $endpoints = $controller->getEndpoints();
//
//    if (!isset($endpoints[$endpointName])) {
//        continue;
//    }
//
//    $endpoint = $endpoints[$endpointName];
//
//    if (!isset($controller->getPublicEndpoints()[$endpoint])
//        && !(isset($requestData['token']) && $tokenService->verifyToken($requestData['token']))) {
//
//        echo $errorController->unauthorized(null);
//        exit;
//    }
//
//    echo $controller->$endpoint($requestData);
//    exit;
//}
//
//echo $errorController->notFound(['endpointName' => $endpointName]);

//phpinfo();

$tns = "
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = orania2.inf.u-szeged.hu)(PORT = 1521))
    )
    (CONNECT_DATA =
      (SID = orania2)
    )
  )";

$conn = oci_connect('C##YTWK3B', $pw, $tns, 'UTF8');
oci_close($conn);
