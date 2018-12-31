<?php

$resultado = array();
$json = true;
if (isset($prc_id)) {
    $json = false;
} else if (isset($args[0]) && !empty($args[0])) {
    $prc_id = intval($args[0]);
} else {
    $prc_id = null;
}

if (!empty($prc_id)) {

    //SELECT concat(ate_secuencial, '. ', COALESCE(ate_codigo, '(sin ID)'))
    //trae los pasos 
    /*
     *
                WHEN nod_no_diferencia_puntos = 1 AND nod_atencion <> nod_atencion_referenciada
                THEN trim(concat('Servicio activo ', ate_secuencial, ' ', COALESCE(ate_codigo, '')))

                WHEN nod_no_diferencia_puntos = 0 AND nod_atencion <> nod_atencion_referenciada
                THEN concat(trim(concat('Servicio activo ', ate_secuencial, ' ', COALESCE(ate_codigo, ''))), ', punto ', nod_codigo)
     * */
    $result = q("
	        SELECT loc_creado,usu_username,loc_campo1,ser_nombre,pro_nombre_comercial,cli_razon_social,loc_campo5 valor,date(loc_campo6) fecha_vigencia,loc_campo7 detalle
         FROM 
             sai_log_cambios
             LEFT JOIN sai_proveedor ON pro_borrado IS NULL AND loc_campo3 = pro_id
             LEFT JOIN sai_cliente ON  cli_borrado IS NULL AND loc_campo4 = cli_id
	     LEFT JOIN sai_servicio ON ser_borrado IS NULL AND loc_campo2 = ser_id
	     LEFT JOIN sai_usuario ON loc_creado_por=usu_id
            WHERE 
                loc_borrado IS NULL
                AND loc_campo8 = {$prc_id}
        ORDER BY loc_creado ASC    
");

    /*
    // se comentó esta parte ya que quitaba los repetidos por distintos destinatarios, en su lugar se agregó el destinatario en el histórico de la atención
    if ($result) {
        $count_null = 0;
        //var_dump($result);
        //quita los valores vacios repetidos: es por pasos de transiciones de un mismo estado pero de distintos destinatarios
        $quiebre = null;
        foreach($result as $k => $r) {
            if ($quiebre != $r['esa_nombre']) {
                $count_null = 0;
            }
            $quiebre = $r['esa_nombre'];

            if (empty($r['cae_texto'])) {
                $count_null++;
                if ($count_null > 1 || (isset($result[$k+1]) && $result[$k+1]['esa_nombre'] == $r['esa_nombre'])) {
                    unset($result[$k]);
                }
            }
        }
    }
     */

    //var_dump($result);

    if ($result) {
        $resultado = array_values($result);
    }
}

if ($json) {
    echo json_encode($resultado);
}
