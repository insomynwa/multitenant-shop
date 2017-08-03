<?php
class Multitenant_Admin {
	protected $version;

	/**
	 * Constructor
	 * @param version
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Registering wordpress custom for this plugin
	 */
	public function RegisterWPCustomMultitenant(){
		/*CREATE NEW ROLES*/
		$this->createCustomUserRoles();

		$groups_name = array( 'Tenant', 'Outlet' );
		$this->createGroups($groups_name);
	}

	/**
	 * Create custom user roles 
	 * @return null
	 */
	private function createCustomUserRoles(){
		/*
		CREATE NEW ROLES
		*/
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$tenant_roles_wp = array(
			'read'						=> true,
			'delete_posts'				=> true,
			'edit_posts'				=> true,
			'delete_published_posts	'	=> true,
			'publish_posts'				=> true,
			'upload_files'				=> true,
			'edit_published_posts'		=> true,
			);
		$outlet_roles_wp = array(
			'read'						=> true,
			'delete_posts'				=> true,
			'edit_posts'				=> true,
			'delete_published_posts	'	=> true,
			'publish_posts'				=> true,
			'upload_files'				=> true,
			'edit_published_posts'		=> true,
			);

		add_role('tenant_role', __( 'Tenant' ), $tenant_roles_wp);
		add_role('outlet_role', __( 'Outlet' ), $outlet_roles_wp);

		/*
			ADD CAPABILITIES TO THE NEW ROLES
		*/
		$capabilities['core'] = array(
			'manage_woocommerce',
			'view_woocommerce_reports',
		);
		$capability_types = array( 'product', 'shop_order', 'shop_coupon', 'shop_webhook' );
		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'tenant_role', $cap );
					$wp_roles->add_cap( 'outlet_role', $cap );
				}
			}
		}
	}

	/**
	 * Create a group
	 * @param  group name
	 * @return created group id or null
	 */
	private function createGroup($group_name){
		if ( !( $group = Groups_Group::read_by_name( $group_name ) ) ) {
			$group_id = Groups_Group::create( array( 'name' => $group_name ) );
			return $group_id;
		}
		return false;
	}

	/**
	 * Create groups
	 * @param  group name
	 * @return null
	 */
	private function createGroups($groups_name){
		foreach($groups_name as $group_name){
			if ( !( $group = Groups_Group::read_by_name( $group_name ) ) ) {
				$group_id = Groups_Group::create( array( 'name' => $group_name ) );
			}
		}
	}

	/**
	 * Get a group id
	 * @param  group name
	 * @return registeres group id
	 */
	private function getGroupId($group_name){
		$registered_group = Groups_Group::read_by_name($group_name);
		if ( !$registered_group ) {
			$registered_group_id = Groups_Group::create( array( 'name' => $group_name ) );
		} else {
			$registered_group_id = $registered_group->group_id;
		}
		return $registered_group_id;
	}

	/**
	 * Collecting new registered user's metadata
	 * @param user id
	 */
	public function GroupingUser($user_id){
		$user_meta=get_userdata($user_id);
		$user_roles = $user_meta->roles;

		$obligate_group_name = "";
		$binder_group_name = "";
		$binder_group_id = 0;
		if(in_array("tenant_role", $user_roles)) {
			$obligate_group_name = "Tenant";
			$binder_group_name = $user_meta->user_login;
			$binder_group_id = $this->createGroup($binder_group_name);
		}
		else if(in_array("outlet_role", $user_roles)) {
			$obligate_group_name = "Outlet";
			update_usermeta($user_id, 'binder_group', $_POST['group-id']);
			$user_meta=get_userdata($user_id);
			$binder_group_id = $user_meta->binder_group;
		}
		$obligate_group_id = $this->getGroupId($obligate_group_name);
		$this->assignGroup($user_id, array($obligate_group_id,$binder_group_id));
	}

	/**
	 * Assign new registered user to groups
	 * @param  new registered user id
	 * @param  groups id
	 * @return null
	 */
	private function assignGroup($user_id, $assinged_groups_id){
		foreach ($assinged_groups_id as $assinged_group_id) {
			Groups_User_Group::create(
				array(
					'user_id'	=> $user_id,
					'group_id'	=> $assinged_group_id
					)
				);
		}
	}

	/**
	 * Create field in user profile form
	 */
	public function CreateCustomUserAddNew(){
?>
		<h3>Tenant Group</h3>
        <select name="group-id">
        	<?php foreach(Groups_Group::get_group_ids() as $group_id): ?>
        	<option value="<?php _e($group_id); ?>"><?php _e(Groups_Group::read($group_id)->name); ?></option>
        	<?php endforeach; ?>
        </select>
<?php
	}

	/**
	 * Delete created group by deleted user
	 * @param deleted user id
	 */
	public function DeleteBinderGroup( $user_id ){
		$user_meta = get_userdata($user_id);
		$user_roles = $user_meta->roles;

		if(in_array("tenant_role",$user_roles)){
			$tenant_group_name = $user_meta->user_login;
			if ( $group = Groups_Group::read_by_name( $tenant_group_name ) ) {
				// update its outlet
				foreach($group->users as $outlet){
					update_usermeta($outlet, 'binder_group', 0);
				}
				// delete group
				Groups_Group::delete( $group->group_id );
			}
		}
	}

	public function HideWooWPSubmenu(){
		$user = wp_get_current_user();
		if(in_array("tenant_role", $user->roles)){
			remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
		}
		if(in_array("outlet_role", $user->roles)){
			remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
		}

		$menu_name = 'woocommerce';
		$removed_submenu = array('wc-addons','wc-status','wc-settings');
		$this->removeSubmenu('tenant_role', $menu_name, $removed_submenu);
		$this->removeSubmenu('outlet_role', $menu_name, $removed_submenu);
		
		$menu_name = 'edit.php?post_type=product';
		$removed_submenu_product = array(
			'edit-tags.php?taxonomy=product_cat&amp;post_type=product',
			'edit-tags.php?taxonomy=product_tag&amp;post_type=product',
			'product_attributes'
			);
		$this->removeSubmenu('tenant_role', $menu_name, $removed_submenu_product);
		$this->removeSubmenu('outlet_role', $menu_name, $removed_submenu_product);
	}


	private function removeSubmenu($role, $menu, $submenus){
		$user = wp_get_current_user();
		foreach ($submenus as $submenu) {
			if(in_array($role, $user->roles)){
				remove_submenu_page( $menu, $submenu );
			}
		}
	}

	function debug_admin_menus() {
		if ( !is_admin())
	        return;
	    global $submenu, $menu, $pagenow;

		//	var_dump(get_taxonomy('product_cat'));die;
	    if ( current_user_can('manage_options') ) { // ONLY DO THIS FOR ADMIN
	        if( $pagenow == 'index.php' ) {  // PRINTS ON DASHBOARD
	            echo '<pre>'; print_r( $menu ); echo '</pre>'; // TOP LEVEL MENUS
	            echo '<pre>'; print_r( $submenu ); echo '</pre>'; // SUBMENUS
	        }
	    }
	}

	public function ShowProductByOwner($query){
		$user = wp_get_current_user();
		if(in_array("tenant_role", $user->roles)){
			$query->set('author', $user->ID);
		}
		if(in_array("outlet_role", $user->roles)){
			$query->set('author', $user->ID);
		}
		//print_r($query);
	}

}