<?php
declare(strict_types = 1);

use Innmind\Doctrine\Type\IdType;
use Doctrine\ORM\{
    Tools\Setup,
    EntityManager,
};
use Doctrine\DBAL\Types\Type;

$params = [
    'host' => '127.0.0.1',
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => 'root',
    'dbname' => 'example',
    'port' => (int) (\getenv('DB_PORT') ? \getenv('DB_PORT') : 3306),
];

$config = Setup::createXMLMetadataConfiguration(
    [__DIR__.'/../example/'],
    true,
);

if (!Type::hasType('object_id')) {
    Type::addType('object_id', IdType::class);
}

return EntityManager::create($params, $config);
