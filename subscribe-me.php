<?php
/*
* Plugin Name: Subscribe Me
* Plugin URI: https://nikhil.wisdmlabs.net
* Author: Nikhil Mhaske
* Author URI: https://nikhil.wisdmlabs.net
* Description: Posts summary on Admin Mail at End of the Day
* Text Domain: subscribe-me
*/

//For Trial at admin site
function my_add_menu_pages()
{
    add_menu_page(
        'Subscribe Me',
        'Subscribe Me',
        'manage_options',
        'subscribe-me',
        'subscribe_me_callback',
        'dashicons-email',
        10
    );
}
add_action('admin_menu', 'my_add_menu_pages');

function subscribe_me_callback()
{
?>

    <!--Add Input fields on Schedule Content Page-->
    <div class="wrap">


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
                $subs_emails[] = $email;
                update_option('subs_emails', $subs_emails);

                // Display a success message
                echo '<script>alert("You have been subscribed Successfully!");</script>';

                send_subscription_mail($email);
            }
        } else {
            //Display Error Message
            echo '<div class="error"><p>Invalid Email: Please Enter Valid Email Address</p></div>';
        }
    }
}

function send_subscription_mail($to)
{
    $headers = array(
        'From: nikhil.mhaske@wisdmlabs.com',
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($to, 'Subscription Mail', 'You are Subscribed to Daily Updates', $headers);
};


function subscribe_me_add_form()
{
    subscribe_me_callback();
}
add_action('wp_head', 'subscribe_me_add_form');
?>