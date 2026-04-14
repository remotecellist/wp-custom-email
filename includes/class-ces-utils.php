<?php
/**
 * Utility functions for Custom Email Sender
 */

if (!defined('ABSPATH')) {
    exit;
}

class CES_Utils
{

    /**
     * Convert PHP ini size string (e.g. '2M', '1G') to bytes.
     *
     * @param string $size_str
     * @return int
     */
    public static function parse_ini_size($size_str)
    {
        $size_str = trim($size_str);
        $last = strtolower($size_str[strlen($size_str) - 1]);
        $size = (int) $size_str;

        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }

        return $size;
    }

    /**
     * Get the lesser of upload_max_filesize and post_max_size.
     *
     * @return int Bytes
     */
    public static function get_server_upload_limit()
    {
        $upload_max = self::parse_ini_size(ini_get('upload_max_filesize'));
        $post_max = self::parse_ini_size(ini_get('post_max_size'));

        return min($upload_max, $post_max);
    }

    /**
     * Get the PHP max_file_uploads limit.
     *
     * @return int
     */
    public static function get_server_file_count_limit()
    {
        $limit = ini_get('max_file_uploads');
        return $limit ? (int) $limit : 20; // PHP default is 20
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    public static function format_bytes($bytes)
    {
        return size_format($bytes);
    }
}
