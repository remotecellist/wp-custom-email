<?php
/**
 * Admin Email Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>

    <?php
    // Output notices/errors if any
    if (!empty($this->errors)) {
        foreach ($this->errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
    }
    if (!empty($this->success_message)) {
        echo '<div class="notice notice-success"><p>' . esc_html($this->success_message) . '</p></div>';
    }
    ?>

    <form method="post" action="" enctype="multipart/form-data" id="email-sender-form">
        <?php wp_nonce_field('ces_send_email'); ?>

        <table class="form-table">

            <tr>
                <th scope="row"><label for="recipient">Recipient Email *</label></th>
                <td>
                    <input type="email" name="recipient" id="recipient" class="regular-text" required>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="reply_to">Reply-To Email</label></th>
                <td>
                    <input type="email" name="reply_to" id="reply_to" class="regular-text" placeholder="Optional">
                    <p class="description">If set, replies will go to this address instead of the default sender.</p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="cc">CC (Carbon Copy)</label></th>
                <td>
                    <input type="text" name="cc" id="cc" class="regular-text"
                        placeholder="email@example.com, another@example.com">
                    <p class="description">Comma-separated for multiple.</p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="subject">Subject *</label></th>
                <td>
                    <input type="text" name="subject" id="subject" class="regular-text" required>
                </td>
            </tr>

            <tr>
                <th scope="row"><label>Message *</label></th>
                <td>
                    <?php
                    wp_editor('', 'message', [
                        'textarea_rows' => 15,
                        'media_buttons' => false,
                        'teeny' => false,
                        'quicktags' => true,
                    ]);
                    ?>
                </td>
            </tr>

            <tr>
                <th scope="row">Attachments</th>
                <td>
                    <input type="file" id="file-picker" style="display:none;" multiple
                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">

                    <button type="button" class="button" id="add-files-btn">+ Add Files</button>

                    <p class="description" style="margin-top:6px;">
                        Max <strong>
                            <?php echo esc_html(size_format($this->options['max_file_size'])); ?>
                        </strong> per file &nbsp;·&nbsp;
                        <strong>
                            <?php echo esc_html(size_format($this->options['max_total_size'])); ?>
                        </strong> total &nbsp;·&nbsp;
                        Up to <strong>
                            <?php echo esc_html($this->options['max_file_count']); ?>
                        </strong> files.<br>
                        Allowed: JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP
                    </p>

                    <ul id="file-list" style="margin-top:10px; padding:0; list-style:none;"></ul>
                    <div id="file-inputs" style="display:none;"></div>
                </td>
            </tr>

        </table>

        <p class="submit">
            <input type="submit" name="ces_send_email_submit" id="submit" class="button button-primary"
                value="Send Email">
        </p>
    </form>
</div>