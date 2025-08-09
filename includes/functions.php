<?php
function sfl_get_file_icon($file_type) {
    $icons = array(
        'pdf' => 'media-default',
        'doc' => 'media-document',
        'docx' => 'media-document',
        'xls' => 'media-spreadsheet',
        'xlsx' => 'media-spreadsheet',
        'ppt' => 'media-interactive',
        'pptx' => 'media-interactive',
        'jpg' => 'format-image',
        'jpeg' => 'format-image',
        'png' => 'format-image',
        'gif' => 'format-image',
        'mp3' => 'format-audio',
        'wav' => 'format-audio',
        'mp4' => 'format-video',
        'mov' => 'format-video',
        'zip' => 'media-archive',
        'rar' => 'media-archive',
        'txt' => 'media-text',
    );
    return isset($icons[$file_type]) ? $icons[$file_type] : 'media-default';
}

function sfl_format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}