<?php
use think\facade\Env;

return [
    'upload_path'    => Env::get('upload.path', 'uploads'),
    'max_file_size'  => Env::get('upload.max_file_size', 10485760),
    'allowed_ext'    => explode(',', Env::get('upload.allowed_ext', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx')),
    'image_ext'      => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
    'file_ext'       => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'],
    'path_mode'      => 'date',
    'sub_dir'        => true,
];
