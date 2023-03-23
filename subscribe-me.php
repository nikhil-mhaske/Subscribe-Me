<?php
/*
* Plugin Name: Subscribe Me
* Plugin URI: https://nikhil.wisdmlabs.net
* Author: Nikhil Mhaske
* Author URI: https://nikhil.wisdmlabs.net
* Description: Posts summary on Admin Mail at End of the Day
* Text Domain: subscribe-me
*/


//enqueue CSS
require plugin_dir_path(__FILE__) . 'includes/scripts.php';


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
        <input type="submit" name="send" id="send" value="Send Mail" />
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

function subscribe_me_add_form()
{
    subscribe_me_callback();
}
add_action('wp_head', 'subscribe_me_add_form');

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
?>