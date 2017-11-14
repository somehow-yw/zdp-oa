<?php
if (empty($exception)) {
    echo json_encode([
        'code'      => $code,
        'data'      => $data,
        'message'   => $message,
    ]);
} else {
    echo json_encode([
        'code'      => $code,
        'data'      => $data,
        'message'   => $message,
        'exception' => $exception,
    ]);
}
