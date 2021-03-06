<?php

$respuestas = array();
$error = array();
$query = $args[0];
$ate_id = $args[1];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    /*
    $result = q("
        SELECT *
        FROM sai_nodo
        ,sai_ubicacion
        WHERE nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND nod_ubicacion = ubi_id
        AND (ubi_direccion ILIKE '%$query%' OR nod_codigo ILIKE '%$query%'  OR nod_descripcion ILIKE '%$query%')
        ORDER BY nod_codigo
    ");
     */

    $int_query = is_numeric($query) ? intval($query) : -1;

    $result = q("
        SELECT 
        *
		
        FROM sai_costo_proveedor
        ,sai_pertinencia_proveedor
	
	LEFT OUTER JOIN sai_atencion
        on pep_id=ate_pertinencia_proveedor


        WHERE cop_borrado IS NULL
        AND pep_borrado IS NULL
		AND cop_servicio=pep_id
        AND (
            cop_nombre ILIKE '%$query%'
	    OR pep_nombre ILIKE '%$query%'
            /*OR nod_codigo ILIKE '%$query%'
            OR nod_descripcion ILIKE '%$query%'*/
        )
        AND (
        ate_id=$ate_id 
        )
        ORDER BY pep_nombre
    ");

    /*
    $result = q("
        SELECT *
        FROM sai_nodo
        ,sai_ubicacion
        ,sai_atencion
        ,sai_estado_atencion
        WHERE nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND ate_borrado IS NULL
        AND esa_borrado IS NULL
        AND nod_ubicacion = ubi_id
        AND nod_atencion = ate_id
        AND ate_estado_atencion = esa_id
        AND NOT (
            esa_nombre ILIKE '%servicio activo%'
            OR esa_nombre ILIKE '%servicio suspendido%'
            OR esa_nombre ILIKE '%incremento%'
            OR esa_nombre ILIKE '%decremento%'
            OR esa_nombre ILIKE '%suspensión%'
        )
        AND (
            ubi_direccion ILIKE '%$query%'
            OR nod_codigo ILIKE '%$query%'
            OR nod_descripcion ILIKE '%$query%'
        )
        ORDER BY nod_codigo
    ");
     */



    /*
        AND (
            ate_nodo = nod_id
            OR ate_extremo = nod_id
        )
     * */
    if ($result) {
        $names = array();
        foreach($result as $r){
            //$respuesta = array('id' => $r['cop_id'], 'name' => ($r['cop_nombre'] . ': ' . $r['pep_nombre']));
            $respuesta = array('id' => $r['cop_id'], 'name' => ($r['cop_nombre']));
            if (!isset($names[$respuesta['name']])) {
                $respuestas[] = $respuesta; 
                $names[$respuesta['name']] = $respuesta['id'];
            }
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));

