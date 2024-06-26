<?php

namespace murica_api;

require_once 'autoloader.php';

use Exception;
use murica_api\Controllers\AuthController;
use murica_api\Controllers\ExamController;
use murica_api\Controllers\MessageController;
use murica_api\Controllers\CourseController;
use murica_api\Controllers\ProgrammeController;
use murica_api\Controllers\RoleController;
use murica_api\Controllers\RoomController;
use murica_api\Controllers\SubjectController;
use murica_api\Controllers\UserController;
use murica_bl\Exceptions\MuricaException;
use murica_bl_impl\DataSource\Factories\DataSourceFactory;
use murica_bl_impl\Router\Router;
use murica_bl_impl\Services\ConfigService\ConfigService;
use murica_bl_impl\Services\TokenService\DataSourceTokenService;

header('Content-Type: application/json; charset=UTF-8');

try {
    $configService = new ConfigService(__DIR__ . '/configs.json');
    ini_set('display_errors', $configService->getDisplayError());
} catch (MuricaException $ex) {
    exit(json_encode(['_success' => false,
                         'error' => [
                             'code' => 500,
                             'message' => 'Failed to load configs',
                             'details' => $ex->getTraceMessages()]]));
} catch (Exception $ex) {
    exit(json_encode(['_success' => false,
                         'error' => [
                            'code' => 500,
                            'message' => 'Failed to load configs',
                            'details' => $ex->getMessage()]]));
}

try {
    $dataSource = (new DataSourceFactory($configService))->createDataSource();
    $userDao = $dataSource->createUserDao();
    $programmeDao = $dataSource->createProgrammeDao();
    $roomDao = $dataSource->createRoomDao();
    $subjectDao = $dataSource->createSubjectDao();
    $examDao = $dataSource->createExamDao();
    $takenExamDao = $dataSource->createTakenExamDao();
    $adminDao = $dataSource->createAdminDao();
    $studentDao = $dataSource->createStudentDao();
    $messageDao = $dataSource->createMessageDao();
    $courseDao = $dataSource->createCourseDao();
    $takenCourseDao = $dataSource->createTakenCourseDao();
    $courseTeachDao = $dataSource->createCourseTeachDao();

    $tokenService = new DataSourceTokenService($dataSource->createTokenDao());

    $router = new Router($configService, $tokenService);

    $authController = new AuthController($router, $userDao, $tokenService);
    $userController = new UserController($router, $userDao, $adminDao, $courseTeachDao, $takenCourseDao, $takenExamDao);
    $programmeController = new ProgrammeController($router, $programmeDao, $adminDao);
    $roomController = new RoomController($router, $roomDao, $adminDao);
    $subjectController = new SubjectController($router, $subjectDao, $adminDao, $courseTeachDao);
    $examController = new ExamController($router, $examDao, $subjectDao, $roomDao, $takenExamDao, $studentDao, $adminDao,$userDao, $takenCourseDao);
    $messageController = new MessageController($router, $messageDao);
    $roleController = new RoleController($router, $userDao, $adminDao, $studentDao, $programmeDao, $courseTeachDao);
    $courseController = new CourseController($router, $courseDao, $subjectDao, $roomDao, $takenCourseDao, $studentDao, $adminDao, $courseTeachDao, $userDao);
} catch (MuricaException $ex) {
    exit(json_encode(['_success' => false,
                         'error' => [
                             'code' => 500,
                             'message' => 'Failed to initialize system',
                             'details' => $ex->getTraceMessages()]]));
} catch (Exception $ex) {
    exit(json_encode(['_success' => false,
                         'error' => [
                             'code' => 500,
                             'message' => 'Failed to initialize system',
                             'details' => $ex->getMessage()]]));
}

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
        //NOTE: implement here any other type of request method that may arise.
        break;
}

if (isset($_SERVER['HTTP_X_API_KEY'])) {
    $requestData['token'] = $_SERVER['HTTP_X_API_KEY'];
}

$parsedURI = parse_url($_SERVER['REQUEST_URI']);
$requestURI = str_replace('/' . $configService->getBaseUri() . '/', '', $parsedURI['path']);

if (empty($requestURI)) {
    $requestURI = '/';
}

echo json_encode($router->resolveRequest($requestURI, $requestData));
