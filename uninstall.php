<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
	die;
}

// delete usermeta
// $meta_type = 'user';
// $user_id = 0;
// $meta_keys = array('binder_group');
// $meta_value = '';
// $delete_all = true;
// foreach($meta_keys as $meta_key){
// 	delete_metadata( $meta_type, $user_id, $meta_key, $meta_value, $delete_all);
// }

// delete role
global $wp_roles;

if ( ! class_exists( 'WP_Roles' ) ) {
	return;
}

if ( ! isset( $wp_roles ) ) {
	$wp_roles = new WP_Roles();
}

remove_role( 'tenant_role' );
remove_role( 'outlet_role' );

// delete group
// if ( $group = Groups_Group::read_by_name( 'Tenant' ) ) {
// 	Groups_Group::delete( $group->group_id );
// }
// if ( $group = Groups_Group::read_by_name( 'Outlet' ) ) {
// 	Groups_Group::delete( $group->group_id );
// }