<?php

/**
 * Priority
 *           Severity
 *            0             kernel messages
 *            1             user-level messages
 *            2             mail system
 *            3             system daemons
 *            4             security/authorization messages (note 1)
 *            5             messages generated internally by syslogd
 *            6             line printer subsystem
 *            7             network news subsystem
 *            8             UUCP subsystem
 *            9             clock daemon (note 2)
 *           10             security/authorization messages (note 1)
 *           11             FTP daemon
 *           12             NTP subsystem
 *           13             log audit (note 1)
 *           14             log alert (note 1)
 *           15             clock daemon (note 2)
 *           16             local use 0  (local0)
 *           17             local use 1  (local1)
 *           18             local use 2  (local2)
 *           19             local use 3  (local3)
 *           20             local use 4  (local4)
 *           21             local use 5  (local5)
 *           22             local use 6  (local6)
 *           23             local use 7  (local7)
 *           
 *           
 *           Facility
 *            0       Emergency: system is unusable              =>  LOG_EMERG
 *            1       Alert: action must be taken immediately    =>  LOG_ALERT
 *            2       Critical: critical conditions              =>  LOG_CRIT
 *            3       Error: error conditions                    =>  LOG_ERR
 *            4       Warning: warning conditions                =>  LOG_WARNING
 *            5       Notice: normal but significant condition   =>  LOG_NOTICE
 *            6       Informational: informational messages      =>  LOG_INFO
 *            7       Debug: debug-level messages                =>  LOG_DEBUG
 *            $output .= "<" . ($facility * 8 + $severity) . ">";
 */
abstract class ConsoleCommand extends CConsoleCommand {

    // Compatibility functions
    public $options = array();

    protected function startup($args) {
        foreach ($args as $key => $arg) {
            $this->options[$arg] = true;
        }
    }

    public static function log($threadname = 'default', $type = 'DEBUG', $message = '', $timestamp = '', $severity = 7, $facility = 16) {
        if ($timestamp == '')
            $timestamp = microtime();

        $output = "";
        list($mili, $time) = explode(' ', $timestamp, 2);
      
        date_default_timezone_set("UTC");
        $output .= date('Y-m-d H:i:s', $time);
        $output .= ',' . substr($mili, 2, 3);
        $output .= " - {$threadname} - {$type} - {$message}";
        $output .= "\n";

        echo $output;

        return true;
    }

}