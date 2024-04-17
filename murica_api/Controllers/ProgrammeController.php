<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IAdminDao;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Models\MessageModel;
use murica_bl_impl\Router\EndpointRoute;

class ProgrammeController extends Controller {
    //region Properties
    private IProgrammeDao $programmeDao;
    private IAdminDao $adminDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IProgrammeDao $programmeDao, IAdminDao $adminDao) {
        parent::__construct($router);
        $this->programmeDao = $programmeDao;
        $this->adminDao = $adminDao;

        $this->router->registerController($this, 'programme')
            ->registerEndpoint('allProgrammes', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getProgrammeByNameAndType', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createProgramme', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateProgramme', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteProgramme', 'delete', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns a collection of all programmes from the datasource.
     * No parameters required.
     */
    public function allProgrammes(string $uri, array $requestData): IModel {
        try {
            $programmes = $this->programmeDao->findAll();

            $programmeEntities = [];

            foreach ($programmes as $programme) {
                $programmeEntities[] = (new EntityModel($this->router, $programme, true))
                    ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                    ->linkTo('delete', ProgrammeController::class, 'deleteProgramme')
                    ->linkTo('update', ProgrammeController::class, 'updateProgramme')
                    ->withSelfRef(ProgrammeController::class, 'getProgrammeByNameAndType', [], ['name' => $programme->getName(),'type' => $programme->getType()]);
            }

            return (new CollectionModel($this->router, $programmeEntities, 'programmes', true))
                ->linkTo('createProgramme', ProgrammeController::class, 'createProgramme')
                ->withSelfRef(ProgrammeController::class, 'allProgrammes');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query programmes',
                                  $e->getTraceMessages());
        }
    }

    /**
     * Returns the programme with the given name from the datasource.
     * Name must be part of the uri.
     */
    public function getProgrammeByNameAndType(string $uri, array $requestData): IModel {
        $name = $requestData['name'] ?? null;
        $type = $requestData['type'] ?? null;

        // Ellenőrizze, hogy mindkét kulcs értéke megvan-e
        if (empty($name) || empty($type)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query programme',
                                  'Both "name" and "type" parameters are required');
        }
        try {
            $programmes = $this->programmeDao->findByCrit(new Programme($name, $type));

            if (empty($programmes)) {
                return new ErrorModel($this->router,
                                      404,
                                      'Programme not found',
                                      "Programme not found with name '$name' and type '$type'");
            }

            return (new EntityModel($this->router, $programmes[0], true))
                ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                ->withSelfRef(ProgrammeController::class, 'getProgrammeByNameAndType', [], ['name' => $programmes[0]->getName(),'type' => $programmes[0]->getType()]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query programme',
                                  $e->getTraceMessages());
        }
    }

    /**
     * Returns with the programme created with the given values.
     * Parameters are expected as part of request data.
     */
    public function createProgramme(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to create Programme', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create Programme', $e->getTraceMessages());
        }

        if (!isset($requestData['name']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "name" is not provided');
        if (!isset($requestData['type']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "type" is not provided');
        if (!isset($requestData['noTerms']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "noTerm" is not provided');

        try {
            $programme = $this->programmeDao->create(new Programme($requestData['name'],
                                                    $requestData['type'],
                                                    (int)$requestData['noTerms']));

            return (new EntityModel($this->router, $programme, true))
                ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                ->withSelfRef(ProgrammeController::class, 'getProgrammeByNameAndType',[],['name' => $programme->getName(),'type' => $programme->getType()]);

        } catch (DataAccessException|ValidationException|ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create programme', $e->getTraceMessages());
        }
    }

    public function updateProgramme(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to update Programme', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update Programme', $e->getTraceMessages());
        }

        if (!isset($requestData['name']))
            return new ErrorModel($this->router, 400, 'Failed to update Programme', 'Parameter "name" is not provided in uri');
        if (!isset($requestData['type']))
            return new ErrorModel($this->router, 400, 'Failed to update Programme', 'Parameter "type" is not provided in uri');

        try {
            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['name'],
                                                                        $requestData['type']));

            if (empty($programmes)) {
                return new ErrorModel($this->router, 404, 'Failed to update programme', "Programme not found with name '{$requestData['name']}' and type '{$requestData['type']}'");
            }

            $this->programmeDao->update(new Programme($requestData['name'],
                                                      $requestData['type'],
                                                      (int)$requestData['noTerms']));

            return new MessageModel($this->router, ['message' => 'Programme updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update programme', $e->getTraceMessages());
        }
    }

    public function deleteProgramme(string $uri, array $requestData): IModel {
        try {
            if (!$this->checkIfAdmin($requestData, $this->adminDao))
                return new ErrorModel($this->router, 403, 'Failed to delete programme', 'Access is forbidden');
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete programme', $e->getTraceMessages());
        }

        if (!isset($requestData['name']) || !isset($requestData['type'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete programme', 'Both "name" and "type" parameters are required');
        }

        try {
            $programmes = $this->programmeDao->findByCrit(new Programme($requestData['name'], $requestData['type']));

            if (empty($programmes)) {
                return new ErrorModel($this->router, 404, 'Failed to delete programme', "Programme not found with name '{$requestData['name']}' and type '{$requestData['type']}'");
            }

            $this->programmeDao->delete($programmes[0]);
            return new MessageModel($this->router, ['message' => 'Programme deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete programme', $e->getTraceMessages());
        }
    }
    //endregion

}
