# Changelog

## 6.5.1
#### 2024-09-27

- Fixed deprecation notices for PHP 8.4
  - Removed the default value `$url = null` from the constructor of `HtmlBuilder`.
    It was followed by a required parameter thus had no effect. Additionally, the class doesn't handle situations where
    the `UrlGenerator $url` property is null, therefore it's safe to assume that nobody can actually use this class with
    a `null` UrlGenerator.
  - Explicitly marked the `$request` parameter as nullable in the `FormBuilder`'s constructor. It was already implicitly
    nullable due to its default value.
- Fixed deprecation notices in test cases by explicitly adding properties to the test classes.

## 6.5.0
#### 2024-03-12

- Fork of laravelcollective/html:6.x on Mar 12, 2024
- Dropped PHP < 8.1 version support
- Dropped Laravel 6 - 9 version support
- Added Laravel 11 version support
- Converted the travis test runners to Github Actions
- Drop-in replacement of the v6.4 version of the original package
- Upgraded PHPUnit to v10
