<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IProgrammeDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Programme;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Router\EndpointRoute;

class ProgrammeController extends Controller {
    //region Properties
    private IProgrammeDao $programmeDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IProgrammeDao $programmeDao) {
        parent::__construct($router);
        $this->programmeDao = $programmeDao;

        $this->router->registerController($this, 'programme')
            ->registerEndpoint('allProgrammes', 'all', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('getProgrammeByName', '', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('createProgramme', 'nev', EndpointRoute::VISIBILITY_PUBLIC);
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
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query programmes',
                                  $e->getMessage()); // Módosítás: getTraceMessages() helyett getMessage() használata
        }

        $programmeEntities = [];

        foreach ($programmes as $programme) {
            try {
                $programmeEntities[] = (new EntityModel($this->router, $programme, true))
                    ->linkTo('allProgrammes', ProgrammeController::class, 'getProgrammeByName') // Módosítás: linkTo() metódus hívása helyes metódusnévvel
                    ->withSelfRef(ProgrammeController::class, 'getProgrammeByName', [$programme->getName(), $programme->getType()]); // Módosítás: withSelfRef() metódus hívása helyes metódusnévvel és a megfelelő paraméterekkel
            } catch (ModelException $e) {
                return new ErrorModel($this->router, 500, 'Failed to query programmes', $e->getMessage());
            }
        }

        try {
            return (new CollectionModel($this->router, $programmeEntities, 'programmes', true))
                ->withSelfRef(ProgrammeController::class, 'allProgrammes');
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query programmes', $e->getMessage());
        }
    }


    /**
     * Returns the programme with the given name from the datasource.
     * Name must be part of the uri.
     */
    public function getProgrammeByName(string $uri, array $requestData): IModel {
        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query programme',
                                  'Parameter "id" is not provided in uri');
        }
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
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query programme',
                                  $e->getMessage());
        }
        if (empty($programmes)) {
            return new ErrorModel($this->router,
                                  404,
                                  'Programme not found',
                                  "Programme not found with name '$name' and type '$type'");
        }
        try {
            return (new EntityModel($this->router, $programmes[0], true))
                ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                ->withSelfRef(ProgrammeController::class, 'getProgrammeByName', [$name, $type]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query programme', $e->getMessage());
        }
    }


    /**
     * Returns with the programme created with the given values.
     * Parameters are expected as part of request data.
     */
    public function createProgramme(string $uri, array $requestData): IModel {
        if (!isset($requestData['name']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "name" is not provided in uri');
        if (!isset($requestData['type']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "type" is not provided in uri');
        if (!isset($requestData['noTerms']))
            return new ErrorModel($this->router, 400, 'Failed to create Programme', 'Parameter "noTerm" is not provided in uri');

        try {
            $programme = $this->programmeDao->create(new Programme($requestData['name'],
                                                    $requestData['type'],
                                                    $requestData['noTerms']));

        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create programme', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $programme, true))
                ->linkTo('allProgrammes', UserController::class, 'allProgrammes')
                ->withSelfRef(ProgrammeController::class, 'getProgrammeByName', [$programme->getName()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create user', $e->getTraceMessages());
        }
    }
    //endregion

}
