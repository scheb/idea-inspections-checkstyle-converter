scheb/idea-inspections-checkstyle-converter
===========================================

Convert inspection results from JetBrains IDEs (like IntelliJ and PHPStorm) from its XML format to the Checkstyle
format.

[![Build Status](https://travis-ci.org/scheb/idea-inspections-checkstyle-converter.svg?branch=master)](https://travis-ci.org/scheb/idea-inspections-checkstyle-converter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/scheb/idea-inspections-checkstyle-converter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/scheb/idea-inspections-checkstyle-converter/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/scheb/idea-inspections-checkstyle-converter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/scheb/idea-inspections-checkstyle-converter/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/scheb/idea-inspections-checkstyle-converter/v/stable.svg)](https://packagist.org/packages/scheb/idea-inspections-checkstyle-converter)
[![License](https://poser.pugx.org/scheb/idea-inspections-checkstyle-converter/license.svg)](https://packagist.org/packages/scheb/idea-inspections-checkstyle-converter)

Installation
------------

```bash
$ composer require scheb/idea-inspections-checkstyle-converter
```

Usage
-----

```bash
./vendor/bin/inspection-converter [inspectionsFolder] [checkstyleOutputFile]

Arguments:
  inspectionsFolder                        Folder with the inspections XML files
  checkstyleOutputFile                     Checkstyle file to be written

Options:
  -r, --projectRoot=PROJECTROOT            Path to the project root [default: ""]
  -i, --ignoreInspection=IGNOREINSPECTION  Ignore inspections matching the regex pattern (multiple values allowed)
  -m, --ignoreMessage=IGNOREMESSAGE        Ignore messages matching the regex pattern (multiple values allowed)
  -f, --ignoreFile=IGNOREFILE              Ignore files matching the regex pattern (multiple values allowed)
  -s, --ignoreSeverity=IGNORESEVERITY      Ignore severities (exact match) (multiple values allowed)
  -S, --mapSeverity=MAPSEVERITY            Map severity from to, format "input:output" (multiple values allowed)
  -D, --defaultSeverity=DEFAULTSEVERITY    Used in combination with mapSeverity to define the default severity
```

Example:

```bash
./vendor/bin/inspection-converter ./inspections ./checkstyle.xml --rootPath=src --ignoreInspection=SpellCheckingInspection --ignoreMessage=type.*long
```

Contribute
----------
You're welcome to [contribute](https://github.com/scheb/idea-inspections-checkstyle-converter/graphs/contributors) to
this library by creating a pull requests or feature request in the issues section. For pull requests, please follow
these guidelines:

- Symfony code style
- PHP7.1 type hints for everything (including: return types, `void`, nullable types)
- Please add/update test cases
- Test methods should be named `[method]_[scenario]_[expected result]`

To run the test suite install the dependencies with `composer install` and then execute `bin/phpunit`.

License
-------
This library is available under the [MIT license](LICENSE).
