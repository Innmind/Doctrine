<?php
declare(strict_types = 1);

namespace Tests\Innmind\Doctrine\Specification;

use Innmind\Doctrine\Specification\{
    ToQueryBuilder,
    Child,
    JsonArray,
};
use Innmind\Specification\{
    Comparator,
    Sign,
    Not,
    Composite,
    Operator,
};
use Doctrine\ORM\{
    EntityManagerInterface,
    EntityRepository,
    QueryBuilder,
    Query\Expr,
    Mapping\ClassMetadata,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ToQueryBuilderTest extends TestCase
{
    use BlackBox;

    public function testEquality()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} = ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testInequality()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} <> ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testLessThan()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::lessThan);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} < ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testMoreThan()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::moreThan);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} > ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testLessThanOrEqual()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::lessThanOrEqual);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} <= ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testMoreThanOrEqual()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::moreThanOrEqual);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} >= ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testIsNull()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::isNull);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} IS NULL", (string) $qb);
                $this->assertCount(0, $qb->getParameters());
            });
    }

    public function testIsNotNull()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::isNotNull);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} IS NOT NULL", (string) $qb);
                $this->assertCount(0, $qb->getParameters());
            });
    }

    public function testStartsWith()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::startsWith);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} LIKE ?1", (string) $qb);
                $this->assertSame("$value%", $qb->getParameter(1)->getValue());
            });
    }

    public function testEndsWith()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::endsWith);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} LIKE ?1", (string) $qb);
                $this->assertSame("%$value", $qb->getParameter(1)->getValue());
            });
    }

    public function testContains()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::contains);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} LIKE ?1", (string) $qb);
                $this->assertSame("%$value%", $qb->getParameter(1)->getValue());
            });
    }

    public function testIn()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Sequence::of(
                    Set\Unicode::strings(),
                    Set\Integers::between(0, 10),
                ),
            )
            ->then(function($property, $values) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::in);
                $specification
                    ->method('value')
                    ->willReturn($values);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$property} IN(?1)", (string) $qb);
                $this->assertSame($values, $qb->getParameter(1)->getValue());
            });
    }

    public function testNot()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $innerSpecification = $this->createMock(Comparator::class);
                $innerSpecification
                    ->method('property')
                    ->willReturn($property);
                $innerSpecification
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $innerSpecification
                    ->method('value')
                    ->willReturn($value);
                $specification = $this->createMock(Not::class);
                $specification
                    ->method('specification')
                    ->willReturn($innerSpecification);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE NOT(entity.{$property} = ?1)", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testAnd()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($leftProperty, $leftValue, $rightProperty, $rightValue) {
                $left = $this->createMock(Comparator::class);
                $left
                    ->method('property')
                    ->willReturn($leftProperty);
                $left
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $left
                    ->method('value')
                    ->willReturn($leftValue);
                $right = $this->createMock(Comparator::class);
                $right
                    ->method('property')
                    ->willReturn($rightProperty);
                $right
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $right
                    ->method('value')
                    ->willReturn($rightValue);
                $specification = $this->createMock(Composite::class);
                $specification
                    ->method('left')
                    ->willReturn($left);
                $specification
                    ->method('right')
                    ->willReturn($right);
                $specification
                    ->method('operator')
                    ->willReturn(Operator::and);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$leftProperty} = ?1 AND entity.{$rightProperty} <> ?2", (string) $qb);
                $this->assertSame($leftValue, $qb->getParameter(1)->getValue());
                $this->assertSame($rightValue, $qb->getParameter(2)->getValue());
            });
    }

    public function testOr()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($leftProperty, $leftValue, $rightProperty, $rightValue) {
                $left = $this->createMock(Comparator::class);
                $left
                    ->method('property')
                    ->willReturn($leftProperty);
                $left
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $left
                    ->method('value')
                    ->willReturn($leftValue);
                $right = $this->createMock(Comparator::class);
                $right
                    ->method('property')
                    ->willReturn($rightProperty);
                $right
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $right
                    ->method('value')
                    ->willReturn($rightValue);
                $specification = $this->createMock(Composite::class);
                $specification
                    ->method('left')
                    ->willReturn($left);
                $specification
                    ->method('right')
                    ->willReturn($right);
                $specification
                    ->method('operator')
                    ->willReturn(Operator::or);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$leftProperty} = ?1 OR entity.{$rightProperty} <> ?2", (string) $qb);
                $this->assertSame($leftValue, $qb->getParameter(1)->getValue());
                $this->assertSame($rightValue, $qb->getParameter(2)->getValue());
            });
    }

    public function testCompositionIsRespected()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
                $this->name(),
                Set\Unicode::strings(),
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function(
                $leftProperty,
                $leftValue,
                $right1Property,
                $right1Value,
                $right2Property,
                $right2Value,
            ) {
                $left = $this->createMock(Comparator::class);
                $left
                    ->method('property')
                    ->willReturn($leftProperty);
                $left
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $left
                    ->method('value')
                    ->willReturn($leftValue);
                $right1 = $this->createMock(Comparator::class);
                $right1
                    ->method('property')
                    ->willReturn($right1Property);
                $right1
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $right1
                    ->method('value')
                    ->willReturn($right1Value);
                $right2 = $this->createMock(Comparator::class);
                $right2
                    ->method('property')
                    ->willReturn($right2Property);
                $right2
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $right2
                    ->method('value')
                    ->willReturn($right2Value);
                $right = $this->createMock(Composite::class);
                $right
                    ->method('left')
                    ->willReturn($right1);
                $right
                    ->method('right')
                    ->willReturn($right2);
                $right
                    ->method('operator')
                    ->willReturn(Operator::or);
                $specification = $this->createMock(Composite::class);
                $specification
                    ->method('left')
                    ->willReturn($left);
                $specification
                    ->method('right')
                    ->willReturn($right);
                $specification
                    ->method('operator')
                    ->willReturn(Operator::and);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE entity.{$leftProperty} = ?1 AND (entity.{$right1Property} <> ?2 OR entity.{$right2Property} <> ?3)", (string) $qb);
                $this->assertSame($leftValue, $qb->getParameter(1)->getValue());
                $this->assertSame($right1Value, $qb->getParameter(2)->getValue());
                $this->assertSame($right2Value, $qb->getParameter(3)->getValue());
            });
    }

    public function testMatchRelation()
    {
        $this
            ->forAll(
                $this->name(),
                $this->name(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($relation, $property, $value, $targetEntity) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn("$relation.$property");
                $specification
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getAssociationMapping')
                    ->with($relation)
                    ->willReturn(['targetEntity' => $targetEntity]);
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));
                // needed for the left join to work
                $expected
                    ->select('entity')
                    ->from('Entity', 'entity');

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT entity FROM Entity entity LEFT JOIN entity.{$relation} {$relation} WHERE {$relation}.{$property} = ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testRelationIsLoadedOnceWhenMultipleConditionOnInt()
    {
        $this
            ->forAll(
                $this->name(),
                $this->name(),
                Set\Unicode::strings(),
                $this->name(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($relation, $leftProperty, $leftValue, $rightProperty, $rightValue, $targetEntity) {
                $left = $this->createMock(Comparator::class);
                $left
                    ->method('property')
                    ->willReturn("$relation.$leftProperty");
                $left
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $left
                    ->method('value')
                    ->willReturn($leftValue);
                $right = $this->createMock(Comparator::class);
                $right
                    ->method('property')
                    ->willReturn("$relation.$rightProperty");
                $right
                    ->method('sign')
                    ->willReturn(Sign::inequality);
                $right
                    ->method('value')
                    ->willReturn($rightValue);
                $specification = $this->createMock(Composite::class);
                $specification
                    ->method('left')
                    ->willReturn($left);
                $specification
                    ->method('right')
                    ->willReturn($right);
                $specification
                    ->method('operator')
                    ->willReturn(Operator::and);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getAssociationMapping')
                    ->with($relation)
                    ->willReturn(['targetEntity' => $targetEntity]);
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));
                // needed for the left join to work
                $expected
                    ->select('entity')
                    ->from('Entity', 'entity');

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT entity FROM Entity entity LEFT JOIN entity.{$relation} {$relation} WHERE {$relation}.{$leftProperty} = ?1 AND {$relation}.{$rightProperty} <> ?2", (string) $qb);
                $this->assertSame($leftValue, $qb->getParameter(1)->getValue());
                $this->assertSame($rightValue, $qb->getParameter(2)->getValue());
            });
    }

    public function testMatchJsonField()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Unicode::strings(),
            )
            ->then(function($property, $value) {
                $specification = $this->createMock(Comparator::class);
                $specification
                    ->method('property')
                    ->willReturn($property);
                $specification
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specification
                    ->method('value')
                    ->willReturn($value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'json']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE json_value(entity.{$property}, '$') = ?1", (string) $qb);
                $this->assertSame($value, $qb->getParameter(1)->getValue());
            });
    }

    public function testJsonArrayContains()
    {
        $this
            ->forAll(
                $this->name(),
                Set\Strings::madeOf(Set\Chars::alphanumerical()),
            )
            ->then(function($property, $value) {
                $specification = JsonArray::contains($property, $value);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'json']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));

                $qb = (new ToQueryBuilder($repository, $em))($specification);

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT WHERE json_contains(entity.{$property}, ?1, '$') = 1", (string) $qb);
                $this->assertSame($value, \json_decode($qb->getParameter(1)->getValue()));
            });
    }

    public function testChildQuery()
    {
        $this
            ->forAll(
                $this->name(),
                $this->name(),
                $this->name(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($relation, $join, $property, $value1, $value2, $targetEntity) {
                $specificationJoin = $this->createMock(Comparator::class);
                $specificationJoin
                    ->method('property')
                    ->willReturn("$relation.$join");
                $specificationJoin
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationJoin
                    ->method('value')
                    ->willReturn($value1);
                $specificationCondition = $this->createMock(Comparator::class);
                $specificationCondition
                    ->method('property')
                    ->willReturn("$relation.$property");
                $specificationCondition
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationCondition
                    ->method('value')
                    ->willReturn($value2);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getAssociationMapping')
                    ->with($relation)
                    ->willReturn(['targetEntity' => $targetEntity]);
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));
                // needed for the left join to work
                $expected
                    ->select('entity')
                    ->from('Entity', 'entity');

                $qb = (new ToQueryBuilder($repository, $em))(new Child(
                    $specificationJoin,
                    $specificationCondition,
                ));

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT entity FROM Entity entity LEFT JOIN entity.{$relation} {$relation}0 WITH {$relation}0.{$join} = ?2 WHERE {$relation}0.{$property} = ?1", (string) $qb);
                $this->assertSame($value2, $qb->getParameter(1)->getValue());
                $this->assertSame($value1, $qb->getParameter(2)->getValue());
            });
    }

    public function testJoinOnceWhenJoiningSameChildTwice()
    {
        $this
            ->forAll(
                $this->name(),
                $this->name(),
                $this->name(),
                $this->name(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($relation, $join, $property1, $property2, $value1, $value2, $value3, $targetEntity) {
                $specificationJoin = $this->createMock(Comparator::class);
                $specificationJoin
                    ->method('property')
                    ->willReturn("$relation.$join");
                $specificationJoin
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationJoin
                    ->method('value')
                    ->willReturn($value1);
                $specificationCondition1 = $this->createMock(Comparator::class);
                $specificationCondition1
                    ->method('property')
                    ->willReturn("$relation.$property1");
                $specificationCondition1
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationCondition1
                    ->method('value')
                    ->willReturn($value2);
                $specificationCondition2 = $this->createMock(Comparator::class);
                $specificationCondition2
                    ->method('property')
                    ->willReturn("$relation.$property2");
                $specificationCondition2
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationCondition2
                    ->method('value')
                    ->willReturn($value3);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getAssociationMapping')
                    ->with($relation)
                    ->willReturn(['targetEntity' => $targetEntity]);
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));
                // needed for the left join to work
                $expected
                    ->select('entity')
                    ->from('Entity', 'entity');

                $qb = (new ToQueryBuilder($repository, $em))(
                    (new Child(
                        $specificationJoin,
                        $specificationCondition1,
                    ))->and(new Child(
                        $specificationJoin,
                        $specificationCondition2,
                    )),
                );

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT entity FROM Entity entity LEFT JOIN entity.{$relation} {$relation}0 WITH {$relation}0.{$join} = ?3 WHERE {$relation}0.{$property1} = ?1 AND {$relation}0.{$property2} = ?2", (string) $qb);
                $this->assertSame($value2, $qb->getParameter(1)->getValue());
                $this->assertSame($value3, $qb->getParameter(2)->getValue());
                $this->assertSame($value1, $qb->getParameter(3)->getValue());
            });
    }

    public function testMultiJoinChildrenWithSameCondition()
    {
        $this
            ->forAll(
                $this->name(),
                $this->name(),
                $this->name(),
                $this->name(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
                Set\Unicode::strings(),
            )
            ->then(function($relation, $join1, $join2, $property, $value1, $value2, $value3, $targetEntity) {
                $specificationJoin1 = $this->createMock(Comparator::class);
                $specificationJoin1
                    ->method('property')
                    ->willReturn("$relation.$join1");
                $specificationJoin1
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationJoin1
                    ->method('value')
                    ->willReturn($value1);
                $specificationJoin2 = $this->createMock(Comparator::class);
                $specificationJoin2
                    ->method('property')
                    ->willReturn("$relation.$join2");
                $specificationJoin2
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationJoin2
                    ->method('value')
                    ->willReturn($value2);
                $specificationCondition = $this->createMock(Comparator::class);
                $specificationCondition
                    ->method('property')
                    ->willReturn("$relation.$property");
                $specificationCondition
                    ->method('sign')
                    ->willReturn(Sign::equality);
                $specificationCondition
                    ->method('value')
                    ->willReturn($value3);
                $em = $this->createMock(EntityManagerInterface::class);
                $em
                    ->method('getExpressionBuilder')
                    ->willReturn(new Expr);
                $em
                    ->method('getClassMetadata')
                    ->willReturn($classMetadata = $this->createMock(ClassMetadata::class));
                $classMetadata
                    ->method('getAssociationMapping')
                    ->with($relation)
                    ->willReturn(['targetEntity' => $targetEntity]);
                $classMetadata
                    ->method('getFieldMapping')
                    ->willReturn(['type' => 'string']);
                $repository = $this->createMock(EntityRepository::class);
                $repository
                    ->expects($this->once())
                    ->method('createQueryBuilder')
                    ->with('entity')
                    ->willReturn($expected = new QueryBuilder($em));
                // needed for the left join to work
                $expected
                    ->select('entity')
                    ->from('Entity', 'entity');

                $qb = (new ToQueryBuilder($repository, $em))(
                    (new Child(
                        $specificationJoin1,
                        $specificationCondition,
                    ))->and(new Child(
                        $specificationJoin2,
                        $specificationCondition,
                    )),
                );

                $this->assertSame($expected, $qb);
                $this->assertSame("SELECT entity FROM Entity entity LEFT JOIN entity.{$relation} {$relation}0 WITH {$relation}0.{$join1} = ?3 LEFT JOIN entity.{$relation} {$relation}1 WITH {$relation}1.{$join2} = ?4 WHERE {$relation}0.{$property} = ?1 AND {$relation}1.{$property} = ?2", (string) $qb);
                $this->assertSame($value3, $qb->getParameter(1)->getValue());
                $this->assertSame($value3, $qb->getParameter(2)->getValue());
                $this->assertSame($value1, $qb->getParameter(3)->getValue());
                $this->assertSame($value2, $qb->getParameter(4)->getValue());
            });
    }

    private function name(): Set
    {
        return Set\Decorate::immutable(
            static fn(array $letters): string => \implode('', $letters),
            Set\Sequence::of(
                Set\Elements::of(...\range('a', 'z'), ...\range('A', 'Z')),
                Set\Integers::between(1, 10),
            ),
        );
    }
}
