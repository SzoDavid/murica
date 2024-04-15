<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Course;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\TakenCourse;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class CourseController extends Controller {
    //region Properties
    private ICourseDao $courseDao;
    private ISubjectDao $subjectDao;
    private IRoomDao $roomDao;
    private ITakenCourseDao $takenCourseDao;
    private IStudentDao $studentDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, ICourseDao $courseDao, ISubjectDao $subjectDao, IRoomDao $roomDao, ITakenCourseDao $takenCourseDao, IStudentDao $studentDao) {
        parent::__construct($router);
        $this->courseDao = $courseDao;
        $this->subjectDao = $subjectDao;
        $this->roomDao = $roomDao;
        $this->takenCourseDao = $takenCourseDao;
        $this->studentDao = $studentDao;

        $this->router->registerController($this, 'course')
            ->registerEndpoint('allCourses', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getCourseByIdAndSubjectId', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createCourse', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateCourse', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteCourse', 'delete', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('registerCourse', 'register', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unregisterCourse', 'unregister', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns a collection of all courses from the datasource.
     * No parameters required.
     */
    public function allCourses(string $uri, array $requestData): IModel {
        try {
            $courses = $this->courseDao->findAll();
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query courses',
                                  $e->getTraceMessages());
        }

        $courseEntities = [];

        foreach ($courses as $course) {
            try {
                $courseEntities[] = (new EntityModel($this->router, $course, true))
                    ->linkTo('allCourses', CourseController::class, 'allCourses')
                    ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
            } catch (ModelException $e) {
                return new ErrorModel($this->router, 500, 'Failed to query courses', $e->getTraceMessages());
            }
        }

        try {
            return (new CollectionModel($this->router, $courseEntities, 'courses', true))
                ->withSelfRef(CourseController::class, 'allCourses');
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query courses', $e->getTraceMessages());
        }
    }

    public function getCourseByIdAndSubjectId(string $uri, array $requestData): IModel {
        $id = $requestData['id'] ?? null;
        $subjectId = $requestData['subjectId'] ?? null;

        if (empty($id) || empty($subjectId)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query course',
                                  'Both "id" and "subjectId" parameters are required');
        }
        try {
            $courses = $this->courseDao->findByCrit(new Course($id, $subjectId));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query course',
                                  $e->getTraceMessages());
        }
        if (empty($courses)) {
            return new ErrorModel($this->router,
                                  404,
                                  'Course not found',
                                  "Course not found with id '$id' and subjectId '$subjectId'");
        }
        try {
            return (new EntityModel($this->router, $courses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query course', $e->getTraceMessages());
        }
    }

    /**
     * Returns with the course created with the given values.
     * Parameters are expected as part of request data.
     */
    public function createCourse(string $uri, array $requestData): IModel {

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['capacity']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "capacity" is not provided in uri');
        if (!isset($requestData['schedule']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "schedule" is not provided in uri');
        if (!isset($requestData['term']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "term" is not provided in uri');
        if (!isset($requestData['roomId']))
            return new ErrorModel($this->router, 400, 'Failed to create Course', 'Parameter "roomId" is not provided in uri');

        try {
            $this->subjectDao -> findByCrit(new Subject($requestData['id']));
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        try {
            $this->roomDao -> findByCrit(new Room($requestData['roomId']));
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        try {
            $courses = $this->courseDao->create(new Course($requestData['id'],
                                                           $requestData['subjectId'],
                                                           $requestData['capacity'],
                                                           $requestData['schedule'],
                                                           $requestData['term'],
                                                           $requestData['roomId']));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $courses[0], true))
                ->linkTo('allCourses', UserController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create user', $e->getTraceMessages());
        }
    }

    public function updateCourse(string $uri, array $requestData): IModel {

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "subjectId" is not provided in uri');

        try {
            $courses = $this->courseDao->findByCrit(new Course($requestData['id'],
                                                               $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to update course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $this->roomDao -> findByCrit(new Room($requestData['roomId']));
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }

        try {
            $this->courseDao->update(new Course($requestData['id'],
                                                $requestData['subjectId'],
                                                $requestData['capacity'],
                                                $requestData['schedule'],
                                                $requestData['term'],
                                                $requestData['roomId']));

            return new MessageModel($this->router, ['message' => 'Course updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }
    }

    public function deleteCourse(string $uri, array $requestData): IModel {

        if (!isset($requestData['id']) || !isset($requestData['subjectId'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete course', 'Both "id" and "subjectId" parameters are required');
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($requestData['id'], $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to delete course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $this->courseDao->delete($courses[0]);
            return new MessageModel($this->router, ['message' => 'Course deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete course', $e->getTraceMessages());
        }
    }

    public function registerCourse(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to register Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to register Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to register Course', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeTame']))
            return new ErrorModel($this->router, 400, 'Failed to register Course', 'Parameter "programmeType" is not provided in uri');

        try {
            $courses = $this->courseDao->findByCrit(new Course($requestData['id'], $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $student = $this->studentDao->findByCrit(new Student($user->getId(),$requestData['programmeName'],$requestData['programmeType']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($student)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Already not found student with id '{$user->getId()}'");
        }

        try {
            $takecourse = $this->takenCourseDao->findByCrit(new TakenCourse(new Student($user->getId(),$requestData['programmeName'],$requestData['programmeType']),$requestData['id'], $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (!empty($takecourse)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Found registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $takecourses = $this->takenCourseDao->create(new TakenCourse($student[0],
                                                                         $courses[0],
                                                                         null,
                                                                         0
                                                         ));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $takecourses[0], true))
                ->linkTo('allCourses', UserController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register user', $e->getTraceMessages());
        }
    }

    public function unregisterCourse(string $uri, array $requestData): IModel {

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Course', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeTame']))
            return new ErrorModel($this->router, 400, 'Failed to unregister Course', 'Parameter "programmeType" is not provided in uri');

        try {
            $courses = $this->courseDao->findByCrit(new Course($requestData['id'], $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $student = $this->studentDao->findByCrit(new Student($user->getId(),$requestData['programmeName'],$requestData['programmeType']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($student)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Already not found student with id '{$user->getId()}'");
        }

        try {
            $takecourses = $this->takenCourseDao->findByCrit(new TakenCourse(new Student($user->getId(),$requestData['programmeName'],$requestData['programmeType']),$requestData['id'], $requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($takecourses)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Taken course not registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $this->courseDao->delete($takecourses[0]);
            return new MessageModel($this->router, ['message' => 'Course unregister successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

    }
    //endregion

}
