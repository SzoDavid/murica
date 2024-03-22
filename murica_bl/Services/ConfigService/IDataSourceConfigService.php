<?php

namespace murica_bl\Services\ConfigService;

interface IDataSourceConfigService
{
    public function getType(): EDataSourceTypes;
}