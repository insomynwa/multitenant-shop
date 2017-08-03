<?php
class Multitenant {
	protected $loader;
	protected $plugin_slug;
	protected $version;
	protected $models;

	public function __construct() {
		$this->plugin_slug = 'multitenant-ecommerce';
		$this->version = '0.1.0';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-multitenant-admin.php';

		require_once plugin_dir_path( __FILE__ ) . 'class-multitenant-loader.php';
		$this->loader = new Multitenant_Loader();
	}

	private function define_admin_hooks() {
		$admin = new Multitenant_Admin( $this->get_version() );
		$this->loader->add_action( 'init', $admin, 'RegisterWPCustomMultitenant');
		$this->loader->add_action( 'admin_menu', $admin, 'HideWooWPSubmenu',400);
		//$this->loader->add_action( 'admin_notices', $admin, 'debug_admin_menus');
		$this->loader->add_action( 'user_register', $admin, 'GrapUserdata');
		$this->loader->add_action( 'user_register', $admin, 'GroupingUser');
		$this->loader->add_action( 'user_new_form', $admin, 'CreateCustomUserAddNew');
		$this->loader->add_action( 'delete_user', $admin, 'DeleteBinderGroup');
		$this->loader->add_action( 'pre_get_posts', $admin, 'ShowProductByOwner');
	}

	public function run() {
		$this->loader->run();
	}

	public function get_version() {
		return $this->version;
	}
}