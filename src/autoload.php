<?php
/**
 * Copyright © PureClarity. All rights reserved.
 * See LICENSE.txt for license details.
 */

spl_autoload_register(function ($class_path) {
    $baseDir = __DIR__;
    $parts = explode('\\', $class_path);
    $class_name = array_pop($parts) . '.php';
    $path = implode(DIRECTORY_SEPARATOR, $parts);
    $path .= DIRECTORY_SEPARATOR . $class_name;

    if (file_exists($baseDir . DIRECTORY_SEPARATOR . $path)) {
        require_once $baseDir . DIRECTORY_SEPARATOR . $path;
    }
});
