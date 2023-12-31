<?php
/**
 * Created by: simon
 * Date: 03.03.2022
 */
namespace ShortPixel\CriticalCSS;

class FileLog {
    private static $instance;
    private        $logPath;

    const DEBUG_AREA_INIT = 1;
    const DEBUG_AREA_API = 2;
    const DEBUG_AREA_DB = 4;
    const DEBUG_AREA_Q = 8;
    const DEBUG_AREA_CACHE = 16;
    const DEBUG_AREA_PROCESS = 32;
    const DEBUG_AREA_ALL = 63;

    /**
     * Make sure only one instance is running.
     */
    public static function instance() {
        if ( !isset ( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct() {
        $upload_dir    = wp_upload_dir();
        $this->logPath = $upload_dir[ 'basedir' ] . '/sp_ccss.log';
    }

    public function log( $msg, $extra = false ) {
        if ( SPCCSS_DEBUG ) {
            $micro_date = microtime();
            $date_array = explode(" ",$micro_date);
            $date = date("Y-m-d H:i:s",$date_array[1]);
            $date = $date . '.' . substr($date_array[0], 2, 3);
            file_put_contents( $this->logPath, '[' . $date . "] $msg" . ( $extra ? json_encode( $extra, JSON_PRETTY_PRINT ) : '' ) . "\n", FILE_APPEND );
        }
    }

    /**
     * Custom (PHP) error handler
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return bool
     */
    public static function errorHandler( $errno, $errstr, $errfile, $errline ) {
        if ( !( error_reporting() & $errno ) ) {
            // this error code doesn't contain in error_reporting,
            // so let default error handler does its job
            return false;
        }

        switch ( $errno ) {
            case E_ERROR:
                $type = 'Run-time ERROR';
                break;

            case E_WARNING:
                $type = 'Run-time WARNING';
                break;

            case E_NOTICE:
                $type = 'Run-time NOTICE';
                break;

            case E_DEPRECATED:
                $type = 'Run-time DEPRECATED';
                break;

            case E_CORE_ERROR:
                $type = 'PHP core ERROR';
                break;

            case E_CORE_WARNING:
                $type = 'PHP core WARNING';
                break;

            case E_USER_ERROR:
                $type = 'Generated ERROR';
                break;

            case E_USER_WARNING:
                $type = 'Generated WARNING';
                break;

            case E_USER_NOTICE:
                $type = 'Generated NOTICE';
                break;

            case E_USER_DEPRECATED:
                $type = 'Generated DEPRECATED';
                break;

            default:
                $type = 'OTHER';
                break;
        }

        $logger = self::instance();

        $logger->log( '--- CAUGHT:BEGIN ---' );
        $logger->log( $type . ': ' . $errstr );
        $logger->log( 'IN FILE: ' . $errfile );
        $logger->log( 'ON LINE: ' . $errline );
        // Backtrace could be uncommented if needed
        // $logger->log( json_encode( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), JSON_PRETTY_PRINT ) );
        $logger->log( '--- CAUGHT:END ---' );

        // don't run default error handler
        return true;
    }

    public function clearLog() {
        @unlink( $this->logPath );
    }

    public function getLogPath() {
        return $this->logPath;
    }
}
