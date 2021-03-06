<?php
declare(strict_types = 1);

use Innmind\Doctrine\Type\IdType;
use Doctrine\ORM\{
    Tools\Setup,
    EntityManager,
};
use Doctrine\DBAL\Types\Type;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mariadb\JsonValue;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;

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
$config->addCustomStringFunction(JsonValue::FUNCTION_NAME, JsonValue::class);
$config->addCustomStringFunction(JsonContains::FUNCTION_NAME, JsonContains::class);

if (!Type::hasType('object_id')) {
    Type::addType('object_id', IdType::class);
}

return EntityManager::create($params, $config);
