<?php
return [
    'enabled' => true,
    'log_requests' => true,
    'log_request_get_data' => false,
    'log_request_post_data' => false,
    'disallowed_post_parameters' => ['password', 'username'],
    'stack_trace_in_full_message' => false,
    'connection' => [
        'host' => '127.0.0.1',
        'port' => '8086',
        'db'=>'mydb',
        'measure'=>'user_logs'
    ]
];
