<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\IExamDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITakenExamDao;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IExam;
use murica_bl\Dto\ITakenExam;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Exam;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\TakenExam;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class ExamController extends Controller {
    //region Properties
    private IExamDao $examDao;
    private ISubjectDao $subjectDao;
    private IRoomDao $roomDao;
    private ITakenExamDao $takenExamDao;
    private IStudentDao $studentDao;
    private IAdminDao $adminDao;
    private IUserDao $userDao;

    //endregion

    //region Ctor
    public function __construct(IRouter $router, IExamDao $examDao, ISubjectDao $subjectDao, IRoomDao $roomDao, ITakenExamDao $takenExamDao, IStudentDao $studentDao, IAdminDao $adminDao, IUserDao $userDao) {
        parent::__construct($router);
        $this->examDao = $examDao;
        $this->subjectDao = $subjectDao;
        $this->roomDao = $roomDao;
        $this->takenExamDao = $takenExamDao;
        $this->studentDao = $studentDao;
        $this->adminDao = $adminDao;
        $this->userDao = $userDao;

        $this->router->registerController($this, 'exam')
            ->registerEndpoint('allExams', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getExamByIdAndSubjectId', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createExam', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateExam', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteExam', 'delete', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getExamsByStudent', 'takenExams', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getExamsByTeacher', 'teachExams', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('registerExam', 'register', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unregisterExam', 'unregister', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    public function allExams(string $uri, array $requestData): IModel {
        try {
            $exams = $this->examDao->findAll();

            $examEntities = [];

            /* @var $exam IExam */

            foreach ($exams as $exam) {
                $examEntities[] = (new EntityModel($this->router, $exam, true))
                    ->linkTo('allExams', ExamController::class, 'allExams')
                    ->withSelfRef(ExamController::class, 'getExamByIdAndSubjectId', [], ['id' => $exam->getId(), 'subjectId' => $exam->getSubject()->getId()]);
            }
            return (new CollectionModel($this->router, $examEntities, 'exams', true))
                ->withSelfRef(ExamController::class, 'allExams');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query exams',
                                  $e->getTraceMessages());
        }
    }

    public function getExamByIdAndSubjectId(string $uri, array $requestData): IModel {
        $id = $requestData['id'] ?? null;
        $subjectId = $requestData['subjectId'] ?? null;

        if (empty($id) || empty($subjectId)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query exams',
                                  'Both "id" and "subjectId" parameters are required');
        }

        try {
            $exams = $this->examDao->findByCrit(new Exam(new Subject($subjectId), $id));

            if (empty($exams)) {
                return new ErrorModel($this->router,
                                      404,
                                      'Exam not found',
                                      "Exam not found with id '$id' and subjectId '$subjectId'");
            }

            return (new EntityModel($this->router, $exams[0], true))
                ->linkTo('allExams', ExamController::class, 'allExams')
                ->withSelfRef(ExamController::class, 'getExamByIdAndSubjectId', [], ['id' => $exams[0]->getId(), 'subjectId' => $exams[0]->getSubject()->getId()]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query course',
                                  $e->getTraceMessages());
        }
    }

    public function getExamsByStudent(string $uri, array $requestData): IModel {
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to query taken exams', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeType']))
            return new ErrorModel($this->router, 400, 'Failed to query taken exams', 'Parameter "programmeType" is not provided in uri');

        /* @var $user IUser */
        $user = $requestData['token']->getUser();

        try {
            $students = $this->studentDao->findByCrit(new Student($user, new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($students)) {
                return new ErrorModel($this->router, 403, 'Failed to query taken exams', "Access is forbidden");
            }

            $takenExams = $this->takenExamDao->findByCrit(new TakenExam($students[0]));
            $takenExamsEntities = [];

            /* @var $takenExam ITakenExam */
            foreach ($takenExams as $takenExam) {
                $takenExamsEntities[] = (new EntityModel($this->router, $takenExam, true))
                    ->withSelfRef(ExamController::class, 'getExamsByStudent', [], [
                        'id' => $takenExam->getExam()->getId(),
                        'subjectId' => $takenExam->getExam()->getSubject()->getId()]);
            }

            return (new CollectionModel($this->router, $takenExamsEntities, 'takenExams', true))
                ->withSelfRef(CourseController::class, 'getExamsByStudent', [], [
                    'programmeName' => $requestData['programmeName'],
                    'programmeType' => $requestData['programmeType']]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query taken exams', $e->getTraceMessages());
        }
    }

    public function getExamsByTeacher(string $uri, array $requestData): IModel {
        /* @var $user IUser */
        $user = $requestData['token']->getUser();

        try {
            $exams = $this->examDao->findByCrit((new Exam())->setTeacher($user));
            $examsEntities = [];

            /* @var $exam ITakenExam */
            foreach ($exams as $exam) {
                $examsEntities[] = (new EntityModel($this->router, $exam, true))
                    ->linkTo('update', ExamController::class, 'updateExam')
                    ->linkTo('delete', ExamController::class, 'deleteExam')
                    ->withSelfRef(ExamController::class, 'getExamByIdAndSubjectId');
            }

            return (new CollectionModel($this->router, $examsEntities, 'exams', true))
                ->linkTo('new', ExamController::class, 'createExam')
                ->withSelfRef(ExamController::class, 'getExamsByTeacher');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query exams', $e->getTraceMessages());
        }
    }

    public function createExam(string $uri, array $requestData): IModel {
        // TODO: validate if user is teacher at given subject
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['startTime']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "startTime" is not provided in uri');
        if (!isset($requestData['endTime']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "endTime" is not provided in uri');
        if (!isset($requestData['teacherId']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "teacherId" is not provided in uri');
        if (!isset($requestData['roomId']))
            return new ErrorModel($this->router, 400, 'Failed to create exam', 'Parameter "roomId" is not provided in uri');

        try {
            $subjects = $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to create exam', "Subject not found with id '{$requestData['subjectId']}'");
            }

            $rooms = $this->roomDao->findByCrit(new Room($requestData['roomId']));

            if (empty($rooms)) {
                return new ErrorModel($this->router, 404, 'Failed to create exam', "Room not found with id '{$requestData['roomId']}'");
            }

            $teachers = $this->userDao->findByCrit(new User($requestData['teacherId']));

            if (empty($teachers)) {
                return new ErrorModel($this->router, 404, 'Failed to create exam', "User not found with id '{$requestData['teacherId']}'");
            }

            $exam = $this->examDao->create(new Exam($subjects[0],
                                                     $requestData['id'],
                                                     $requestData['startTime'],
                                                     $requestData['endTime'],
                                                     $teachers[0],
                                                     $rooms[0]));

            return (new EntityModel($this->router, $exam, true))
                ->linkTo('allExams', ExamController::class, 'allExams')
                ->withSelfRef(ExamController::class, 'getExamByIdAndSubjectId', [], ['id' => $exam->getId(), 'subjectId' => $exam->getSubject()->getId()]);

        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create exam', $e->getTraceMessages());
        }
    }

    public function updateExam(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to update exam', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update exam', $e->getTraceMessages());
        }

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update exam', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to update exam', 'Parameter "subjectId" is not provided in uri');

        try {

            $exams = $this->examDao->findByCrit(new Exam(new Subject($requestData['subjectId']), $requestData['id']));

            if (empty($exams)) {
                return new ErrorModel($this->router, 404, 'Failed to update exam', "Exam not found with id '{$requestData['subjectId']}-{$requestData['id']}'");
            }

            $rooms = $this->roomDao->findByCrit(new Room($requestData['roomId']));

            if (empty($rooms)) {
                return new ErrorModel($this->router, 404, 'Failed to update exam', "Room not found with id '{$requestData['roomId']}'");
            }

            $teacher = $this->userDao->findByCrit(new User($requestData['teacherId']));

            if (empty($teacher)) {
                return new ErrorModel($this->router, 404, 'Failed to update exam', "Already not found teacher (user) with id '{$requestData['teacherId']}'");
            }

            $subjects = $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to update exam', "Already not found subject with id '{$requestData['subjectId']}'");
            }

            $this->examDao->create(new Exam($subjects[0],
                                            $requestData['id'],
                                            $requestData['startTime'],
                                            $requestData['endTime'],
                                            $teacher[0],
                                            $rooms[0]));

            return new MessageModel($this->router, ['message' => 'Exam updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update exam', $e->getTraceMessages());
        }
    }

    public function deleteExam(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to delete exam', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete exam', $e->getTraceMessages());
        }

        if (!isset($requestData['id']) || !isset($requestData['subjectId'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete exam', 'Both "id" and "subjectId" parameters are required');
        }

        try {
            $subjects = $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to delete exam', "Already not found subject with id '{$requestData['subjectId']}'");
            }

            $exams = $this->examDao->findByCrit(new Exam($subjects[0], $requestData['id']));

            if (empty($exams)) {
                return new ErrorModel($this->router, 404, 'Failed to delete exam', "Exam not found with id '{$requestData['subjectId']}-{$requestData['id']}'");
            }

            $this->examDao->delete($exams[0]);
            return new MessageModel($this->router, ['message' => 'Exam deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete exam', $e->getTraceMessages());
        }
    }

    public function registerExam(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to register Exam', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to register Exam', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to register Exam', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeTame']))
            return new ErrorModel($this->router, 400, 'Failed to register Exam', 'Parameter "programmeType" is not provided in uri');

        /* @var $user IUser */
        $user = $requestData['token']->getUser();

        try {
            $subjects = $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to create exam', "Already not found subject with id '{$requestData['subjectId']}'");
            }

            $students = $this->studentDao->findByCrit(new Student($user, new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($students)) {
                return new ErrorModel($this->router, 403, 'Failed to register exam', 'Access is forbidden');
            }

            $exams = $this->examDao->findByCrit(new Exam($subjects[0], $requestData['id']));

            if (empty($exams)) {
                return new ErrorModel($this->router, 404, 'Failed to register exam', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $takeExams = $this->takenExamDao->findByCrit(new TakenExam($students[0], $exams[0]));

            if (!empty($takeExams)) {
                return new ErrorModel($this->router, 404, 'Failed to register exam', "Found registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $takeExams = $this->takenExamDao->create(new TakenExam(
                                                         $students[0],
                                                         $exams[0]
                                                     ));


            return (new EntityModel($this->router, $takeExams[0], true))
                ->linkTo('allExams', ExamController::class, 'allExams')
                ->withSelfRef(ExamController::class, 'getExamByIdAndSubjectId', [], ['id' => $exams[0]->getId(), 'subjectId' => $exams[0]->getSubjectId()]);
        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register exam', $e->getTraceMessages());
        }
    }

    public function unregisterExam(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Exam', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Exam', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Exam', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeTame']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Exam', 'Parameter "programmeType" is not provided in uri');

        /* @var $user IUser */
        $user = $requestData['token']->getUser();

        try {
            $subjects = $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to unregister exam', "Already not found subject with id '{$requestData['subjectId']}'");
            }

            $students = $this->studentDao->findByCrit(new Student($user, new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($students)) {
                return new ErrorModel($this->router, 403, 'Failed to unregistered exam', 'Access is forbidden');
            }

            $exams = $this->examDao->findByCrit(new Exam(new Subject($subjects[0], $requestData['id'])));

            if (empty($exams)) {
                return new ErrorModel($this->router, 404, 'Failed to unregister exam', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $takeExams = $this->takenExamDao->findByCrit(new TakenExam($students[0], $exams[0]));

            if (empty(!$takeExams)) {
                return new ErrorModel($this->router, 404, 'Failed to unregister exam', "Not found registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $this->takenExamDao->delete($takeExams[0]);
            return new MessageModel($this->router, ['message' => 'Exam unregistered successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister exam', $e->getTraceMessages());
        }

    }
    //endregion

}
