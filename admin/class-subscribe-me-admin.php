<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://nikhil.wisdmlabs.net
 * @since      1.0.0
 *
 * @package    Subscribe_Me
 * @subpackage Subscribe_Me/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Subscribe_Me
 * @subpackage Subscribe_Me/admin
 * @author     Nikhil <nikhil.mhaske@wisdmlabs.com>
 */
class Subscribe_Me_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscribe_Me_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscribe_Me_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/subscribe-me-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subscribe_Me_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subscribe_Me_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/subscribe-me-admin.js', array('jquery'), $this->version, false);
	}


	//For Trial at admin site
	function my_add_menu_pages()
	{
		add_menu_page(
			'Subscribe Me',
			'Subscribe Me',
			'manage_options',
			'subscribe-me',
			array($this, 'subscribe_me_cb'),
			'dashicons-email',
			10
		);
		add_submenu_page(
			'subscribe-me',
			'Subscribers List',
			'Subscribers',
			'manage_options',
			'subscribers',
			array($this, 'subscribers_cb'),
		);
	}


	function subscribe_me_cb()
	{
?>
		<div class="wrap">
			<h2>Subscribe Me!</h2>
			<form method="post" action="options.php">
				<?php
				settings_fields('my_plugin_settings_group');
				do_settings_sections('subscribe-me-settings');
				?>
				<?php submit_button('Save Changes'); ?>
			</form>
		</div>
	<?php
	}

	function reg_settings()
	{
		register_setting('my_plugin_settings_group', 'no_of_posts');
		add_settings_section('subs_settings', 'Subscription Mail Settings', '', 'subscribe-me-settings');
		add_settings_field('no_of_posts', 'No of Posts', array($this, 'no_of_posts_cb'), 'subscribe-me-settings', 'subs_settings');
	}

	public function no_of_posts_cb()
	{
	?>
		<input type="text" name="no_of_posts" value="<?php echo esc_attr(get_option('no_of_posts')) ?>">
	<?php
	}


	//Submenu Subscribers List & Send Mail to all
	function subscribers_cb()
	{
		$subscribers_list = get_option('subs_emails');
		echo '<table id="sm-table"><th>Subscribers Emails</th>';

		foreach ($subscribers_list as $mail) {
			echo '<tr><td>' . $mail . '</tr></td>';
		}
		echo '</table>';
	?>

		<form method="post">
			<input type="submit" name="send" id="send" value="Send Mail to All" class="button button-primary" />
		</form>

		<?php

		if (isset($_POST['send'])) {
			$this->send_mail_to_all();
		}
	}


	//sending mail to all
	function send_mail_to_all()
	{
		$subscribers_list = get_option('subs_emails');

		foreach ($subscribers_list as $mail) {
			$subject = 'Hello! We have something special for you';
			$summary = $this->get_daily_post_summary();

			$message = "Our Latest articles (May Be Helpful to You)";
			$message .= "\n";
			foreach ($summary as $post_data) {
				$message .= 'Title: ' . $post_data['title'] . "\n";
				$message .= 'URL: ' . $post_data['url'] . "\n";
				$message .= "\n";
			}

			$headers = array(
				'From: nikhil.mhaske@wisdmlabs.com',
				'Content-Type: text/html; charset=UTF-8'
			);

			wp_mail($mail, $subject, $message, $headers);
		}
	}

	//subscribe me section
	public function subscribe_me_callback()
	{
		?>
		<!--Add Input fields on Schedule Content Page-->
		<div class="widget-form">
			<h5>Subscribe for Updates!</h5>
			<form class="subscribe-me-form" method="post">
				<input type="hidden" name="action" value="subs_form">

				<input type="email" name="email" id="email" placeholder="Email" /><br />

				<input type="submit" name="submit" value="Subscribe" />

			</form>
		</div>

<?php

		if (isset($_POST['email'])) {
			$email = sanitize_email($_POST['email']);
			$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'; //regex validation 

			if (preg_match($pattern, $email)) {
				if (isset($_POST['submit'])) {

					$subs_emails = get_option('subs_emails');

					if (!$subs_emails) {
						$subs_emails = array();
					}

					if (in_array($email, $subs_emails)) {
						echo '<script>alert("You are already subscribed!");</script>';
					} else {
						$subs_emails[] = $email;
						update_option('subs_emails', $subs_emails);

						// Display a success message
						echo '<script>alert("You have been subscribed Successfully!");</script>';

						$this->send_subscription_mail($email);
					}
				}
			} else {
				//Display Error Message
				echo '<div class="error"><p>Invalid Email: Please Enter Valid Email Address</p></div>';
			}
		}
	}

	//Sending mail
	function send_subscription_mail($to)
	{
		$subject = 'Congratulations! You are Subscribed';
		$summary = $this->get_daily_post_summary();
		$message = 'You are Successfully added to our Daily Update List';
		$message .= "\n\n";
		$message .= "Here are our Top latest Posts";
		$message .= "\n";
		foreach ($summary as $post_data) {
			$message .= 'Title: ' . $post_data['title'] . "\n";
			$message .= 'URL: ' . $post_data['url'] . "\n";
			$message .= "\n";
		}

		$headers = array(
			'From: nikhil.mhaske@wisdmlabs.com',
			'Content-Type: text/html; charset=UTF-8'
		);

		wp_mail($to, $subject, $message, $headers);
	}

	//Getting Latest N Posts
	function get_daily_post_summary()
	{
		/*For sending posts in last 24 hours*/
		// $args = array(
		//     'date_query' => array(
		//         array(
		//             'after' => '24 hours ago',
		//         ),
		//     ),
		// );

		/*For sending latest n posts */
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => get_option('no_of_posts'),
			'post_status' => 'publish'
		);

		$query = new WP_Query($args);
		$posts = $query->posts;
		$mail_list = array();

		foreach ($posts as $post) {
			$post_data = array(
				'title' => $post->post_title,
				'url' => get_permalink($post->ID),
			);
			array_push($mail_list, $post_data);
		}
		return $mail_list;
	}

	//Register Widget
	function register_subscription_widget()
	{
		register_widget('Subscription_Widget');
	}
}

