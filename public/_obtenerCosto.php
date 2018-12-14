<?php

$cop_id = $args[0];

$result = array();

if (!empty($cop_id)) {

    $result = q("
        SELECT *
        FROM sai_costo_proveedor
	
	LEFT OUTER JOIN sai_pertinencia_proveedor
          ON pep_borrado IS NULL
          AND pep_id = cop_servicio
        
	WHERE cop_borrado IS NULL
        AND cop_id = $cop_id
    ");
}

echo json_encode($result);
