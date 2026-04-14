<?php
/**
 * Class for handling admin UI and settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class CES_Admin
{

    private $options;
    private $errors = [];
    private $success_message = '';

    public function __construct()
    {
        $this->load_options();

        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    private function load_options()
    {
        $defaults = [
            'max_file_size' => 2 * 1024 * 1024,
            'max_total_size' => 8 * 1024 * 1024,
            'max_file_count' => 10,
            'allowed_types' => [
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
                'image/gif' => ['gif'],
                'image/webp' => ['webp'],
                'application/pdf' => ['pdf'],
                'application/msword' => ['doc'],
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
                'application/vnd.ms-excel' => ['xls'],
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
                'application/vnd.ms-powerpoint' => ['ppt'],
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
                'text/plain' => ['txt'],
                'application/zip' => ['zip'],
                'application/x-zip-compressed' => ['zip'],
            ]
        ];

        $saved_options = get_option('ces_settings', []);
        $this->options = wp_parse_args($saved_options, $defaults);
    }

    public function add_menu()
    {
        add_menu_page(
            'Send Custom Email',
            'Send Email',
            'manage_options',
            'custom-email-sender',
            [$this, 'render_admin_page'],
            'dashicons-email',
            100
        );

        add_submenu_page(
            'custom-email-sender',
            'Settings',
            'Settings',
            'manage_options',
            'custom-email-sender-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        register_setting('ces_settings_group', 'ces_settings', [$this, 'sanitize_settings']);

        add_settings_section(
            'ces_main_section',
            'Upload Constraints',
            null,
            'custom-email-sender-settings'
        );

        add_settings_field(
            'max_file_size',
            'Max File Size (MB)',
            [$this, 'render_size_field'],
            'custom-email-sender-settings',
            'ces_main_section',
            ['label_for' => 'max_file_size', 'desc' => 'Individual file size limit in MB.']
        );

        add_settings_field(
            'max_total_size',
            'Max Total Size (MB)',
            [$this, 'render_size_field'],
            'custom-email-sender-settings',
            'ces_main_section',
            ['label_for' => 'max_total_size', 'desc' => 'Total size across all attachments in MB.']
        );

        add_settings_field(
            'max_file_count',
            'Max File Count',
            [$this, 'render_number_field'],
            'custom-email-sender-settings',
            'ces_main_section',
            ['label_for' => 'max_file_count', 'desc' => 'Maximum number of files allowed.']
        );
    }

    public function sanitize_settings($input)
    {
        $sanitized = [];
        $size_limit = CES_Utils::get_server_upload_limit();
        $count_limit = CES_Utils::get_server_file_count_limit();

        if (isset($input['max_file_size'])) {
            $val_mb = (float) $input['max_file_size'];
            $val_bytes = $val_mb * 1024 * 1024;
            $sanitized['max_file_size'] = min((int) $val_bytes, $size_limit);
        }

        if (isset($input['max_total_size'])) {
            $val_mb = (float) $input['max_total_size'];
            $val_bytes = $val_mb * 1024 * 1024;
            $sanitized['max_total_size'] = min((int) $val_bytes, $size_limit);
        }

        if (isset($input['max_file_count'])) {
            $val = (int) $input['max_file_count'];
            $sanitized['max_file_count'] = min($val, $count_limit);
        }

        return $sanitized;
    }

    public function render_size_field($args)
    {
        $name = $args['label_for'];
        $value_bytes = isset($this->options[$name]) ? $this->options[$name] : 0;
        $value_mb = round($value_bytes / 1024 / 1024, 2);
        echo '<input type="number" step="0.1" name="ces_settings[' . esc_attr($name) . ']" id="' . esc_attr($name) . '" value="' . esc_attr($value_mb) . '" class="regular-text">';
        if (!empty($args['desc'])) {
            echo '<p class="description">' . esc_html($args['desc']) . '</p>';
        }
    }

    public function render_number_field($args)
    {
        $name = $args['label_for'];
        $value = isset($this->options[$name]) ? $this->options[$name] : '';
        echo '<input type="number" name="ces_settings[' . esc_attr($name) . ']" id="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="regular-text">';
        if (!empty($args['desc'])) {
            echo '<p class="description">' . esc_html($args['desc']) . '</p>';
        }
    }

    public function enqueue_assets($hook)
    {
        if (strpos($hook, 'custom-email-sender') === false) {
            return;
        }

        wp_enqueue_style('ces-admin-style', CES_PLUGIN_DIR_URL . 'assets/css/admin.css', [], CES_VERSION);
        wp_enqueue_script('ces-admin-script', CES_PLUGIN_DIR_URL . 'assets/js/admin.js', ['jquery'], CES_VERSION, true);

        $server_size_limit = CES_Utils::get_server_upload_limit();
        $server_count_limit = CES_Utils::get_server_file_count_limit();

        wp_localize_script('ces-admin-script', 'cesData', [
            'max_file_size' => min((int) $this->options['max_file_size'], $server_size_limit),
            'max_total_size' => min((int) $this->options['max_total_size'], $server_size_limit),
            'max_file_count' => min((int) $this->options['max_file_count'], $server_count_limit),
            'allowed_extensions' => $this->get_all_extensions(),
        ]);
    }

    private function get_all_extensions()
    {
        $exts = [];
        foreach ($this->options['allowed_types'] as $mime => $m_exts) {
            $exts = array_merge($exts, $m_exts);
        }
        return array_unique($exts);
    }

    public function render_admin_page()
    {
        if (isset($_POST['ces_send_email_submit'])) {
            $this->handle_form_submission();
        }
        include CES_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function render_settings_page()
    {
        include CES_PLUGIN_DIR . 'templates/settings-page.php';
    }

    private function handle_form_submission()
    {
        // Nonce check first
        if (!check_admin_referer('ces_send_email')) {
            wp_die('Security check failed.');
        }

        $mailer = new CES_Mailer($this->options);
        $result = $mailer->send_custom_email();

        if ($result['success']) {
            $this->success_message = 'Email sent successfully!';
        } else {
            $this->errors = $result['messages'];
        }
    }
}
