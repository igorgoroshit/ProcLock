<?php

namespace Igorgoroshit\ProcLock;


class Lock
{
    protected $delay        = 200000;
    protected $resource     = '';
    protected $lock         = null;
    protected $waitingTime  = 0;
    protected $filepath     = null;

    public function __construct ($resource, $delay = 200000)
    {
        if(empty($resource)) {
          throw new \BadMethodCallException('you must provide non empty resource name');
        }

        if($delay < 200) {
          throw new \BadMethodCallException('you must provide delay > 200 to to keep cpu from bieng overloaded');  
        }
        
        $this->delay     = $delay;
        $this->resource  = $resource;
        $this->filepath  = $this->filepath();
    }

    //compute tmp filename for lock
    protected function filepath() {
        $file   = "proclock__{$this->resource}__proclock.lock";
        $tmpDir = sys_get_temp_dir(); 
        return $tmpDir . DIRECTORY_SEPARATOR . $file;
    }

    public function getPathToLockFile() {
        return $this->filepath;
    }

    //Get lock for resource
    public function lock()
    {
        if ($this->lock !== null)  {
            throw new \BadMethodCallException('you cant acquire lock on non relased resource');
        }

        $this->waitingTime = 0;

        $this->lock = fopen($this->filepath, 'c+');
      
        $sTime = microtime(true);

        //Exclusive Non Blocking Lock
        while (!flock($this->lock, LOCK_EX | LOCK_NB)) {
          usleep($this->delay);
        }

        $eTime = microtime(true);

        $this->waitingTime = $eTime - $sTime;

        return $this->lock;
    }

    //Unlock resource
    public function unlock ()
    {
        if ($this->lock === null) {
            throw new \BadMethodCallException('You cant relase non accoured lock!');
        }

        flock($this->lock, LOCK_UN);
        fclose($this->lock);
        unlink($this->filepath);
        $this->lock = null;
    }


    //Get total time in secondes waiting for lock
    public function getWaitingTime ()
    {
        return round($this->waitingTime * 1000);
    }


    //Get full resource name (procude-124)
    public function getResourceName() {
        return $this->resource;
    }


    //Relaese lock in case user forgot to call Lock::unlock manualy
    //Please do not count on this method in production code always release 
    //locks manualy with Lock::unlock method
    public function __destruct() {
      if($this->lock !== null) {
        $this->unlock();
      } 
    }
}
