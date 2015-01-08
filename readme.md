# Composer for Symphony

Integrate third party APIs into Symphony CMS using [Composer](https://getcomposer.org/).


## Installation

1. Upload the 'composer_for_symphony' folder in this archive to your Symphony 'extensions' folder.
2. Enable it by selecting the "Composer for Symphony", choose Enable from the with-selected menu, then click Apply.


## Usage

Composer for Symphony makes sure that the Composer autoloader is loaded as early as possible, giving you easy access to any libraries you have installed. You can integrate with Composer by adding the following delegate to your own extensions:

```php
public function getSubscribedDelegates()
{
    return [
        [
            'page'      => '/all/',
            'delegate'  => 'SymphonyComposerReady',
            'callback'  => 'onComposerReady'
        ]
    ];
}
```

This delegate is called when the composer autoloader is initialised, giving you easy access as early as possible during Symphony execution:

```php
public function onComposerReady($context)
{
    // Add a PSR-4 library:
    $context['autoloader']->setPsr4('Your\\Namespace\\', __DIR__ . '/lib');
}
```

In addition to this, the autoloader can be accessed at any time by calling:

```php
Symphony::ExtensionManager()->create('composer_for_symphony')->getAutoloader();
```

And the configuration file can be read by calling:

```php
Symphony::ExtensionManager()->create('composer_for_symphony')->getConfiguration();
```