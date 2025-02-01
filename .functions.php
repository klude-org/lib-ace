<?php
namespace {
    \define('_\console\WIDTH',(function(){
        $output = [];
        exec('mode con', $output);
        foreach ($output as $line) {
            if (preg_match('/Columns:\s*(\d+)/', $line, $matches)) {
                return (int) $matches[1];
            }
        }
        return null; // Return null if unable to determine width
    })());    
}
namespace _ { if(!\function_exists(args::class)){ function args(array $defaults){
    $return = \array_replace($_REQUEST, $defaults);
    return $return;
}}} 
namespace _ { if(!\function_exists(target_path::class)){ function target_path(){
    if($value = $_REQUEST[1] ?? null){
        $cwd = \_\p(\realpath($value));
    } else {
        $cwd = \_\p(\getcwd());
    }
    0 AND \_\console\prt("Target: {#4097}{$cwd}\n");
    return $cwd;
}}} 
namespace _ { if(!\function_exists(target_dir::class)){ function target_dir(){
    if (!is_dir($cwd = \_\target_path())) {
        die("Error: '$cwd' is not a valid directory.\n");
    }
    return $cwd;
}}} 
namespace _ { if(!\function_exists(target_file::class)){ function target_file(){
    if (!is_dir($cwd = \_\target_path())) {
        die("Error: '$cwd' is not a valid directory.\n");
    }
    return $cwd;
}}} 
namespace _ { if(!\function_exists(jdump::class)){ function jdump($o){
    echo \json_encode($o, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
}}} 
namespace _ { if(!\function_exists(ldump::class)){ function ldump(iterable $x, callable $each__fn){
    foreach($x as $k => $v){
        \_\console\prt(($each__fn)($v,$k),PHP_EOL);
    }
}}} 
namespace _ { if(!\function_exists(p::class)){ function p($x){
    return \str_replace('\\','/',$x);
}}} 
namespace _\console { if(!\function_exists(style__a::class)){ function style__a($input){
    if(\str_contains($input, '{#')){
        // Define the regex patterns
        $colorPattern = '/\{#(\d{1,4})\}/';
        
        // Step 1: Color code transformation
        $input = preg_replace_callback($colorPattern, function ($matches) {
            $code = $matches[1];

            // Single digit codes (like 0)
            if (strlen($code) === 1) {
                return "\033[{$code}m";
            }

            // Two-digit codes (like 91)
            if (strlen($code) === 2) {
                return "\033[{$code}m";
            }

            // Four-digit codes (like 4097)
            $bg = substr($code, 0, 2); // Background color
            $fg = substr($code, -2);  // Foreground color
            return "\033[{$bg};{$fg}m";
        }, $input);
        
    }
    return $input;    
}}}
namespace _\console { if(!\function_exists(style::class)){ function style($input){
    return style__a($input)."\033[0m";    
}}} 
namespace _\str { if(!\function_exists(substitute::class)){ function substitute(string $input, array $substitutions = []){
    $variablePattern = '/\{\@(\w+)\}/';
    $input = preg_replace_callback($variablePattern, function ($matches) use ($substitutions) {
        $key = $matches[1];
        return $substitutions[$key] ?? "@{{$key}}";
    }, $input);
    return $input;
}}} 
namespace _\console { if(!\function_exists(get_width::class)){ function get_width(){
    $output = [];
    exec('mode con', $output);
    foreach ($output as $line) {
        if (preg_match('/Columns:\s*(\d+)/', $line, $matches)) {
            return (int) $matches[1];
        }
    }
    return null; // Return null if unable to determine width
}}} 
namespace _\console { if(!\function_exists(put::class)){ function put($str){
    $str = \_\console\style($str);
    if($str && $str[0] === "\r"){
        $strx = \str_pad(
            (\strlen($str) > (\_\console\WIDTH - 4)) 
                ? '... ' . \substr($str, -(\_\console\WIDTH - 4)) 
                : $str
            , 
            \_\console\WIDTH, 
            ' ', 
            STR_PAD_RIGHT
        );
    } else {
        $strx = $str;
    }
    echo $strx;
}}}
namespace _\console { if(!\function_exists(prt::class)){ function prt($str){
    \_\console\put($str); 
    echo "\033[0m";
}}}
namespace _\console { if(!\function_exists(prtl::class)){ function prtl($str){
    \_\console\put($str); 
    echo "\033[0m\n";
}}}

namespace _\console { if(!\function_exists(auto_format::class)){ function auto_format(\closure $do__fn){
    static $reentry = null;
    if(!$reentry){
        $reentry = true;
        try {
            ob_start(function ($buffer) { return \_\console\style($buffer); });
            // Enable automatic flushing after each echo
            ob_implicit_flush();
            ($do__fn)();
        } finally {
            $reentry = false;
            ob_implicit_flush(0);
            ob_end_clean();
            //$d = ob_get_contents();
            //echo \_\console\style($d);
            //echo "\033[0m\n";
        }
    }
}}}
namespace _ { if(!\function_exists(flex::class)){ function flex(\closure $do__fn){
    return ($do__fn->bindTo(new class() extends \stdClass {
        public readonly object $fn;
        private readonly string $trace;
        public function __construct(){
            $this->fn = new class($this) extends \stdClass {
                private $parent;
                public function __construct($o){ $this->parent = $o; }
                public function __set(string $n, \closure $v){ 
                    $this->$n = ($v)->bindTo($this->parent); 
                }
            };
            if($t = $_REQUEST['tee'] ?? null){
                if(\is_string($t) && !empty($t)){
                    $this->trace = \_\CWD.DIRECTORY_SEPARATOR.$t;
                } else {
                    $this->trace = \_\CWD.DIRECTORY_SEPARATOR.'.local-tee.txt';
                }
            } else {
                $this->trace = '';
            }
        }
        public function __set($n, $v){
            switch($n){
                
                case 'prtl':{
                    $v = (string) ($v).PHP_EOL;
                } //----FALLTHRU----
                case 'prt':{
                    if($this->trace){
                        echo ($l = \_\console\style($v));
                        \file_put_contents($this->trace, \preg_replace('/\033\[[0-9;]*m/', '', $l), LOCK_EX | FILE_APPEND);
                    } else {
                        echo \_\console\style($v);
                    }
                } break;
                case 'echo':{
                    echo $v;
                } break;
                default:{
                    $this->$n = $v;
                } break;
            }
        }
        public function __call($n, $args){
            if(isset($this->fn->$n)){
                return ($this->fn->$n)(...$args);
            } else {
                throw new \Exception("Flex method '{$n}' not found");
            }
        }
    }))();
}}}
namespace _\console { if(!\function_exists(echo_via_post::class)){ function echo_via_post(\closure $do__fn){
    static $reentry = null;
    if(!$reentry){
        $reentry = true;
        try {
            $_POST = new class() implements \ArrayAccess {
                public function offsetSet($x, $e):void {  echo \_\console\style($e); }
                public function offsetExists($n):bool { return false; }
                public function offsetUnset($n):void { }
                public function offsetGet($n):mixed { }
            };
            ($do__fn)();
        } finally {
            $reentry = false;
            $_POST = [];
        }
    }
}}}
namespace _ { if(!\function_exists(prt::class)){ function prt($str){
    \_\console\put($str); 
    echo "\033[0m";
}}}
namespace _ { if(!\function_exists(prtl::class)){ function prtl($str = null){
    $str AND \_\console\put($str); 
    echo "\033[0m\n";
}}}
namespace _ { if(!\function_exists(prtj::class)){ function prtj($o = null){
    $str = \json_encode($o, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $str AND \_\console\put($str); 
    echo "\033[0m\n";
}}}
namespace _\fs { if(!\function_exists(write::class)){ function write(string $filename , mixed $data, int $flags = 0){
    \is_dir($d = \dirname($filename)) OR \mkdir($d, 0777, true);
    return \file_put_contents($filename, $data, $flags);
}}}
namespace _\fs { if(!\function_exists(read::class)){ function read(string $filename){
    if(\is_file($filename)){
        return \file_get_contents($filename);
    }
}}}