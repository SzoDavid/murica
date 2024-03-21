<?php

namespace murica_bl\Services\ConfigService;

interface IDataSourceConfigService
{
    public function getType(): EDataSourceTypes;
    public function getUserTableName(): string;
}