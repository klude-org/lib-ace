<?php namespace _\plugin\package\installer;

final class github {
        
    public static function _() { static $i;  return $i ?: ($i = new static()); }
    
    private function __construct(){ 
        $this->enable(true); 
    }
    
    public function enable($enable = true){
        static $fn = null;
        $fn OR $fn = function($n){
            if(\str_starts_with($n,'pkg_github__')){
                $pkg_class = \strtok($n,'\\');
                $plugin_slug = \str_replace('\\','/', \strtok(''));
                $pkg_version = \_\plugin\package::version_of($pkg_class) ?: 'main';
                if(
                    ($f = \stream_resolve_include_path(($p = "{$pkg_class}/{$pkg_version}/{$plugin_slug}")."/-#.php"))
                    || ($f = \stream_resolve_include_path("{$p}-#.php"))
                ){
                    include $f;
                } else if(!\is_dir($f = \stream_resolve_include_path("{$pkg_class}/{$pkg_version}"))){
                    $this->install_from_github($pkg_class, $pkg_version);
                    \_\plugin\package::version_of($pkg_class, $pkg_version);
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
    
    private function install_from_github($pkg_class, $pkg_version){
        if(!\str_starts_with($pkg_class,'pkg_github__')){
            return null;
        }
        if(\count($slug = \explode('__', $pkg_class)) !== 3){
            return null;
        }
        
        $this_class = static::class;
        $lib_dir = \_\LIB_DIR;
        $seg_a = \str_replace('_','-',$slug[1]);
        $seg_b = \str_replace('_','-',$slug[2]);
        $repo_url = "https://github.com/{$seg_a}/pkg-{$seg_b}.git";

        $pkg_dir = "{$lib_dir}/{$pkg_class}";
        
        $repo_branch = $pkg_version;
        $repo_path = "{$pkg_class}/{$repo_branch}";
        $repo_dir = "{$lib_dir}/{$repo_path}";
        
        $pkg_workspace_file = "{$pkg_dir}/_.code-workspace";
        $pkg_info_file = "{$pkg_dir}/.info.json";
        $pkg_type_file = "{$pkg_dir}/-#.php";
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
            \escapeshellarg($pkg_version), 
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
                
        
        \file_exists($pkg_type_file) OR \file_put_contents($pkg_type_file, <<<PHP
        <?php 
        
        class {$pkg_class} extends \\_\\plugin\\package\\__b {
        
        }
        PHP);
        include $pkg_type_file;
        
        \file_exists($pkg_info_file) OR \file_put_contents(
            $pkg_info_file, 
            \json_encode([
                'repo_url' => $repo_url,
            ], \JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        
        \file_exists($pkg_workspace_file) OR \file_put_contents($pkg_workspace_file, <<<TXT
        {
            "folders": [
                {
                    "name": "--package--",
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
                "window.title": "[ {$pkg_class} ] / \${folderName} ",
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
            if (!file_exists($pkg_workspace_file)) {
                die("Workspace file does not exist: $pkg_workspace_file");
            }
            
            // Command to open the workspace file in VS Code
            $command = sprintf('code %s 2>&1', escapeshellarg($pkg_workspace_file));
            
            // Execute the command
            exec($command, $output, $returnCode);
            
            // Check the result
            if ($returnCode !== 0) {
                \_\prtl("{#91}Failed to open VS Code workspace. Output:\n" . implode("\n", $output));
            }        
        }
    }
}