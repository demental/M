<?php
class SecurityException extends Exception {
    function getError() {
        return print_r(ini_get('include_path'),true);
    }
}