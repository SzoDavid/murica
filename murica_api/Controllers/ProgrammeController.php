<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IProgrammeDao;
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
            ->registerEndpoint('getProgrammeById', '', EndpointRoute::VISIBILITY_PUBLIC);
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
                                  $e->getTraceMessages());
        }

        $programmeEntities = [];

        foreach ($programmes as $programme) {
            try {
                $programmeEntities[] = (new EntityModel($this->router, $programme, true))
                    ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                    ->withSelfRef(ProgrammeController::class, 'getProgrammeById', [$programme->getName()]);
            } catch (ModelException $e) {
                return new ErrorModel($this->router, 500, 'Failed to query programmes', $e->getTraceMessages());
            }
        }

        try {
            return (new CollectionModel($this->router, $programmeEntities, 'programmes', true))
                ->withSelfRef(ProgrammeController::class, 'allProgrammes');
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query programmes', $e->getTraceMessages());
        }
    }

    /**
     * Returns the programme with the given name from the datasource.
     * Name must be part of the uri.
     */
    public function getProgrammeById(string $uri, array $requestData): IModel {
        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query programme',
                                  'Parameter "id" is not provided in uri');
        }

        try {
            $programme = $this->programmeDao->findByCrit(new Programme($uri));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query programme',
                                  $e->getTraceMessages());
        }

        if ($programme === null) {
            return new ErrorModel($this->router,
                                  404,
                                  'Programme not found',
                                  "Programme not found with name '$uri'");
        }

        try {
            return (new EntityModel($this->router, $programme[0], true))
                ->linkTo('allProgrammes', ProgrammeController::class, 'allProgrammes')
                ->withSelfRef(ProgrammeController::class, 'getProgrammeById', [$uri]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query programme', $e->getTraceMessages());
        }
    }
    //endregion
}
