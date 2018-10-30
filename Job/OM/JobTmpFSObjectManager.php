<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobRunnerBundle\Job\OM;

use Bnza\JobRunnerBundle\Exception\JobRunnerJobEntityNotFoundException;
use Bnza\JobRunnerBundle\Exception\JobRunnerTaskEntityNotFoundException;
use Bnza\JobRunnerBundle\Job\Entity\JobRunnerEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\JobEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\TaskEntityInterface;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\JobEntity;
use Bnza\JobRunnerBundle\Job\Entity\TmpFS\TaskEntity;
use Doctrine\Common\Inflector\Inflector;

class JobTmpFSObjectManager implements JobObjectManagerInterface
{
    const BASE_DIR_NAME = 'bnza_jr';
    const JOBS_DIR_NAME = 'jobs';
    const TASKS_DIR_NAME = 'tasks';

    /**
     * The tmpfs directory (must exist) e.g. /tmp /var/tmp.
     *
     * @var string
     */
    private $tmpDir = '';

    /**
     * The base job runner directory.
     *
     * @var string
     */
    private $baseWorkDir = '';

    /**
     * The env related job runner directory.
     *
     * @var string
     */
    private $envWorkDir = 'dev';

    /**
     * The job's job runner directory.
     *
     * @var string
     */
    private $jobsWorkDir = '';

    /**
     * @var Inflector
     */
    private $inflector;

    public function __construct(string $tmpDir = '')
    {
        $this->envWorkDir = getenv('APP_ENV');

        $this->tmpDir = $tmpDir ?: sys_get_temp_dir();
    }

    public function getPaths(string $key = '')
    {
        static $paths;

        if (!$paths) {
            $paths = [
                'tmp' => $this->tmpDir,
                'base' => $this->getBaseWorkDir(false),
                'env' => $this->getEnvWorkDir(false),
                'jobs' => $this->getJobsWorkDir(false),
            ];
        }

        if ($key) {
            if (array_key_exists($key, $paths)) {
                return $paths[$key];
            } else {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid path key', $key));
            }
        } else {
            return $paths;
        }
    }

    public function getJobPropertyList()
    {
        return [
            'status',
            'class',
            'name',
            'current_task_num'
        ];
    }

    public function getTaskPropertyList()
    {
        return [
            'class',
            'current_step_num'
        ];
    }


    protected function getInflector(): Inflector
    {
        if (!$this->inflector) {
            $this->inflector = new Inflector();
        }

        return $this->inflector;
    }

