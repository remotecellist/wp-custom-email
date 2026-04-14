<?php
/**
 * Settings Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Custom Email Sender Settings</h1>

    <?php settings_errors(); ?>

    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2 class="title">Server Capabilities</h2>
        <p>The following are hard limits set by your PHP configuration. Your plugin settings should not exceed these.
        </p>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Directive</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>upload_max_filesize</code></td>
                    <td>
                        <?php echo esc_html(ini_get('upload_max_filesize')); ?>
                    </td>
                </tr>
                <tr>
                    <td><code>post_max_size</code></td>
                    <td><?php echo esc_html(ini_get('post_max_size')); ?></td>
                </tr>
                <tr>
                    <td><code>max_file_uploads</code></td>
                    <td><?php echo esc_html(ini_get('max_file_uploads')); ?></td>
                </tr>
                <tr>
                    <td><strong>Safe Max Ceiling (Size)</strong></td>
                    <td><strong><?php echo esc_html(size_format(CES_Utils::get_server_upload_limit())); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><strong>Safe Max Ceiling (Count)</strong></td>
                    <td><strong><?php echo esc_html(CES_Utils::get_server_file_count_limit()); ?> files</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <form method="post" action="options.php" style="margin-top: 20px;">
        <?php
        settings_fields('ces_settings_group');
        do_settings_sections('custom-email-sender-settings');
        submit_button();
        ?>
    </form>
</div>