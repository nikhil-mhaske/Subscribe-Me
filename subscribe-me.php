<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://nikhil.wisdmlabs.net
 * @since             1.0.0
 * @package           Subscribe_Me
 *
 * @wordpress-plugin
 * Plugin Name:       Subscribe Me
 * Plugin URI:        https://nikhil.wisdmlabs.net
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Nikhil
 * Author URI:        https://nikhil.wisdmlabs.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subscribe-me
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SUBSCRIBE_ME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-subscribe-me-activator.php
 */
function activate_subscribe_me() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscribe-me-activator.php';
	Subscribe_Me_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subscribe-me-deactivator.php
 */
function deactivate_subscribe_me() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subscribe-me-deactivator.php';
	Subscribe_Me_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_subscribe_me' );
register_deactivation_hook( __FILE__, 'deactivate_subscribe_me' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-subscribe-me.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_subscribe_me() {

	$plugin = new Subscribe_Me();
	$plugin->run();

}
run_subscribe_me();


//For Trial at admin site
function my_add_menu_pages()
{
    add_menu_page(
        'Subscribe Me',
        'Subscribe Me',
        'manage_options',
        'subscribe-me',
        'subscribe_me_cb',
        'dashicons-email',
        10
    );
    add_submenu_page(
        'subscribe-me',
        'Subscribers List',
        'Subscribers',
        'manage_options',
        'subscribers',
        'subscribers_cb',
    );
}

add_action('admin_menu', 'my_add_menu_pages');

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
    add_settings_field('no_of_posts', 'No of Posts', 'no_of_posts_cb', 'subscribe-me-settings', 'subs_settings');
}
add_action('admin_init', 'reg_settings');

function no_of_posts_cb()
{
?>
    <input type="text" name="no_of_posts" value="<?php echo esc_attr(get_option('no_of_posts')) ?>">
<?php
}


//Submenu Subscribers List
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
        <input type="submit" name="send" id="send" value="Send Mail" class="button button-primary" />
    </form>

    <?php

    if (isset($_POST['send'])) {
        send_mail_to_all();
    }
}

function send_mail_to_all()
{
    $subscribers_list = get_option('subs_emails');

    foreach ($subscribers_list as $mail) {
        $subject = 'Hello! We have something special for you';
        $summary = get_daily_post_summary();

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


function subscribe_me_callback()
{
    ?>
    <!--Add Input fields on Schedule Content Page-->
    <div class="wrap subs-wrap">

        <form class="subscribe-me-form" method="post">
            <input type="hidden" name="action" value="subs_form">

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" /><br />

            <input type="submit" name="submit" value="Subscribe" />

        </form>
    </div>

<?php

    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

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

                    send_subscription_mail($email);
                }
            }
        } else {
            //Display Error Message
            echo '<div class="error"><p>Invalid Email: Please Enter Valid Email Address</p></div>';
        }
    }
}

// function subscribe_me_add_form()
// {
//     subscribe_me_callback();
// }
// add_action('wp_head', 'subscribe_me_add_form');

function send_subscription_mail($to)
{
    $subject = 'Congratulations! You are Subscribed';
    $summary = get_daily_post_summary();
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
};

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

class Subscription_Widget extends WP_Widget
{

    // Constructor function
    public function __construct()
    {
        $widget_options = array(
            'classname' => 'subscription_widget',
            'description' => 'A widget for subscribing to our newsletter'
        );
        parent::__construct('subscription_widget', 'Subscription Widget', $widget_options);
    }

    // Output the widget content on the front-end
    public function widget($args, $instance)
    {
        // Code to output the widget HTML goes here
        subscribe_me_callback();
    }

    // Output the widget form in the admin area
    public function form($instance)
    {
        // Code to output the widget form HTML goes here
    }

    // Handle saving the widget options
    public function update($new_instance, $old_instance)
    {
        // Code to handle saving the widget options goes here
    }
}

function register_subscription_widget()
{
    register_widget('Subscription_Widget');
}
add_action('widgets_init', 'register_subscription_widget');

?>
