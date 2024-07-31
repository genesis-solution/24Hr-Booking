<?php

namespace MPHB\Addons\Invoice;

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(function ($className) {
    // "Namespace\Package\ClassX"
    $className = ltrim($className, '\\');

    $vendors = [
        // Class name => Custom relative path
    ];

    if (strpos($className, __NAMESPACE__) === 0) {
        // "classes\Package\ClassX"
        $pluginFile = str_replace(__NAMESPACE__, 'classes', $className);
        // "classes/Package/ClassX"
        $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
        // "classes/Package/Class-X"
        $pluginFile = preg_replace('/([a-z])([A-Z])/', '$1-$2', $pluginFile);
        $pluginFile = preg_replace('/([A-Z])([A-Z][a-z])/', '$1-$2', $pluginFile);
        // "classes/package/class-x"
        $pluginFile = strtolower($pluginFile);
        // "classes/package/class-x.php"
        $pluginFile .= '.php';
        // ".../project-dir/classes/package/class-x.php"
        $pluginFile = PLUGIN_DIR . $pluginFile;

        require $pluginFile;

    } else if (array_key_exists($className, $vendors)) {
        // ".../project-dir/vendors/name/**.php"
        $pluginFile = PLUGIN_DIR . $vendors[$className];

        require $pluginFile;
    }
});