    /**
     * Creates the given directory.
     *
     * @param string $dir
     * @param int    $mode
     * @param bool   $recursive
     */
    protected function createDirectory(string $dir, int $mode = 0770, bool $recursive = false)
    {
        if (0 !== strpos($dir, $this->tmpDir)) {
            throw new \RuntimeException(sprintf('Cannot create directory outside root tmp dir [%s]', $this->tmpDir));
        }
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
    protected function getBaseWorkDir(bool $create = true): string
    {
        if (!$this->baseWorkDir) {
            $this->baseWorkDir = $this->tmpDir.DIRECTORY_SEPARATOR.self::BASE_DIR_NAME;
        }

        if ($create) {
            $this->createDirectory($this->baseWorkDir);
        }

        return $this->baseWorkDir;
    }

    /**
     * Gets the base Env Directory depending on the app environment value.
     * If the create flag is set true (as default) and the folder does not exist creates it.
     *
     * @param bool $create
     *
     * @return string
     */
    protected function getEnvWorkDir(bool $create = true)
    {
        if (0 !== strpos($this->envWorkDir, DIRECTORY_SEPARATOR)) {
            $this->envWorkDir = $this->getBaseWorkDir($create).DIRECTORY_SEPARATOR.$this->envWorkDir;
        }

        if ($create) {
            $this->createDirectory($this->envWorkDir, 0770, true);
        }

        return $this->envWorkDir;
    }

    /**
     * Gets the base Jobs Directory (where running jobs data are stored).
     * If the create flag is set true (as default) and the folder does not exist creates it.
     *
     * @param bool $create
     *
     * @return string
     */
    protected function getJobsWorkDir(bool $create = true)
    {
        if (!$this->jobsWorkDir) {
            $this->jobsWorkDir = $this->getEnvWorkDir($create).DIRECTORY_SEPARATOR.self::JOBS_DIR_NAME;
        }

        if ($create) {
            $this->createDirectory($this->jobsWorkDir);
        }

        return $this->jobsWorkDir;
    }

    protected function getJobWorkDir(string $id)
    {
        if (ctype_xdigit($id) && strlen($id) == 40) {
            return $this->getJobsWorkDir(false).DIRECTORY_SEPARATOR.$id;
        } else {
            throw new \InvalidArgumentException(sprintf("\"%s\" is not a valid sha1 hash", $id));
        }
    }

    protected function getTasksWorkDir(string $jobId)
    {
        return $this->getJobWorkDir($jobId).DIRECTORY_SEPARATOR.self::TASKS_DIR_NAME;
    }

    protected function getTasksWorkDirNumbers(string $jobId): array
    {
        $numbers = [];
        $tasksDir = $this->getTasksWorkDir($jobId);

        if (file_exists($tasksDir)) {
            $dirs = new \DirectoryIterator($tasksDir);
            foreach ($dirs as $file) {
                if(!$file->isDot()) {
                    if ($file->isDir() && is_numeric($file->getFilename())) {
                        $numbers[] = (int) $file->getFilename();
                    }
                }
            }
            sort($numbers);
        }
        return $numbers;
    }

    protected function getTaskWorkDir(string $jobId, int $taskNum)
    {
        return $this->getTasksWorkDir($jobId).DIRECTORY_SEPARATOR.$taskNum;
    }

    /**
     * For security reason read/write file operations are limited to env work directory
     * @param $path
     * @return mixed
     */
    protected function checkFilePath($path)
    {
        $envDir = $this->getEnvWorkDir();
        if (strpos($path, $envDir) !== 0) {
            throw new \InvalidArgumentException(sprintf("\"%s\" directory is outside the job runner env work dir \"%s\"", $path, $envDir));
        }
        return $path;
    }

    /**
     * @param string $filePath Relative path from $jobsWorkDir
     * @param $value
     *
     * @throws \RuntimeException
     */
    private function writeFile(string $filePath, $value)
    {
        $done = false;
        //$path = $this->getJobsWorkDir().DIRECTORY_SEPARATOR.$filePath;
        $path = $this->checkFilePath($filePath);
        if (!file_exists($dir = dirname($path))) {
            $this->createDirectory($dir, 0770, true);
        }
        $fp = fopen($path, 'w');
        while (false === $done) {
            if (flock($fp, LOCK_EX)) {
                $done = fwrite($fp, $value);
                if ($done === false) {
                    $error = error_get_last();
                    throw new \RuntimeException($error['message']);
                }
                fclose($fp);
            }
        }
    }

    /**
     * @param string $filePath
     * @return string
     * @throws \RuntimeException
     */
    private function readFile(string $filePath)
    {
        $value = false;
        //$path = $this->getJobsWorkDir().DIRECTORY_SEPARATOR.$filePath;
        $path = $this->checkFilePath($filePath);
        $fp = fopen($path, "r");
        if (!$fp) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        while ($value === false) {
            if (flock($fp, LOCK_SH)) {
                $value = filesize($path) ? fread($fp, filesize($path)) : '';
                fclose($fp);
            }
        }

        return $value;
    }

    protected function getPropertyFilePath(string $property, string $jobId, int $taskNum = -1)
    {
        $fileName = strtolower($property);
        $relPath = $taskNum == -1 ? $fileName : self::TASKS_DIR_NAME.DIRECTORY_SEPARATOR.$taskNum.DIRECTORY_SEPARATOR.$fileName;
        return $this->getJobWorkDir($jobId).DIRECTORY_SEPARATOR.$relPath;
    }

    /**
     * AbstractJob specific persist action. When the $property parameter is set it will be persisted.
     *
     * @param JobEntityInterface $entity
     * @param string             $property
     */
    protected function persistJob(JobEntityInterface $entity, string $property = '')
    {
        /**
         * @var array Valid job properties
         */
        $props = $this->getJobPropertyList();

        if (!$property) {
            // Persist all properties
            foreach ($props as $prop) {
                $this->persistJob($entity, $prop);
            }
            // Persist all tasks
            foreach ($entity->getTasks() as $task) {
                $this->persistTask($task);
            }
        } else {
            // Persist the given property
            $property = strtolower($property);
            if (!in_array($property, $props)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid job property)', $property));
            }
            $filePath = $this->getPropertyFilePath($property, $entity->getId());
            // e.g. getStatus
            $method = 'get'.$this->getInflector()->classify($property);
            $this->writeFile($filePath, $entity->$method());
        }
    }

