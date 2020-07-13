# doctrine

[![Build Status](https://github.com/innmind/doctrine/workflows/CI/badge.svg)](https://github.com/innmind/doctrine/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/doctrine/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/doctrine)
[![Type Coverage](https://shepherd.dev/github/innmind/doctrine/coverage.svg)](https://shepherd.dev/github/innmind/doctrine)

This library is an abstraction on top of [Doctrine](https://packagist.org/packages/doctrine/orm) with the intention to remove all implicit states.

Managing the state in an application can become hard when the codebase grows and states (especially implicit ones) is one of the source of bugs in applications.

Doctrine is usually the default choice (not an actual fact) when interacting with databases as it comes bundled with [Symfony](https://symfony.com). Its interface cover many use cases but expose many implicit states such as transactions or cursors in collections.

This is solved here by using principles from the functional programming paradigm.

## Installation

```sh
composer require innmind/doctrine
```

## Design choices

<details>
    <summary>Click to expand</summary>

### `Sequence` vs `Set`

`Set` has been discarded for this library as the unicity of entities cannot be guaranted from the returned collections. It also would prevent the use of the `map` function as many entities may be mapped to a single new value, this may lead to unexpected behaviour for newcomers to such paradigm. This is mainly why the choice has been toward `Sequence`.

In case you really want to use sets, you may use [`innmind/immutable`](https://github.com/innmind/immutable/#set).

### `Sequence` is not an iterator

Iterators are widely used as they can be used in `foreach` statements, however as described above iterators contains an implicit state: their cursor. This can lead to bugs as it allows us to do `\current($iterator)` (frequently used to get the first value) but the returned value may defer if some other function moved the iterator's cursor prior to that call.

### Minimal `Sequence` interface

The interface doesn't include methods such as `first`, `last` or `get($index)` as it tends to lead to calls based on asumptions that the sequence has a given size.

`Sequence`s here are used as a collection of elements that match a predicate. This means that a collection may be empty in any given case, thus forcing you to check the size before accessing a value.

### `Sequence`s are immutable

With immutable structures you avoid implicit changes when passing the sequence as an argument to another method. You will never have a change of order or a change of number of elements.

The only state change left is the state of your entities, but this is an _explicit_ location for a change of state.

### Enforcing the use of an `Id`

Doctrine allows you to generate an id for you when your entities are persisted. This is an implicit state change.

In order to avoid this implicit you need to specify the id before persisting your entities. This prevents you from relying on the auto generated id from your database as you can't avoid collisions.

The unique solution (that I'm aware of) is to use `UUID`s. The `Id` provided by this library use them so you don't have to think of it anymore.

### A single `Id` class for all entities

This is no longer a problem as it is provided with a template understood by [`vimeo/psalm`](https://github.com/vimeo/psalm/blob/master/docs/annotating_code/templated_annotations.md).

The class is not declared final in case you really need to extend the behaviour.

### No `flush` method on the `Manager`

Being free to call the `persist` and `flush` methods when you wish it opens the door to implicit states in your codebase. You may end up either flushing unwanted persisted entities (`persist` calls before an error occured) or forgetting to `flush` persisted entities (resulting in lost state change).

Here this is avoided by forcing to execute all mutations in a given context (via `Manager::mutate()` and `Manager::transaction()`). So it's always all or nothing.
</details>

## Usage

All the use cases below use the code declared in the [`example` folder](example/).

Pre-requisite for all use cases:

```php
use Innmind\Doctrine\Manager;
use Example\Innmind\Doctrine\User;

$manager = new Manager($entityManager);
```

### Fetching all entities from the database

```php
$manager
    ->repository(User::class)
    ->all()
    ->sort('username', 'asc')
    ->foreach(function(User $user): void {
        echo $user->username()."\n";
    });
```

**Note**: The queries are delayed to the last moment possible to leverage the database as most as possible.

### Pagination

```php
$numberOfElementPerPage = 10;
$manager
    ->repository(User::class)
    ->all()
    ->sort('username', 'asc')
    ->drop($page * $numberOfElementPerPage)
    ->take($numberOfElementPerPage)
    ->foreach(function(User $user): void {
        echo $user->username()."\n";
    });
```

### Filtering

It uses the [`Specification` pattern](https://en.wikipedia.org/wiki/Specification_pattern) (normalized in the library [`innmind/specification`](https://github.com/innmind/specification)).

```php
use Example\Innmind\Doctrine\Username;

$manager
    ->repository(User::class)
    ->matching(
        Username::of('alice')->or(
            Username::of('jane'),
        ),
    )
    ->sort('username', 'asc')
    ->drop(20)
    ->take(10)
    ->foreach(function(User $user): void {
        echo $user->username()."\n";
    });
```

This example is the equivalent of `SELECT * FROM user WHERE username = 'alice' OT username = 'jane' ORDER BY username OFFSET 20 LIMIT 10`.

**Note**: This chain of method calls result once again in a single database call.

### Adding new entities

```php
use Innmind\Doctrine\Id;

$user = $manager->mutate(function($manager): User {
    $user = new User(
        Id::new(),
        'someone',
    );
    $manager
        ->repository(User::class)
        ->add($user);

    return $user;
});
```

If you try to call `Repository::add()` or `Repository::remove()` outside the function it will raise an exception.

**Note**: If the function throws an exception nothing will be flushed to the database.

### Transactions

```php
$manager->transaction(function($manager, $flush) {
    $progress = 0;
    $repository = $manager->repository(User::class);

    foreach ($someSource as $args) {
        $repository->add(new User(...$args));
        ++$progress;

        if ($progress % 20 === 0) {
            // flush entities to the database every 20 additions
            $flush();
        }
    }
});
```

**Note**: Call the `$flush` function only when in a context of imports as it will detach all the entities from entity manager, meaning if you kept references to entities they will no longer be understood by doctrine
