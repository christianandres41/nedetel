<?php

$resultado = array();
$json = true;
if (isset($ate_id)) {
    $json = false;
} else if (isset($args[0]) && !empty($args[0])) {
    $ate_id = intval($args[0]);
} else {
    $ate_id = null;
}

$filtro_atencion="";
$filtro_division="";

if($ate_id)
	$filtro_atencion="AND ate_id=".$ate_id;

if (isset($args[1]) && !empty($args[1])) {
	$filtro_division="AND dif_id=".$args[1];
	
}

if ((!empty($ate_id) or !empty($filtro_division))) {
    $codigos = array();


    $result = q("
					/*DIVISIONES ASIGNADAS A PADRE*/
	    SELECT * ,
		CASE WHEN dif_bw=0
		THEN concat(dif_porcentaje,'%')
		ELSE concat(dif_bw,' Mbps') END as dif_bw_asignado
		,dif_id
		,concat('Cuenta ',
        CASE WHEN count_hijos > 0 
            THEN 'padre' 
            ELSE  (
                CASE WHEN cue_padre IS NULL 
                    THEN 'independiente' 
                    ELSE 'hijo' 
                END
            )
        END, ' de ',cli_razon_social
    ) AS cue_codigo
        FROM sai_atencion
		JOIN sai_pertinencia_proveedor
			on ate_pertinencia_proveedor=pep_id
		JOIN sai_proveedor
			on pep_proveedor=pro_id
		JOIN sai_servicio
			on ser_id=pep_servicio
		JOIN sai_division_factura
			on dif_borrado IS NULL AND dif_atencion = ate_id
		JOIN (SELECT *
			,(
				SELECT count(*)
				FROM sai_cuenta AS hijos
				WHERE hijos.cue_borrado IS NULL
				AND hijos.cue_padre = padre.cue_id
			) AS count_hijos
			FROM sai_cuenta AS padre
			INNER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cue_cliente = cli_id) as c
			on c.cue_borrado IS NULL AND dif_cuenta = c.cue_id
		LEFT JOIN (select prc_id,prc_cliente,prc_nombre,prc_servicio,prc_proveedor from sai_precio_cliente p1
				  where prc_id IN (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= date_trunc('month', now() + interval '3 days' ) + interval '1 month' 
					and p2.prc_cliente=p1.prc_cliente and p2.prc_servicio=p1.prc_servicio and p2.prc_proveedor=p1.prc_proveedor order by prc_fecha_ejecucion desc, prc_creado desc
					/*fetch first 1 rows only*/) 
					
				  ) as p
		on p.prc_cliente=c.cue_cliente and p.prc_servicio=pep_servicio and p.prc_proveedor=pep_proveedor
		and p.prc_id=ate_precio_cliente
        
		
		
		/*LEFT JOIN (select prc_id,prc_cliente,prc_nombre from sai_precio_cliente p1
				  where prc_id= (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= now() 
					and p2.prc_cliente=p1.prc_cliente order by prc_fecha_ejecucion desc, prc_creado desc
					fetch first 1 rows only)
				  ) as p
		on p.prc_cliente=c.cue_cliente*/
		
		
		
        WHERE ate_borrado IS NULL
        $filtro_atencion
		$filtro_division
		AND c.cue_cliente=ate_cliente
		
		UNION   /*DIVISIONES ASIGNADAS A DEPENDENCIAS*/
		
		SELECT * ,
		CASE WHEN dif_bw=0
		THEN concat(dif_porcentaje,'%')
		ELSE concat(dif_bw,' Mbps') END as dif_bw_asignado
		,dif_id
		,concat('Cuenta ',
        CASE WHEN count_hijos > 0 
            THEN 'padre' 
            ELSE  (
                CASE WHEN cue_padre IS NULL 
                    THEN 'independiente' 
                    ELSE 'hijo' 
                END
            )
        END, ' de ',cli_razon_social
    ) AS cue_codigo
        FROM sai_atencion
		JOIN sai_pertinencia_proveedor
			on ate_pertinencia_proveedor=pep_id
		JOIN sai_proveedor
			on pep_proveedor=pro_id
		JOIN sai_servicio
			on ser_id=pep_servicio
		JOIN sai_division_factura
			on dif_borrado IS NULL AND dif_atencion = ate_id
		JOIN (SELECT *
			,(
				SELECT count(*)
				FROM sai_cuenta AS hijos
				WHERE hijos.cue_borrado IS NULL
				AND hijos.cue_padre = padre.cue_id
			) AS count_hijos
			FROM sai_cuenta AS padre
			INNER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cue_cliente = cli_id) as c
			on c.cue_borrado IS NULL AND dif_cuenta = c.cue_id
		LEFT JOIN (select prc_id,prc_cliente,prc_nombre,prc_servicio,prc_proveedor from sai_precio_cliente p1
				  where prc_id= (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= now() 
					and p2.prc_cliente=p1.prc_cliente and p2.prc_servicio=p1.prc_servicio and p2.prc_proveedor=p1.prc_proveedor order by prc_fecha_ejecucion desc, prc_creado desc
					fetch first 1 rows only) 
					
				  ) as p
		on p.prc_cliente=c.cue_cliente and p.prc_servicio=pep_servicio and p.prc_proveedor=pep_proveedor
        
		
		
		/*LEFT JOIN (select prc_id,prc_cliente,prc_nombre from sai_precio_cliente p1
				  where prc_id= (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= now() 
					and p2.prc_cliente=p1.prc_cliente order by prc_fecha_ejecucion desc, prc_creado desc
					fetch first 1 rows only)
				  ) as p
		on p.prc_cliente=c.cue_cliente*/
		
		
		
        WHERE ate_borrado IS NULL
        $filtro_atencion
		$filtro_division
		AND c.cue_cliente<>ate_cliente
		
        ORDER BY dif_creado
	");
    $etiquetas = array();
    $result_etiquetas = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='cae_codigo_etiquetas'");
    if ($result_etiquetas) {
        $etiquetas = $result_etiquetas[0]['cat_texto'];
        $etiquetas = (array) json_decode($etiquetas, true);
    }
    //var_dump($etiquetas);


        //SELECT concat(nod_codigo, ': ',  nod_descripcion, ' (', ubi_direccion, ')')


    //$resultado = array_values($codigos);
    $resultado = array_values($result);
}

if ($json) {
    echo json_encode($resultado);
}
