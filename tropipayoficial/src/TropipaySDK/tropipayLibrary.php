<?php

 
///////////////////// FUNCIONES DE VALIDACION



function generateIdLog() {
    $vars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $stringLength = strlen($vars);
    $result = '';
    for ($i = 0; $i < 20; $i++) {
        $result .= $vars[rand(0, $stringLength - 1)];
    }
    return $result;
}


///////////////////// FUNCIONES DE LOG
function escribirLog($texto,$activo) {
	if($activo=="si"){
		// Log
		$logfilename = dirname(__DIR__).'/Logs/log.log';
		file_put_contents($logfilename, date('M d Y G:i:s') . ' -- ' . $texto . "\r\n", is_file($logfilename)?FILE_APPEND:0);
	}
}