<?php
declare(strict_types = 1);

namespace Innmind\Doctrine\Type;

use Innmind\Doctrine\{
    Id,
    Exception\LogicException,
};
use Doctrine\DBAL\{
    Platforms\AbstractPlatform,
    Types\GuidType,
};

class IdType extends GuidType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        if (\is_null($value)) {
            return null;
        }

        if (!\is_string($value)) {
            $type = \gettype($value);

            throw new LogicException("Value type must be null or string, $type given");
        }

        return $this->instanciate($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (\is_null($value)) {
            return null;
        }

        if (!$value instanceof Id) {
            $type = \gettype($value);

            throw new LogicException("Value must be an Id, $type given");
        }

        return $value->toString();
    }

    protected function instanciate(string $value): Id
    {
        return new Id($value);
    }
}
