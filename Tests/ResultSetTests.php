<?php declare(strict_types=1);
namespace Belin\Which;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\{Test, TestDox};

/**
 * Tests the features of the {@see ResultSet} class.
 */
#[TestDox("ResultSet")]
class ResultSetTests extends TestCase {

	#[Test, TestDox("all")]
	public function all(): void {
		$paths = ["Resources"];

		// It should return the path of the `Executable.cmd` file on Windows.
		$executables = which("Executable", paths: $paths)->all;
		if (!Finder::isWindows()) $executables |> Should::beEmpty();
		else {
			$executables |> count(...) |> Should::be(1);
			$executables[0] |> Should::endWith("\\Resources\\Executable.cmd");
		}

		// It should return the path of the `Executable.sh` file on POSIX.
		$executables = which("Executable.sh", paths: $paths)->all;
		if (Finder::isWindows()) $executables |> Should::beEmpty();
		else {
			$executables |> count(...) |> Should::be(1);
			$executables[0] |> Should::endWith("/Resources/Executable.sh");
		}

		// It should return an empty array if the searched command is not executable or not found.
		which("NotExecutable.sh", paths: $paths)->all |> Should::beEmpty();
		which("foo", paths: $paths)->all |> Should::beEmpty();
	}

	#[Test, TestDox("first")]
	public function first(): void {
		$paths = ["Resources"];

		// It should return the path of the `Executable.cmd` file on Windows.
		$executable = which("Executable", paths: $paths)->first;
		if (Finder::isWindows()) $executable ?? "" |> Should::endWith("\\Resources\\Executable.cmd");
		else $executable |> Should::beEmpty();

		// It should return the path of the `Executable.sh` file on POSIX.
		$executable = which("Executable.sh", paths: $paths)->first;
		if (Finder::isWindows()) $executable |> Should::beEmpty();
		else $executable ?? "" |> Should::endWith("/Resources/Executable.sh");

		// It should return an empty string if the searched command is not executable or not found.
		which("NotExecutable.sh", paths: $paths)->first |> Should::beEmpty();
		which("foo", paths: $paths)->first |> Should::beEmpty();
	}
}
