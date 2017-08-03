<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
class Multitenant_Outlet {

	private $tenant_id;
	private $area_id;

	private $id;
	private $name;
	private $desc;
	private $address;
	private $longitude;
	private $latitude;
	private $role;

	private $products;
	private $groups;


	public function __construct(){
	}

	public static function Get($outlet_id){
		$this->id = $outlet_id;
		//$user = get_userdata()
	}
}