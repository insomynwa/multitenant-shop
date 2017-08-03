<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class Multitenant_Utility{
	public static function id( $id ) {
		$result = false;
		if ( is_numeric( $id ) ) {
			$id = intval( $id );
			//if ( $id > 0 ) {
			if ( $id >= 0 ) { // 0 => anonymous
				$result = $id;
			}
		}
		return $result;
	}
}