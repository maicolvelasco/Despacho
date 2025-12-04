<?php
// /config/autoload.php

spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/../models/' . $class_name . '.php',
        __DIR__ . '/../controllers/' . $class_name . '.php',
        // A単ade otros directorios si es necesario
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
?>