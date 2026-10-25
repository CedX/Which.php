<?php declare(strict_types=1);
use function Belin\Which\which;

// Finds all instances of an executable.
$paths = which("foobar")->all;
if (!$paths) echo "The 'foobar' command cannot be found.";
else {
	echo "The 'foobar' command is available at these locations:", PHP_EOL;
	foreach ($paths as $path) echo "- $path", PHP_EOL;
}
