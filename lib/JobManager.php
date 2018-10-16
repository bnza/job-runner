<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunner;

/**
 * Class JobManager
 * @package Bnza\JobRunner
 */
class JobManager
{
    const DIR_NAME = 'bnza_jr';

    /**
     * @var string
     */
    private $baseWorkDir = '';

    /**
     * Creates the given directory.
     *
     * @param string $dir
     * @param int    $mode
     * @param bool   $recursive
     */
    private function createDirectory(string $dir, int $mode = 0660, bool $recursive = false)
    {
        if (!@mkdir($dir, $mode, $recursive)) {
            throw new \RuntimeException("Unable to create $dir directory");
        }
    }

    /**
     * Gets the base Jobs Work Directory. If the create flag is set true (as default) creates it if it not exists.
     *
     * @param bool $create
     *
     * @return string
     */
    public function getBaseWorkDir(bool $create = true): string
    {
        if (!$this->baseWorkDir) {
            $this->baseWorkDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.self::DIR_NAME;
        }

        if ($create && !file_exists($this->baseWorkDir)) {
            $this->createDirectory($this->baseWorkDir);
        }

        return $this->baseWorkDir;
    }
}
