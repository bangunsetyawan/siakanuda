<?php

namespace Config;

/**
 * Paths Configuration
 *
 * This file contains the paths to the system, application,
 * writable, and tests directories.
 */
class Paths
{
    /**
     * System Directory
     *
     * The path to the system directory.
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * Application Directory
     *
     * The path to the application directory.
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Writable Directory
     *
     * The path to the writable directory.
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * Tests Directory
     *
     * The path to the tests directory.
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * View Directory
     *
     * The path to the directory that contains the views.
     */
    public string $viewDirectory = __DIR__ . '/../Views';
}
