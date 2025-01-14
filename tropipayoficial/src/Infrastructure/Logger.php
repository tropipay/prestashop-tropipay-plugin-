<?php

require_once(dirname(__DIR__).'/TropipaySDK/ILogger.php');

class Logger implements Ilogger
{
    private string $id;

    public function __construct() {
        $this->id = IdGenerator::generate();
    }
    
    private function log(string $level, string $message): void
    {
        $logfilename = dirname(__DIR__).'/../Logs/log.log';
		file_put_contents("[" .date('M d Y G:i:s') ."] " .$level.": ". $this->id . ' -- ' . $texto . "\r\n", is_file($logfilename) ? FILE_APPEND : 0);
    }

    public function info(string $message): void
    {
        $this->log("INFO", $message);
    }
    public function error(string $message): void
    {
        $this->log("ERROR", $message);
    }
}


class IdGenerator
{
    public static function generate() {
        $vars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stringLength = strlen($vars);
        $result = '';
        for ($i = 0; $i < 20; $i++) {
            $result .= $vars[rand(0, $stringLength - 1)];
        }
        return $result;
    }
}