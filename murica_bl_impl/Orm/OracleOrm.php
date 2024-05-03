<?php

namespace murica_bl_impl\Orm;

use murica_bl\Orm\Exception\OciException;
use murica_bl\Orm\IOrm;

class OracleOrm implements IOrm {
    private $connection;
    private $stmt;

    /**
     * @throws OciException
     */
    public function __construct($user, $password, $connectionString) {
        $this->connection = oci_connect(
            $user,
            $password,
            $connectionString
        );

        if (!$this->connection) {
            throw new OciException('Failed to establish connection with database: ' . oci_error());
        }
    }

    public function close(): void {
        oci_close($this->connection);
    }

    public function query(string $sql): IOrm {
        if (!$this->stmt = oci_parse($this->connection, $sql))
            throw new OciException(json_encode(oci_error($this->stmt)));

        return $this;
    }

    public function execute(int $mode): IOrm {
        if (!oci_execute($this->stmt, $mode))
            throw new OciException(json_encode(oci_error($this->stmt)));
    }

    public function result(): array {
        while ($res[] = oci_fetch_assoc($this->stmt));

        // delete last NULL element
        array_pop($res);

        return $res;
    }

    public function firstResult(): array {
        return oci_fetch_assoc($this->stmt);
    }

    public function bind($name, &$variable): IOrm {
        $resp = is_numeric($variable)
            ? oci_bind_by_name($this->stmt, $name, $variable, -1, SQLT_INT)
            : oci_bind_by_name($this->stmt, $name, $variable, -1);

        if (!$resp) throw new OciException("Couldn't bind parameter $name: " . json_encode(oci_error($this->stmt)));

        return $this;
    }
}