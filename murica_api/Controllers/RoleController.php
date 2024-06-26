<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\ICourseTeachDao;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dao\IStudentDao;
use murica_bl\Dao\IUserDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Admin;
use murica_bl_impl\Dto\CourseTeach;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Dto\Role;
use murica_bl_impl\Dto\Student;
use murica_bl_impl\Dto\User;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class RoleController extends Controller {
    //region Properties
    private IUserDao $userDao;
    private IAdminDao $adminDao;
    private IStudentDao $studentDao;
    private IProgrammeDao $programmeDao;
    private ICourseTeachDao $courseTeachDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IUserDao $userDao, IAdminDao $adminDao, IStudentDao $studentDao, IProgrammeDao $programmeDao, ICourseTeachDao $courseTeachDao) {
        parent::__construct($router);
        $this->userDao = $userDao;
        $this->adminDao = $adminDao;
        $this->studentDao = $studentDao;
        $this->programmeDao = $programmeDao;
        $this->courseTeachDao = $courseTeachDao;

        $this->router->registerController($this, 'role')
            ->registerEndpoint('allRoles', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('setAdmin', 'setAdmin', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unsetAdmin', 'unsetAdmin', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('setStudent', 'setStudent', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('unsetStudent', 'unsetStudent', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns with the user roles.
     * Parameters are expected as part of request data.
     */
    public function allRoles(string $uri, array $requestData): IModel {
        $role = new Role();

        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to get roles',
                                  'Parameter "id" is not provided in request');
        }

        try {
            $users = $this->userDao->findByCrit(new User($uri));

            if (empty($users)) {
                return new ErrorModel($this->router,
                                      404,
                                      'Failed to get roles',
                                      "User not found with id '$uri'");
            }

            $admin = $this->adminDao->findByCrit(new Admin(new User($uri)));
            $teacher = $this->courseTeachDao->findByCrit(new CourseTeach(new User($uri)));
            $students = $this->studentDao->findByCrit(new Student(new User($uri)));

            !empty($admin) ? $role->setAdminRole(true) : $role->setAdminRole(false);
            !empty($teacher) ? $role->setTeacherRole(true) : $role->setTeacherRole(false);
            $role->setStudents($students);

            return (new EntityModel($this->router, $role, true))
                ->linkTo('setAdmin', RoleController::class, 'setAdmin', [$uri])
                ->linkTo('unsetAdmin', RoleController::class, 'unsetAdmin', [$uri])
                ->linkTo('setStudent', RoleController::class, 'setStudent')
                ->linkTo('unsetStudent', RoleController::class, 'unsetStudent')
                ->withSelfRef(RoleController::class, 'allRoles', [$uri]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query course',
                                  $e->getTraceMessages());
        }
    }

    /**
     * Returns with the admin created.
     * Parameters are expected as part of request data.
     */
    public function setAdmin(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to give admin role', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to give admin role', $e->getTraceMessages());
        }

        if (!isset($uri))
            return new ErrorModel($this->router, 400, 'Failed to give admin role', 'Parameter "id" is not provided in uri');

        try {
            $users = $this->userDao->findByCrit(new User($uri));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to give admin role', "User not found with id '$uri'");
            }
            $admin = $this->adminDao->create(new Admin($users[0]));
            return (new EntityModel($this->router, $admin, true))
                ->linkTo('role', RoleController::class, 'allRoles')
                ->withSelfRef(RoleController::class, 'setAdmin', [], ['id' => $admin->getUser()->getId()]);

        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to give admin role', $e->getTraceMessages());
        }
    }

    public function unsetAdmin(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to unset admin role', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unset admin role', $e->getTraceMessages());
        }

        if (!isset($uri)) {
            return new ErrorModel($this->router, 400, 'Failed to unset admin role', 'Parameter "id" is not provided in uri');
        }

        try {
            $admin = $this->adminDao->findByCrit(new Admin(new User($uri)));

            if (empty($admin)) {
                return new ErrorModel($this->router, 404, 'Failed to unset admin role', "Admin not found with id '$uri'");
            }

            $this->adminDao->delete($admin[0]);
            return new MessageModel($this->router, ['message' => 'Admin role unsetted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unset admin role', $e->getTraceMessages());
        }
    }

    /**
     * Returns with the student created with the given values.
     * Parameters are expected as part of request data.
     */
    public function setStudent(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to set Student role', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to set Student role', $e->getTraceMessages());
        }

        if (!isset($requestData['userId']))
            return new ErrorModel($this->router, 400, 'Failed to set Student role', 'Parameter "userId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to set Student role', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeType']))
            return new ErrorModel($this->router, 400, 'Failed to set Student role', 'Parameter "programmeType" is not provided in uri');
        if (!isset($requestData['startTerm']))
            return new ErrorModel($this->router, 400, 'Failed to set Student role', 'Parameter "startTerm" is not provided in uri');
        try {
            $users = $this->userDao->findByCrit(new User($requestData['userId']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to set Student role', "User not found with id '{$requestData['userId']}'");
            }

            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['programmeName'], $requestData['programmeType']));

            if (empty($programmes)) {
                return new ErrorModel($this->router, 404, 'Failed to set Student role', "Programme not found with name '{$requestData['programmeName']}' and type '{$requestData['programmeType']}'");
            }
            $student = $this->studentDao->create(new Student($users[0], $programmes[0], $requestData['startTerm']));
            return (new EntityModel($this->router, $student, true))
                ->linkTo('role', RoleController::class, 'allRoles')
                ->withSelfRef(RoleController::class, 'setStudent', [], ['userId' => $student->getUser()->getId(), 'programmeName' => $student->getProgramme()->getName(), 'programmeType' => $student->getProgramme()->getType()]);

        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to set student role', $e->getTraceMessages());
        }
    }

    public function unsetStudent(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to unset Student role', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unset Student role', $e->getTraceMessages());
        }

        if (!isset($requestData['userId']))
            return new ErrorModel($this->router, 400, 'Failed to unset Student role', 'Parameter "userId" is not provided in uri');
        if (!isset($requestData['programmeName']))
            return new ErrorModel($this->router, 400, 'Failed to unset Student role', 'Parameter "programmeName" is not provided in uri');
        if (!isset($requestData['programmeType']))
            return new ErrorModel($this->router, 400, 'Failed to unset Student role', 'Parameter "programmeType" is not provided in uri');

        try {
            $student = $this->studentDao->findByCrit(new Student(new User($requestData['userId']), new Programme($requestData['programmeName'], $requestData['programmeType'])));

            if (empty($student)) {
                return new ErrorModel($this->router, 404, 'Failed to unset Student role', "Student not found with userId '{$requestData['userId']}' and programme '{$requestData['programmeName']}/{$requestData['programmeType']}'");
            }

            $this->studentDao->delete($student[0]);
            return new MessageModel($this->router, ['message' => 'Student role unsetted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to unset Student role', $e->getTraceMessages());
        }
    }
    //endregion

}