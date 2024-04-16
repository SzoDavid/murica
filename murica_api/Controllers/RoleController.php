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

        $this->router->registerController($this, 'user')
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
        $id = $requestData['id'] ?? null;
        $role = new Role();

        if (empty($id)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to get roles',
                                  'Parameter "id" is not provided in request');
        }

        try {
            $users = $this->userDao->findByCrit(new User($id));

            if (empty($users)) {
                return new ErrorModel($this->router,
                                      404,
                                      'Failed to get roles',
                                      "User not found with id '$id'");
            }

            $admin = $this->adminDao->findByCrit(new Admin(new User($requestData['id'])));
            $teacher = $this->courseTeachDao->findByCrit(new CourseTeach(new User($requestData['id'])));
            $students = $this->studentDao->findByCrit(new Student(new User($requestData['id'])));

            !empty($admin) ? $role->setAdminRole(true) : $role->setAdminRole(false);
            !empty($teacher) ? $role->setTeacherRole(true) : $role->setTeacherRole(false);
            $role->setStudents($students);

            return (new EntityModel($this->router, $role, true))
                ->withSelfRef(RoleController::class, 'allRoles');
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

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to give admin role', 'Parameter "id" is not provided in uri');

        try {
            $users = $this->userDao->findByCrit(new User($requestData['id']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to give admin role', "User not found with id '{$requestData['id']}'");
            }
            $admin = $this->adminDao->create(new Admin($users[0]));
            return (new EntityModel($this->router, $admin[0], true))
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

        if (!isset($requestData['id'])) {
            return new ErrorModel($this->router, 400, 'Failed to unset admin role', 'Parameter "id" is not provided in uri');
        }

        try {
            $admin = $this->adminDao->findByCrit(new Admin(new User($requestData['id'])));

            if (empty($admin)) {
                return new ErrorModel($this->router, 404, 'Failed to unset admin role', "Admin not found with id '{$requestData['id']}'");
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
            $users = $this->userDao->findByCrit(new User($requestData['id']));

            if (empty($users)) {
                return new ErrorModel($this->router, 404, 'Failed to set Student role', "User not found with id '{$requestData['id']}'");
            }

            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['programmeName'], $requestData['programmeType']));

            if (empty($programmes)) {
                return new ErrorModel($this->router, 404, 'Failed to set Student role', "Programme not found with name '{$requestData['programmeName']}' and type '{$requestData['programmeType']}'");
            }
            $students = $this->studentDao->create(new Student($users[0], $programmes[0], $requestData['startTerm']));
            return (new EntityModel($this->router, $students[0], true))
                ->linkTo('role', RoleController::class, 'allRoles')
                ->withSelfRef(RoleController::class, 'setStudent', [], ['userId' => $students->getUser()->getId(), 'programmeName' => $students->getProgramme()->getName(), 'programmeType' => $students->getProgramme()->getType()]);

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