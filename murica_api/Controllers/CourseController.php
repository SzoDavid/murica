<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Admin;
use murica_bl_impl\Dto\Course;
use murica_bl_impl\Dto\CourseTeach;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Dto\TakenCourse;
use murica_bl_impl\Dto\User;
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
    private IProgrammeDao $programmeDao;
    private IAdminDao $adminDao;
    private ICourseTeachDao $cousreTeachDao;
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, ICourseDao $courseDao, ISubjectDao $subjectDao, IRoomDao $roomDao, ITakenCourseDao $takenCourseDao, IStudentDao $studentDao, IProgrammeDao $programmeDao,IAdminDao $adminDao, ICourseTeachDao $courseTeachDao, IUserDao $userDao) {
        parent::__construct($router);
        $this->courseDao = $courseDao;
        $this->subjectDao = $subjectDao;
        $this->roomDao = $roomDao;
        $this->takenCourseDao = $takenCourseDao;
        $this->studentDao = $studentDao;
        $this->adminDao = $adminDao;
        $this->programmeDao = $programmeDao;
        $this->cousreTeachDao = $courseTeachDao;
        $this->userDao = $userDao;

        $this->router->registerController($this, 'course')
            ->registerEndpoint('allCourses', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getCourseByIdAndSubjectId', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createCourse', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateCourse', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteCourse', 'delete', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('registerCourse', 'register', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unregisterCourse', 'unregister', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('addTeacherToCourse', 'add', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('removeTeacherToCourse', 'remove', EndpointRoute::VISIBILITY_PRIVATE);

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
                    ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $course->getId(),'subjectId' => $course->getSubjectId()]);
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

        try {
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        if (empty($id) || empty($subjectId)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query course',
                                  'Both "id" and "subjectId" parameters are required');
        }
        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0], $id));
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

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $admin = $this->adminDao->findByCrit(new Admin($user));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($admin)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found admin with id '{$user->getId()}'");
        }

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
           $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $room = $this->roomDao -> findByCrit(new Room($requestData['roomId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }
        if (empty($room)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found room with id '{$requestData['roomId']}'");
        }

        try {
            $courses = $this->courseDao->create(new Course($subject[0],
                                                           $requestData['id'],
                                                           $requestData['capacity'],
                                                           $requestData['schedule'],
                                                           $requestData['term'],
                                                           $room[0]));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $courses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create user', $e->getTraceMessages());
        }
    }

    public function updateCourse(string $uri, array $requestData): IModel {

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $admin = $this->adminDao->findByCrit(new Admin($user));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($admin)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found admin with id '{$user->getId()}'");
        }

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "subjectId" is not provided in uri');

        try {
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to update course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $room = $this->roomDao->findByCrit(new Room($requestData['roomId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }

        if (empty($room)) {
            return new ErrorModel($this->router, 404, 'Failed to update course', "Room not found with id '{$requestData['roomId']}'");
        }

        try {
            $this->courseDao->update(new Course($subject[0],
                                                $requestData['id'],
                                                $requestData['capacity'],
                                                $requestData['schedule'],
                                                $requestData['term'],
                                                $room[0]));

            return new MessageModel($this->router, ['message' => 'Course updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }
    }

    public function deleteCourse(string $uri, array $requestData): IModel {

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $admin = $this->adminDao->findByCrit(new Admin($user));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($admin)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found admin with id '{$user->getId()}'");
        }

        if (!isset($requestData['id']) || !isset($requestData['subjectId'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete course', 'Both "id" and "subjectId" parameters are required');
        }

        try {
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
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
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['programmeName'],$requestData['programmeType']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($programmes)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Programme not found with name '{$requestData['programmeName']}' and type '{$requestData['programmeType']}'");
        }

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $student = $this->studentDao->findByCrit(new Student($user,$programmes[0]));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($student)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Already not found student with id '{$user->getId()}'");
        }

        try {
            $takeCourses = $this->takenCourseDao->findByCrit(new TakenCourse($student[0],$courses[0]));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (!empty($takeCourses)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Found registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $takeCourses = $this->takenCourseDao->create(new TakenCourse($student[0],
                                                                         $courses[0],
                                                                         null,
                                                                         0
                                                         ));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $takeCourses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
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
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to create course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['programmeName'],$requestData['programmeType']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to register course', $e->getTraceMessages());
        }

        if (empty($programmes)) {
            return new ErrorModel($this->router, 404, 'Failed to register course', "Programme not found with name '{$requestData['programmeName']}' and type '{$requestData['programmeType']}'");
        }

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $student = $this->studentDao->findByCrit(new Student($user,$programmes[0]));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($student)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Already not found student with id '{$user->getId()}'");
        }

        try {
            $takeCourses = $this->takenCourseDao->findByCrit(new TakenCourse($student[0],$courses[0]));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }

        if (empty($takeCourses)) {
            return new ErrorModel($this->router, 404, 'Failed to unregister course', "Taken course not registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $this->courseDao->delete($takeCourses[0]);
            return new MessageModel($this->router, ['message' => 'Course unregister successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }
    }

    public function addTeacherToCourse(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to  Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['teacherId']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to  Course', 'Parameter "teacherId" is not provided in uri');

        try {
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to  course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to add teacher to  course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to add teacher to course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $users = $this->userDao->findByCrit(new User($requestData['teacherId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to course', $e->getTraceMessages());
        }

        if (empty($users)) {
            return new ErrorModel($this->router, 404, 'Failed to add teacher to course', "Course not found user with id '{$requestData['teacherId']}' and subjectId");
        }

        try {
            $teachCourse = $this->cousreTeachDao->create(new CourseTeach($users[0],
                                                                         $courses[0],
                                                         ));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to course', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $teachCourse[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to course', $e->getTraceMessages());
        }
    }

    public function removeTeacherToCourse(string $uri, array $requestData): IModel {

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher to Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher to  Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['teacherId']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher to  Course', 'Parameter "teacherId" is not provided in uri');

        try {
            $subject =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher to course', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to remove teacher to course', "Already not found subject with id '{$requestData['subjectId']}'");
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course($subject[0],$requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher to course', $e->getTraceMessages());
        }

        if (empty($courses)) {
            return new ErrorModel($this->router, 404, 'Failed to remove teacher to course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
        }

        try {
            $users = $this->userDao->findByCrit(new User($requestData['teacherId']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher to course', $e->getTraceMessages());
        }

        if (empty($users)) {
            return new ErrorModel($this->router, 404, 'Failed to remove teacher to course', "Course not found user with id '{$requestData['teacherId']}' and subjectId");
        }

        try {
            $teachCourses = $this->cousreTeachDao->findByCrit(new CourseTeach($users[0],$courses[0]));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher to course', $e->getTraceMessages());
        }

        if (empty($teachCourses)) {
            return new ErrorModel($this->router, 404, 'Failed to remove teacher to course', "Teach course not have with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}' and teacher '{$requestData['teacherId']}'");
        }

        try {
            $this->cousreTeachDao->delete($teachCourses[0]);
            return new MessageModel($this->router, ['message' => 'Remove teacher successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher to course', $e->getTraceMessages());
        }
    }
    //endregion

}
