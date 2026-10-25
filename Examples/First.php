<?php declare(strict_types=1);
use function Belin\Which\which;

// Finds the first instance of an executable.
$path = which("foobar")->first;
if (!$path) echo "The 'foobar' command cannot be found.";
else echo "The 'foobar' command is located at: $path";
