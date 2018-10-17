<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunner;

/**
 * Class JobManager.
 */
class JobManager
{
    const BASE_DIR_NAME = 'bnza_jr';
    const JOBS_DIR_NAME = 'jobs';

    /**
     * @var string
     */
    private $baseWorkDir = '';

    /**
     * @var string
     */
    private $jobsWorkDir = '';

    /**
     * Creates the given directory.
     *
     * @param string $dir
     * @param int    $mode
     * @param bool   $recursive
     */
    private function createDirectory(string $dir, int $mode = 0770, bool $recursive = false)
    {
        if (!file_exists($dir)) {
            if (!@mkdir($dir, $mode, $recursive)) {
                throw new \RuntimeException("Unable to create $dir directory");
            }
        }
    }

    /**
     * Gets the base Jobs Work Directory.
     * If the create flag is set true (as default) and the folder does not exist creates it.
     *
     * @param bool $create
     *
     * @return string
     */
    public function getBaseWorkDir(bool $create = true): string
    {
        if (!$this->baseWorkDir) {
            $this->baseWorkDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.self::BASE_DIR_NAME;
        }

        if ($create) {
            $this->createDirectory($this->baseWorkDir);
        }

        return $this->baseWorkDir;
    }

    /**
     * Gets the base Jobs Directory (where running jobs data are stored).
     * If the create flag is set true (as default) and the folder does not exist creates it.
     *
     * @param bool $create
     *
     * @return string
     */
    public function getJobsWorkDir(bool $create = true)
    {
        if (!$this->jobsWorkDir) {
            $this->jobsWorkDir = $this->getBaseWorkDir($create).DIRECTORY_SEPARATOR.self::JOBS_DIR_NAME;
        }

        if ($create) {
            $this->createDirectory($this->jobsWorkDir);
        }

        return $this->jobsWorkDir;
    }
}
