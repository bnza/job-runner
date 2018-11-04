[![Build Status](https://travis-ci.org/bnza/job-runner.svg?branch=master)](https://travis-ci.org/bnza/job-runner)

# JobRunner
JobRunner is a Symfony bundle intended for manage, execute, display and log jobs. 


## Installation

Repository currently marked as dev. Set up your ```composer.json``` consequently

```json
"minimum-stability": "dev",
"prefer-stable": true,
 "repositories": [
    {
                "type": "vcs",
                "url": "https://github.com/bnza/job-runner.git"
    }
],
```

## Usage

Job can be created extending ```\Bnza\Job\AbstractJob``` and particularly 
implementing ```setName``` and ```getTasks``` method.

```getTasks``` returns an ```Iterable``` object which contains the 
job's Task list.

```php
<?php
// in src\Job\Task\DummyTask.php

use \Bnza\JobRunnerBundle\Job\AbstractTask;

class DummyTask extends AbstractTask
{
    public function getName(): string 
    {
        return "Dummy Task";
    }
    
    public function getCallable(): callable
    {
        return function($a, $b) {
          return $a + $b;  
        };
    }
    
    public function getData(): iterable
    {
        return [10, 25];
    }
}
```

```php
<?php
// in src\Job\Task\DummyJob.php

use \Bnza\JobRunnerBundle\Job\AbstractJob;
use \Bnza\JobRunnerBundle\Job\AbstractTask;

class DummyJob extends AbstractJob
{
    public function getName(): string 
    {
        return "Dummy Job";
    }
    
    public function configure(): void
    {        
    }
    
    public function getTasks(): iterable
    {
        return [
            [AbstractTask::class]
        ];
    }
}
```

```php
<?php
// elsewhere in your logic
use \YourNamespace\DummyJob;
use \Bnza\JobRunnerBundle\Job\OM\JobTmpFSObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

$om = new JobTmpFSObjectManager();
$dispatcher = new EventDispatcher();
$pb = new ParameterBag();
$job = new DummyJob($dispatcher, $om, $pb);
$job->run();
```    
