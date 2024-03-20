<?php

namespace murica_api\Controllers;

class Controller
{
    //region Parameters
    protected string $baseUri;
    //endregion

    //region Ctor
    /**
     * @param string $baseUri
     */
    public function __construct(string $baseUri)
    {
        $this->baseUri = $baseUri;
    }
    //endregion


    //region IController members
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getEndpoints(): array
    {
        return array();
    }
    //endregion
}