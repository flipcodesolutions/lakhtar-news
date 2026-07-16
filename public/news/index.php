<?php

/**
 * Safety net if Apache serves this directory index for /news.
 * Force Laravel to treat the request as the main front controller
 * so routes resolve to /news instead of /public/news/...
 */
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).DIRECTORY_SEPARATOR.'index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require dirname(__DIR__).'/index.php';
