<?php


$result = q("
insert into sai_orden_pedido
  select i.secuencial ope_secuencial,
         d.dif_codigo ope_codigo,
         /*concat('''', cli_ruc),*/
         cli_ruc ::text ope_ruc,
         CASE
           WHEN length(cli_ruc) = 10 THEN
            'C'
           ELSE
            'R'
         END as OPE_TIPOIDE,
         cli_razon_social ope_razon_social,
         cli_direccion_correspondencia ope_direccion_correspondencia,
         cli_representante_legal_email ope_cli_representante_legal_email,
         cli_telefono ope_telefono,
         (SELECT prv_codigo
            FROM sai_provincia
           where prv_borrado IS NULL
             AND prv_id = cli_provincia) as ope_provincia,
         (SELECT substring(can_codigo, 3, 2)
            FROM sai_canton
           where can_borrado IS NULL
             AND can_id = cli_canton) as ope_canton,
         (SELECT substring(par_codigo, 5, 2)
            FROM sai_parroquia
           WHERE par_borrado IS NULL
             AND par_id = cli_representante_legal_parroquia) as ope_parroquia,
         date_trunc('month', now() + interval '3 days') ope_fecha,
         pep_codigo ope_pertinencia,
         1 ope_cantidad,
         CASE
           WHEN (capacidad_facturada_actual - capacidad_facturada_previa) = 0 or
                fecha_ejecucion is null THEN
            CASE
              WHEN dif_bw = 0 THEN
               concat(ate_capacidad_facturada * dif_porcentaje / 100,
                      ' MB ',
                      ser_nombre)
              ELSE
               concat(dif_bw, ' MB ', ser_nombre)
            END
           ELSE
            concat('PROPORCIONAL ',
                   capacidad_facturada_actual - capacidad_facturada_previa,
                   ' MB ')
         END as OPE_DATOADICIONAL,
         CASE
           WHEN (fecha_ejecucion is not null) AND
                (capacidad_facturada_actual - capacidad_facturada_previa) = 0 THEN
            ROUND(prc_precio_mb * ate_capacidad_facturada, 4)
           WHEN (fecha_ejecucion is not null) AND
                (capacidad_facturada_actual - capacidad_facturada_previa) > 0 THEN
            ROUND((prc_precio_mb / 30) *
                  (ate_capacidad_facturada - capacidad_facturada_previa) *
                  dias_fact,
                  4)
           WHEN (fecha_ejecucion is not null) AND
                (capacidad_facturada_actual - capacidad_facturada_previa) < 0 THEN
            ROUND((prc_precio_mb / 30) *
                  (capacidad_facturada_previa - ate_capacidad_facturada) *
                  (DATE_PART('days',
                             DATE_TRUNC('month', current_date + interval '3 days') +
                             '1 MONTH' ::INTERVAL - '1 DAY' ::INTERVAL) -
                  dias_fact - 1) ::numeric,
                  4)
           ELSE
            ROUND(prc_precio_mb * ate_capacidad_facturada, 4)
         END as OPE_SUBTOTAL,
         pro_nombre_comercial ope_nombre_comercial
  /*,prc_precio_mb,
  inc.* */
    from sai_division_factura d
    JOIN (select row_number() OVER(order by dif_cuenta, dif_codigo) AS secuencial,
                 dif_cuenta,
                 dif_id,
                 dif_codigo
            from sai_division_factura
           group by dif_id, dif_cuenta, dif_codigo) as i
      on i.dif_cuenta = d.dif_cuenta
     and d.dif_codigo = i.dif_codigo
     and d.dif_id = i.dif_id, sai_cliente, sai_atencion
    LEFT JOIN (SELECT dif_atencion atencion,
                      pro_nombre_comercial proveedor,
                      vae_fecha ::date fecha_ejecucion,
                      (pep_dia_del_corte -
                      date_part('days', vae_fecha ::date) ::int + 1) dias_fact,
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
                          AND p2.paa_atencion = dif_atencion
                          and cae_codigo = 'CAPACIDAD_FACTURADA'
                        ORDER BY p2.paa_confirmado DESC limit 1 offset 1) capacidad_facturada_previa
                 FROM sai_paso_atencion
                 LEFT OUTER JOIN sai_transicion_estado_atencion
                   ON tea_borrado IS NULL
                  AND tea_id = paa_transicion_estado_atencion
               
                 LEFT OUTER JOIN sai_estado_atencion
                   ON esa_borrado IS NULL
                  AND esa_id = tea_estado_atencion_actual,
                sai_valor_extra, sai_campo_extra, sai_atencion,
                sai_division_factura, sai_pertinencia_proveedor,
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
                  AND paa_atencion = dif_atencion
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
                                 WHERE esa_codigo = 'decrementos')))
                  AND paa_borrado IS NULL
                  AND NOT paa_confirmado IS NULL
                  AND vae_fecha >=
                      date_trunc('month', now() + interval '3 days') +
                      interval
                '1 day'
                  AND vae_fecha <
                      date_trunc('month', now() + interval '3 days') +
                      interval
                '1 month'
                  AND (pep_dia_del_corte -
                      date_part('days', vae_fecha ::date) ::int + 1) > 0
                group by ate_capacidad_facturada,
                         dif_atencion,
                         pro_nombre_comercial,
                         fecha_ejecucion,
                         pep_dia_del_corte
               
               UNION
               SELECT dif_atencion,
                      pro_nombre_comercial proveedor,
                      vae_fecha ::date fecha_ejecucion,
                      (pep_dia_del_corte -
                      date_part('days', vae_fecha ::date) ::int + 1) dias_fact,
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
                          AND p2.paa_atencion = dif_atencion
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
                sai_division_factura, sai_pertinencia_proveedor,
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
                  AND paa_atencion = dif_atencion
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
                                 WHERE esa_codigo = 'decrementos')))
                  AND paa_borrado IS NULL
                  AND NOT paa_confirmado IS NULL
                  AND vae_fecha >=
                      date_trunc('month', now() + interval '3 days') +
                      interval
                '1 day'
                  AND vae_fecha <
                      date_trunc('month', now() + interval '3 days') +
                      interval '1 month'
                group by ate_capacidad_facturada,
                         dif_atencion,
                         pro_nombre_comercial,
                         fecha_ejecucion,
                         pep_dia_del_corte) INC
      ON ate_id = inc.atencion, sai_pertinencia_proveedor, sai_proveedor,
   sai_servicio, sai_cuenta
    LEFT JOIN (select prc_id, prc_cliente, prc_nombre, prc_precio_mb
                 from sai_precio_cliente p1
                where prc_id =
                      (select prc_id
                         from sai_precio_cliente p2
                        where prc_fecha_ejecucion <= now()
                          and p2.prc_cliente = p1.prc_cliente
                        order by prc_fecha_ejecucion desc,
                                 prc_creado          desc fetch first 1 rows only)) as p
      on p.prc_cliente = cue_cliente
   where d.dif_cuenta = cue_id
     and cue_cliente = cli_id
     and dif_atencion = ate_id
     and ate_pertinencia_proveedor = pep_id
     and pep_servicio = ser_id
     and pep_proveedor = pro_id
     AND pep_nombre not ilike '%concentrador%'

");
			
 $respuesta=array();			
foreach($result[0] as $k => $v) {
    $respuesta[str_replace('ope_', '', $k)] = $v;
}
echo json_encode(array($respuesta))
?>