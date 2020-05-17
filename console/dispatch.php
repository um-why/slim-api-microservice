<?php
use Symfony\Component\Console\Application;

return function (Application $app) {
    $app->add(new Console\Service\Creation\MakeCommand());

    $commands = availableCommand();
    foreach ($commands as $k => $v) {
        $commands[$k] = new $v;
    }
    if (isset($commands[0])) {
        $app->addCommands($commands);
    }
};

function availableCommand(): array
{
    $classRs = getAllFilesClass();
    foreach ($classRs as $k => $v) {
        $ref = new \ReflectionClass($v);
        $parentClass = $ref->getParentClass()->getName();
        if ($parentClass !== 'Symfony\Component\Console\Command\Command') {
            unset($classRs[$k]);
            continue;
        }
    }
    return array_values($classRs);
}

function getAllFilesClass($path = ''): array
{
    if ($path == '') {
        $path = ROOT_PATH . 'console' . DIRECTORY_SEPARATOR . 'Command';
    }
    $filRs = scandir($path);
    $return = array();
    foreach ($filRs as $v) {
        if ($v == '.' || $v == '..') {
            continue;
        }
        $v = $path . DIRECTORY_SEPARATOR . $v;
        if (is_dir($v)) {
            $return = array_merge($return, getAllFilesClass($v));
        } else {
            $class = str_replace(ROOT_PATH . 'console', 'Console', $v);
            $class = str_replace('.php', '', $class);
            $return[] = str_replace('/', '\\', $class);
        }
    }
    return $return;
}
