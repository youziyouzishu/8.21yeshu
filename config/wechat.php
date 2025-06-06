<?php

return [
    'DriverMiniApp'=>[
        'app_id' => 'wx28afe61b57804d1a', // 开放平台账号的 appid
        'secret' => 'b5bf322ad37bb43f620528adc7968a70',   // 开放平台账号的 secret
        'response_type' => 'object',
        'log' => [
            'level' => 'debug',
            'file' => './runtime/logs/wechat.log',
        ],
    ],
    'TransportMiniApp'=>[
        'app_id' => 'wx4504f42b53ac9046',
        'secret' => 'b032d86a0042b0745990986d8b74200a',
        'response_type' => 'object',
        'log' => [
            'level' => 'debug',
            'file' => './runtime/logs/wechat.log',
        ],
    ],
    'UserMiniApp'=>[
        'app_id' => 'wx65be4a4d436b7d2c',
        'secret' => '2bd487fe7dc0b012abb8a84d382a2774',
        'response_type' => 'object',
        'log' => [
            'level' => 'debug',
            'file' => './runtime/logs/wechat.log',
        ],
    ]
];
