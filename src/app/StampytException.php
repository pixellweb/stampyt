<?php


namespace Citadelle\Stampyt\app;


class StampytException extends \Exception
{
    /**
     * StampytException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        \Log::channel('stampyt')->alert($message . ($previous ? ' => ' . $previous->getMessage() : ''));
    }
}
