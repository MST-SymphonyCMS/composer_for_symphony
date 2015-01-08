<?php

class Extension_Composer_For_Symphony extends Extension
{
    protected static $autoloader;
    protected static $prepared;

    public function getAutoloader()
    {
        return static::$autoloader;
    }

    public function getConfiguration()
    {
        $file = DOCROOT . '/' . trim(Symphony::Configuration()->get('file', 'symphony-composer'), '/');

        if (false === is_file($file)) {
            return false;
        }

        $config = json_decode(file_get_contents($file));

        if (false === is_object($config)) {
            return false;
        }

        $config->{'root-dir'} = dirname(realpath($file));

        return $config;
    }

    public function getSubscribedDelegates()
    {
        return [
            [
                'page' =>       '/system/preferences/',
                'delegate' =>   'AddCustomPreferenceFieldsets',
                'callback' =>   'onAppendPreferences'
            ],
            [
                'page' =>       '/system/preferences/',
                'delegate' =>   'Save',
                'callback' =>   'onSavePreferences'
            ],
            [
                'page' =>       '/all/',
                'delegate' =>   'ModifySymphonyLauncher',
                'callback' =>   'onApplicationReady'
            ]
        ];
    }

    public function onApplicationReady()
    {
        $this->prepareInstance();
    }

    public function onAppendPreferences($context)
    {
        $fieldset = new XMLElement('fieldset');
        $fieldset->setAttribute('class', 'settings');
        $fieldset->appendChild(new XMLElement('legend', __('Entry Tools')));

        $label = Widget::Label(__('Composer File'));
        $input = Widget::Input(
            'settings[symphony-composer][file]',
            Symphony::Configuration()->get('file', 'symphony-composer')
        );
        $input->setAttribute('placeholder', 'composer.json');
        $label->appendChild($input);

        if (isset($context['errors']['symphony-composer']['file'])) {
            $label = Widget::Error($label, $context['errors']['symphony-composer']['file']);
        }

        $fieldset->appendChild($label);

        $context['wrapper']->appendChild($fieldset);
    }

    public function onSavePreferences($context)
    {
        if (
            false === isset($context['settings']['symphony-composer']['file'])
            || '' === trim($context['settings']['symphony-composer']['file'])
        ) {
            $context['errors']['symphony-composer']['file'] = __('Enter the relative path to your composer file.');
        }
    }

    public function prepareInstance()
    {
        if (false === isset(static::$prepared)) {
            static::$prepared = true;

            $config = $this->getConfiguration();

            if (false === $config) {
                return false;
            }

            static::$autoloader = require_once (
                $config->{'root-dir'}
                . '/'
                . (
                    isset($config->{'vendor-dir'})
                        ? $config->{'vendor-dir'}
                        : 'vendor'
                )
                . '/autoload.php'
            );

            /**
             * When the composer autoloader has been loaded.
             *
             * @delegate SymphonyComposerReady
             * @param string $context
             *  '/all/'
             * @param Composer\Autoload\ClassLoader $autoloader
             *  The Composer autoloader.
             */
            Symphony::ExtensionManager()->notifyMembers('SymphonyComposerReady', '/all/', [
                'autoloader' => static::$autoloader
            ]);

            return true;
        }
    }
}