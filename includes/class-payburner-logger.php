<?php

class PayburnerLogger {

    public static function log( $log ) {

        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( 'Payburner :: '.print_r( $log, true ) );
        } else {
            error_log( 'Payburner :: '.$log );
        }
    }
}
