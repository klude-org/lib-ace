<?php namespace _;

final class plugin {
    
    private static $TAGS = [];
    
    public static function version_of(string $class, string $tag = null){
        if(!\is_null($tag)){
            static::$TAGS[$class] ??= $tag;
        } else {
            return static::$TAGS[$class] ?? null;
        }        
    }    
    
}