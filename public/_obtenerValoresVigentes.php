<?php

$resultado = array();
if (isset($args[0]) && !empty($args[0])) {
    $ate_id = intval($args[0]);
    $codigos = array();

    $result = q("
        SELECT *
        ,concat(vae_texto, vae_numero, to_char(vae_fecha, 'yyyy-MM-dd'), vae_nodo, vae_conexion, vae_ciudad, to_json(vae_nodos)) AS valor
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
    if ($result){
        foreach($result as $r){
            if (!empty($r[valor])) {
                $codigo = $r[cae_codigo];
                $codigo = str_replace('_', ' ', $codigo);
                $codigo = ucfirst($codigo);

                $valor = $r[valor];
                $codigos[$r['cae_codigo']] = array('codigo' => $codigo, 'valor' => $valor);
            }
        }
    }
    $resultado = array_values($codigos);
}
echo json_encode($resultado);
