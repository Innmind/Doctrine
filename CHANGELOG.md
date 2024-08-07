# Changelog

## 3.0.0 - 2024-07-07

### Changed

- Requires `innmind/specification:~4.0`

## 2.5.1 - 2023-09-18

### Fixed

- `Sign::contains`, `Sign::startsWith` and `Sign::endsWith` would not yield the expected result when containing special characters `\`, `_` and `%` (as they're special pattern characters), these characters are now escaped so it would exactly match

## 2.5.0 - 2023-09-16

### Added

- Support for `innmind/immutable` `5`

### Removed

- Support for PHP `8.1`

## 2.4.1 - 2023-08-19

### Fixed

- Fix loading non lazy sequences when using a fetch join

## 2.4.0 - 2023-08-10

### Added

- `Innmind\Doctrine\Matching::lazy()`

## 2.3.0 - 2023-05-10

### Added

- `Innmind\Doctrine\Matching::map()`

### Changed

- `Innmind\Doctrine\Repository::count()` the specification argument is now optional (it will count all entities)

## 2.2.1 - 2023-02-21

### Fixed

- `Innmind\Doctrine\Matching::drop()`, `take()` and `sort()` no longer lose the type of object

## 2.2.0 - 2023-02-17

### Added

- `Innmind\Doctrine\Repository::count()`

## 2.1.0 - 2023-01-01

### Added

- `Innmind\Doctrine\Id::of()` named constructor

## 2.0.0 - 2022-04-16

### Added

- `Innmind\Doctrine\Sort`
- `Innmind\Doctrine\Matching`

### Changed

- The callable passed to `Innmind\Doctrine\Manager::mutate` and `Innmind\Doctrine\Manager::transaction` must return an instance of `Innmind\Immutable\Either`
- `Innmind\Doctrine\Id` is now `final`
- `Innmind\Doctrine\Id::new` now expect the `class-string` of the entity it is for
- `Innmind\Doctrine\Manager` constructor is now private, use `::of` named constructor instead
- `Innmind\Doctrine\Repository::get` now returns a `Innmind\Immutable\Maybe` instead of throwing an exception
- Update `innmind/immutable` to version `~4.0`
- `Innmind\Doctrine\Repository::matching()` now returns an instance of `Innmind\Doctrine\Matching`
- `Innmind\Doctrine\Repository::all()` now returns an instance of `Innmind\Doctrine\Matching`
- Update `innmind/specification` to version `~3.0`

### Removed

- `Innmind\Doctrine\Sequence`
- `Innmind\Doctrine\Sequence\Concrete`
- `Innmind\Doctrine\Sequence\DeferFindBy`
- `Innmind\Doctrine\Sequence\DeferQuery`
- `Innmind\Doctrine\unwrap`
- Support for php `7.4` and `8.0`
