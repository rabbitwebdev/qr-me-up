<?php
/**
 * Plugin Name: QR Me Up
 * Description: Generate a downloadable QR Code from user-entered details via shortcode [qr_generator].
 * Version: 1.0
 * Author: Pip
 */

if (!defined('ABSPATH')) exit; // No direct access

class QR_Me_Up {

    public function __construct() {
        add_shortcode('qr_generator', [$this, 'render_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_qrme_generate', [$this, 'generate_qr']);
        add_action('wp_ajax_nopriv_qrme_generate', [$this, 'generate_qr']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('qrme-script', plugin_dir_url(__FILE__) . 'qrme-script.js', ['jquery'], '1.0', true);
        wp_localize_script('qrme-script', 'qrme_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
        wp_enqueue_style('qrme-style', plugin_dir_url(__FILE__) . 'qrme-style.css');
    }

    public function render_form() {
        ob_start(); ?>
        <div class="qrme-form">
            <h3>Generate Your QR Code</h3>
            <form id="qrme-generator-form">
                <label>Your Name:</label>
                <input type="text" name="name" required>
                <label>Email Address:</label>
                <input type="email" name="email" required>
                <label>Website URL:</label>
                <input type="url" name="url">
                <label>Message:</label>
                <textarea name="message"></textarea>
                <button type="submit">Generate QR Code</button>
            </form>
            <div id="qrme-result" style="margin-top:20px;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function generate_qr() {
        if (!isset($_POST['name']) || !isset($_POST['email'])) {
            wp_send_json_error('Missing data');
        }

        $data = "Name: " . sanitize_text_field($_POST['name']) . "\n";
        $data .= "Email: " . sanitize_email($_POST['email']) . "\n";
        $data .= "Website: " . esc_url_raw($_POST['url']) . "\n";
        $data .= "Message: " . sanitize_textarea_field($_POST['message']);

        // Use Google Chart API for quick QR generation
        $encoded_data = urlencode($data);
        $qr_url = "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl={$encoded_data}&choe=UTF-8";

        wp_send_json_success(['qr_url' => $qr_url]);
    }
}

new QR_Me_Up();
