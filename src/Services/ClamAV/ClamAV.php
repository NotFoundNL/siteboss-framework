<?php

namespace NotFound\Framework\Services\ClamAV;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Exceptions\ClamAV\ClamAVException;

//TODO: Convert to Laravel facade
class ClamAV
{
    /*
     * @param string $filePath the file that is being uploaded
     */
    public static function uploadIsClean(string $filePath)
    {
        if (config('clamav.socket_type') === 'none') {
            return true;
        }

        if (! is_readable($filePath)) {
            throw ClamAVException::unreadableFile($filePath);
        }

        $fileStream = fopen($filePath, 'rb');

        $client = self::GetClient();

        $result = $client->scanResourceStream($fileStream);

        return $result->isOk();
    }

    /*
     * @param string $filePath the file that needs to be moved
     * @param string $newRelativeFilePath Relative file path. File path always starts with storage_path('app')
     */
    public static function moveFile(string $filePath, string $relativeFilePath, string $storageDisk = 'site'): bool
    {
        if (! is_readable($filePath)) {
            throw ClamAVException::unreadableFile($filePath);
        }

        $newFilePath = Storage::disk($storageDisk)->path($relativeFilePath);
        if (! move_uploaded_file($filePath, $newFilePath)) {
            throw ClamAVException::unmoveableFile($filePath);
        }

        if (config('clamav.socket_type') === 'none') {
            chmod($newFilePath, 0755);

            return true;
        }
        $client = self::GetClient();
        $result = $client->scanFile($newFilePath);

        if (! $result->isOk()) {
            self::virusUploaded($newFilePath, $relativeFilePath, $storageDisk);

            return false;
        }

        chmod($newFilePath, 0755);

        return true;
    }

    private static function virusUploaded($virusLocation, $relativeFilePath, $storageDisk)
    {
        $newFilePath = Storage::disk($storageDisk)->path(config('clamav.quarantine_folder').$relativeFilePath);
        Log::warning(
            sprintf('Virus has been uploaded: "%s". Trying to move to: "%s"', $virusLocation, $newFilePath)
        );

        if (! move_uploaded_file($virusLocation, $newFilePath)) {
            Log::error(sprintf('Couldn\'t move virus: "%s". Please remove manually', $virusLocation));
            // TODO: Maybe send a mail?
        }
    }

    private static function GetClient(): \Xenolope\Quahog\Client
    {
        $socketString = self::getClamAVSocket();
        $socket = (new \Socket\Raw\Factory())->createClient($socketString);

        return new \Xenolope\Quahog\Client($socket);
    }

    private static function getClamAVSocket(): string
    {
        $preferredSocket = config('clamav.socket_type');

        if ($preferredSocket === 'unix') {
            $unixSocket = config('clamav.unix_socket');

            return 'unix://'.$unixSocket;
        } elseif ($preferredSocket === 'tcp') {
            $tcpSocket = config('clamav.tcp_socket');

            return $tcpSocket;
        }

        throw ClamAVException::noSocketFound();
    }
}
