<?php

require __DIR__ . '/../vendor/autoload.php';

use Igorgoroshit\ProcLock\Lock;


$userId   = intval($argv[1]);
$resource = $argv[2];
$seconds  = intval($argv[3]);

$user = new \stdClass;
$user->id = $userId;

//get lock for some resource: procude-154
$lock = new Lock("{$resource}-{$user->id}");
echo "Got lock for {$lock->getResourceName()}\n";
$lock->lock();
  echo "Sleep for $seconds seconds…\n";
  sleep($seconds);
$lock->unlock();
$wait = $lock->getWaitingTime();
echo "Wait for {$wait}ms\n";
