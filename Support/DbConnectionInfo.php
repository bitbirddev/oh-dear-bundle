<?php

namespace bitbirddev\OhDearBundle\Support;

use bitbirddev\OhDearBundle\Exceptions\DatabaseNotSupported;
use Doctrine\DBAL\Connection;

class DbConnectionInfo
{
    public function connectionCount(Connection $connection): int
    {
        $driver = $connection->getParams()['driver'];

        return match (true) {
            $driver == 'pdo_mysql' => (int) $connection->createQueryBuilder()->select('COUNT(*)')->from('information_schema.PROCESSLIST')->fetchOne(),
            $driver == 'pdo_pgsql' => (int) $connection->createQueryBuilder()->select('count(*) as connections')->from('pg_stat_activity')->fetchOne(),
            default => throw DatabaseNotSupported::make($connection),
        };
    }

    public function tableSizeInMb(Connection $connection, string $table): float
    {
        $driver = $connection->getParams()['driver'];
        $sizeInBytes = match (true) {
            $driver == 'pdo_mysql' => (int) $this->getMySQLTableSize($connection, $table),
            $driver == 'pdo_pgsql' => (int) $this->getPostgresTableSize($connection, $table),
            default => throw DatabaseNotSupported::make($connection),
        };

        return $sizeInBytes / 1024 / 1024;
    }

    public function databaseSizeInMb(Connection $connection): float
    {
        $driver = $connection->getParams()['driver'];

        return match (true) {
            $driver == 'pdo_mysql' => (int) $this->getMySQLDatabaseSize($connection),
            $driver == 'pdo_pgsql' => (int) $this->getPostgresDatabaseSize($connection),
            default => throw DatabaseNotSupported::make($connection),
        };
    }

    protected function getMySQLTableSize(Connection $connection, string $table): int
    {
        $builder = $connection->createQueryBuilder();

        return
            $builder
            ->select('(data_length + index_length) AS size')
            ->from('information_schema.TABLES')
            ->where('table_schema = :table_schema')
            ->andWhere('table_name = :table_name')

            ->setParameter('table_schema', $connection->getParams()['dbname'])
            ->setParameter('table_name', $table)
            ->fetchOne();
    }

    protected function getPostgresTableSize(Connection $connection, string $table): int
    {
        $builder = $connection->createQueryBuilder();

        return $builder->select('g_total_relation_size(:table_name) AS size')->setParameter('table_name', $table)->fetchOne();
    }

    protected function getMySQLDatabaseSize(Connection $connection): int
    {
        $builder = $connection->createQueryBuilder();

        return $builder->select('size from (SELECT table_schema "name", ROUND(SUM(data_length + index_length) / 1024 / 1024) as size FROM information_schema.tables GROUP BY table_schema) alias_one where name = :database_name')
            ->setParameter('database_name', $connection->getParams()['dbname'])->fetchOne();
    }

    protected function getPostgresDatabaseSize(Connection $connection): int
    {
        $builder = $connection->createQueryBuilder();

        return $builder->select('pg_database_size(:database_name) / 1024 / 1024 AS size;')
            ->setParameter('database_name', $connection->getParams()['dbname'])->fetchOne();
    }
}
