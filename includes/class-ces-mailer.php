<?php
/**
 * Class for handling email sending logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class CES_Mailer
{

    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Process the email form submission
     *
     * @return array [success => bool, messages => array]
     */
    public function send_custom_email()
    {
        // 1. Initial nonce check must be done before this is called

        // 2. Unslash all POST data
        $post_data = wp_unslash($_POST);

        $to = sanitize_email($post_data['recipient']);
        $subject = sanitize_text_field($post_data['subject']);
        $message = wp_kses_post(wpautop($post_data['message']));
        $reply_to = sanitize_email($post_data['reply_to']);
        $cc_raw = sanitize_text_field($post_data['cc']);

        $errors = [];
        $attachments = [];

        // Validate recipient
        if (empty($to) || !is_email($to)) {
            $errors[] = 'Invalid recipient email address.';
        }

        // Validate reply-to
        if (!empty($reply_to) && !is_email($reply_to)) {
            $errors[] = 'Invalid reply-to email address.';
        }

        // Validate CC strings and sanitize each
        $cc_header_emails = [];
        if (!empty($cc_raw)) {
            $cc_emails = array_map('trim', explode(',', $cc_raw));
            foreach ($cc_emails as $cc_email) {
                $sanitized_cc = sanitize_email($cc_email);
                if (!is_email($sanitized_cc)) {
                    $errors[] = 'Invalid CC email address: ' . $cc_email; // We use raw here for display, templates will escape
                } else {
                    $cc_header_emails[] = $sanitized_cc;
                }
            }
        }

        // 3. Validate & process attachments
        if (!empty($_FILES['attachments']['tmp_name'])) {
            $upload_dir = wp_upload_dir();

            // Check for upload directory error
            if (!empty($upload_dir['error'])) {
                $errors[] = 'Upload directory error: ' . $upload_dir['error'];
            } else {
                $total_size = 0;
                $file_count = count(array_filter($_FILES['attachments']['tmp_name']));

                if ($file_count > $this->options['max_file_count']) {
                    $errors[] = 'Too many attachments. Maximum allowed is ' . $this->options['max_file_count'];
                } else {
                    foreach ($_FILES['attachments']['tmp_name'] as $index => $tmp_name) {
                        if (empty($tmp_name))
                            continue;

                        $upload_error = $_FILES['attachments']['error'][$index];
                        if ($upload_error !== UPLOAD_ERR_OK) {
                            $errors[] = 'Upload error for file: ' . $_FILES['attachments']['name'][$index] . ' (code ' . $upload_error . ')';
                            continue;
                        }

                        if (!is_uploaded_file($tmp_name)) {
                            $errors[] = 'Security check failed for file: ' . $_FILES['attachments']['name'][$index];
                            continue;
                        }

                        $original_name = $_FILES['attachments']['name'][$index];
                        $file_size = $_FILES['attachments']['size'][$index];

                        if ($file_size > $this->options['max_file_size']) {
                            $errors[] = $original_name . ' exceeds the size limit.';
                            continue;
                        }

                        $total_size += $file_size;
                        if ($total_size > $this->options['max_total_size']) {
                            $errors[] = 'Total attachment size limit exceeded.';
                            break;
                        }

                        // 4. MIME type check with fallback
                        $mime_type = $this->get_mime_type($tmp_name);
                        if (!$mime_type) {
                            $errors[] = 'Could not determine file type for ' . $original_name . '. Server lacks required extensions (fileinfo or mime_content_type).';
                            continue;
                        }

                        $allowed_types = $this->options['allowed_types'];
                        if (!array_key_exists($mime_type, $allowed_types)) {
                            $errors[] = $original_name . ' has a disallowed file type (' . $mime_type . ').';
                            continue;
                        }

                        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                        $allowed_exts = $allowed_types[$mime_type];
                        if (!in_array($ext, $allowed_exts, true)) {
                            $errors[] = $original_name . ' has a mismatched file extension.';
                            continue;
                        }

                        $safe_name = uniqid('ces_', true) . '_' . sanitize_file_name($original_name);
                        $dest = $upload_dir['path'] . '/' . $safe_name;

                        if (move_uploaded_file($tmp_name, $dest)) {
                            chmod($dest, 0600);
                            $attachments[] = $dest;
                        } else {
                            $errors[] = 'Failed to process attachment: ' . $original_name;
                        }
                    }
                }
            }
        }

        $success = false;
        if (empty($errors)) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            if (!empty($reply_to)) {
                $headers[] = 'Reply-To: ' . $reply_to;
            }
            foreach ($cc_header_emails as $cc_email) {
                $headers[] = 'Cc: ' . $cc_email;
            }

            if (wp_mail($to, $subject, $message, $headers, $attachments)) {
                $success = true;
            } else {
                $errors[] = 'Failed to send email. Please check your mail server configuration.';
            }
        }

        // 5. Cleanup with file_exists() guard
        foreach ($attachments as $file) {
            if (file_exists($file)) {
                wp_delete_file($file);
            }
        }

        return [
            'success' => $success,
            'messages' => $errors,
        ];
    }

    /**
     * Detect MIME type with fallback chain
     */
    private function get_mime_type($path)
    {
        if (extension_loaded('fileinfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            return $finfo->file($path);
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($path);
        }

        return false; // Hard stop
    }
}
