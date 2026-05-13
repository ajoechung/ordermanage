<?php
return [
    'upload_path'    => $_ENV['UPLOAD_PATH'] ?? 'uploads',
    'max_file_size'  => (int)($_ENV['UPLOAD_MAX_FILE_SIZE'] ?? 10485760),
    'allowed_ext'    => explode(',', $_ENV['UPLOAD_ALLOWED_EXT'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'),
    'image_ext'      => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
    'file_ext'       => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'],
    'path_mode'      => 'date',
    'sub_dir'        => true,
];
