<?php

namespace murica_api\Controllers;

use murica_bl\Dao\Exceptions\DataAccessException;
use murica_bl\Dao\IMessageDao;
use murica_bl\Dto\IMessage;
use murica_bl\Dto\IUser;
use murica_bl\Models\Exceptions\ModelException;
use murica_bl\Models\IModel;
use murica_bl\Router\IRouter;
use murica_bl_impl\Dto\Message;
use murica_bl_impl\Models\CollectionModel;
use murica_bl_impl\Models\EntityModel;
use murica_bl_impl\Models\ErrorModel;
use murica_bl_impl\Router\EndpointRoute;

class MessageController extends Controller {
    //region Properties
    private IMessageDao $messageDao;
    //endregion

    /**
     * @param IRouter $router
     * @param IMessageDao $messageDao
     */
    public function __construct(IRouter $router, IMessageDao $messageDao) {
        parent::__construct($router);
        $this->messageDao = $messageDao;

        $this->router->registerController($this, 'message')
            ->registerEndpoint('getAllByUser', 'all', EndpointRoute::VISIBILITY_PRIVATE)
            ->registerEndpoint('getByDate', '', EndpointRoute::VISIBILITY_PRIVATE);
    }

    public function getAllByUser(string $uri, array $requestData): IModel {
        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        try {
            $messages = $this->messageDao->findByCrit(new Message(null, null, null, $user));

            $messageEntities = [];

            /* @var $message IMessage */
            foreach ($messages as $message) {
                $messageEntities[] = (new EntityModel($this->router, $message, true))
                    ->linkTo('getAllByUser', RoomController::class, 'Messages')
                    ->withSelfRef(MessageController::class, 'getAllByUser');
            }

            return (new CollectionModel($this->router, $messageEntities, 'messages', true))
                ->withSelfRef(MessageController::class, 'getAllByUser');
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query messages',
                                  $e->getTraceMessages());
        }
    }

    public function getByDate(string $uri, array $requestData): IModel {
        /* @var $user IUser */
        $user = $this->$requestData['token']->getUser();

        if (empty($uri)) {
            return new ErrorModel($this->router,
                                  400,
                                  'Failed to query messages',
                                  '"Date" parameters are required');
        }

        try {
            $messages = $this->messageDao->findByCrit(new Message($uri, null, null, $user));

            return (new EntityModel($this->router, $messages[0], true))
                ->withSelfRef(MessageController::class, 'getByDate', [$uri]);
        } catch (DataAccessException|ModelException $e) {
            return new ErrorModel($this->router,
                                  500,
                                  'Failed to query message',
                                  $e->getTraceMessages());
        }
    }
}