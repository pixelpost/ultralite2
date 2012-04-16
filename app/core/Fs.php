<?php

namespace pixelpost\core;

use RecursiveIteratorIterator  as RII,
	RecursiveDirectoryIterator as RDI;

/**
 * File System support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Fs
{
	/**
	 * Copy all content of the $src to the $dest folder.
	 *
	 * @param  string $src  The source folder.
	 * @param  string $dest The destination folder
	 * @return bool
	 */
	public static function install($src, $dest)
	{
		if (!is_dir($src)) return true;

		assert('pixelpost\core\Log::info("(Fs) install %s to %s", $src, $dest)');

		$len = mb_strlen($src);

		foreach(new RII(new RDI($src), RII::LEAVES_ONLY) as $file)
		{
			$path = $dest . mb_substr($file->getPath(), $len);

			assert('pixelpost\core\Log::debug("(Fs) create folder %s", $path)');

			if (!is_dir($path) && false === mkdir($path, 0775, true))
			{
				assert('pixelpost\core\Log::error("(Fs) Fail to create folder %s", $path)');

				Error::create(24, $path);
			}

			$fsrc  = $file->getPathName();
			$fdest = $path . '/' . $file->getBaseName();

			assert('pixelpost\core\Log::debug("(Fs) Copy %s to %s", $fsrc, $fdest)');

			if (false === copy($fsrc, $fdest))
			{
				assert('pixelpost\core\Log::warning("(Fs) Fail to copy %s to %s", $fsrc, $fdest)');
			}
		}

		return true;
	}

	/**
	 * Delete all content under the $dir folder and $dir itself.
	 *
	 * @param  string $dir The folder to delete.
	 * @return bool
	 */
	public static function delete($dir)
	{
		if (!is_dir($dir)) return true;

		assert('pixelpost\core\Log::info("(Fs) delete folder %s", $dir)');

		foreach(new RII(new RDI($dir), RII::CHILD_FIRST) as $file)
		{
			$isDir = $file->isDir();
			$file  = $file->getPathName();

			if ($isDir)
			{
				assert('pixelpost\core\Log::debug("(Fs) delete folder %s", $file)');

				if (!rmdir($file))
				{
					assert('pixelpost\core\Log::warning("(Fs) Fail to delete folder %s", $file)');
				}
			}
			else
			{
				assert('pixelpost\core\Log::debug("(Fs) delete file %s", $file)');

				if (!unlink($file))
				{
					assert('pixelpost\core\Log::warning("(Fs) Fail to delete file %s", $file)');
				}
			}
		}

		$result = rmdir($dir);

		if (!$result)
		{
			assert('pixelpost\core\Log::warning("(Fs) Fail to delete folder %s", $dir)');
		}

		return $result;
	}
}