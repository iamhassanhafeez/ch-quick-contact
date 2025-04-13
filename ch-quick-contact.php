<?php
/**
 * Plugin Name: CH Quick Contact
 * Description: A simple Ajax based contact form plugin for WordPress. Use this shortcode [qc_contact_form] to display the form.
 * Version: 1.0
 * Author: Hassan Hafeez
 * Author URI: https://iamhassanhafeez.github.io/portfolio/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ch-quick-contact
 */

function qc_enqueue_scripts() {
   // wp_enqueue_style('qc-style', plugin_dir_url(__FILE__) . 'css/style.css');
    wp_enqueue_script('qcc-script', plugin_dir_url(__FILE__) . 'js/ch-quick-contact.js', array('jquery'), null, true);

    wp_localize_script('qcc-script', 'qc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qc_nonce'),
    ));
}

add_action('wp_enqueue_scripts', 'qc_enqueue_scripts');

function qc_contact_form_shortcode() {
    ob_start();
    ?> <div id="qc-contact-form">
    <form id="qc-form">
        <input type="text" name="name" id="qc-name" placeholder="Your Name" required>
        <input type="email" name="email" id="qc-email" placeholder="Your Email" required>
        <textarea name="message" id="qc-message" placeholder="Your Long Message" required></textarea>
        <button type="submit" id="qc-submit">Send</button>
    </form>
    <div id="qc-response"></div>
</div> <?php
    return ob_get_clean();
}
add_shortcode('qc_contact_form', 'qc_contact_form_shortcode');

function qc_handle_form_submission() {
    if(!isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'qc_nonce')){
        wp_send_json_error('Security check failed.');
    }
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    if(empty($name) || empty($email) || empty($message)){
        wp_send_json_error('All fields are required');
    }
    if(! is_email($email)){
        wp_send_json_error('Invalid email address.');
    }

    wp_send_json_success("Your message has been sent successfully.\n Name:$name\n Email:$email\n Message:$message");
    //Submit to DB
    global $wpdb;
    $table_name = $wpdb->prefix . 'qc_contact_messages';

    $wpdb->insert(
        $table_name,
        [
            'name'    => $name,
            'email'   => $email,
            'message' => $message
        ],
        [
            '%s',
            '%s',
            '%s'
        ]
    );

    if ($wpdb->insert_id) {
        wp_send_json_success('Your message has been saved successfully.');
    } else {
        wp_send_json_error('Failed to save your message. Please try again.');
    }
}
add_action('wp_ajax_qc_submit_form', 'qc_handle_form_submission');
add_action('wp_ajax_nopriv_qc_submit_form', 'qc_handle_form_submission');

register_activation_hook(__FILE__, 'qc_create_db_table');
function qc_create_db_table() {
    global $wpdb;
    $table_name = $wpdb->prefix.'qc_contact_form';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email VARCHAR(100) NOT NULL,
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
}
?>