<div class="page-header">
<h1> Órdenes de pedido </h1>
</div>
<?php

/*
if (isset($_POST['respaldar']) && !empty($_POST['respaldar'])) {

    $respaldar = $_POST['respaldar'];

    if ($respaldar == 'bdd') {
        $nombre = 'sait-bdd-'. date('Ymd-His') . '.backup';
        $comando = ('export PGPASSWORD="'.$bdd_config['password'].'" && pg_dump --file "'.$nombre.'" --host "'.$bdd_config['host'].'" --port "'.$bdd_config['port'].'" --username "'.$bdd_config['user'].'" --no-password --verbose --role "'.$bdd_config['user'].'" --format=c --blobs "'.$bdd_config['dbname'].'"');
        //echo $comando;
        exec($comando . ' 2>&1', $output);

        if (file_exists($nombre) && filesize($nombre) > 100) {
            $size = round(filesize($nombre) / (1024 * 1024));
            echo "<div class='alert alert-success'><h3>Respaldo de la base de datos generado con éxito</h3>";
            echo "<a class='btn btn-warning' href='/$nombre'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> Descargar $nombre ($size MB)</a>";
            echo "</div>";
            l('Respaldo generado: ' . $nombre);
        } else {
            echo "<div class='alert alert-danger'>Hubo un error al generar el respaldo de la base de datos.</div>";
            l('ERROR al generar respaldo ' . $nombre);
        }
    }
    echo "<hr>";
}
*/

?>


<form method="POST" action="descargar_ordenes">
<input type="hidden" name="generar" value="ordenes_pedido">
<button class="btn btn-success"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Ver &oacute;rdenes (Mes <?php echo date('M/Y',time()+60*60*24*3);?>)</button>
</form>
<?php

$fecha_cargada=q("SELECT max(ope_fecha)::date fecha from sai_orden_pedido");
$fecha_vigente=date('Y-m',time()+60*60*24*3)."-01";
$disabled="";
if($fecha_cargada[0]["fecha"]>=$fecha_vigente){
		$disabled="disabled=true";
}

?>
<!--form method="POST" action=""-->
<input type="hidden" name="cargar" value="ordenes_pedido">
<button class="btn btn-primary" name=btn_cargar id=btn_cargar <?=$disabled?> onclick="cargar_ordenes()"><span class="glyphicon glyphicon-saved" aria-hidden="true"></span> Carga definitiva (Mes <?php echo date('M/Y',time()+60*60*24*3);?>)</button>
<!--/form-->

<script>
function cargar_ordenes(){
	if(confirm("¿Seguro desea realizar la carga definitiva de las órdenes?")){
    console.log('En _cargarOrdenes');
    $.get('/_cargarOrdenes/', function(data){
        console.log('/_cargarOrdenes/', data);
        data = JSON.parse(data);
        console.log('data:', data);
        if (data[0]){
			if (data['ERROR']){
                alert(data['ERROR']);
            } else {
                alert("Cargado exitosamente");
				$('#btn_cargar').attr("disabled", true);
            }
		} else {
                alert("Error al cargar");
            }
    });
	}
}
</script>
