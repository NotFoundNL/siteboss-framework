<?php

namespace NotFound\Framework\Exceptions\ClamAV;

use Exception;

class ClamAVException extends Exception
{
    public static function noSocketFound(): ClamAVException
    {
        return new self(
            sprintf('Socket type was not found in config/clamav.php')
        );
    }

    public static function unreadableFile($filePath): ClamAVException
    {
        return new self(
            sprintf('cannot read file: '.$filePath)
        );
    }

    public static function unmoveableFile($filePath): ClamAVException
    {
        return new self(
            sprintf('cannot move file: '.$filePath)
        );
    }
}
