<?php
/**
 * Plugin Name: Custom Authentication Plugin
 * Plugin URI:  https://example.com/
 * Description: This plugin creates custom login and registration forms with session management.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin path for easy access
define( 'MY_CUSTOM_AUTH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include template files
require_once MY_CUSTOM_AUTH_PLUGIN_DIR . 'templates/login-form.php';
require_once MY_CUSTOM_AUTH_PLUGIN_DIR . 'templates/register-form.php';

// Load CSS and JS
function my_custom_auth_enqueue_scripts() {
    wp_enqueue_style( 'my-custom-auth-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
    wp_enqueue_script( 'my-custom-auth-script', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array('jquery'), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'my_custom_auth_enqueue_scripts' );


// Handle Login form
function my_custom_auth_handle_login() {
    if ( isset( $_POST['login_submit'] ) ) {
        $username = sanitize_text_field( $_POST['username'] );
        $password = $_POST['password'];

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            // Login failed
            $login_error = $user->get_error_message();
            echo '<p class="error">' . esc_html( $login_error ) . '</p>';
            
        } else {
            // Login success
            wp_set_current_user( $user->ID, $user->user_login );
            wp_set_auth_cookie( $user->ID );
            do_action( 'wp_login', $user->user_login, $user );

            // Redirect after successful login
            wp_redirect( home_url() ); 
            exit;
        }
    }
}

add_action( 'template_redirect', 'my_custom_auth_handle_login' );


// Handle Registration Form
function my_custom_auth_handle_registration() {
    if ( isset( $_POST['register_submit'] ) ) {
        $username = sanitize_text_field( $_POST['username'] );
        $email = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];
       
        $errors = new WP_Error();

        // Validate username and email
         if (username_exists($username)){
             $errors->add('username_exists','نام کاربری تکراری است');
         }
        if(email_exists($email)){
           $errors->add('email_exists', 'ایمیل تکراری است');
        }
        if (empty($username) || empty($email) || empty($password)) {
            $errors->add( 'empty_field', 'لطفا همه فیلدها را پر کنید' );
        }

        if ( $errors->get_error_messages() ) {
             foreach ($errors->get_error_messages() as $error) {
                 echo '<p class="error">' . esc_html( $error ) . '</p>';
            }
            
        } else {
            
            $user_id = wp_create_user( $username, $password, $email );

            if ( is_wp_error( $user_id ) ) {
                // Registration failed
                $register_error = $user_id->get_error_message();
                 echo '<p class="error">' . esc_html( $register_error ) . '</p>';

            } else {
                // Registration success
                wp_set_current_user( $user_id, $username );
                wp_set_auth_cookie( $user_id );
                do_action( 'wp_login', $username, wp_get_current_user() );

                 // Redirect after successful registration
                wp_redirect( home_url() );
                 exit;
            }
       }
    }
}
add_action( 'template_redirect', 'my_custom_auth_handle_registration' );


// Shortcodes for login and registration forms
function my_custom_auth_login_form_shortcode() {
    ob_start();
    my_custom_auth_login_form();
    return ob_get_clean();
}
add_shortcode( 'my_login_form', 'my_custom_auth_login_form_shortcode' );

function my_custom_auth_register_form_shortcode() {
    ob_start();
    my_custom_auth_register_form();
    return ob_get_clean();
}
add_shortcode( 'my_register_form', 'my_custom_auth_register_form_shortcode' );

// Session management
function my_custom_auth_check_session(){
   // Check for session
   if (is_user_logged_in()) {
        echo '<p class="success">شما وارد سیستم شده اید</p>';
       // Optionally, display user information or a logout link
   }else{
        echo '<p class="error">شما هنوز وارد سیستم نشده اید</p>';
   }

}
add_action('wp_head', 'my_custom_auth_check_session');

function my_custom_auth_logout_link() {
    if(is_user_logged_in()){
        $logout_url = wp_logout_url( home_url() );
        echo '<a href="' . esc_url( $logout_url ) . '">خروج</a>';
    }
}

add_action('wp_head', 'my_custom_auth_logout_link');


<div class="my-custom-auth-form">
    <h2>ورود</h2>
    <form method="post" action="">
        <label for="username">نام کاربری:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">رمز عبور:</label>
        <input type="password" name="password" id="password" required>
        <br>
        <input type="submit" name="login_submit" value="ورود">
    </form>
</div>


<div class="my-custom-auth-form">
    <h2>ثبت نام</h2>
    <form method="post" action="">
       <label for="username">نام کاربری:</label>
        <input type="text" name="username" id="username" required>

         <label for="email">ایمیل:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">رمز عبور:</label>
        <input type="password" name="password" id="password" required>
         <br>
        <input type="submit" name="register_submit" value="ثبت نام">
    </form>
</div>


.my-custom-auth-form {
    width: 300px;
    margin: 20px auto;
    padding: 20px;
    border: 1px solid #ddd;
    text-align: right;
}

.my-custom-auth-form label {
    display: block;
    margin-bottom: 5px;
}

.my-custom-auth-form input[type="text"],
.my-custom-auth-form input[type="email"],
.my-custom-auth-form input[type="password"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
}

.my-custom-auth-form input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
}
.error{
    color: red;
    text-align: right;
}
.success{
    color: green;
    text-align: right;
}


/*
نحوه استفاده:

پوشه my-custom-auth را در پوشه wp-content/plugins وردپرس خود ایجاد کنید.
فایل‌های بالا را در این پوشه قرار دهید.
پلاگین را از داشبورد وردپرس فعال کنید.
از شورتکد‌های [my_login_form] و [my_register_form] در صفحات خود استفاده کنید.
توضیحات:

این کد یک نقطه شروع خوب برای ایجاد یک سیستم ورود و ثبت نام سفارشی در وردپرس است.
می‌توانید کد را بر اساس نیاز خود سفارشی سازی کنید.
بهتر است قبل از استفاده از این کد در یک محیط تولید، آن را به طور کامل تست کنید و از امنیت آن اطمینان حاصل کنید.
برای اضافه کردن فیلدهای بیشتر به فرم ثبت نام، باید هم در فایل قالب و هم در تابع my_custom_auth_handle_registration تغییرات ایجاد کنید.
می توانید از توابع وردپرس برای نمایش پیام های خطا و موفقیت استفاده کنید.
این کد، یک نسخه ابتدایی و ساده است و می تواند بر اساس نیازهای خاص شما گسترش یابد. به عنوان مثال، می توانید قابلیت های زیر را به آن اضافه کنید:

تایید ایمیل
بازیابی رمز عبور
حفظ کاربر در سیستم بعد از بستن مرورگر
اعتبارسنجی بهتر داده های ورودی
امنیت بیشتر
استفاده از AJAX برای پردازش فرم
*/

