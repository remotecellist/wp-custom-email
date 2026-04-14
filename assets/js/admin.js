(function($) {
    'use strict';

    $(function() {
        const MAX_FILE_SIZE  = parseInt(cesData.max_file_size);
        const MAX_TOTAL_SIZE = parseInt(cesData.max_total_size);
        const MAX_FILE_COUNT = parseInt(cesData.max_file_count);
        const ALLOWED_EXTS   = cesData.allowed_extensions || [];

        const fileQueue  = [];
        const picker     = document.getElementById('file-picker');
        const addBtn     = document.getElementById('add-files-btn');
        const fileList   = document.getElementById('file-list');
        const fileInputs = document.getElementById('file-inputs');

        if (!picker || !addBtn) return;

        addBtn.addEventListener('click', function() {
            picker.value = '';
            picker.click();
        });

        picker.addEventListener('change', function() {
            Array.from(picker.files).forEach(function(file) {

                // Deduplicate
                const exists = fileQueue.some(function(e) {
                    return e.file.name === file.name &&
                           e.file.size === file.size &&
                           e.file.lastModified === file.lastModified;
                });
                if (exists) return;

                const id        = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const ext       = file.name.split('.').pop().toLowerCase();
                const validExt  = ALLOWED_EXTS.indexOf(ext) !== -1;
                const validSize = file.size <= MAX_FILE_SIZE;
                const totalSize = fileQueue.reduce(function(sum, e) { return sum + e.file.size; }, 0) + file.size;
                const validTotal = totalSize <= MAX_TOTAL_SIZE;
                const validCount = fileQueue.length < MAX_FILE_COUNT;

                let errorMsg = null;
                if (!validCount)  errorMsg = 'Max ' + MAX_FILE_COUNT + ' files reached';
                else if (!validExt)  errorMsg = 'File type not allowed';
                else if (!validSize) errorMsg = 'Exceeds ' + fmtSize(MAX_FILE_SIZE) + ' limit';
                else if (!validTotal) errorMsg = 'Total size would exceed ' + fmtSize(MAX_TOTAL_SIZE);

                const li = document.createElement('li');
                li.id    = id;

                if (errorMsg) {
                    // Show error row but don't add to queue or form inputs
                    li.classList.add('error');
                    li.innerHTML =
                        '<span class="file-name" title="' + escHtml(file.name) + '">' + escHtml(file.name) + '</span>' +
                        '<span class="file-error">' + escHtml(errorMsg) + '</span>' +
                        '<button type="button" class="remove-file" title="Dismiss">&times;</button>';
                    li.querySelector('.remove-file').addEventListener('click', function() { li.remove(); });
                    fileList.appendChild(li);
                    return;
                }

                // Valid file — create hidden input
                const dt    = new DataTransfer();
                dt.items.add(file);
                const input = document.createElement('input');
                input.type  = 'file';
                input.name  = 'attachments[]';
                input.files = dt.files;
                fileInputs.appendChild(input);

                fileQueue.push({ file: file, id: id, inputEl: input });

                li.innerHTML =
                    '<span class="file-name" title="' + escHtml(file.name) + '">' + escHtml(file.name) + '</span>' +
                    '<span class="file-size">' + fmtSize(file.size) + '</span>' +
                    '<button type="button" class="remove-file" title="Remove file">&times;</button>';

                li.querySelector('.remove-file').addEventListener('click', function() { removeFile(id); });
                fileList.appendChild(li);
            });
        });

        function removeFile(id) {
            const idx = fileQueue.findIndex(function(e) { return e.id === id; });
            if (idx === -1) return;
            fileQueue[idx].inputEl.remove();
            fileQueue.splice(idx, 1);
            const li = document.getElementById(id);
            if (li) li.remove();
        }

        function fmtSize(bytes) {
            if (bytes < 1024)    return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function escHtml(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    });

})(jQuery);
