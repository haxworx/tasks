## Contributing Guide

The recommended way to install PHP CS Fixer is to use [Composer](https://getcomposer.org/download/)
in a dedicated `composer.json` file in your project, for example in the
`tools/php-cs-fixer` directory:

```console
mkdir -p tools/php-cs-fixer
composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer
```

Before commit, call `composer standardize` to apply code styling