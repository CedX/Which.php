<?php declare(strict_types=1);
namespace Belin\Which;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};

/**
 * Tests the features of the {@see Finder} class.
 */
#[TestDox("Finder")]
class FinderTests extends TestCase {

	#[Test, TestDox("__construct()")]
	public function constructor(): void {
		// It should set the `paths` property to the value of the `PATH` environment variable by default.
		$pathEnv = getenv("PATH");
		$paths = $pathEnv ? explode(PATH_SEPARATOR, $pathEnv) : [];
		new Finder()->paths |> Should::be($paths);

		// It should set the `extensions` property to the value of the `PATHEXT` environment variable by default.
		$pathExt = getenv("PATHEXT");
		$extensions = $pathExt ? explode(";", $pathExt) |> (fn($list) => array_map(mb_strtolower(...), $list)) : [".exe", ".cmd", ".bat", ".com"];
		new Finder()->extensions |> Should::be($extensions);

		// It should put in lower case the list of file extensions.
		new Finder(extensions: [".EXE", ".JS", ".PS1"])->extensions |> Should::be([".exe", ".js", ".ps1"]);
	}

	#[Test, TestDox("find()")]
	public function find(): void {
		$finder = new Finder(["Resources"]);

		// It should return the path of the `Executable.cmd` file on Windows.
		$executables = [...$finder->find("Executable")];
		$executables |> count(...) |> Should::be(Finder::isWindows() ? 1 : 0);
		if (Finder::isWindows()) $executables[0]->getPathname() |> Should::endWith("\\Resources\\Executable.cmd");

		// It should return the path of the `Executable.sh` file on POSIX.
		$executables = [...$finder->find("Executable.sh")];
		$executables |> count(...) |> Should::be(Finder::isWindows() ? 0 : 1);
		if (!Finder::isWindows()) $executables[0]->getPathname() |> Should::endWith("/Resources/Executable.sh");

		// It should return an empty array if the searched command is not executable or not found.
		[...$finder->find("NotExecutable.sh")] |> Should::beEmpty();
		[...$finder->find("foo")] |> Should::beEmpty();
	}

	#[Test, TestDox("isExecutable()")]
	public function isExecutable(): void {
		$finder = new Finder;

		// It should return `false` if the searched command is not executable or not found.
		$finder->isExecutable("Resources/NotExecutable.sh") |> Should::beFalse();
		$finder->isExecutable("foo/bar/baz.qux") |> Should::beFalse();

		// It should return `false` for a POSIX executable, when test is run on Windows.
		$finder->isExecutable("Resources/Executable.sh") |> Should::notBe(Finder::isWindows());

		// It should return `false` for a Windows executable, when test is run on POSIX.
		$finder->isExecutable("Resources/Executable.cmd") |> Should::be(Finder::isWindows());
	}
}
