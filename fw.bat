::<?php echo "\r   \r"; \ob_start() ?>
@echo off
if not defined FY__SHELL (
    SET FY__SHELL=1
    cmd /k
    exit /b 0
)

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: DEFAULT SETTINGS
SET FX__PHP_EXEC_STD_PATH=C:/xampp/current/php/php.exe
SET FX__PHP_EXEC_XDBG_PATH=C:/xampp/current/php__xdbg/php.exe
SET FX__ENV_FILE="%USERPROFILE%\___set_cli_env_vars__.bat"
SET FX__DEBUG=1

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: SESSION
goto :L__START
:getGUID
for /f "tokens=2 delims==" %%a in ('wmic os get localdatetime /value') do set dt=%%a
set "%~1=%dt:~0,8%-%dt:~8,4%-%dt:~12,2%%dt:~15,3%-%dt:~6,2%%dt:~8,2%%dt:~10,2%-%dt:~15,3%%dt:~12,2%%dt:~6,2%"
exit /b
:L__START

if not defined FX__SESSION (
    call :getGUID FX__SESSION
)

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: FW__PHP_EXEC_PATH
if "%FX__DEBUG%" NEQ "" (
    SET FW__PHP_EXEC_PATH=%FX__PHP_EXEC_XDBG_PATH%
) else (
    SET FW__PHP_EXEC_PATH=%FX__PHP_EXEC_STD_PATH%
)

if NOT exist %FW__PHP_EXEC_PATH% (
    SET FW__PHP_EXEC_PATH=php.exe
)

::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: FW__ENV_FILE
if defined FW__ENV_FILE (
    set "FW__ENV_FILE=%FX__ENV_FILE%"
)

SET CMD_FILE_A=%~dp0__\%1\.bat
SET CMD_FILE_B=%~dp0__\%1.bat
SET CMD_FILE_C=%~dp0__\%1-@\.php
SET CMD_FILE_D=%~dp0__\%1-@.php

if exist "%CMD_FILE_A%" (
    call %CMD_FILE_B% %*
) else if exist "%CMD_FILE_B%" (
    call %CMD_FILE_B% %*
) else if exist "%CMD_FILE_C%" (
    %FW__PHP_EXEC_PATH% "%~f0" %*
) else if exist "%CMD_FILE_D%" (
    %FW__PHP_EXEC_PATH% "%~f0" %*
) else if "%~1" NEQ "" (
    echo [91mCommand Not Found: %~1 [0m
) else (
    echo [91mCommand Not Found: / [0m
)

if %ERRORLEVEL%==2 (
    pause
) else if exist "%FW__ENV_FILE%" (
    call "%FW__ENV_FILE%"
    del "%FW__ENV_FILE%"
)

exit /b 0

<?php ob_end_clean(); 

try {
    \defined('_\MSTART') OR \define('_\MSTART', \microtime(true));
    global $_;
    (isset($_) && \is_array($_)) OR $_ = [];
    \define('_\LIB_DIR', \str_replace('\\','/',__DIR__));
    \set_include_path(\_\LIB_DIR.PATH_SEPARATOR.\get_include_path());
    \spl_autoload_extensions('-#.php,/-#.php');
    \spl_autoload_register();        
    \set_error_handler(function($severity, $message, $file, $line){
        throw new \ErrorException(
            $message, 
            0,
            $severity, 
            $file, 
            $line
        );
    });
    
    \class_exists(\_\plugin\installer\github::class) AND \_\plugin\installer\github::_();    
    
    $_REQUEST = (function(){
        $offset = 1;
        $argc = $_SERVER['argc'] ?? "";
        $argv = $_SERVER['argv'] ?? "";
        $parsed = [];
        $key = null;
        $args = \array_slice($argv, $offset);
        foreach ($args as $arg) {
            if (\str_starts_with($arg, '-') && !\is_numeric($arg)) {
                if ($key !== null) {
                    $parsed[$key] = true;
                }
                $key = $arg;
            } else {
                if ($key !== null) {
                    $parsed[$key] = $arg;
                    $key = null;
                } else {
                    $parsed[] = $arg;
                }
            }
        }
        if ($key !== null) {
            $parsed[$key] = true;
        }

        $parsed['--debug'] = match($dbg = $parsed['--debug'] ?? 'off'){
            'on' => 5,
            'off' => 0,
            default => $dbg,
        };
        
        return $parsed;
    })();
    
    \is_file($f = __DIR__.'/.functions.php') AND include_once $f;
    $intfc = 'cli';
    $path = \trim('__/'.($_REQUEST[0] ?? ''),'/');
    if(
        ($file = \stream_resolve_include_path("{$path}/-@{$intfc}.php"))
        || ($file = \stream_resolve_include_path("{$path}-@{$intfc}.php"))
         || ($file = \stream_resolve_include_path("{$path}/-@.php"))
        || ($file = \stream_resolve_include_path("{$path}-@.php"))
    ){
        include $file;
    } else {
        throw new \Exception("Not Found: ".$_REQUEST[0] ?? "/");
    }
    
} catch (\Throwable $ex) {
    echo "\033[91m\n"
        .$ex::class.": {$ex->getMessage()}\n"
        ."File: {$ex->getFile()}\n"
        ."Line: {$ex->getLine()}\n"
        ."\033[31m{$ex}\033[0m\n"
    ;
}    

