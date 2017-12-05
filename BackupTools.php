<?php
namespace martyn911;

Class BackupTools
{
    private $settings;
    public function __construct($config_file = false)
    {
        $this->settings = include ($config_file ? $config_file : dirname(__FILE__) . '/config.php');
    }

    public function run(){
        $this->log('Запуск бекапирования');
        if($this->getOption('db.status')){
            $this->log('Запуск бекапирования баз данных');

            if(!file_exists($this->getOption('db.dump_path')) || !is_writable($this->getOption('db.dump_path'))){
                throw new \Exception('Error: Директория: ' . $this->getOption('db.dump_path') . ' не создана или нет прав на запись');
            }

            $conn = mysqli_connect($this->getOption('db.host'), $this->getOption('db.user'), $this->getOption('db.password'));
            if (!$conn) {
                throw new \Exception('Не удалось установить соединение с базой данных. Проверьте параметры подключения');
            }

            if(!$this->getOption('db.databases')){
                $cmd = 'mysql -u ' . $this->getOption('db.user') . ' -p' . $this->getOption('db.password') . ' -h ' . $this->getOption('db.host') . ' -e "SHOW DATABASES;" | tr -d "| " | grep -v Database';
                exec($cmd, $databases);
            } else {
                $databases = $this->getOption('db.databases');
            }

            $this->log('Всего баз: ' . count($databases));
            foreach ($databases as $database){
                if(in_array($database, $this->getOption('db.exclude_databases'))){
                    $this->log('Пропускаем базу: ' . $database);
                    continue;
                }
                $this->log('Дампим базу: ' . $database);

                $ignore_tables = $this->getOption('db.exclude_tables') ? ' --ignore-table=' . implode(' --ignore-table=', $this->getOption('db.exclude_tables')) : '';
                $dump_path = $this->getOption('db.dump_path') . date("h:i_d.m.Y") . '_' . $database . '.sql';
                $cmd = '@mysqldump -u @user -p@password -h @host @ignore_tables --skip-lock-tables @db_name > @dump_name';
                $cmd = strtr($cmd, [
                        '@mysqldump'        => $this->getOption('db.mysqldump_path'),
                        '@user'             => $this->getOption('db.user'),
                        '@password'         => $this->getOption('db.password'),
                        '@host'             => $this->getOption('db.host'),
                        '@db_name'          => $database,
                        '@ignore_tables'    => $ignore_tables,
                        '@dump_name'        => $dump_path
                    ]
                );

                $this->log('Команда: ' . $cmd);
                exec($cmd);

                if($this->getOption('db.upload_ftp')){
                    $this->uploadFTP($dump_path);
                }

                if($this->getOption('db.upload_ssh')){
                    $this->uploadSSH($dump_path);
                }

                if($this->getOption('db.delete_file')){
                    unlink($dump_path);
                }
            }
        }

        if($this->getOption('files.status')){
            $this->log('Запуск бекапирования директорий');
            if(!file_exists($this->getOption('files.dump_path')) || !is_writable($this->getOption('files.dump_path'))){
                throw new \Exception('Error: Директория: ' . $this->getOption('files.dump_path') . ' не создана или нет прав на запись');
            }

            if(!$this->getOption('files.path')){
                throw new \Exception('Error: Директории не заданы. Проверьте параметр files.path');
            }

            $ignore_dir = $this->getOption('files.exclude_path') ? '--exclude ' . implode(' --exclude ', $this->getOption('files.exclude_path')) : '';
            $filedump = $this->getOption('files.dump_path') . date("h:i_d.m.Y") . '_backup.tar.gz';
            $cmd = '@tar -zcf @filedump @directories @exclude';
            $cmd = strtr($cmd, [
                    '@tar'              => $this->getOption('files.tar_path'),
                    '@filedump'         => $filedump,
                    '@directories'      => implode(' ', $this->getOption('files.path')),
                    '@exclude'          => $ignore_dir
                ]
            );
            $this->log('Команда: ' . $cmd);
            exec($cmd);

            if($this->getOption('files.upload_ftp')){
                $this->uploadFTP($filedump);
            }

            if($this->getOption('files.upload_ssh')){
                $this->uploadSSH($filedump);
            }

            if($this->getOption('files.delete_file')){
                unlink($filedump);
            }
        }
    }

    public function uploadFTP($file){
        $this->log('Выгружаем файл на ftp: ' . $file);
        $conn = ftp_connect($this->getOption('upload.ftp.host'), $this->getOption('upload.ftp.port'));
        if(!$conn){
            throw new \Exception('Не удалось подключиться к FTP серверу: ' . $this->getOption('upload.ftp.host') . ':' . $this->getOption('upload.ftp.port'));
        }
        $login = ftp_login($conn, $this->getOption('upload.ftp.user'), $this->getOption('upload.ftp.password'));
        if(!$login){
            throw new \Exception('Не удалось авторизоваться на FTP сервере: ' . $this->getOption('upload.ftp.host') . ':' . $this->getOption('upload.ftp.port'));
        }
        // включение пассивного режима
        ftp_pasv($conn, true);
        if (ftp_put($conn, $this->getOption('upload.ftp.remote_path') . basename($file), $file, FTP_BINARY)) {
            $this->log("Файл $file успешно загружен");
        } else {
            $this->log("При закачке $file произошла проблема");
        }
        ftp_close($conn);
    }

    public function uploadSSH($file){
        $this->log('Выгружаем файл по SSH: ' . $file);
        $cmd = '@rsync --progress --bwlimit=@speed_limit -e "ssh -p @port -i @private_key" @source @user@@host:@destination';
        $cmd = strtr($cmd, [
                '@rsync'            => $this->getOption('upload.ssh.rsync_path'),
                '@speed_limit'      => $this->getOption('upload.ssh.speed_limit'),
                '@port'             => $this->getOption('upload.ssh.port'),
                '@private_key'      => $this->getOption('upload.ssh.private_key'),
                '@source'           => $file,
                '@user'             => $this->getOption('upload.ssh.user'),
                '@host'             => $this->getOption('upload.ssh.host'),
                '@destination'      => $this->getOption('upload.ssh.remote_path') . basename($file)
            ]
        );
        $this->log('Команда: ' . $cmd);
        exec($cmd);
    }

    public function getOption($key, $settings = false){
        $settings = $settings ?: $this->settings;
        if (($pos = strrpos($key, '.')) !== false) {
            $settings = $this->getOption(substr($key, 0, $pos), $settings);
            $key = substr($key, $pos + 1);
        }
        return (isset($settings[$key]) || array_key_exists($key, $settings)) ? $settings[$key] : null;
    }

    public function log($value){
        if($this->getOption('debug')){
            echo date("h:i:s d.m.Y") . ' ' . $value . "\n";
        }
    }
}
