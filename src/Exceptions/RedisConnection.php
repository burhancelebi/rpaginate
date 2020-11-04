<?php

namespace RPaginate\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Predis\Connection\ConnectionException;
use Exception;

class RedisConnection extends Exception
{

}