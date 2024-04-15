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
use murica_bl_impl\Models\MessageModel;
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
            ->registerEndpoint('allRooms', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getRoomById', '', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('createRoom', 'new', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('updateRoom', 'update', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('deleteRoom', 'delete', EndpointRoute::VISIBILITY_PRIVATE);
    }
    //endregion

    //region Endpoints
    /**
     * Returns a collection of all rooms from the datasource.
     * No parameters required.
     */
    public function allRooms(string $uri, array $requestData): IModel {
        // TODO: check if user is admin
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
        // TODO: check if user is admin

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
        // TODO: check if user is admin

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

    public function updateRoom(string $uri, array $requestData): IModel {
        // TODO: check if user is admin

        if (!isset($requestData['id']))
            return new ErrorModel($this->router, 400, 'Failed to update room', 'Parameter "id" is not provided in request data');

        try {
            $rooms = $this->roomDao->findByCrit(new Room($requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update room', $e->getTraceMessages());
        }

        if (empty($rooms)) {
            return new ErrorModel($this->router, 404, 'Failed to update room', "Subject not found with id '{$requestData['id']}'");
        }
        try {
            $this->roomDao->update(new Room($requestData['id'],
                                            $requestData['capacity']));

            return new MessageModel($this->router, ['message' => 'Room updated successfully'], true);
        } catch (DataAccessException|ValidationException $e) {
            return new ErrorModel($this->router, 500, 'Failed to update room', $e->getTraceMessages());
        }
    }

    public function deleteRoom(string $uri, array $requestData): IModel {
        // TODO: check if user is admin

        if (!isset($requestData['id'])) {
            return new ErrorModel($this->router, 400, 'Failed to delete room', 'Parameter "id" is not provided in request data');
        }

        try {
            $subjects = $this->roomDao->findByCrit(new Room($requestData['id']));
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete room', $e->getTraceMessages());
        }

        if (empty($subjects)) {
            return new ErrorModel($this->router, 404, 'Failed to delete room', "Room not found with id '{$requestData['id']}'");
        }

        try {
            $this->roomDao->delete($subjects[0]);
            return new MessageModel($this->router, ['message' => 'Room deleted successfully'], true);
        } catch (DataAccessException $e) {
            return new ErrorModel($this->router, 500, 'Failed to delete subject', $e->getTraceMessages());
        }
    }
    //endregion
}
