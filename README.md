# BackupTool скрипт для создания копий файлов и баз данных

Создание бекапов директорий и баз данных, сохранение в локальном хранилище, выгрузка на удаленный сервер через FTP или SSH.

## Требования

 * PHP 5.4
 * Unix OS

## Установка

### Загрузите библиотеку

    git clone git://github.com/Martyn911/BackupTask.git

### Конфигурация

    Все настройки выполняются в файле config.php
    Опции детально прокомментированы

### Использование

    Создайте файл, который нужно будет добавить в крон с нужным интервалом запуска.
    Пример:
    <?php
    require_once 'BackupTools.php';

    $backup= new martyn911\BackupTools();
    try {
        $backup->run();
    } catch (\Exception $e) {
        echo $e->getMessage();
    }


### Настройка cron заданий

    @daily  /usr/bin/php /path/to/cron.php
    @weekly /usr/bin/php /path/to/cron.php
    @monthly /usr/bin/php /path/to/cron.php