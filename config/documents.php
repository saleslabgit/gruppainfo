<?php

$configuredMaximum = env('DOCUMENT_MAX_UPLOAD_KB');

return [
    'disk' => 'local',
    'max_upload_kb' => is_numeric($configuredMaximum) ? (int) $configuredMaximum : null,
];
