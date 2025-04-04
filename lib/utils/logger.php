<?php
/**
 * Logger
 * Simple logging utility for the application
 */
class Logger {
    
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    
    /**
     * Log a debug message
     * 
     * @param string $message Message to log
     */
    public static function debug($message) {
        self::log(self::DEBUG, $message);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message Message to log
     */
    public static function info($message) {
        self::log(self::INFO, $message);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message Message to log
     */
    public static function warning($message) {
        self::log(self::WARNING, $message);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message Message to log
     */
    public static function error($message) {
        self::log(self::ERROR, $message);
    }
    
    /**
     * Log a message with the specified level
     * 
     * @param string $level Log level
     * @param string $message Message to log
     */
    private static function log($level, $message) {
        if (!defined('ESIGNET_LOG_ENABLED') || !ESIGNET_LOG_ENABLED) {
            return;
        }
        
        $logLevels = [
            self::DEBUG => 1,
            self::INFO => 2,
            self::WARNING => 3,
            self::ERROR => 4
        ];
        
        $configLevel = defined('ESIGNET_LOG_LEVEL') ? ESIGNET_LOG_LEVEL : self::INFO;
        
        if ($logLevels[$level] < $logLevels[$configLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Log to file
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/esignet_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // Also log to PHP error log for convenience
        error_log($logMessage);
    }
}
?>

