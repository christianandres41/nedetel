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
/*
    $result = q("
        SELECT 
        max (dif_codigo) 
		
        FROM sai_division_factura

		JOIN sai_atencion
			on dif_atencion=ate_id
		
        JOIN sai_cliente
		on ate_cliente=cli_id
		and ate_cliente=(select ate_cliente from sai_atencion where ate_id=$ate_id)
		and ate_pertinencia_proveedor=(select ate_pertinencia_proveedor from sai_atencion where ate_id=$ate_id)
		
		
		JOIN sai_pertinencia_proveedor
			on ate_pertinencia_proveedor=pep_id

        WHERE dif_borrado IS NULL
        AND ate_borrado IS NULL
		AND dif_codigo is not NULL
		
        ORDER BY 1
    ");*/
	 $result = q("
        SELECT 
        max (dif_codigo) 
		
        FROM sai_division_factura
		
        ORDER BY 1
    ");

 
    if ($result) {

            $respuestas = $result;
            
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }

//echo json_encode(array('lista' => $respuestas, 'error' => $error));
//echo json_encode(array($result));
echo json_encode(array('results' => $result));

