<?php
namespace App\Libs\Exception;


class WhatsappException extends \Exception
{
    public function __construct($message = "", $code = 400, \Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
