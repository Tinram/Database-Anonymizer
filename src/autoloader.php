<?php

declare(strict_types=1);

spl_autoload_register(function(string $sClassName): void
{
    $sClassName = str_replace('\\', DIRECTORY_SEPARATOR, $sClassName);
    require $sClassName . '.php';
});
