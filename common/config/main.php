<?php
return [
	'name' => 'Attendance Taking System',
    'timeZone' => 'Asia/Singapore',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
