<?php


//header('Content-Type: application/json');



if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
	//$dataset_json = "TEST";
}
$respuesta = array();
if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
	//$respuesta []= $dataset_json;
    //if (isset($dataset->codigo) && !empty($dataset->codigo)) {
        $id = ( (isset($dataset->id) && !empty($dataset->id)) ? $dataset->id : null);
        //$codigo = $dataset->codigo;

        if (isset($dataset->borrar) && !empty($dataset->borrar)) {
            $result = q("UPDATE sai_division_factura SET dif_borrado=now() WHERE dif_id=$id RETURNING *");
        } else if (isset($dataset->recuperar) && !empty($dataset->recuperar)) {
            $sql= ("SELECT COUNT(*) FROM sai_division_factura WHERE dif_borrado IS NULL AND dif_codigo='$codigo'");
            $result = q($sql);
            $count_cuentas_codigo = $result[0]['count']; 

            if ($count_cuentas_codigo == 0) {
                $result = q("UPDATE sai_division_factura SET dif_borrado=null WHERE dif_id=$id RETURNING *");
            } else {
                $result = array(array('ERROR'=>"No se puede recuperar, ya existe cuenta con codigo $codigo"));
            }
        } else {
            //guarda datos de cuenta

             
            //echo "[count_cuentas_codigo: $count_cuentas_codigo]";

            //if ($count_cuentas_codigo == 0) {
            if (empty($dataset->id)) {
                    //crea cuenta
                    $padre = empty($dataset->padre) ? 'null' : $dataset->padre;
                    $sql = ("
                        INSERT INTO sai_division_factura(
                            dif_atencion
                            ,dif_bw
                            ,dif_cuenta
                            ,dif_porcentaje
							,dif_creado_por
							,dif_ciudad
							,dif_direccion
							,dif_codigo
                        ) VALUES(
                            {$dataset->ate_id}
                            ,{$dataset->bw_cantidad}
                            ,{$dataset->hijo}
                            ,{$dataset->bw_proporcion}
							,{$_SESSION['usu_id']}
							,'{$dataset->txt_ciudad_nueva}'
							,'{$dataset->txt_direccion}'
							,'{$dataset->orden}'
                        ) RETURNING *
                    ");
                    //echo "[[$sql]]";
                    $result = q($sql);

            //} else if (!empty($id) && $count_cuentas_codigo == 1) {
            } else if (!empty($id)) {
                //actualiza sai_division_factura
				
                    //si no hay padre, o si el padre es distinto al propio hijo (para filtrar casos que una cuenta sea su propia padre):
                    //$campos = 'codigo,peso,padre,cliente,responsable_cobranzas,usuario_tecnico,contacto';
                    
                    $sql = ("
                        UPDATE sai_division_factura 
                        SET  
                        dif_bw = {$dataset->bw_cantidad}
                        ,dif_cuenta = {$dataset->hijo}
                        ,dif_porcentaje = {$dataset->bw_proporcion}
                        ,dif_actualizado_por = {$_SESSION['usu_id']}
						,dif_ciudad = '{$dataset->txt_ciudad_nueva}'
						,dif_direccion= '{$dataset->txt_direccion}'
						,dif_codigo= {$dataset->orden}
                        
                        WHERE dif_borrado IS NULL
                        AND dif_id = $id 
                        RETURNING *
                    ");
                    //echo $sql;
                    $result = q($sql);

            } else {
                //borra cuentas con codigo repetida
                $result = array(array('ERROR' => "Ya existe una cuenta con descripcion $codigo"));
            }
        }
    //} else {
    //    $result = array(array('ERROR' => 'No se ha enviado el codigo', 'dataset' => $dataset));
    // }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}

foreach($result[0] as $k => $v) {
    $respuesta[str_replace('cue_', '', $k)] = $v;
}
echo json_encode(array($respuesta));

