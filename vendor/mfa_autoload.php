<?php

// Register the autoloader
spl_autoload_register(function ($class) {
    // Map namespaces to directories
    $namespaces = [
        'Otp\\' => __DIR__ . '/otp/src',
        'ParagonIE\\ConstantTime\\' => __DIR__ . '/paragonie/constant_time_encoding/src'
    ];

    // Check each namespace
    foreach ($namespaces as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        // Get the relative class name
        $relative_class = substr($class, $len);

        // Replace namespace separator with directory separator
        $file = $base_dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return true;
        }

        // For debugging
        error_log("Tried to load class file: " . $file);
    }

    return false;
}); 