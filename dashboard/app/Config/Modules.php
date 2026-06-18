<?php

namespace Config;

use CodeIgniter\Modules\Modules as BaseModules;

/**
 * Modules Configuration.
 *
 * NOTE: This class is required prior to Autoloader instantiation,
 *       and does not extend BaseConfig.
 */
class Modules extends BaseModules
{
    /**
     * Should modular discovery be enabled?
     */
    public $enabled = true;

    /**
     * Should CodeIgniter discover components in Composer packages?
     */
    public $discoverInComposer = true;

    /**
     * The Composer package list for Auto-Discovery
     */
    public $composerPackages = [];

    /**
     * Auto-discovery rules
     */
    public $aliases = [
        'events',
        'filters',
        'registrars',
        'routes',
        'services',
    ];
}
