<?php
declare(strict_types = 1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__.'/../vendor/autoload.php';

$entityManager = require 'entity-manager.php';

return ConsoleRunner::createHelperSet($entityManager);
