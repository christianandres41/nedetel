<?php

$resultado = array();
$json = true;
if (isset($cop_id)) {
    $json = false;
} else if (isset($args[0]) && !empty($args[0])) {
    $cop_id = intval($args[0]);
} else {
    $cop_id = null;
}

if (!empty($cop_id)) {

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
	        SELECT loc_creado,usu_username,loc_campo1,pep_nombre,loc_campo5 valor,date(loc_campo6) fecha_vigencia,loc_campo7 detalle
         FROM 
                sai_log_cambios
		LEFT JOIN sai_pertinencia_proveedor on  pep_borrado IS NULL AND pep_id=loc_campo3
		LEFT JOIN sai_usuario ON usu_id=loc_creado_por
            WHERE 
                loc_borrado IS NULL
                AND loc_campo8 = {$cop_id}
		AND loc_campo2 = 0
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
