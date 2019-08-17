<?php

//var_dump($_POST);
//return;
#require_once('/var/www/nedetel/vendor/phpmailer/phpmailer/PHPMailerAutoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
#require '/var/www/nedetel/vendor/phpmailer/phpmailer/src/Exception.php';
#require '/var/www/nedetel/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '/var/www/nedetel/private/config.php';
                try {
                    //MAIL
                    //echo "[[$pla_asunto - $pla_cuerpo]]";
                    $mail = new PHPMailer();
                    $mail->CharSet = 'UTF-8';
                    $mail->IsSMTP();
                    $mail->SMTPSecure = 'none';
                    $mail->SMTPAuth = true;

                    if ($es_zenix) {
                        $mail->Host = SMTP_SERVER_ZENIX;
                        $mail->Port = SMTP_PORT_ZENIX;
                        $mail->Username = SMTP_USERNAME_ZENIX;
                        $mail->Password = SMTP_PASSWORD_ZENIX;
                        $mail->SetFrom(MAIL_ORDERS_ADDRESS_ZENIX, MAIL_ORDERS_NAME);
                    } else {
                        $mail->Host = SMTP_SERVER;
                        $mail->Port = SMTP_PORT;
                        $mail->Username = SMTP_USERNAME;
                        $mail->Password = SMTP_PASSWORD;
                        $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
                    }

                    $mail->SMTPDebug = 2;
		$emails=array("christianandres41@gmail.com","jvera@nedetel.net");
		$emails=array("christianandres41@gmail.com");
		$cc='christianandres41@gmail.com';
		$asunto="Correo de prueba nuevo servidor";
		$mensaje="<br>Mensaje de pruebai 2";
                    if (!empty($cc)) {
                        foreach($cc as $email) {
                            if (!empty($email)) {
                                $mail->addCC($email);
                            }
                        }
                    }
                    $mail->Subject = $asunto;
                    $mail->MsgHTML($mensaje);

                    foreach ($emails as $email) {
                        if (!empty($email)) {
                            $mail->AddAddress($email);
                        }
                    }

                    //$mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
                    //$mail->AddBCC(MAIL_COPY_ALL_ADDRESS, MAIL_COPY_ALL_NAME);


                    if (!$mail->Send()) { 
                        throw new Exception($mail->ErrorInfo);
                    } else {
                        $confirmada_ejecucion_accion = true;

                    }
                } catch (Exception $e) {
                    //echo $e->getMessage();
                    echo ('Error en ' . $e->getFile() . ', linea ' . $e->getLine() . ': ' . $e->getMessage());
                    echo json_encode(array('ERROR'=>$e->getMessage()));
                    return;
                }




