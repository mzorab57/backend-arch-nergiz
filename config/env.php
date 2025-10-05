<?php

/**
 * Environment configuration loader
 * Loads variables from .env file into $_ENV superglobal
 */
class EnvLoader {
    
    /**
     * Load environment variables from .env file
     * @param string $path Path to .env file
     */
    public static function load($path = null) {
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Set environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
    
    /**
     * Get environment variable with optional default
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

// Auto-load environment variables
EnvLoader::load();
?>