<?php
/*
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '100M');
ini_set('max_input_time', '1000');
  */
ini_set('max_execution_time', '1000');
ini_set('memory_limit', '256M');
date_default_timezone_set('America/Lima');
require("phpMailer/class.smtp.php");
require("phpMailer/class.phpmailer.php");

class CronBackupDB
{

    private $pahtBd; //ruta del archivo cron
    private $baseDeDatos = array(
        'bd1' => 'test',
       // 'bd2' => 'nombre_db2'
    );

    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function getCarpetaBd()
    {
        $meses = array(
            1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril", 5 => "mayo", 6 => "junio",
            7 => "julio", 8 => "agosto", 9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"
        );
        $carpetaMesAnio = $meses[date("n")] . '-' . date("Y");
        return $this->pahtBd . $carpetaMesAnio;
    }


    public function realizarBackupBd($cliente)
    {
        $this->pahtBd = "{$this->config['ruta_backup']}/$cliente/";
        $carpetaMesAnio = $this->getCarpetaBd();
        if (!is_dir($carpetaMesAnio)) {
            mkdir($carpetaMesAnio, 0777, true);
        }
        $archivoSql = "{$carpetaMesAnio}/{$cliente}_copia_seguridad" . date("d-m-Y") . ".sql.gz";
        $bd = $this->baseDeDatos[$cliente];
        $mysql = $this->config['mysql'];
        exec("mysqldump --user={$mysql['user']} --password={$mysql['password']} $bd | gzip > $archivoSql");
        return $archivoSql;
    }

    public function enviarCorreo($archivos)
    {
        $smtp = $this->config['smtp'];
        $remitente = $smtp['username'];
        $asunto = "Backup BD " . date("d/m/Y");
        $mascaraRemitente = "Soporte";
        $mensajeContent = "backup realizado el " . date("d/m/Y H:i:s");

        $instMail = new PHPMailer();
        $instMail->IsSMTP(true);
        $instMail->SMTPAuth = true;
        $instMail->Host = $smtp['host'];
        $instMail->Port = $smtp['port'];
        $instMail->Username = $remitente;
        $instMail->Password = $smtp['password'];
        $instMail->From = $remitente; //de..
        //-------------------------------------------------
        $instMail->FromName = $mascaraRemitente;
        foreach ($this->config['destinos'] as $correo) {
            $instMail->AddAddress($correo); //para...
        }
        $instMail->Subject = $asunto;
        $instMail->Body = $mensajeContent;
        $instMail->IsHTML(true);
        foreach ($archivos as $archivo) {
            $nombreArchivo = basename($archivo);
            if (!is_file($archivo)) {
                return "no existe el archivo";
            }
            $instMail->AddAttachment($archivo, $nombreArchivo);
        }
        if ($instMail->Send()) {
            $msj = "ok";
        } else {
            $msj = "CorreoNoEnviado: " . $instMail->ErrorInfo;
        }
        return $msj;
    }
}
//cambiar la configuración
$config = array(
    "smtp" => array(
        'host' => "",
        'port' => 25,
        'username' => "soporte@.....",
        'password' => 'password'
    ),
    'destinos' => array("destino@gmail.com"),
    'ruta_backup' => '/home/backup',
    'mysql' => array(
        'user' => 'root',
        'password' =>''
    )
);
$cron = new CronBackupDB($config);
$files = array(
    $cron->realizarBackupBd("bd1"),
  //  $objCron->realizarBackupBd("bd2")
);
echo $cron->enviarCorreo($files);
//---------------------------------------------
//para realizar la prueba desde la consola ejecutar el siguiente comando:
// php /rutaArchivoCron/index.php
//crear crontab
//sudo crontab -e
//30 23 * * * php /rutaArchivoCron/index.php
//ejcutar todos los dias a las 11:30 pm
