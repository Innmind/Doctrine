# Changelog

## [Unreleased]

### Added

- `Innmind\Doctrine\Sort`
- `Innmind\Doctrine\Matching`

### Changed

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
