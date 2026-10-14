<?php declare(strict_types=1);
namespace Belin\Which;

use Symfony\Component\Filesystem\Path;

/**
 * Finds the instances of an executable in the system path.
 */
class Finder {

	/**
	 * The list of executable file extensions.
	 * @var string[]
	 */
	public array $extensions {
		set => array_map(mb_strtolower(...), $value);
	}

	/**
	 * The list of system paths.
	 * @var string[]
	 */
	public array $paths {
		set => array_map(fn($path) => (string) preg_replace('/^"|"$/', "", $path), $value);
	}

	/**
	 * Creates a new finder.
	 * @param string[] $paths The system path. Defaults to the `PATH` environment variable.
	 * @param string[] $extensions The executable file extensions. Defaults to the `PATHEXT` environment variable.
	 */
	public function __construct(array $paths = [], array $extensions = []) {
		if (!$paths) {
			$pathEnv = getenv("PATH") ?: "";
			$paths = $pathEnv ? explode(PATH_SEPARATOR, $pathEnv) : [];
		}

		if (!$extensions) {
			$pathExt = getenv("PATHEXT") ?: "";
			$extensions = $pathExt ? explode(";", $pathExt) : [".exe", ".cmd", ".bat", ".com"];
		}

		$this->extensions = $extensions;
		$this->paths = $paths;
	}

	/**
	 * Gets a value indicating whether the current platform is Windows.
	 * @return bool `true` if the current platform is Windows, otherwise `false`.
	 */
	public static function isWindows(): bool {
		return PHP_OS_FAMILY == "Windows";
	}

	/**
	 * Finds the instances of an executable in the system path.
	 * @param string $command The command to be resolved.
	 * @return \Generator<int, \SplFileInfo> The paths of the executables found.
	 */
	public function find(string $command): iterable {
		foreach ($this->paths as $directory) yield from $this->findExecutables($directory, $command);
	}

	/**
	 * Gets a value indicating whether the specified file is executable.
	 * @param string $file The path of the file to be checked.
	 * @return bool `true` if the specified file is executable, otherwise `false`.
	 */
	public function isExecutable(string $file): bool {
		$fileInfo = new \SplFileInfo($file);
		if (!$fileInfo->isFile()) return false;
		if (self::isWindows()) return in_array("." . mb_strtolower($fileInfo->getExtension()), $this->extensions);
		return posix_access($fileInfo->getPathname(), POSIX_X_OK);
	}

	/**
	 * Finds the instances of an executable in the specified directory.
	 * @param string $directory The directory path.
	 * @param string $command The command to be resolved.
	 * @return \Generator<int, \SplFileInfo> The paths of the executables found.
	 */
	private function findExecutables(string $directory, string $command): iterable {
		$basePath = (string) getcwd();
		$extensions = self::isWindows() ? $this->extensions : [];
		foreach (["", ...$extensions] as $extension) {
			$resolvedPath = Path::makeAbsolute(Path::join($directory, "$command$extension"), $basePath);
			if ($this->isExecutable($resolvedPath)) yield new \SplFileInfo(strtr($resolvedPath, ["/" => DIRECTORY_SEPARATOR]));
		}
	}
}
