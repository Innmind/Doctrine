<?php
declare(strict_types = 1);

use Doctrine\ORM\{
    Tools\Setup,
    EntityManager,
};

$params = [
    'host' => '127.0.0.1',
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => 'root',
    'dbname' => 'example',
];

$config = Setup::createXMLMetadataConfiguration(
    [__DIR__.'/../example/'],
    true,
);

return EntityManager::create($params, $config);
