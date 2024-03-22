<?php

namespace murica_api\Controllers;

use murica_bl\Models\IModel;
use murica_bl\Services\ConfigService\IConfigService;
use murica_bl_impl\Models\MessageModel;
use Override;

class ErrorController extends Controller
{
    //region Parameters
    private IConfigService $configService;
    //endregion

    //region Ctor
    public function __construct(string $baseUri, IConfigService $configService) {
        parent::__construct($baseUri);
        $this->configService = $configService;
    }
    //endregion

    //region IController members
    #[Override]
    public function getEndpoints(): array {
        return [
            $this->baseUri . '/401' => 'unauthorized',
            $this->baseUri . '/403' => 'forbidden',
            $this->baseUri . '/404' => 'notFound',
            $this->baseUri . '/500' => 'internalServerError'
        ];
    }

    #[Override]
    public function getPublicEndpoints(): array {
        return [
            'unauthorized' => '',
            'forbidden' => '',
            'notFound' => '',
            'internalServerError' => ''
        ];
    }
    //endregion

    //region Endpoints
    public function unauthorized($requestData): IModel {
        return (new MessageModel($this->configService))
            ->of(['error' => [
                'code' => 401,
                'message' => 'Client request has not been completed because it lacks valid authentication credentials for the requested resource.']])
            ->linkTo('login', 'auth/login');
    }

    public function forbidden($requestData): IModel {
        return (new MessageModel($this->configService))
            ->of(['error' => [
                'code' => 403,
                'message' => 'Client request has not been completed because client has no rights to access the requested resource.']]);
    }

    public function notFound($requestData): IModel {
        $message = 'Requested resource not found';
        if (isset($requestData['endpoint'])) {
            $message = 'Endpoint ' . $requestData['endpoint'] . ' not found.';
        } elseif (isset($requestData['resource'])) {
            $message = $requestData['resource'];
        }

        return (new MessageModel($this->configService))
            ->of(['error' => [
                'code' => 404,
                'message' => $message]]);
    }

    public function internalServerError($requestData): IModel {
        $message = 'Internal server error';
        if (isset($requestData['errorMessage'])) {
            $message .= ': ' . $requestData['errorMessage'];
        }

        return (new MessageModel($this->configService))
            ->of(['error' => [
                'code' => 500,
                'message' => $message]]);
    }
    //endregion
}