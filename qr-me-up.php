<?php
/**
 * Plugin Name: QR Me Up
 * Description: Generate downloadable QR codes locally and save them to the Media Library. Use shortcode [qr_generator].
 * Version: 2.0
 * Author: Pip
 */

if (!defined('ABSPATH')) exit;

class QR_Me_Up {

    public function __construct() {
        add_shortcode('qr_generator', [$this, 'render_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_qrme_generate', [$this, 'generate_qr']);
        add_action('wp_ajax_nopriv_qrme_generate', [$this, 'generate_qr']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('qrme-script', plugin_dir_url(__FILE__) . 'qrme-script.js', ['jquery'], '2.0', true);
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
        require_once plugin_dir_path(__FILE__) . 'lib/phpqrcode/qrlib.php';

        $name = sanitize_text_field($_POST['name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $url = esc_url_raw($_POST['url'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');

        if (empty($name) || empty($email)) {
            wp_send_json_error('Please provide name and email.');
        }

        $data = "Name: $name\nEmail: $email\nWebsite: $url\nMessage: $message";

        $upload_dir = wp_upload_dir();
        $qr_dir = $upload_dir['basedir'] . '/qr-me-up/';
        $qr_url_base = $upload_dir['baseurl'] . '/qr-me-up/';

        if (!file_exists($qr_dir)) {
            wp_mkdir_p($qr_dir);
        }

        $filename = 'qr_' . time() . '_' . sanitize_title($name) . '.png';
        $filepath = $qr_dir . $filename;

        // Generate the QR code image
        QRcode::png($data, $filepath, QR_ECLEVEL_L, 5);

        // Add to media library
        $filetype = wp_check_filetype($filename, null);
        $attachment = [
            'guid' => $qr_url_base . $filename,
            'post_mime_type' => $filetype['type'],
            'post_title' => 'QR Code for ' . $name,
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attach_id = wp_insert_attachment($attachment, $filepath);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);

        $qr_url = wp_get_attachment_url($attach_id);

        wp_send_json_success(['qr_url' => $qr_url]);
    }
}

new QR_Me_Up();
