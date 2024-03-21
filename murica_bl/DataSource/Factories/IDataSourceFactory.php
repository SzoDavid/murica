<?php

namespace murica_bl\DataSource\Factories;

use murica_bl\DataSource\Exceptions\DataSourceException;
use murica_bl\DataSource\IDataSource;

interface IDataSourceFactory
{
    /**
     * @throws DataSourceException
     */
    public function createDataSource(): IDataSource;
}