<?php

$respuestas = array();
$error = array();

$ate_id = $args[0];



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
        distinct (dif_codigo) id,
		concat('OP:',dif_codigo) as text
		
        FROM sai_division_factura
		
		JOIN sai_atencion
			on dif_atencion=ate_id
		JOIN sai_pertinencia_proveedor
			on ate_pertinencia_proveedor=pep_id
		JOIN sai_proveedor
			on pep_proveedor=pro_id
        JOIN sai_cliente
			on ate_cliente=cli_id
		and ate_cliente=(select ate_cliente from sai_atencion where ate_id=$ate_id)
		
		/*JOIN (select distinct pro_id proveedor from sai_atencion, sai_pertinencia_proveedor pro ,sai_proveedor where ate_pertinencia_proveedor=pep_id and pep_proveedor=pro_id
		 		and ate_id=$ate_id  ) w
			on pro_id=w.proveedor*/
			
		WHERE dif_borrado IS NULL
        AND ate_borrado IS NULL
		AND dif_codigo is not NULL
		
        ORDER BY 1
		/*and ate_pertinencia_proveedor=(select ate_pertinencia_proveedor from sai_atencion where ate_id=$ate_id)*/
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

            $respuestas = $result;
            
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }

//echo json_encode(array('lista' => $respuestas, 'error' => $error));
//echo json_encode(array($result));
echo json_encode(array('results' => $result));

