# Changelog

## 2.20 - 2023-02-17

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
