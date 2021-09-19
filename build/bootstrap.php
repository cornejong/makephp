#!/usr/bin/php
<?php declare(strict_types = 1);

/* Sorry windows folks, another time */
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    print("\033[31m✘\033[0m Windows is currently not supported!") . PHP_EOL;
    exit(1);
}

/* Turn off read only */
@ini_set('phar.readonly', 'Off');

/* Map app structure */
Phar::mapPhar('self.phar');

/* Add the phar to the include path */
/* If you want to extend instead of replace the existing path */
/* Uncomment the first part of the argument below */
set_include_path(/* get_include_path() . PATH_SEPARATOR .  */ implode(PATH_SEPARATOR, [
    'phar://self.phar/'
]));

/* Define some constants to be used in your app */
define('IS_BUILD', true);
define('BUILD_ID', '{{build_id}}');
define('BUILD_TIME', '{{build_time}}');
define('BUILD_NUMBER', '{{build_number}}');

/* Lets get easy access to the location of the packaged file */
define('SELF_LOCATION', __FILE__);

/* Require the boot script */
require 'phar://self.phar/boot.php';
/* Require the main entry file */
require 'phar://self.phar/main.php';

/* turn read only back on */
ini_set('phar.readonly', 'On');

__HALT_COMPILER();
