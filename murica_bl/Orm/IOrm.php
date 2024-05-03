<?php

namespace murica_bl\Orm;

use murica_bl\Orm\Exception\OciException;

interface IOrm {
    public function close(): void;
    /**
     * @throws OciException
     */
    public function query(string $sql): IOrm;
    /**
     * @throws OciException
     */
    public function execute(int $mode): IOrm;
    public function result(): array;
    public function firstResult(): array;
    /**
     * @throws OciException
     */
    public function bind($name, &$variable): IOrm;
}