<?php

function q($sql, $callback = false) {
    global $conn;

    /*
    if (strpos($sql, 'SELECT') === false) {
    }
     */
    l('SQL: ' . $sql);
    $sql = str_replace("\n", ' ', $sql);
    $sql = str_replace("\r", ' ', $sql);

    $data = null;
    $result = pg_query($conn, $sql);
    if ($result) {
        if ($callback) {
            while($row = pg_fetch_array($result)){
                $callback($row);
            }
        } else {
            $data = pg_fetch_all($result);
            //var_dump($data);
            //$data = count($data) === 1 ? (count($data[0]) === 1 ? $data[0][0] : $data[0]) : $data;
        }
    } else {
        l(pg_last_error($conn) . " [$sql]");
    }

	//if(isset($data[0]['prc_id']))
	if(strpos($sql, 'INTO sai_costo_proveedor')>0 or strpos($sql,'INTO sai_precio_cliente')>0 or strpos($sql,'UPDATE sai_costo_proveedor')===0 or strpos($sql,'UPDATE sai_precio_cliente')===0 )
	log_precios($data[0]);

    return $data;
}

function l($texto){
    global $conn;
    $log = pg_escape_literal($texto);
    $usuario = ((isset($_SESSION['usu_id']) && !empty($_SESSION['usu_id'])) ? pg_escape_string($_SESSION['usu_id']) : 'null');
    $ip = ((isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? pg_escape_literal($_SERVER['REMOTE_ADDR']) : 'null');
    pg_send_query($conn, "INSERT INTO sai_log(log_texto, log_creado_por, log_ip) VALUES ($log, $usuario, $ip)");

}

function log_precios($data){
	global $conn;	
	$usuario = ((isset($_SESSION['usu_id']) && !empty($_SESSION['usu_id'])) ? pg_escape_string($_SESSION['usu_id']) : 'null');
	$ip = ((isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? pg_escape_literal($_SERVER['REMOTE_ADDR']) : 'null');
	$id=isset($data['cop_id'])? $data['cop_id'] : $data['prc_id'];
		$campo1=pg_escape_literal(isset($data['cop_nombre'])? $data['cop_nombre'] : $data['prc_nombre']);
                $campo2=isset($data['prc_servicio'])? $data['prc_servicio'] : 0;
                $campo3=isset($data['cop_servicio'])? $data['cop_servicio'] : $data['prc_proveedor'];
                $campo4=isset($data['prc_cliente'])? $data['prc_cliente'] : 0;
                $campo5=isset($data['cop_costo_mb'])? $data['cop_costo_mb'] : $data['prc_precio_mb'];
                $campo6="to_date('".(isset($data['cop_fecha_ejecucion'])? $data['cop_fecha_ejecucion'] : $data['prc_fecha_ejecucion'])."', 'YYYY-MM-DD hh24:mi:ss')";
                $campo7=pg_escape_literal(isset($data['cop_detalle'])? $data['cop_detalle'] : $data['prc_detalle']);
      	q("INSERT INTO sai_log_cambios(loc_creado_por, loc_ip, loc_campo1,loc_campo2,loc_campo3,loc_campo4,loc_campo5,loc_campo6,loc_campo7,loc_campo8) VALUES ($usuario, $ip,$campo1,$campo2,$campo3,$campo4,$campo5,$campo6,$campo7,$id)");

}

/*function log_precios($texto){
		global $conn;
		$usuario = ((isset($_SESSION['usu_id']) && !empty($_SESSION['usu_id'])) ? pg_escape_string($_SESSION['usu_id']) : 'null');
		$ip = ((isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? pg_escape_literal($_SERVER['REMOTE_ADDR']) : 'null');
		$sql=substr($texto,strpos($texto,'SET '), strpos($texto,'WHERE')-strpos($texto,'SET '));
        $campos=str_replace(',',',"',$sql);
        $campos=str_replace('SET ','"',$campos);
        $campos=str_replace('=','":',$campos);
        $campos=str_replace('to_timestamp','"to_timestamp',$campos);
        $campos=str_replace('),',')",',$campos);
        $campos=str_replace('," ',',',$campos);
        $campos=str_replace('(\'','(',$campos);
        $campos=str_replace('\',\'',',',$campos);
        $campos=str_replace('\')',')',$campos);
        $campos=str_replace('\'','"',$campos);
        $campos='{'.$campos.'}';
        $log=json_decode($campos);
		$campo1=pg_escape_literal($log->{'prc_nombre'});
		$campo2=$log->{'prc_servicio'};
		$campo3=$log->{'prc_proveedor'};
		$campo4=$log->{'prc_cliente'};
		$campo5=$log->{'prc_precio_mb'};
		$campo6=$log->{'prc_fecha_ejecucion'};
		$campo6=str_replace('(','(\'',$campo6);
		$campo6=str_replace(',','\',\'',$campo6);
		$campo6=str_replace(')','\')',$campo6);
		$campo7=pg_escape_literal($log->{'prc_detalle'});
		$campo8=substr($texto,strpos($texto,'prc_id =')+9, strpos($texto,'RETURNING')-strpos($texto,'prc_id =')-9);
        $log=pg_escape_literal($log->{'prc_nombre'});
	q("INSERT INTO sai_log_cambios(loc_creado_por, loc_ip, loc_campo1,loc_campo2,loc_campo3,loc_campo4,loc_campo5,loc_campo6,loc_campo7,loc_campo8) VALUES ($usuario, $ip,$campo1,$campo2,$campo3,$campo4,$campo5,$campo6,$campo7,$campo8)");
	
}*/

function c($codigo){
    $codigo = pg_escape_literal($codigo);
    $result = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo=$codigo");
    $resultado = '';
    if ($result) {
        $resultado = $result[0]['cat_texto'];
    }
    return $resultado;
}
