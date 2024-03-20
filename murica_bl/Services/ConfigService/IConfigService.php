<?php

namespace murica_bl\Services\ConfigService;

interface IConfigService
{
    public function getDataSourceConfigService(): IDataSourceConfigService;
}