    protected function refreshJob(JobEntityInterface $entity, string $property = '', bool $refreshTasks = true)
    {
        /**
         * @var array Valid job properties
         */
        $props = $this->getJobPropertyList();

        if (!$property) {
            // Refresh all properties
            foreach ($props as $prop) {
                $this->refreshJob($entity, $prop);
            }
            if ($refreshTasks) {
                $this->refreshTasks($entity);
            }
        } else {
            // Refresh the given property
            $property = strtolower($property);
            if (!in_array($property, $props)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid job property)', $property));
            }
            $filePath = $this->getPropertyFilePath($property, $entity->getId());
            // e.g. getStatus
            $method = 'set'.$this->getInflector()->classify($property);
            $entity->$method($this->readFile($filePath));
        }
    }

    protected function refreshTasks(JobEntityInterface $entity)
    {
        $entity->clearTasks();
        // Refresh all tasks
        foreach ($this->getTasksWorkDirNumbers($entity->getId()) as $num) {
            $class = $this->getTaskEntityClass();
            $task = new $class($entity, $num);
            $this->refreshTask($task);
        }
    }

    protected function refreshTask(TaskEntityInterface $entity, string $property = '', bool $refreshJob = false)
    {
        /**
         * @var array Valid task properties
         */
        $props = $this->getTaskPropertyList();

        if (!$property) {
            // Persist all properties
            foreach ($props as $prop) {
                $this->refreshTask($entity, $prop);
            }
            if ($refreshJob) {
                $this->refreshJob($entity->getJob(), '', false);
            }
        } else {
            // Persist the given property
            $property = strtolower($property);
            if (!in_array($property, $props)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid task property)', $property));
            }
            $filePath = $this->getPropertyFilePath($property, $entity->getJob()->getId(), $entity->getNum());

            // e.g. setStatus
            $method = 'set'.$this->getInflector()->classify($property);
            $entity->$method($this->readFile($filePath));
        }
    }

    protected function persistTask(TaskEntityInterface $entity, string $property = '')
    {
        /**
         * @var array Valid job properties
         */
        $props = $this->getTaskPropertyList();

        if (!$property) {
            // Persist all properties
            foreach ($props as $prop) {
                $this->persistTask($entity, $prop);
            }
        } else {
            // Persist the given property
            $property = strtolower($property);
            if (!in_array($property, $props)) {
                throw new \RuntimeException(sprintf('"%s" is not a valid task property)', $property));
            }
            $filePath = $this->getPropertyFilePath($property, $entity->getJob()->getId(), $entity->getNum());
            // e.g. getStatus
            $method = 'get'.$this->getInflector()->classify($property);
            $this->writeFile($filePath, $entity->$method());
        }
    }

    /**
     * @param string $id
     * @return JobEntityInterface
     * @throws JobRunnerJobEntityNotFoundException
     */
    public function findJob(string $id): JobEntityInterface
    {
        $jobDir = $this->getJobWorkDir($id);
        if (file_exists($jobDir)) {
            $class = $this->getJobEntityClass();
            $entity = new $class($id);
            $this->refreshJob($entity);
            return $entity;
        } else {
            throw new JobRunnerJobEntityNotFoundException($id);
        }
    }

    /**
     * @param string $jobId
     * @param int $num
     * @return TaskEntityInterface
     * @throws JobRunnerTaskEntityNotFoundException
     */
    public function findTask(string $jobId, int $num): TaskEntityInterface
    {
        $taskDir = $this->getTaskWorkDir($jobId, $num);
        if (file_exists($taskDir)) {
            $class = $this->getTaskEntityClass();
            $entity = new $class($jobId, $num);
            $this->refreshTask($entity);
            return $entity;
        } else {
            throw new JobRunnerTaskEntityNotFoundException($jobId.'.'.$num);
        }
    }


    /**
     * Persist the given entity to a file.
     *
     * @param JobRunnerEntityInterface $entity
     * @param string $property
     */
    public function persist(JobRunnerEntityInterface $entity, string $property = '')
    {
        if ($entity instanceof JobEntityInterface) {
            $this->persistJob($entity, $property);
        } elseif ($entity instanceof TaskEntityInterface) {
            $this->persistTask($entity, $property);
        } else {
            throw new \InvalidArgumentException('Invalid entity');
        }
    }

    /**
     * @param JobRunnerEntityInterface $entity
     * @param string $property
     */
    public function refresh(JobRunnerEntityInterface $entity, string $property = '')
    {
        if ($entity instanceof JobEntityInterface) {
            $this->refreshJob($entity, $property);
        } elseif ($entity instanceof TaskEntityInterface) {
            $this->refreshTask($entity, $property);
        } else {
            throw new \InvalidArgumentException('Invalid entity');
        }
    }

    public function getJobEntityClass(): string
    {
        return JobEntity::class;
    }

    public function getTaskEntityClass(): string
    {
        return TaskEntity::class;
    }
}
