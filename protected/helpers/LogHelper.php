<?php

/**
 *
 */
class LogHelper {   
    
    /**
     * Returns the user ip
     */
    public static function getUserIP() {

        return Yii::app()->request->userHostAddress;
    }
    
    
    /**
     * Returns the hostname
     */
    public static function getHostName() {

        return gethostbyaddr(Yii::app()->request->userHostAddress);
    }
    
    public static function pre($arr) {
        return '<pre>' . print_r($arr, true) . '</pre>';
    }

}