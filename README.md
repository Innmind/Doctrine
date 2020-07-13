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

## Usage

Todo
