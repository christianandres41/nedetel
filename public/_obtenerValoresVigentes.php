<?php

$resultado = array();
if (isset($args[0]) && !empty($args[0])) {
    $ate_id = intval($args[0]);
    $codigos = array();

    $result = q("
        SELECT *
        ,concat(vae_texto, vae_numero, to_char(vae_fecha, 'yyyy-MM-dd'), vae_nodo, vae_conexion, vae_ciudad, to_json(vae_nodos)) AS valor
    , (
        SELECT nod_codigo
        FROM 
        sai_nodo
        , sai_ubicacion
        WHERE 
        nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND nod_id = vae_nodo
        AND ubi_id = nod_ubicacion
    ) AS nodo
    , (
        SELECT ciu_nombre 
        FROM 
         sai_ciudad
        WHERE 
        ciu_borrado IS NULL
        AND ciu_id = vae_ciudad
    ) AS ciudad
        FROM sai_paso_atencion
        , sai_valor_extra
        , sai_campo_extra
        WHERE paa_borrado IS NULL
        AND vae_borrado IS NULL
        AND cae_borrado IS NULL
        AND vae_paso_atencion = paa_id
        AND vae_campo_extra = cae_id
        AND NOT paa_paso_anterior IS NULL
        AND paa_atencion = $ate_id
        ORDER BY vae_creado
    ");

    $etiquetas = array();
    $result_etiquetas = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='cae_codigo_etiquetas'");
    if ($result_etiquetas) {
        $etiquetas = $result_etiquetas[0]['cat_texto'];
        $etiquetas = (array) json_decode($etiquetas, true);
    }
    //var_dump($etiquetas);


        //SELECT concat(nod_codigo, ': ',  nod_descripcion, ' (', ubi_direccion, ')')
    if ($result){
        foreach($result as $r){
            if ($r[valor] === '0' || !empty($r[valor])) {
                $codigo = $r[cae_codigo];
                $codigo = str_replace('_', ' ', $codigo);
                $codigo = ucfirst(strtolower($codigo));
                $etiqueta = isset($etiquetas[$r[cae_codigo]]) ? $etiquetas[$r[cae_codigo]] : $codigo;
                $nodo = $r[nodo];
                $ciudad = $r[ciudad];
                $valor = $r[valor];
                $valor_detallado = (empty($nodo) && empty($ciudad)) ? $valor : $nodo . $ciudad;

                $codigos[$r['cae_codigo']] = array(
                    'codigo' => $codigo
                    , 'etiqueta' => $etiqueta
                    , 'valor' => $valor
                    , 'valor_detallado' => $valor_detallado
                    , 'nodo' => $nodo
                    , 'ciudad' => $ciudad

                );
            }
        }
    }
    $resultado = array_values($codigos);
}
echo json_encode($resultado);
