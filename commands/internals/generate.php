<?php

// Add the command to the wp-cli
WP_CLI::addCommand('generate', 'GenerateCommand');

/**
 * Implement generate command
 *
 * @package wp-cli
 * @subpackage commands/internals
 * @author Cristi Burca
 */
class GenerateCommand extends WP_CLI_Command {

	/**
	 * Generate posts
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @return void
	 **/
	public function posts( $args, $assoc_args ) {
		global $wpdb;

		$defaults = array(
			'count' => 100,
			'type' => 'post',
			'status' => 'publish'
		);

		extract( wp_parse_args( $assoc_args, $defaults ), EXTR_SKIP );

		if ( !post_type_exists( $type ) ) {
			WP_CLI::warning( 'invalid post type.' );
			exit;
		}

		// Get the total number of posts
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s", $type ) );

		$label = get_post_type_object( $type )->labels->singular_name;

		$limit = $count + $total;

		for ( $i = $total; $i < $limit; $i++ ) {
			wp_insert_post( array(
				'post_type' => $type,
				'post_title' =>  "$label $i",
				'post_status' => $status
			) );
		}
	}

	/**
	 * Generate users
	 *
	 * @param array $args
	 * @param array $assoc_args
	 * @return void
	 **/
	public function users( $args, $assoc_args ) {
		global $blog_id;

		$defaults = array(
			'count' => 100,
			'role' => get_option('default_role'),
		);

		extract( wp_parse_args( $assoc_args, $defaults ), EXTR_SKIP );

		if ( is_null( get_role( $role ) ) ) {
			WP_CLI::warning( "invalid role." );
			exit;
		}

		$user_count = count_users();

		$total = $user_count['total_users'];

		$limit = $count + $total;

		for ( $i = $total; $i < $limit; $i++ ) {
			$login = sprintf( 'user_%d_%d', $blog_id, $i );
			$name = "User $i";

			$r = wp_insert_user( array(
				'user_login' => $login,
				'user_pass' => $login,
				'nickname' => $name,
				'display_name' => $name,
				'role' => $role
			) );
		}
	}

	/**
	 * Help function for this command
	 *
	 * @param array $args
	 * @return void
	 */
	public function help( $args = array() ) {
		WP_CLI::out( <<<EOB
usage: wp generate <object-type> [--count=100]

Available object types:
    posts         generate some posts
      --count     number of users to generate (default: 100)
      --type      post type (default: 'post')
      --status    post status (default: 'publish')
    users         generate some users
      --count     number of users to generate (default: 100)
      --role      user role (default: default_role option)

EOB
		);
	}
}
