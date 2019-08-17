<?php

$ruta_plantilla = 'uploads/plantilla_ordenes_2019.xlsx';
$ext = strtolower(pathinfo($ruta_plantilla, PATHINFO_EXTENSION));
//echo $ext;
if (file_exists($ruta_plantilla)) {
	
                                    if ($ext == 'xls' || $ext == 'xlsx') {
                                        //////////////
                                        //Excel

                                        //echo "sacando Excel <br>";

                                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta_plantilla);

                                        //$worksheet = $spreadsheet->getActiveSheet();
$result = q("
  select i.secuencial,
       d.dif_codigo,
       /*concat('''', cli_ruc),*/
	   cli_ruc::text,
       CASE
         WHEN length(cli_ruc) = 10 THEN
          'C'
         ELSE
          'R'
       END as TIPOIDE,
       cli_razon_social,
       cli_direccion_correspondencia,
       cli_representante_legal_email,
       cli_telefono,
       (SELECT prv_codigo
          FROM sai_provincia
         where prv_borrado IS NULL
           AND prv_id = cli_provincia) as prv_codigo,
       (SELECT substring(can_codigo, 3, 2)
          FROM sai_canton
         where can_borrado IS NULL
           AND can_id = cli_canton) as can_codigo,
       (SELECT substring(par_codigo, 5, 2)
          FROM sai_parroquia
         WHERE par_borrado IS NULL
           AND par_id = cli_representante_legal_parroquia) as par_codigo,
       to_char(date_trunc('month', now() + interval '3 days'), 'DD/MM/YYYY'),
       pep_codigo,
       1 cantidad,
              CASE
         WHEN (fecha_ejecucion is null or ((capacidad_facturada_nueva - capacidad_facturada_previa) = 0 AND dias_fact = 30) ) THEN
          CASE
            WHEN dif_bw = 0 THEN
             concat(ate_capacidad_facturada * dif_porcentaje / 100,
                    ' MB ',
                    ser_nombre)
            ELSE
             concat(dif_bw, ' MB ', ser_nombre)
          END
         ELSE
			 CASE
				WHEN (capacidad_facturada_nueva - capacidad_facturada_previa) > 0 THEN
				concat('PROPORCIONAL ',
                 capacidad_facturada_nueva - capacidad_facturada_previa,
                 ' MB ')
				ELSE
					concat('PROPORCIONAL ',
                 capacidad_facturada_nueva ,
                 ' MB ')
				 END
       END as DATOADICIONAL,
       CASE
         /*WHEN (fecha_ejecucion is not null) AND
              (capacidad_facturada_actual - capacidad_facturada_previa) = 0 THEN
          ROUND(prc_precio_mb * ate_capacidad_facturada, 4)*/
         WHEN (fecha_ejecucion is not null) AND (dias_fact is not null) AND
              (capacidad_facturada_nueva - capacidad_facturada_previa) > 0 THEN
          ROUND((prc_precio_mb / 30) *
                (capacidad_facturada_nueva - capacidad_facturada_previa) *
                dias_fact::numeric,
                4)
		WHEN (fecha_ejecucion is not null) AND (dias_fact is not null) AND
              (capacidad_facturada_nueva - capacidad_facturada_previa) = 0 THEN
		 CASE
            WHEN dif_bw = 0 THEN
             ROUND(((prc_precio_mb / 30) *
                (ate_capacidad_facturada * dif_porcentaje / 100) *
                dias_fact)::numeric,
                4)
            ELSE
             ROUND(((prc_precio_mb / 30) *
                (dif_bw) *
                dias_fact)::numeric,
                4)
          END
          
		WHEN (fecha_ejecucion is not null) AND
              (capacidad_facturada_nueva - capacidad_facturada_previa) = 0 THEN
          ROUND((prc_precio_mb / 30) *
                (ate_capacidad_facturada) *
                dias_fact::numeric,
                4)
        WHEN (fecha_ejecucion is not null AND dias_fact is null)
            THEN
          ROUND((prc_precio_mb / 30) *
                (capacidad_facturada_nueva - capacidad_facturada_previa) *
                (30 -date_part('days', fecha_ejecucion)::int +1),
                4)
         ELSE
          ROUND(prc_precio_mb * ate_capacidad_facturada, 4) 
       END as SUBTOTAL,
       pro_nombre_comercial 
       ,prc_precio_mb
	   ,prc_nombre
	   ,concat('ATE: ', ate_secuencial)
       ,inc.* 
  from sai_division_factura d
  JOIN (select row_number() OVER(order by dif_cuenta, dif_codigo) AS secuencial,
               dif_cuenta,
				dif_id,
               dif_codigo
          from sai_division_factura
         group by dif_id,dif_cuenta, dif_codigo) as i
    on i.dif_cuenta = d.dif_cuenta
   and d.dif_codigo = i.dif_codigo and d.dif_id = i.dif_id
  
  LEFT JOIN sai_atencion
  on dif_atencion = ate_id
   JOIN (
   /*  PROPORCIONALES DE INCREMENTOS EN EL MES*/
   SELECT * FROM (
   SELECT ate_id atencion,
					pro_nombre_comercial proveedor,
                    vae_fecha ::date as fecha_ejecucion,
                    pep_dia_del_corte +1 - date_part('days', vae_fecha ::date)::int dias_fact,
                    (select vae_numero from sai_valor_extra where vae_id = ( SELECT max(vae_id)
                       FROM sai_paso_atencion paa2
					   LEFT OUTER JOIN sai_transicion_estado_atencion tea2
                         ON tea2.tea_borrado IS NULL
                        AND tea2.tea_id = paa2.paa_transicion_estado_atencion
                       LEFT OUTER JOIN sai_valor_extra vae2
                         ON vae2.vae_borrado IS NULL
                        AND vae2.vae_paso_atencion = paa2.paa_id
                     
                       LEFT OUTER JOIN sai_campo_extra cae2
                         ON cae2.cae_id = vae2.vae_campo_extra
                      WHERE paa2.paa_borrado IS NULL
                        AND NOT paa2.paa_confirmado IS NULL
					    AND tea2.tea_estado_atencion_actual in (29,43,34)
						AND paa2.paa_atencion = ate_id
                        and cae2.cae_codigo = 'CAPACIDAD_FACTURADA'
            		AND vae2.vae_creado::date < vae.vae_creado::date
                       ) ) capacidad_facturada_previa,
                    (SELECT distinct vae2.vae_numero
                       FROM sai_paso_atencion paa2
					   LEFT OUTER JOIN sai_transicion_estado_atencion tea2
                         ON tea2.tea_borrado IS NULL
                        AND tea2.tea_id = paa2.paa_transicion_estado_atencion
                       LEFT OUTER JOIN sai_valor_extra vae2
                         ON vae2.vae_borrado IS NULL
                        AND vae2.vae_paso_atencion = paa2.paa_id
                     
                       LEFT OUTER JOIN sai_campo_extra cae2
                         ON cae2.cae_id = vae2.vae_campo_extra
                      WHERE paa2.paa_borrado IS NULL
                        AND NOT paa2.paa_confirmado IS NULL
					 	AND tea2.tea_estado_atencion_actual = 29
						AND paa2.paa_atencion = ate_id
                        and cae2.cae_codigo = 'CAPACIDAD_FACTURADA'
            		AND vae2.vae_creado > vae.vae_creado
                      ORDER BY 1 ASC limit 1 ) capacidad_facturada_nueva
               FROM sai_paso_atencion
               LEFT OUTER JOIN sai_transicion_estado_atencion
                 ON tea_borrado IS NULL
                AND tea_id = paa_transicion_estado_atencion
             
               LEFT OUTER JOIN sai_estado_atencion
                 ON esa_borrado IS NULL
                AND esa_id = tea_estado_atencion_actual,
              sai_valor_extra vae, sai_campo_extra, sai_atencion ate,
              sai_pertinencia_proveedor,
              sai_proveedor
              WHERE paa_borrado IS NULL
                AND ate_borrado IS NULL
                AND vae_borrado IS NULL
                AND cae_borrado IS NULL
                AND pep_id = ate_pertinencia_proveedor
                AND pro_id = pep_proveedor
                AND vae_paso_atencion = paa_id
                AND vae_campo_extra = cae_id
                AND NOT paa_confirmado IS NULL
                AND ate_id = paa_atencion
                AND cae_codigo = 'FECHA_EJECUCION'
                AND paa_atencion = ate_id
                AND (tea_estado_atencion_actual = 26 OR
                    tea_estado_atencion_actual IN
                    (SELECT esa_id
                        FROM sai_estado_atencion
                       WHERE esa_padre =
                             (SELECT esa_id
                                FROM sai_estado_atencion
                               WHERE esa_codigo = 'incrementos')) OR
                    tea_estado_atencion_actual IN
                    (SELECT esa_id
                        FROM sai_estado_atencion
                       WHERE esa_padre =
                             (SELECT esa_id
                                FROM sai_estado_atencion
                               WHERE esa_codigo = 'decrementos N')))
                AND paa_borrado IS NULL
                AND NOT paa_confirmado IS NULL
                AND vae_fecha >= date_trunc('month', now() + interval '3 days' ) + interval
              '1 day'
                AND vae_fecha < date_trunc('month', now() + interval '3 days' ) + interval
              '1 month'
                AND (pep_dia_del_corte - date_part('days', vae_fecha ::date)
                     ::int + 1) > 0
	  
              group by ate_capacidad_facturada,
                       ate_id,
					   ate.ate_id,
					   vae.vae_fecha,
					   vae.vae_creado,
                       pro_nombre_comercial,
                       fecha_ejecucion,
                       pep_dia_del_corte 
			) as a where capacidad_facturada_previa < capacidad_facturada_nueva
             
     UNION  /* ACTIVOS MES COMPLETO + INC/DEC EN PROCESO*/
             SELECT ate_id,
                    pro_nombre_comercial proveedor,
                    vae_texto ::date fecha_ejecucion,
                    (pep_dia_del_corte) dias_fact,
                    ate_capacidad_facturada capacidad_facturada_actual,
                    ate_capacidad_facturada capacidad_facturada_previa
               FROM sai_paso_atencion
               LEFT OUTER JOIN sai_transicion_estado_atencion
                 ON tea_borrado IS NULL
                AND tea_id = paa_transicion_estado_atencion
             
               LEFT OUTER JOIN sai_estado_atencion
                 ON esa_borrado IS NULL
                AND esa_id = tea_estado_atencion_actual,
              sai_valor_extra, sai_campo_extra, sai_atencion,
              sai_pertinencia_proveedor,
              sai_proveedor
              WHERE paa_borrado IS NULL
                AND ate_borrado IS NULL
                AND vae_borrado IS NULL
                AND cae_borrado IS NULL
                AND pep_id = ate_pertinencia_proveedor
                AND pro_id = pep_proveedor
                AND vae_paso_atencion = paa_id
                AND vae_campo_extra = cae_id
                AND NOT paa_confirmado IS NULL
                AND ate_id = paa_atencion
				AND sai_campo_extra.cae_codigo = 'FECHA_CREACION'::text 
				/*AND (sai_transicion_estado_atencion.tea_estado_atencion_actual IN ( SELECT sai_estado_atencion_1.esa_id
           FROM sai_estado_atencion sai_estado_atencion_1
          WHERE sai_estado_atencion_1.esa_nombre = 'Servicio Nuevo'::text  
				))*/
																																					 
                AND paa_atencion = ate_id
                AND paa_borrado IS NULL
                AND NOT paa_confirmado IS NULL
                and vae_texto  like '____-__-__'
                AND vae_texto::date < date_trunc('month', now() + interval '3 days' ) + interval
              '1 month'
			 /*and ate_capacidad_contratada=paa_capacidad_contratada*/
			 /*AND ate_estado_atencion = 26*/
			 AND ate_estado_atencion  
					IN ( SELECT sai_estado_atencion_1.esa_id
					FROM sai_estado_atencion sai_estado_atencion_1
					WHERE esa_padre in (4,31,32,33,45) ) /*Activo, INC, DEC, ANUL, SUSP*/
			AND ate_estado_atencion  
					NOT IN ( 27,28 ) /* Serv suspendido,anulado */
			 and ate_capacidad_facturada > 0
              group by ate_capacidad_facturada,
                       ate_id,
                       pro_nombre_comercial,
                       fecha_ejecucion,
                       pep_dia_del_corte
					   
					   
  UNION  /* ACTIVADOS PROPORCIONAL*/
             SELECT ate_id,
                    pro_nombre_comercial proveedor,
                    vae_texto ::date fecha_ejecucion,
                    (pep_dia_del_corte - date_part('days', vae_texto ::date)
                     ::int + 1) dias_fact,
                    ate_capacidad_facturada capacidad_facturada_actual,
                    (SELECT vae_numero
                       FROM sai_paso_atencion p2
                       LEFT OUTER JOIN sai_valor_extra
                         ON vae_borrado IS NULL
                        AND vae_paso_atencion = p2.paa_id
                     
                       LEFT OUTER JOIN sai_campo_extra
                         ON cae_id = vae_campo_extra
                      WHERE p2.paa_borrado IS NULL
                        AND NOT p2.paa_confirmado IS NULL
                        AND p2.paa_atencion = ate_id
                        and cae_codigo = 'CAPACIDAD_FACTURADA'
                      ORDER BY p2.paa_confirmado DESC limit 1) capacidad_facturada_previa
               FROM sai_paso_atencion
               LEFT OUTER JOIN sai_transicion_estado_atencion
                 ON tea_borrado IS NULL
                AND tea_id = paa_transicion_estado_atencion
             
               LEFT OUTER JOIN sai_estado_atencion
                 ON esa_borrado IS NULL
                AND esa_id = tea_estado_atencion_actual,
              sai_valor_extra, sai_campo_extra, sai_atencion,
              sai_pertinencia_proveedor,
              sai_proveedor
              WHERE paa_borrado IS NULL
                AND ate_borrado IS NULL
                AND vae_borrado IS NULL
                AND cae_borrado IS NULL
                AND pep_id = ate_pertinencia_proveedor
                AND pro_id = pep_proveedor
                AND vae_paso_atencion = paa_id
                AND vae_campo_extra = cae_id
                AND NOT paa_confirmado IS NULL
                AND ate_id = paa_atencion
                AND cae_codigo = 'FECHA_APROBACION_CLIENTE'
                AND paa_atencion = ate_id
                /*AND (tea_estado_atencion_actual = 43)*/
                AND paa_borrado IS NULL
                AND NOT paa_confirmado IS NULL
				and vae_texto  like '____-__-__'
                AND vae_texto::date >= date_trunc('month', now() + interval '3 days' ) + interval
              '1 day'
                AND vae_texto::date < date_trunc('month', now() + interval '3 days' ) + interval
              '1 month'
              group by ate_capacidad_facturada,
                       ate_id,
                       pro_nombre_comercial,
                       fecha_ejecucion,
                       pep_dia_del_corte
					   
   UNION /* ANULADOS, SUSP PROPORCIONAL*/

SELECT ate_id,
                    pro_nombre_comercial proveedor,
                    vae_fecha ::date fecha_ejecucion,
                    date_part('days', vae_fecha ::date) dias_fact,
					0 capacidad_facturada_previa,
                    ate_capacidad_facturada capacidad_facturada_actual
                    
               FROM sai_paso_atencion
               LEFT OUTER JOIN sai_transicion_estado_atencion
                 ON tea_borrado IS NULL
                AND tea_id = paa_transicion_estado_atencion
             
               LEFT OUTER JOIN sai_estado_atencion
                 ON esa_borrado IS NULL
                AND esa_id = tea_estado_atencion_actual,
              sai_valor_extra, sai_campo_extra, sai_atencion,
              sai_pertinencia_proveedor,
              sai_proveedor
              WHERE paa_borrado IS NULL
                AND ate_borrado IS NULL
                AND vae_borrado IS NULL
                AND cae_borrado IS NULL
                AND pep_id = ate_pertinencia_proveedor
                AND pro_id = pep_proveedor
                AND vae_paso_atencion = paa_id
                AND vae_campo_extra = cae_id
                AND NOT paa_confirmado IS NULL
                AND ate_id = paa_atencion
                AND paa_atencion = ate_id
                AND (ate_estado_atencion in (27,28)) /*Servicio Anulado/SuSP*/
				 AND 
                    tea_estado_atencion_actual IN
                    (36,47 ) /* Anulacion/ SUSP En proceso */
                AND paa_borrado IS NULL
                AND NOT paa_confirmado IS NULL
                AND vae_fecha::date >= date_trunc('month', now() + interval '3 days' ) + interval
              '1 day'
                AND vae_fecha::date < date_trunc('month', now() + interval '3 days' ) + interval
              '1 month'
              group by ate_capacidad_facturada,
                       ate_id,
                       pro_nombre_comercial,
                       fecha_ejecucion,
                       pep_dia_del_corte   
					   
					   ) INC
    ON ate_id = inc.atencion
 LEFT JOIN sai_pertinencia_proveedor
					 on ate_pertinencia_proveedor = pep_id  
 LEFT JOIN sai_proveedor
					 on pep_proveedor = pro_id
 LEFT JOIN sai_servicio
					 on pep_servicio = ser_id
 LEFT JOIN sai_cuenta
					on d.dif_cuenta = cue_id  
 LEFT JOIN sai_cliente
  on cue_cliente = cli_id
 LEFT JOIN (
 
	/* PRECIOS DIVISIONES ASIGNADAS A PADRE*/
	    SELECT
		dif_id,
		pre.prc_nombre,
		pre.prc_precio_mb
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
		LEFT JOIN (select prc_id,prc_cliente,prc_nombre,prc_servicio,prc_proveedor,prc_precio_mb from sai_precio_cliente p1
				  where prc_id IN (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= date_trunc('month', now() + interval '3 days' ) + interval '1 month' 
					and p2.prc_cliente=p1.prc_cliente and p2.prc_servicio=p1.prc_servicio and p2.prc_proveedor=p1.prc_proveedor order by prc_fecha_ejecucion desc, prc_creado desc
					/*fetch first 1 rows only*/) 
					
				  ) as pre
		on pre.prc_cliente=c.cue_cliente and pre.prc_servicio=pep_servicio and pre.prc_proveedor=pep_proveedor
		and pre.prc_id=ate_precio_cliente

        WHERE ate_borrado IS NULL
		AND c.cue_cliente=ate_cliente
		
		UNION   /* PRECIOS DIVISIONES ASIGNADAS A DEPENDENCIAS*/
		
		SELECT 		
		dif_id,
		pre.prc_nombre,
		pre.prc_precio_mb
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
		LEFT JOIN (select prc_id,prc_cliente,prc_nombre,prc_servicio,prc_proveedor,prc_precio_mb from sai_precio_cliente p1
				  where prc_id= (select prc_id from sai_precio_cliente p2
				  where prc_fecha_ejecucion <= date_trunc('month', now() + interval '3 days' ) + interval '1 month' 
					and p2.prc_cliente=p1.prc_cliente and p2.prc_servicio=p1.prc_servicio and p2.prc_proveedor=p1.prc_proveedor order by prc_fecha_ejecucion desc, prc_creado desc
					fetch first 1 rows only) 
					
				  ) as pre
		on pre.prc_cliente=c.cue_cliente and pre.prc_servicio=pep_servicio and pre.prc_proveedor=pep_proveedor
	
        WHERE ate_borrado IS NULL

		AND c.cue_cliente<>ate_cliente
					
				  ) as p
		on p.dif_id=d.dif_id
 where 
   cli_razon_social not ilike '%NEGOCIOS Y TELEFONIA NEDETEL S.A.%'
   AND ate_borrado IS NULL
   AND dif_borrado IS NULL

 order by 1, 15

");
										$i=2;
										foreach($result as $row){
											//echo $ext;
                                        $rowArray = array_values($row);
										$spreadsheet->getActiveSheet()
											->fromArray(
												$rowArray,   // The data to set
												NULL,        // Array values with this value will not be set
												"A$i"         // Top left coordinate of the worksheet range where
															 //    we want to set these values (default is A1)
											);
											$i++;
										}
                                        //$worksheet->getCell('A1')->setValue('John');
                                        //$worksheet->getCell('A2')->setValue('Smith');

                                        $nombre = 'REPORTE_ORDENES_'.date('Y_m_d').'.xlsx';
										$file	= "uploads/" . $nombre;
                                        //echo " [[NOMBRE: $nombre]]";
                                        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
                                        //$writer->setPreCalculateFormulas(true); 
                                        //$writer->save($file); 
                                        //$xls_generado = true;
								header('Pragma: public');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Cache-Control: private', false);
								header('Content-Transfer-Encoding: binary');
								header('Content-Disposition: inline; filename="'.$nombre.'";');
								header('Content-Type:  application/octet-stream');
								//header('Content-Length: ' . filesize($file));
										$writer->save("php://output");
										exit();

                                        //} else if ($ext == 'doc' || $ext == 'docx' || $ext == 'odt') { //no funciona con .doc, sale este error:  
                                        //                        ZipArchive::getFromName(): Invalid or uninitialized Zip object
                                    }
                                    //$respuesta['plantillas'][$pla_id]['adjuntos_generados'][] =  $dirname . $nombre;
                                } else {
                                    l('No existe el archivo plantilla: ' . $ruta_plantilla);
                                }

?>