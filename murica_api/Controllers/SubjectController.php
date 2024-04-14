<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\ISubjectDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Subject;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class SubjectController extends Controller {
    //region Properties
    private ISubjectDao $subjectDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, ISubjectDao $subjectDao) {
        parent::__construct($router);
        $this->subjectDao = $subjectDao;

        $this->router->registerController($this, 'subject')
            ->registerEndpoint('allSubjects', 'all', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('getSubjectById', '', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('createSubject', 'new', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('updateSubject', '', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('deleteSubject', 'new', EndpointRoute::VISIBILITY_PUBLIC);
    }
    //endregion

    //region Endpoints
    /**
     * Returns a collection of all subjects from the datasource.
     * No parameters required.
     */
    public function allSubjects(string $uri, array $requestData): IModel {
        try {
            $subjects = $this->subjectDao->findAll();
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query subjects',
                                  $e->getTraceMessages());
        }

        $subjectEntities = array();

        foreach ($subjects as $subject) {
            try {
                $subjectEntities[] = (new EntityModel($this->router, $subject, true))
                    ->linkTo('allSubjects', SubjectController::class, 'allSubjects')
                    ->withSelfRef(SubjectController::class, 'getSubjectById', [$subject->getId()]);
            } catch (ModelException $e) {
                return new ErrorModel($this->router, 500, 'Failed to query subjects', $e->getTraceMessages());
            }
        }

        try {
            return (new CollectionModel($this->router, $subjectEntities, 'subjects', true))
                ->withSelfRef(SubjectController::class, 'allSubjects');
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query subjects', $e->getTraceMessages());
        }
    }

    /**
     * Returns the subject with the given id from the datasource.
     * Id must be part of the uri.
     */
    public function getSubjectById(string $uri, array $requestData): IModel {
        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query subject',
                                  'Parameter "id" is not provided in uri');
        }

        try {
            $subjects = $this->subjectDao->findByCrit(new Subject($uri));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query subject',
                                  $e->getTraceMessages());
        }

        if (empty($subjects)) {
            return new ErrorModel($this->router,
                                  404,
                                  'Subject not found',
                                  "Subject not found with id '$uri'");
        }

        try {
            return (new EntityModel($this->router, $subjects[0], true))
                ->linkTo('allSubjects', SubjectController::class, 'allSubjects')
                ->withSelfRef(SubjectController::class, 'getSubjectById', [$uri]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query subject', $e->getTraceMessages());
        }
    }

    /**
     * Creates a new subject with the provided data.
     * Parameters are expected as part of request data.
     */
    public function createSubject(string $uri, array $requestData): IModel {
            if (!isset($requestData['id']))
                return new ErrorModel($this->router, 400, 'Failed to create subject', 'Parameter "id" is not provided in request data');
            if (!isset($requestData['name']))
                return new ErrorModel($this->router, 400, 'Failed to create subject', 'Parameter "name" is not provided in request data');
            if (!isset($requestData['approval']))
                return new ErrorModel($this->router, 400, 'Failed to create subject', 'Parameter "approval" is not provided in request data');
            if (!isset($requestData['credit']))
                return new ErrorModel($this->router, 400, 'Failed to create subject', 'Parameter "credit" is not provided in request data');
            if (!isset($requestData['type']))
                return new ErrorModel($this->router, 400, 'Failed to create subject', 'Parameter "type" is not provided in request data');

        try {
            $subject = $this->subjectDao->create(new Subject($requestData['id'],
                                                    $requestData['name'],
                                                    (int)$requestData['approval'],
                                                    (int)$requestData['credit'],
                                                    $requestData['type']));
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create subject', $e->getTraceMessages());
        }
        try {
            return (new EntityModel($this->router, $subject, true))
                ->linkTo('allSubjects', SubjectController::class, 'allSubjects')
                ->withSelfRef(SubjectController::class, 'getSubjectById', [$subject->getId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create subject', $e->getTraceMessages());
        }
    }

    public function updateSubject(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update subject', 'Parameter "id" is not provided in request data');

        try {
            $subject = $this->subjectDao->findByCrit($requestData['id']);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update subject', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to update subject', "Subject not found with id '{$requestData['id']}'");
        }

        try {
            $updatedSubject = $this->subjectDao->update(new Subject(
                                                            $requestData['id'],
                                                            $requestData['name'],
                                                            (int)$requestData['approval'],
                                                            (int)$requestData['credit'],
                                                            $requestData['type']
                                                        ));

            return new MessageModel($this->router, ['message' => 'Subject updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update subject', $e->getTraceMessages());
        }
    }

    public function deleteSubject(string $uri, array $requestData): IModel {
        if (!isset($requestData['id'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete subject', 'Parameter "id" is not provided in request data');
        }

        try {
            $subjects = $this->subjectDao->findByCrit($requestData['id']);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete subject', $e->getTraceMessages());
        }

        if (empty($subject)) {
            return new ErrorModel($this->router, 404, 'Failed to delete subject', "Subject not found with id '{$requestData['id']}'");
        }

        try {
            $this->subjectDao->delete($subject);
            return new MessageModel($this->router, ['message' => 'Subject deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete subject', $e->getTraceMessages());
        }
    }

    //endregion
}
