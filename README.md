# Cron-backup-db
Proyecto para realizar copias de seguridad y enviar por correo usando phpMailer


## Requisitos
- mysqldump
- gzip

## Instalación
Editar el archivo index.php
```sh
<?php
....
$config = array(
    "smtp" => array(
        'host' => "",
        'port' => 25,
        'username' => "soporte@.....",
        'password' => 'password'
    ),
    'destinos' => array("destino@..."),
    'ruta_backup' => '/home/backup',
    'mysql' => array(
        'user' => 'root',
        'password' =>''
    )
);
```

Para ejecutar:
```sh
php index.php
```

## crontab
Configurar crontab, ejecutar todos los días a las 11:30 pm

```sh
sudo crontab -e
30 23 * * * php /rutaArchivoCron/index.php
```