<?php

declare(strict_types=1);

namespace Kanvas\Packages\Social\Providers;

use Exception;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use PDOException;
use PDO;

use function Canvas\Core\envValue;

class DatabaseProvider implements ServiceProviderInterface
{
    /**
     * @param DiInterface $container
     */
    public function register(DiInterface $container)
    {
        $container->setShared(
            'dbSocial',
            function () {
                $options = [
                    'host' => envValue('DATA_API_SOCIAL_MYSQL_HOST', 'localhost'),
                    'username' => envValue('DATA_API_SOCIAL_MYSQL_USER', 'nanobox'),
                    'password' => envValue('DATA_API_SOCIAL_MYSQL_PASS', ''),
                    'dbname' => envValue('DATA_API_SOCIAL_MYSQL_NAME', 'kanvas_social'),
                    'charset' => 'utf8',
                    "options" => [ PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING ]
                ];

                try {
                    $connection = new Mysql($options);

                    // Set everything to UTF8
                    $connection->execute('SET NAMES utf8mb4', []);
                } catch (PDOException $e) {
                    throw new Exception($e->getMessage());
                }

                return $connection;
            }
        );
    }
}