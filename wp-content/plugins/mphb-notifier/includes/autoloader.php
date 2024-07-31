<?php

namespace MPHB\Notifier;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($class) {
    if (strpos($class, __NAMESPACE__) !== 0) {
        return; // Not ours
    }

    $vendors = [
        'MPHB\Notifier\Async\BackgroundProcess' => 'vendors/wp-background-processing/background-process.php'
    ];

    if (array_key_exists($class, $vendors)) {
        $file = $vendors[$class];
    } else {
        // "MPHB\Notifier\Package\SubPackage\ClassX" -> "classes/package/sub-package/class-x.php"
        $file = str_replace(__NAMESPACE__, 'classes', $class);
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
        $file = preg_replace('/([a-z])([A-Z])/', '$1-$2', $file);
        $file = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $file);
        $file = strtolower($file);
        $file .= '.php';
    }

    // ".../classes/package/sub-package/class-x.php"
    require PLUGIN_DIR . $file;
});
