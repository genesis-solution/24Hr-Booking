<?php

namespace MPHB\Addons\RequestPayment;

require ROOT . 'includes/functions.php';

spl_autoload_register(function ($className) {
    // "MPHB\Addons\RequestPayment\Subpackage\ClassX"
    $className = ltrim($className, '\\');

    if (strpos($className, __NAMESPACE__) !== 0) {
        return false;
    }

    // "classes\Subpackage\ClassX"
    $pluginFile = str_replace(__NAMESPACE__, 'classes', $className);
    // "classes/Subpackage/ClassX"
    $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
    // "classes/Subpackage/ClassX.php"
    $pluginFile .= '.php';

    require ROOT . $pluginFile;

    return true;
});
