<?php namespace MicroStatic;

/* Load all helpers from directory  */
foreach (glob(__DIR__ . "/*.php") as $filename) {
    require_once $filename;
}

/* Default PSR-4 paths */
$autoloadPaths = [
    [
        'namespace' => 'MicroStatic',
        'directory' => realpath(__DIR__ . '/../') . '/libraries'
    ],
    [
        'namespace' => 'App',
        'directory' => realpath(__DIR__ . '/../') . '/modules'
    ],
    [
        'namespace' => 'Acme',
        'directory' => realpath(__DIR__ . '/../') . '/modules'
    ],
];

/* SPL-Autoloader */
spl_autoload_register(function ($requestedClass) use ($autoloadPaths) {
    $fileLoaded = false;

    foreach ($autoloadPaths as $item) {
        $namespace = rtrim($item['namespace'], '\\') . '\\';
        $directory = rtrim($item['directory'], '/') . '/';
        $lowerCase = isset($item['lowerCasePath']) ? $item['lowerCasePath'] : false;

        $strlen = strlen($namespace);
        if (strncmp($namespace, $requestedClass, $strlen) !== 0) {
            continue;
        }

        $relativeClass = substr($requestedClass, $strlen);
        $relativeFile = str_replace('\\', '/', $relativeClass) . '.php';

        if ($lowerCase) {
            $dir = dirname($relativeFile) . '/';
            // Convert CamelCase to hyphen-case
            $dir = preg_replace_callback('/.+(?<!\/)([A-Z]+)/',
                create_function('$matches',
                    'return strtolower(substr($matches[0], 0, -1)) . \'-\' . strtolower($matches[1]);'),
                $dir);
            $dir = strtolower($dir);
        } else {
            $dir = dirname($relativeFile) . '/';
        }

        $file = $directory . $dir . basename($relativeFile);

        if (file_exists($file)) {
            require_once $file;
            $fileLoaded = true;
            break;
        }
    }

    if (!$fileLoaded) {
        $path = __DIR__ . '/../../' . str_replace('\\', '/',
                $requestedClass) . '.php';
        $file = realpath(strtolower(dirname($path)) . '/' . basename($path));

        if (file_exists($file)) {
            require_once $file;
        }
    }

});


