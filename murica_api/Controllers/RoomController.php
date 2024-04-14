<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IRoomDao;
use murica_bl\Dto\Exceptions\ValidationException;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Room;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Router\EndpointRoute;

class RoomController extends Controller {
    //region Properties
    private IRoomDao $roomDao;
    //endregion

    //region Ctor
    public function __construct(IRouter $router, IRoomDao $roomDao) {
        parent::__construct($router);
        $this->roomDao = $roomDao;

        $this->router->registerController($this, 'room')
            ->registerEndpoint('allRooms', 'all', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('getRoomById', '', EndpointRoute::VISIBILITY_PUBLIC)
            ->registerEndpoint('createRoom', 'create', EndpointRoute::VISIBILITY_PUBLIC);
    }
    //endregion

    //region Endpoints
    /**
     * Returns a collection of all rooms from the datasource.
     * No parameters required.
     */
    public function allRooms(string $uri, array $requestData): IModel {
        try {
            $rooms = $this->roomDao->findAll();
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query rooms',
                                  $e->getTraceMessages());
        }

        $roomEntities = array();

        foreach ($rooms as $room) {
            try {
                $roomEntities[] = (new EntityModel($this->router, $room, true))
                    ->linkTo('allRooms', RoomController::class, 'allRooms')
                    ->withSelfRef(RoomController::class, 'getRoomById', [$room->getId()]);
            } catch (ModelException $e) {
                return new ErrorModel($this->router, 500, 'Failed to query rooms', $e->getTraceMessages());
            }
        }

        try {
            return (new CollectionModel($this->router, $roomEntities, 'rooms', true))
                ->withSelfRef(RoomController::class, 'allRooms');
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query rooms', $e->getTraceMessages());
        }
    }

    /**
     * Returns the room with the given id from the datasource.
     * Id must be part of the uri.
     */
    public function getRoomById(string $uri, array $requestData): IModel {
        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query room',
                                  'Parameter "id" is not provided in uri');
        }

        try {
            $rooms = $this->roomDao->findByCrit(new Room($uri));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query room',
                                  $e->getTraceMessages());
        }

        if (empty($rooms)) {
            return new ErrorModel($this->router,
                                  404,
                                  'Room not found',
                                  "Room not found with id '$uri'");
        }

        try {
            return (new EntityModel($this->router, $rooms[0], true))
                ->linkTo('allRooms', RoomController::class, 'allRooms')
                ->withSelfRef(RoomController::class, 'getRoomById', [$uri]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to query room', $e->getTraceMessages());
        }
    }

    /**
     * Creates a new room with the provided data.
     * Parameters are expected as part of request data.
     */
    public function createRoom(string $uri, array $requestData): IModel {
        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to create Room', 'Parameter "id" is not provided in uri');
        if (!isset($requestData['capacity']))
            return new ErrorModel($this->router, 400, 'Failed to create Room', 'Parameter "capacity" is not provided in uri');

        $id = $requestData['id'];
        $capacity = (int)$requestData['capacity'];

        $room = new Room($id, $capacity);

        try {
            $createdRoom = $this->roomDao->create($room);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create room', $e->getTraceMessages());
        }

        try {
            return (new EntityModel($this->router, $createdRoom, true))
                ->linkTo('allRooms', RoomController::class, 'allRooms')
                ->withSelfRef(RoomController::class, 'getRoomById', [$createdRoom->getId()]);
        } catch (ModelException $e) {
            return new ErrorModel($this->router, 500, 'Failed to create room', $e->getTraceMessages());
        }
    }
    //endregion
}
