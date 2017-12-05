<?php
return [
    'debug' => true,
    'upload' => [
        //настройки для выгрузки бекапов на удаленный сервер с помощью ftp
        'ftp' => [
            'user' => '',
            'password' => '',
            'host' => '',
            'port' => 21,
            //путь на удаленном сервере
            'remote_path' => '/'
        ],
        //настройки для выгрузки бекапов на удаленный сервер с помощью rsync
        'ssh' => [
            'user' => 'root',
            'host' => '',
            'port' => 22,
            'private_key' => '/home/root/.ssh/id_rsa.pub',
            'remote_path' => '/backup/',
            //путь к rsync на сервере, можно узнать командой command -v rsync
            'rsync_path' => '/usr/bin/rsync',
            //лимит скорости в КБ, 0 - без ограничений
            'speed_limit' => '0'
        ]
    ],
    'db' => [
        //создавать копии баз
        'status' => true,
        //путь к mysqldump на сервере, можно узнать командой command -v mysqldump
        'mysqldump_path' => '/usr/local/mysql/bin/mysqldump',
        //директория для хранения дампов
        'dump_path' => dirname(__FILE__) . '/backups/db/',
        'user' => 'root',
        //пароль root пользователя MYSQL
        'password' => '',
        //хост базы данных
        'host' => 'localhost',
        //массив имен баз данных, если не задан берутся все базы
        'databases' => [],
        //массив имен баз данных, которые необходимо пропустить
        'exclude_databases' => ['information_schema','mysql','performance_schema','phpmyadmin'],
        //массив таблиц, которые необходимо пропустить: имя базы.имя таблицы
        'exclude_tables' => [],
        //выгрузить на ftp
        'upload_ftp' => false,
        //выгрузить по ssh
        'upload_ssh' => true,
        //удалить после выгрузки
        'delete_file' => true
    ],
    'files' => [
        //создавать копии директорий
        'status' => true,
        //путь к tar, можно узнать командой command -v tar
        'tar_path' => '/usr/bin/tar',
        //директория для хранения дампов
        'dump_path' => dirname(__FILE__) . '/backups/files/',
        //путь к диреториям которые необходимо заархивировать
        'path' => [
            '/home/root/mydir'
        ],
        //исключить директории
        'exclude_path' => [
            '/home/root/mydir/exclude'
        ],
        //выгрузить на ftp
        'upload_ftp' => true,
        //выгрузить по ssh
        'upload_ssh' => true,
        //удалить после выгрузки
        'delete_file' => true
    ]
];