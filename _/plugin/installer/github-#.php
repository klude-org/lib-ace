<?php namespace _\plugin\installer;

final class github {
        
    public static function _() { static $i;  return $i ?: ($i = new static()); }
    
    private function __construct(){ 
        $this->enable(true); 
    }
    
    public function enable($enable = true){
        static $fn = null;
        $fn OR $fn = function($n){
            if(\str_starts_with($n,'zzz_github__')){
                $plugin_class = \strtok($n,'\\');
                $plugin_rest = \str_replace('\\','/', \strtok(''));
                $plugin_version = \_\plugin::version_of($plugin_class) ?: 'main';
                if(
                    ($f = \stream_resolve_include_path(($p = "{$plugin_class}/{$plugin_version}/{$plugin_rest}")."/-#.php"))
                    || ($f = \stream_resolve_include_path("{$p}-#.php"))
                ){
                    include $f;
                } else if(!\is_dir($f = \stream_resolve_include_path("{$plugin_class}/{$plugin_version}"))){
                    $this->install_from_github($plugin_class, $plugin_version);
                    \_\plugin::version_of($plugin_class, $plugin_version);
                    if(
                        ($f = \stream_resolve_include_path("{$p}/-#.php"))
                        || ($f = \stream_resolve_include_path("{$p}-#.php"))
                    ){
                        include $f;
                    }
                }
            }
        };
        if($enable){
            \spl_autoload_register($fn,true,false);
        } else {
            \spl_autoload_unregister($fn);
        }
    }
    
    private function install_from_github($plugin_class, $plugin_version){
        if(!\str_starts_with($plugin_class,'zzz_github__')){
            return null;
        }
        if(\count($slug = \explode('__', $plugin_class)) !== 3){
            return null;
        }
        
        $this_class = static::class;
        $lib_dir = \_\LIB_DIR;
        $seg_a = \str_replace('_','-',$slug[1]);
        $seg_b = \str_replace('_','-',$slug[2]);
        $repo_url = "https://github.com/{$seg_a}/zzz-{$seg_b}.git";

        $plugin_dir = "{$lib_dir}/{$plugin_class}";
        
        $repo_branch = $plugin_version;
        $repo_path = "{$plugin_class}/{$repo_branch}";
        $repo_dir = "{$lib_dir}/{$repo_path}";
        
        $plugin_workspace_file = "{$plugin_dir}/_.code-workspace";
        $plugin_info_file = "{$plugin_dir}/.info.json";
        $plugin_type_file = "{$plugin_dir}/-#.php";
        //\_\console\logger__set(function(){ });
        //\_\console\tracer__set(\_\f($plugin_trace_file)));
        \_\prtj(\get_defined_vars());
        \_\prtl('Cloning Repository ... Please Wait');
        
        // Verify if the URL is valid
        $verifyCommand = sprintf('git ls-remote %s 2>&1', escapeshellarg($repo_url));
        exec($verifyCommand, $verifyOutput, $verifyReturnCode);
        
        if ($verifyReturnCode !== 0) {
            throw new \Exception("Invalid or inaccessible repository URL. Output:\n" . implode("\n", $verifyOutput));
        }
        
        // Check if the directory already exists
        if (!is_dir($repo_dir)) {
            // Create the directory if it doesn't exist
            if (!mkdir($repo_dir, 0777, true)) {
                throw new \Exception("Failed to create directory: $repo_dir");
            }
        }
        // Run the git clone command
        $cloneCommand = sprintf(
            'git clone --branch %s --depth 1 %s %s 2>&1', 
            \escapeshellarg($plugin_version), 
            \escapeshellarg($repo_url), 
            \escapeshellarg($repo_dir)
        );
        exec($cloneCommand, $cloneOutput, $cloneReturnCode);
        
        if ($cloneReturnCode !== 0) {
            \_\prtl("{#91}Failed to clone repository. Output:\n" . implode("\n", $cloneOutput));
            return;
        }
        
        \_\prtl("{#92}Repository cloned successfully into $repo_dir\n");
        
        if(\is_dir($drec = "{$repo_dir}/.git")){
            foreach (
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($drec, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ) as $x
            ){
                if (!is_writable($x->getRealPath())) {
                    chmod($x->getRealPath(), 0666); // Change file permissions to writable
                }
                $x->isDir() 
                    ? \rmdir($x->getRealPath()) 
                    : \unlink($x->getRealPath())
                ;
            }
            \rmdir($drec);
        }
                
        
        \file_exists($plugin_type_file) OR \file_put_contents($plugin_type_file, <<<PHP
        <?php 
        
        class {$plugin_class} extends \\_\\plugin\\__b {
        
        }
        PHP);
        include $plugin_type_file;
        
        \file_exists($plugin_info_file) OR \file_put_contents(
            $plugin_info_file, 
            \json_encode([
                'repo_url' => $repo_url,
            ], \JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        
        \file_exists($plugin_workspace_file) OR \file_put_contents($plugin_workspace_file, <<<TXT
        {
            "folders": [
                {
                    "name": "--plugin--",
                    "path": "."
                },
                {
                    "name": "--lib--",
                    "path": ".."
                },
                {
                    "name": "--xdbg--",
                    "path": "C:/xampp/.setup/php_current__xdbg"
                }
            ],
            "settings": {
                "window.title": "[ {$plugin_class} ] / \${folderName} ",
                // "files.exclude": {
                // 	"**/---": true,
                // 	//"**/.ach/**": true,
                // 	"**/desktop.ini": true,
                // },	
                "workbench.colorCustomizations": {
                    "sideBar.background": "#202733",
                    "settings.headerBorder": "#d68b8b",
                    "statusBar.background": "#333",
                    "activityBar.background": "#325369",
                    "activityBar.inactiveForeground": "#a18c8c",
                    "titleBar.activeBackground": "#2b3a23",
                    "titleBar.activeForeground": "#dad1e7",
                    "icon.foreground": "#00ddff",
                    "symbolIcon.colorForeground": "#a77d7d",
                    "icon.colorForeground": "#ff0000",
                }
            }	
        }  
        TXT);
            
        if(0){
            // Ensure the file exists before attempting to open it
            if (!file_exists($plugin_workspace_file)) {
                die("Workspace file does not exist: $plugin_workspace_file");
            }
            
            // Command to open the workspace file in VS Code
            $command = sprintf('code %s 2>&1', escapeshellarg($plugin_workspace_file));
            
            // Execute the command
            exec($command, $output, $returnCode);
            
            // Check the result
            if ($returnCode !== 0) {
                \_\prtl("{#91}Failed to open VS Code workspace. Output:\n" . implode("\n", $output));
            }        
        }
    }
}