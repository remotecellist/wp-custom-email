# Custom Email Sender for Wordpress

Send custom HTML emails with multiple attachments from the WordPress admin dashboard.

## Features
- **Modern Admin UI**: Clean, responsive interface for sending emails.
- **Multiple Attachments**: Drag and drop system (file picker) with real-time validation.
- **Configurable Limits**: Set max file size, total size, and file count in settings.
- **Server Guard**: Automatically detects and enforces PHP server limits (`upload_max_filesize`, `post_max_size`).
- **Security Hardened**: Properly handles slashed characters (e.g., `spouse's`), prevents CC header injection, and uses late-escaping for all outputs.
- **Robust Type Checking**: Uses `fileinfo` with a `mime_content_type` fallback for secure file type validation.

## Changelog
### 1.1.3
- **UI Cleanup**: Removed redundant server limit info from the email form.

### 1.1.2
- **JS Limit Sync**: Client-side JavaScript limits are now strictly capped at current server capacity at enqueue time.
- **Server Guard**: Settings for file count are now properly capped by the server's `max_file_uploads` limit.
- **UI Improvement**: Added a "Server Capabilities" card to the settings page displaying all hard limits at a glance.

### 1.1.1
- **UI Improvement**: Changed settings inputs for file sizes from Bytes to MB for better readability.
- **Server Guard**: Added `max_file_uploads` detection to prevent exceeding server file count limits.
- **Bug Fix**: Corrected plugin author to Syed Badar.

### 1.1.0
- **Refactoring**: Split single-file plugin into `includes`, `templates`, and `assets` folders.
- **Settings Page**: Added a new settings page for administrative configuration.
- **Security Fix**: Implemented `wp_unslash()` to fix persistent slashes in input (e.g., `spouse\'s`).
- **Security Fix**: Added individual `sanitize_email()` for CC addresses to prevent header injection.
- **Security Fix**: Improved error output with late `esc_html()` escaping.
- **Edge Case**: Added `finfo` fallback logic with a hard stop if no MIME detection is available.
- **Performance**: Assets are now enqueued properly with version numbers.

### 1.0.0
- Initial release.
