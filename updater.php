<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class WPFTR_Updater {
	protected $file;
	protected $plugin;
	protected $basename;
	protected $active;
	private $username;
	private $repository;
	private $github_response;  
	public function __construct($file, $username, $repo) {
		$this->file = $file;
		add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );
		$this->username = $username;
		$this->repository = $repo;
		$this->initialize();
	}
	public function set_plugin_properties() {
		$this->plugin   = get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active   = is_plugin_active( $this->basename );
	}
	private function get_repository_info() {
		if ( is_null( $this->github_response ) ) {
			$request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository ); // Build URI
			$response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri ) ), true );
			if( is_array( $response ) ) {
					$response = current( $response );
			}
			$this->github_response = $response;
		}
	}
	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3);
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}	
	public function modify_transient( $transient ) {
		if (empty($transient->checked) || empty($transient->checked[$this->basename])) {
			return $transient;
		}
		$this->get_repository_info();
		$out_of_date = version_compare( $this->github_response['tag_name'], $transient->checked[$this->basename], 'gt' );
		if( $out_of_date ) {
			$new_files = $this->github_response['zipball_url'];
			$slug = current( explode('/', $this->basename ) );
			$plugin = array(
				'url' => $this->plugin["PluginURI"],
				'slug' => $slug,
				'package' => $new_files,
				'new_version' => $this->github_response['tag_name']
			);
			$transient->response[ $this->basename ] = (object) $plugin;
		}
		return $transient;
	}
	public function plugin_popup( $result, $action, $args ) {
		if( ! empty( $args->slug ) ) {
			if( $args->slug == current( explode( '/' , $this->basename ) ) ) {
				$this->get_repository_info();
				$plugin = array(
					'name'              => $this->plugin["Name"],
					'slug'              => $this->basename,
					'version'           => $this->github_response['tag_name'],
					'author'            => $this->plugin["AuthorName"],
					'author_profile'    => $this->plugin["AuthorURI"],
					'last_updated'      => $this->github_response['published_at'],
					'homepage'          => $this->plugin["PluginURI"],
					'short_description' => $this->plugin["Description"],
					'sections'          => array( 
							'description'   => $this->plugin["Description"],
							'changelog'     =>  nl2br($this->github_response['body'], false),
					),
					'download_link'     => $this->github_response['zipball_url']
				);
				return (object) $plugin;
			}
		}   
		return $result;
	}
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;
		$install_directory = plugin_dir_path( $this->file );
		$wp_filesystem->move( $result['destination'], $install_directory );
		$result['destination'] = $install_directory;
		if ( $this->active ) {
			activate_plugin( $this->basename );
		}
		return $result;
	}
}