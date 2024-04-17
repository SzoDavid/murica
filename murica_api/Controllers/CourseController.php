<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\ICourseDao;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dao\ITakenCourseDao;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Dto\ICourseTeach;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
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
    private IAdminDao $adminDao;
    private ICourseTeachDao $courseTeachDao;
    private IUserDao $userDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, ICourseDao $courseDao, ISubjectDao $subjectDao, IRoomDao $roomDao, ITakenCourseDao $takenCourseDao, IStudentDao $studentDao, IAdminDao $adminDao, ICourseTeachDao $courseTeachDao, IUserDao $userDao) {
        parent::__construct($router);
        $this->courseDao = $courseDao;
        $this->subjectDao = $subjectDao;
        $this->roomDao = $roomDao;
        $this->takenCourseDao = $takenCourseDao;
        $this->studentDao = $studentDao;
        $this->adminDao = $adminDao;
        $this->courseTeachDao = $courseTeachDao;
        $this->userDao = $userDao;

        $this->router->registerController($this, 'course')
            ->registerEndpoint('allCourses', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getCourseByIdAndSubjectId', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getCourseByTeacher', 'byTeacher', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createCourse', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateCourse', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteCourse', 'delete', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('registerCourse', 'register', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unregisterCourse', 'unregister', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('addTeacherToCourse', 'add', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('removeTeacherFromCourse', 'remove', EndpointRoute::VISIBILITY_PRIVATE);

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
            $courseEntities = [];

            foreach ($courses as $course) {
                $courseEntities[] = (new EntityModel($this->router, $course, true))
                    ->linkTo('allCourses', CourseController::class, 'allCourses')
                    ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $course->getId(), 'subjectId' => $course->getSubjectId()]);
            }

            return (new CollectionModel($this->router, $courseEntities, 'courses', true))
                ->withSelfRef(CourseController::class, 'allCourses');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query courses',
                                  $e->getTraceMessages());
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
            $courses = $this->courseDao->findByCrit(new Course(new Subject($subjectId), $id));

            if (empty($courses)) {
                return new ErrorModel($this->router,
                                      404,
                                      'Failed to query course',
                                      "Course not found with id '$subjectId-$id'");
            }

            return (new EntityModel($this->router, $courses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $courses[0]->getId(), 'subjectId' => $courses[0]->getSubjectId()]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query course',
                                  $e->getTraceMessages());
        }
    }

    public function getCourseByTeacher(string $uri, array $requestData): IModel {
        /* @var $user IUser */
        $user = $requestData['token']->getUser();

        try {
            $teachCourses = $this->courseTeachDao->findByCrit((new CourseTeach($user)));
            $coursesEntities = [];

            /* @var $teachCourse ICourseTeach */
            foreach ($teachCourses as $teachCourse) {
                $coursesEntities[] = (new EntityModel($this->router, $teachCourse, true))
                    ->withSelfRef(CourseController::class, 'getCourseByTeacher');
            }

            return (new CollectionModel($this->router, $coursesEntities, 'courses', true))
                ->withSelfRef(CourseController::class, 'getCourseByTeacher');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query courses', $e->getTraceMessages());
        }
    }

    /**
     * Returns with the course created with the given values.
     * Parameters are expected as part of request data.
     */
    public function createCourse(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to create course', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
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
           $subjects =  $this->subjectDao->findByCrit(new Subject($requestData['subjectId']));

            if (empty($subjects)) {
                return new ErrorModel($this->router, 404, 'Failed to create course', "Subject not found with id '{$requestData['subjectId']}'");
            }

            $rooms = $this->roomDao -> findByCrit(new Room($requestData['roomId']));

            if (empty($rooms)) {
                return new ErrorModel($this->router, 404, 'Failed to create course', "Room not found with id '{$requestData['roomId']}'");
            }

            $courses = $this->courseDao->create(new Course($subjects[0],
                                                           $requestData['id'],
                                                           $requestData['capacity'],
                                                           $requestData['schedule'],
                                                           $requestData['term'],
                                                           $rooms[0]));
            return (new EntityModel($this->router, $courses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(), 'subjectId' => $courses[0]->getSubjectId()]);

        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create course', $e->getTraceMessages());
        }
    }

    public function updateCourse(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to update course', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to update Course', 'Parameter "subjectId" is not provided in uri');

        try {
            $courses = $this->courseDao->findByCrit(new Course(new Subject($requestData['subjectId']), $requestData['id']));

            if (empty($courses)) {
                return new ErrorModel($this->router, 404, 'Failed to update course', "Course not found with id '{$requestData['subjectId']}-{$requestData['id']}'");
            }

            $rooms = $this->roomDao->findByCrit(new Room($requestData['roomId']));

            if (empty($rooms)) {
                return new ErrorModel($this->router, 404, 'Failed to update course', "Room not found with id '{$requestData['roomId']}'");
            }

            $this->courseDao->update(new Course($courses[0]->getSubject(),
                                                $requestData['id'],
                                                $requestData['capacity'],
                                                $requestData['schedule'],
                                                $requestData['term'],
                                                $rooms[0]));

            return new MessageModel($this->router, ['message' => 'Course updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update course', $e->getTraceMessages());
        }
    }

    public function deleteCourse(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to delete course', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete course', $e->getTraceMessages());
        }

        if (!isset($requestData['id']) || !isset($requestData['subjectId'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete course', 'Both "id" and "subjectId" parameters are required');
        }

        try {
            $courses = $this->courseDao->findByCrit(new Course(new Subject($requestData['subjectId']), $requestData['id']));

            if (empty($courses)) {
                return new ErrorModel($this->router, 404, 'Failed to delete course', "Course not found with id '{$requestData['subjectId']}-{$requestData['id']}'");
            }

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

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $students = $this->studentDao->findByCrit(new Student($user, new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($students)) {
                return new ErrorModel($this->router, 403, 'Failed to register course', 'Access is forbidden');
            }

            $courses = $this->courseDao->findByCrit(new Course(new Subject($requestData['subjectId']), $requestData['id']));

            if (empty($courses)) {
                return new ErrorModel($this->router, 404, 'Failed to register course', "Course not found with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $takeCourses = $this->takenCourseDao->findByCrit(new TakenCourse($students[0], $courses[0]));

            if (!empty($takeCourses)) {
                return new ErrorModel($this->router, 404, 'Failed to register course', "Found registered with id '{$requestData['id']}' and subjectId '{$requestData['subjectId']}'");
            }

            $takeCourses = $this->takenCourseDao->create(new TakenCourse($students[0],
                                                                         $courses[0],
                                                                         0,
                                                                         false));

            return (new EntityModel($this->router, $takeCourses[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId', [], ['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (DataAccessException|ValidationException|ModelException $e) {
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

        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $students = $this->studentDao->findByCrit(new Student($user, new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($students)) {
                return new ErrorModel($this->router, 403, 'Failed to unregister course', "Access is forbidden");
            }

            $takeCourses = $this->takenCourseDao->findByCrit(new TakenCourse($students[0],
                                                                             new Course(new Subject($requestData['subjectId']),
                                                                                        $requestData['id'])));

            if (empty($takeCourses)) {
                return new ErrorModel($this->router, 404, 'Failed to unregister course', "Course not registered with id '{$requestData['subjectId']}-{$requestData['id']}' for user '{$user->getId()}'");
            }

            $this->courseDao->delete($takeCourses[0]);
            return new MessageModel($this->router, ['message' => 'Course unregister successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unregister course', $e->getTraceMessages());
        }
    }

    public function addTeacherToCourse(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to add teacher to Course', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to Course', $e->getTraceMessages());
        }

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['teacherId']))
            return new ErrorModel($this->router, 400, 'Failed to add teacher to Course', 'Parameter "teacherId" is not provided in uri');

        try {
            $courses = $this->courseDao->findByCrit(new Course(new Subject($requestData['subjectId']), $requestData['id']));

            if (empty($courses)) {
                return new ErrorModel($this->router, 404, 'Failed to add teacher to course', "Course not found with id '{$requestData['subjectId']}-{$requestData['id']}'");
            }

            $users = $this->userDao->findByCrit(new User($requestData['teacherId']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to add teacher to course', "User not found with id '{$requestData['teacherId']}'");
            }

            $teachCourse = $this->courseTeachDao->create(new CourseTeach($users[0],
                                                                         $courses[0]));

            return (new EntityModel($this->router, $teachCourse[0], true))
                ->linkTo('allCourses', CourseController::class, 'allCourses')
                ->withSelfRef(CourseController::class, 'getCourseByIdAndSubjectId',[],['id' => $courses[0]->getId(),'subjectId' => $courses[0]->getSubjectId()]);
        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to add teacher to course', $e->getTraceMessages());
        }
    }

    public function removeTeacherFromCourse(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed remove add teacher from Course', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher from Course', $e->getTraceMessages());
        }

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher from Course', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['subjectId']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher from Course', 'Parameter "subjectId" is not provided in uri');
        if (!isset($requestData['teacherId']))
            return new ErrorModel($this->router, 400, 'Failed to remove teacher from Course', 'Parameter "teacherId" is not provided in uri');

        try {
            $teachCourses = $this->courseTeachDao->findByCrit(new CourseTeach(new User($requestData['teacherId']), new Course(new Subject($requestData['subjectId'], $requestData['id']))));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to remove teacher from course', "Teacher not found user with id '{$requestData['teacherId']}' for course {$requestData['subjectId']}-{$requestData['id']}");
            }

            $this->courseTeachDao->delete($teachCourses[0]);
            return new MessageModel($this->router, ['message' => 'Remove teacher successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to remove teacher from course', $e->getTraceMessages());
        }
    }
    //endregion
}
