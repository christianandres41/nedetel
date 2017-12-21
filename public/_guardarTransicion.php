<?php

//header('Content-Type: application/json');


/*
$result = q("SELECT * FROM esamyn.esa_rol");
$roles = array();
foreach($result as $r){
    $roles[$r['rol_id']] = $r['rol_nombre']; 
}
 */

$error = '';
//var_dump($_POST);
//var_dump($_FILES);

//$desde = $_POST['desde'];
//$hacia= $_POST['hacia'];
foreach($_POST as $k => $v) {
    $$k = $v;
}

$pertinencia_usuario='null';

if (isset($usuario_responsable) && !empty($usuario_responsable) && isset($ser_id) && !empty($ser_id)) {
//    $pertinencia_usuario = "(
//        SELECT peu_id
//        FROM sai_pertinencia_usuario
//        WHERE peu_usuario = $usuario_responsable
//        AND peu_servicio = $ser_id
//    )";
    $pertinencia_usuario = $usuario_responsable; 
}
$pertinencia_proveedor='null';
if (isset($pro_id) && !empty($pro_id) && isset($ser_id) && !empty($ser_id)) {

    $pertinencia_proveedor = "(
        SELECT pep_id
        FROM sai_pertinencia_proveedor
        WHERE pep_proveedor = $pro_id
        AND pep_servicio = $ser_id
    )";
}


$automatico = (isset($automatico)) ? 1 : 0;
//echo "AUTOMATICO[$automatico]";
$tiempo_alerta_horas = (isset($tiempo_alerta_horas) && !empty($tiempo_alerta_horas)) ? $tiempo_alerta_horas : 0;

$comparacion_usuario = ($pertinencia_usuario == 'null') ? 'is' : '=';
$comparacion_proveedor = ($pertinencia_provedor == 'null') ? 'is' : '=';
$sql = ("
    UPDATE sai_transicion_estado_atencion 
    SET tea_borrado=now() 
    WHERE tea_borrado IS NULL
    AND tea_estado_atencion_padre=$desde 
    AND tea_estado_atencion_hijo=$hacia 
    AND tea_pertinencia_proveedor $comparacion_proveedor $pertinencia_proveedor
    AND tea_destinatario = $des_id
    RETURNING *
    ");
    //AND tea_pertinencia_usuario $comparacion_usuario $pertinencia_usuario
$result = q($sql);
//echo 'UPDATE'."[$sql] (";
//var_dump($result);
//echo ')UPDATE';
//return;
$tea_id_old = $result[0]['tea_id'];
$sql = ("
    INSERT INTO 
    sai_transicion_estado_atencion(
        tea_estado_atencion_padre,
        tea_estado_atencion_hijo,
        tea_pertinencia_usuario,
        tea_pertinencia_proveedor,
        tea_destinatario,
        tea_automatico,
        tea_tiempo_alerta_horas
    ) VALUES (
        $desde,
        $hacia,
        $pertinencia_usuario,
        $pertinencia_proveedor,
        $des_id,
        $automatico,
        $tiempo_alerta_horas
    ) RETURNING *
");
//echo $sql;
$result = q($sql);

//echo json_encode(array('data' =>$result, 'error' => $error));
//echo json_encode($result);

if ($result) {
    $tea_id = $result[0]['tea_id'];
    $asunto = (empty($asunto)) ? 'null' : "'$asunto'";
    $cuerpo = (empty($cuerpo)) ? 'null' : "'$cuerpo'";
    $pla_adjunto_texto = (empty($adjunto_texto)) ? 'null' : "'$adjunto_texto'";
    $pla_adjunto_nombre = (empty($adjunto_nombre)) ? 'null' : "'$adjunto_nombre'";
    $result_plantilla = q("
        INSERT INTO sai_plantilla (
            pla_transicion_estado_atencion,
            pla_asunto,
            pla_cuerpo,
            pla_adjunto_texto,
            pla_adjunto_nombre
        ) VALUES (
            $tea_id,
            $asunto,
            $cuerpo,
            $pla_adjunto_texto,
            $pla_adjunto_nombre
        ) RETURNING *
    ");

    if ($result_plantilla) {
        //var_dump($result_plantilla);
        //pasa los archivos a la nueva plantilla
        $pla_id = $result_plantilla[0]['pla_id'];
        q("
            UPDATE sai_adjunto_plantilla 
            SET adp_plantilla = $pla_id 
            WHERE adp_plantilla = (
                SELECT pla_id 
                FROM sai_plantilla 
                WHERE pla_transicion_estado_atencion=$tea_id_old
            )
        ");
        
        //guardado de archivo y registro de archivo en bdd
        $errormsg = null;
        $arc_id = null;
        $tipo_archivo = null;
        $error = $_FILES["archivo-adjunto"]["error"];
        $tmp_name = $_FILES["archivo-adjunto"]["tmp_name"];
        if (!empty($tmp_name)) {

            $nombre = basename($_FILES["archivo-adjunto"]["name"]);

            if ($error == UPLOAD_ERR_OK){
                $ruta = "uploads/".$nombre;
                if ( move_uploaded_file($tmp_name, $ruta) ) {
                    $md5 = md5_file($ruta);
                    $peso = $_FILES["archivo-adjunto"]["size"];
                    $tipo_archivo = $_FILES["archivo-adjunto"]["type"];

                    $result_archivo = q("
                        INSERT INTO sai_archivo (
                            arc_ruta,
                            arc_md5,
                            arc_nombre,
                            arc_descripcion,
                            arc_peso
                        ) VALUES (
                            '$ruta',
                            '$md5',
                            '$nombre',
                        'Plantilla de transicion $tea_id',
                        $peso
                        ) RETURNING *
                    ");
                    if ($result_archivo) {
                        $arc_id = $result_archivo[0]['arc_id'];
                    } else {
                        $errormsg .= "No se pudo crear el registro del archivo en la bdd.";
                    }
                } else {
                    $errormsg .= "Could not move uploaded file '".$tmp_name."' to '".$nombre."'<br/>\n";
                }
            } else {
                $errormsg .= "Upload error. [".$error."] on file '".$nombre."'<br/>\n";
            }
        }

        if (!empty($arc_id)) {
            //creacion de plantilla de archivo
            $pla_id = $result_plantilla[0]['pla_id'];
            $arc_id = (empty($arc_id)) ? 'null' : "$arc_id";
            $tipo_archivo = (empty($tipo_archivo)) ? 'null' : "'$tipo_archivo'";

            q("
                INSERT INTO sai_adjunto_plantilla (
                    adp_plantilla,
                    adp_archivo,
                    adp_tipo_archivo
                ) VALUES (
                    $pla_id,
                    $arc_id,
                    $tipo_archivo
                ) RETURNING *   
            ");
        }
    }
}

$args = array($desde, $hacia, $ser_id, $pro_id, $des_id);
require_once('_obtenerDetalleTransicion.php');