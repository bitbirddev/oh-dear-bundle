<?php

namespace bitbirddev\OhDearBundle\Exceptions;

use Doctrine\DBAL\Connection;
use Exception;

class DatabaseNotSupported extends Exception
{
    public static function make(Connection $connection): self
    {
        return new self("The database driver `{$connection->getParams()['driver']}` is not supported by this package.");
    }
}
