<?php

$prc_id = $args[0];

$result = array();

if (!empty($prc_id)) {

    $result = q("
        SELECT *
        
	FROM sai_precio_cliente
	
	LEFT OUTER JOIN sai_cliente
          ON cli_borrado IS NULL
          AND cli_id = prc_cliente	

	LEFT OUTER JOIN sai_servicio
          ON ser_borrado IS NULL
          AND ser_id = prc_servicio
	
        WHERE prc_borrado IS NULL
        AND prc_id = $prc_id
    ");
}

echo json_encode($result);
