<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Honeypot extends BaseConfig
{
    /**
     * Makes Honeypot visible or not to human
     *
     * @var bool
     */
    public $hidden = true;

    /**
     * Honeypot Label Content
     *
     * @var string
     */
    public $label = 'Fill This Field';

    /**
     * Honeypot Field Name
     *
     * @var string
     */
    public $name = 'honeypot';

    /**
     * Honeypot HTML Template
     *
     */
    public string $template = '<label>{label}</label><input type="text" name="{name}" value="">';

    /**
     * Honeypot container
     *
     * @var string
     */
    public $container = '<div style="display:none">{template}</div>';

    /**
     * The id attribute for Honeypot container tag
     *
     * Used when CSP is enabled.
     */
    public string $containerId = 'hpc';
}
