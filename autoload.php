<?php

// Setup autoloading
spl_autoload_register(function ($class_name) {
    list($namespace, $class_name) = explode('\\', $class_name);
    
    include __DIR__ . '/lib/' . $class_name . '.php';
});