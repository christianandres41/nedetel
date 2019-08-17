<?php
//echo ($_solo_lectura ? 'SI solo lectura' : 'NO solo lectura');
//echo '<pre>';
//var_dump($_SESSION['seguridades']);
//echo '</pre>';

$titulo_proceso = 'Servicios';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre = 'Servicio'))";

$filtro_cliente="";
if (isset($args[0]) && !empty($args[0])) {
    $razon_social = (trim($args[0]));
    $filtro_cliente = " AND cli_razon_social = '$razon_social'";
}

    $result_destinatarios = q("
        SELECT des_nombre FROM sai_destinatario
    ");
    //$destinatarios = array('cliente', 'proveedor', 'usuario');
    $destinatarios = array();
    if ($result_destinatarios) {
        foreach($result_destinatarios  as $r) {
            $destinatarios[] = $r['des_nombre'];
        }
    }
    $tipos_contactos = array();
    $result_tipos_contactos = q("
        SELECT *
        FROM sai_tipo_contacto
        WHERE tco_borrado IS NULL
    ");
    if ($result_tipos_contactos) {
        foreach ($result_tipos_contactos as $r) {

            $nombre = ($r['tco_nombre']);
            $nombre = strtolower($nombre);
            $nombre = str_replace(' ', '_', $nombre);
            $nombre = limpiar_nombre_archivo($nombre);
            $tipos_contactos[$nombre] = $r['tco_id'];
        }
    }
    //var_dump($tipos_contactos);
?>
<html style="height: auto; min-height: 100%;">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Starter</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect. -->
  <link rel="stylesheet" href="/css/skins/skin-blue.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<!--script type="text/javascript" src="chrome-extension://aggiiclaiamajehmlfpkjmlbadmkledi/lib/popup.js" async=""></script><script type="text/javascript" src="chrome-extension://aggiiclaiamajehmlfpkjmlbadmkledi/lib/tat_popup.js" async=""></script><script src="chrome-extension://hbhhpaojmpfimakffndmpmpndcmonkfa/generated/eval.js"></script--></head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<!-- body class="skin-blue sidebar-mini" style="height: auto; min-height: 100%;" -->
<body class="skin-blue-light sidebar-mini" style="height: auto; min-height: 100%;">
<?php

?>
<div class="wrapper" style="height: auto; min-height: 100%;">

  <!-- Main Header -->
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <!--div class="user-panel">
        <div class="pull-left image">
          <img src="/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div-->
<h4 class="header">CLIENTES</h4>
      <!-- search form (Optional) -->
      <form action="#" method="POST" id="busqueda" name="busqueda" onsubmit="p_enviar_busqueda(this)" class="sidebar-form">
        <div class="">
		<select required id="busqueda_query" name="busqueda_query" class="form-control combo-select2"  style="width:50%" onchange="p_enviar_busqueda(this.parentNode.parentNode);this.parentNode.parentNode.submit();">
        <option value="<?=$razon_social?>"><?=$razon_social?></option>
      </select>
          <!--input type="text" name="busqueda_query" id="busqueda_query" class="form-control typeahead-cliente" placeholder="Buscar..."-->
          


        </div>
		<span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
      </form>
      <!-- /.search form -->

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu tree" data-widget="tree">
         <!--li class="header">CLIENTES</li-->
<?php
/*$result = q("
    SELECT * 
    FROM sai_cliente ORDER BY cli_razon_social
");*/
    foreach($result as $r){
        echo <<<EOF
          <li><a href="proceso_facturacion/{$r['cli_razon_social']}">{$r['cli_razon_social']}</a></li>
EOF;
    }
	
    function p_tree($hijos, $texto = null, $esa_codigo_padre = null) {
        if (!empty($texto)) {
            echo <<<EOF
            <li class="treeview">
              <a href="#"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> <span>$texto</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
              </a>
              <ul class="treeview-menu">
EOF;
        }
        foreach ($hijos as $id => $hijo) {
            if (isset($hijo['hijos']) && !empty($hijo['hijos'])) {
                //no es hoja, tiene hijos
                $esa_codigo = (!empty($hijo['esa_codigo'])) ? $hijo['esa_codigo'] : $esa_codigo_padre;
                p_tree($hijo['hijos'], $hijo['esa_nombre'], $esa_codigo);
            } else {
                //es hoja
                echo <<<EOF
                    <li><a href="/{$esa_codigo_padre}/{$hijo['esa_id']}">{$hijo['esa_nombre']}</a></li>
EOF;
            }
        }
        if (!empty($texto)) {
            echo <<<EOF
                <li><a href="/{$esa_codigo_padre}">TODOS</a></li>
              </ul>
            </li>
EOF;
        }
    }
    #p_tree($tree[""]['hijos']);
    //echo "<pre>";
    //var_dump($tree[""]);

    //VERIFICA FILTRO DE DATOS, Y TITULO:
$filtro = isset($filtro) ? " AND tea_estado_atencion_actual IN $filtro" : '';
$filtro_raw = isset($filtro_raw) ? $filtro_raw : '';
$esa_id = isset($esa_id) ? intval($esa_id) : null;


#####################
	#ID PARA SERVICIOS ACTIVOS
	$esa_id=26;
	$filtro = " AND (tea_estado_atencion_actual = $esa_id
		OR tea_estado_atencion_actual IN (SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'incrementos'))
		OR tea_estado_atencion_actual IN (SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'decrementos'))
			)";
	
	
	$busqueda ='';
	//$filtro_busqueda="AND 1=0";
if (isset($args[1]) && !empty($args[1])) {
    $busqueda = pg_escape_string($args[1]);
    $busqueda = strtolower($busqueda);
    $ate_secuencial_busqueda = intval($busqueda);
    $filtro_busqueda = " AND ate_secuencial = $ate_secuencial_busqueda OR ate_codigo ILIKE '%{$busqueda}%'";
}

if (!empty($esa_id)) {
    $esa_nombre = q("SELECT esa_nombre FROM sai_estado_atencion WHERE esa_borrado IS NULL AND esa_id = $esa_id")[0]['esa_nombre'];
}
?>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="min-height: 368px;">
    <!--div style="padding: 20px 30px; background: rgb(243, 156, 18); z-index: 999999; font-size: 16px; font-weight: 600;"><a class="pull-right" href="#" data-toggle="tooltip" data-placement="left" title="Never show me this again!" style="color: rgb(255, 255, 255); font-size: 20px;">×</a><a href="https://themequarry.com" style="color: rgba(255, 255, 255, 0.9); display: inline-block; margin-right: 10px; text-decoration: none;">Ready to sell your theme? Submit your theme to our new marketplace now and let over 200k visitors see it!</a><a class="btn btn-default btn-sm" href="https://themequarry.com" style="margin-top: -5px; border: 0px; box-shadow: none; color: rgb(243, 156, 18); font-weight: 600; background: rgb(255, 255, 255);">Let's Do It!</a>
</div-->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
      <?=(isset($titulo_proceso) ? $titulo_proceso : 'Resultados de búsqueda')?>
      <span class="badge"><?=$razon_social?></span>
        <!--small>Optional description</small-->
      </h1>
      <!--ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol-->
    </section>

    <!-- Main content -->
    <section class="content container-fluid">

      <!--------------------------
        | Your Page Content Here |
        -------------------------->

<?php
//if (isset($_POST['estado']) && !empty($_POST['estado'])) {

$sql = ("
    SELECT * 
    ,e1.esa_nombre AS estado_actual
    ,e1.esa_id AS estado_actual_id
    ,e2.esa_nombre AS estado_siguiente
    ,e2.esa_id AS estado_siguiente_id
    ,e2.esa_orden AS estado_siguiente_orden
    ,(usu_tecnico.usu_nombres || ' ' || usu_tecnico.usu_apellidos) AS usu_tecnico_nombre
    ,(usu_comercial.usu_nombres || ' ' || usu_comercial.usu_apellidos) AS usu_comercial_nombre
    ,(
        SELECT to_char(paa_creado, 'YYYY-MM-DD ')
        FROM sai_paso_atencion
        WHERE paa_borrado IS NULL
        AND paa_atencion = ate_id
        ORDER BY paa_creado DESC
        LIMIT 1 
    ) AS fecha_vigencia
    ,(
        DATE_PART('day', now() - ate_fecha_cambio_estado)
    ) AS dias_suspencion
    , CASE WHEN ate_extremo is null THEN (
	SELECT ciu_nombre
        FROM sai_nodo

        LEFT OUTER JOIN sai_ubicacion
          ON ubi_borrado IS NULL
          AND nod_ubicacion = ubi_id

        LEFT OUTER JOIN sai_provincia
          ON prv_borrado IS NULL
          AND ubi_provincia = prv_id

        LEFT OUTER JOIN sai_canton
          ON can_borrado IS NULL
          AND ubi_canton = can_id

        LEFT OUTER JOIN sai_parroquia
          ON par_borrado IS NULL
          AND ubi_parroquia = par_id

        LEFT OUTER JOIN sai_ciudad
          ON ciu_borrado IS NULL
          AND ubi_ciudad = ciu_id

        WHERE nod_borrado IS NULL
        AND nod_id = ate_nodo
	) ELSE 
		(
	SELECT ciu_nombre
        FROM sai_nodo

        LEFT OUTER JOIN sai_ubicacion
          ON ubi_borrado IS NULL
          AND nod_ubicacion = ubi_id

        LEFT OUTER JOIN sai_provincia
          ON prv_borrado IS NULL
          AND ubi_provincia = prv_id

        LEFT OUTER JOIN sai_canton
          ON can_borrado IS NULL
          AND ubi_canton = can_id

        LEFT OUTER JOIN sai_parroquia
          ON par_borrado IS NULL
          AND ubi_parroquia = par_id

        LEFT OUTER JOIN sai_ciudad
          ON ciu_borrado IS NULL
          AND ubi_ciudad = ciu_id

        WHERE nod_borrado IS NULL
        AND nod_id = ate_extremo
	)

	END as ciu_nombre
    FROM sai_atencion

    LEFT OUTER JOIN sai_servicio
        ON ser_borrado IS NULL
        AND ate_servicio = ser_id

    LEFT OUTER JOIN sai_cuenta
        ON cue_borrado IS NULL
        AND cue_id = ate_cuenta

    LEFT OUTER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cli_id = ate_cliente

    LEFT OUTER JOIN sai_pertinencia_proveedor
        ON pep_borrado IS NULL
        AND ate_pertinencia_proveedor = pep_id

    LEFT OUTER JOIN sai_proveedor
        ON pro_borrado IS NULL
        AND pep_proveedor = pro_id

    LEFT OUTER JOIN sai_contacto
        ON con_borrado IS NULL
        AND ate_contacto = con_id

    LEFT OUTER JOIN sai_usuario AS usu_tecnico
        ON usu_tecnico.usu_borrado IS NULL
        AND usu_tecnico.usu_id = ate_usuario_tecnico

    LEFT OUTER JOIN sai_usuario AS usu_comercial
        ON usu_comercial.usu_borrado IS NULL
        AND usu_comercial.usu_id = ate_usuario_comercial


    INNER JOIN sai_transicion_estado_atencion
        ON tea_borrado IS NULL
        AND tea_pertinencia_proveedor = pep_id
        AND tea_estado_atencion_actual = ate_estado_atencion
        AND NOT tea_estado_atencion_siguiente IS NULL

    INNER JOIN sai_estado_atencion AS e1 
        ON e1.esa_borrado IS NULL
        AND tea_estado_atencion_actual = e1.esa_id
        AND (SELECT count(*) FROM sai_estado_atencion AS esa_hijos WHERE esa_hijos.esa_borrado IS NULL AND esa_hijos.esa_padre = e1.esa_id) = 0

    INNER JOIN sai_estado_atencion AS e2 
        ON e2.esa_borrado IS NULL
        AND tea_estado_atencion_siguiente = e2.esa_id
        AND (SELECT count(*) FROM sai_estado_atencion AS esa_hijos WHERE esa_hijos.esa_borrado IS NULL AND esa_hijos.esa_padre = e2.esa_id) = 0

    WHERE ate_borrado IS NULL
        $filtro
        $filtro_busqueda
		$filtro_cliente
		AND cli_razon_social not ilike '%NEGOCIOS Y TELEFONIA NEDETEL S.A.%'
    ORDER BY 
        ate_id ASC
");
if(!empty($filtro_cliente))
$result = q($sql);
//echo $sql;
if ($result) {
    $estado_actual = null;
    $estado_siguiente = null;
    $atenciones = array();
    foreach ($result as $r) {
        if (!isset($atenciones[$r[ate_id]])) {
            $atenciones[$r[ate_id]] = $r;
            $atenciones[$r[ate_id]]['estados_siguientes'] = array();
        }
        $atenciones[$r[ate_id]]['estados_siguientes'][$r[estado_siguiente_id]] = $r;

    }

    echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
    foreach ($atenciones as $ate_id => $atencion) {
        $estados_siguentes = $atencion['estados_siguientes'];
        $tea_id_actual = $atencion['tea_id'];
        $r = $atencion;

        $fecha_formateada = p_formatear_fecha($r['ate_creado']);
        $dias_suspencion = $r['dias_suspencion'];
        $estado_actual = empty($r[estado_actual]) ? 'ATENCION SIN ESTADO': $r[estado_actual];
        $codigo = empty($r[ate_codigo]) ? '' : "  ID: {$r[ate_codigo]}";
	$capacidad_facturada= empty($r[ate_capacidad_facturada]) ? "0 Mbps" : "  {$r[ate_capacidad_facturada]} Mbps";
	$ciudad = $r[ciu_nombre];
        echo <<<EOT
      <a name="atencion_{$r[ate_secuencial]}"></a>
<div class="panel panel-info panel-atencion" id="panel_atencion_{$r[ate_id]}"  xxxstyle="width:500px;">
  <div class="panel-heading">
    <div class="pull-right">
      $fecha_formateada
    </div>
    <h3 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_{$r[ate_secuencial]}" aria-expanded="false" aria-controls="collapse_{$r[ate_secuencial]}" >
        {$r[ate_secuencial]}. <strong>BW: $capacidad_facturada</strong>  {$r[ser_nombre]} ({$r[pro_nombre_comercial]}) <!--span> a {$r[cli_razon_social]} <span--> $codigo Ciudad: $ciudad
      </a>
    </h3>
  </div>

  <div id="collapse_{$r[ate_secuencial]}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_{$r[ate_secuencial]}">
EOT;

        $es_fin = false;
        foreach ($estados_siguentes as $estado_siguiente_id => $estado_siguiente) {
            if (strtolower($estado_siguiente[estado_siguiente]) === 'fin') {
                $es_fin = true;
            }
        }

        if (!$_solo_lectura && !$es_fin) {
            echo <<<EOT
      <div class="pull-right well" style="padding:20px;margin:20px;text-align:center;">
      <h4>Agregar división:</h4>
EOT;
            #foreach ($estados_siguentes as $estado_siguiente_id => $estado_siguiente) {
             #   $rsig = $estado_siguiente;
                echo <<<EOT
<form method="POST" onsubmit="p_nuevo({$r[cue_id]});return false;">
<input type="hidden" name="estado" value="{$rsig['estado_siguiente_id']}">
<input type="hidden" name="tea_id" value="{$rsig['tea_id']}">
<input type="hidden" name="id" value="{$rsig['ate_id']}">
<button class="btn btn-success" style="align:center;">
<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
 {$rsig['estado_siguiente']}
</button>
</form>
EOT;

            #}
            echo '</div>';
        }

        $glue = '';
        $contacto_empresa = '';
        if (!empty($r[con_telefono])) {
            $contacto_empresa .= $glue . $r[con_telefono];
            $glue = ', ';
        }

        if (!empty($r[con_celular])) {
            $contacto_empresa .= $glue . $r[con_celular];
            $glue = ', ';
        }

        if (!empty($r[con_correo_electronico])) {
            $contacto_empresa .= $glue . "<a href='mailto:{$r[con_correo_electronico]}'>{$r[con_correo_electronico]}</a>";
            $glue = ', ';
        }

      //<strong>Dependencia de empresas:</strong> {$r[cue_codigo]}
        echo <<<EOT
    <div class="panel-body">
      <!--span><strong>Dependencia de empresas:</strong> <span data-cue-id="{$r[cue_id]}" id="cuenta_{$r[ate_id]}"></span>
      <br>
      <span><strong>Servicio:</strong> {$r[ser_nombre]}
      <br>
	<span><strong>Proveedor:</strong> {$r[pro_nombre_comercial]}
      <br>
<span><strong>Razón social:</strong> {$r[cli_razon_social]}
      <br>
      <strong>Contacto de la empresa:</strong> {$r[con_nombres]} {$r[con_apellidos]} ({$contacto_empresa})
      <br>
      <strong>Usuario técnico:</strong> {$r[usu_tecnico_nombre]}
      <br>
      <strong>Usuario comercial:</strong> {$r[usu_comercial_nombre]}
      <br>
      <strong>Fecha de transición:</strong> {$r[fecha_vigencia]} </span-->
<div id="campos_estado_vigente_{$r[ate_id]}"></div>
      <div>&nbsp;</div>
	  <form id="form_{$r[ate_id]}"  xxxstyle="width:500px;">
<input id="pro_nombre_comercial{$r[ate_id]}" type=hidden value='{$r[pro_nombre_comercial]}'>
<input id="ate_servicio{$r[ate_id]}" type=hidden value='{$r[ser_nombre]}'>
<input id="ate_codigo{$r[ate_id]}" type=hidden value='{$codigo}'>
<input id="ate_ciudad{$r[ate_id]}" type=hidden value='{$ciudad}'>
<input id="ate_capacidad_facturada{$r[ate_id]}" type=hidden value='{$r[ate_capacidad_facturada]}'>
<input id="cli_razon_social" type=hidden value='{$r[cli_razon_social]}'>
<input id="cue_cliente" type=hidden value='{$r[cue_cliente]}'>
<input id="cue_id" type=hidden value='{$r[cue_id]}'>
</form>
<!--a class="btn btn-info" href="#" onclick="p_toggle_historico({$r[ate_id]}, {$r[ate_secuencial]});return false;">Mostrar historial</a-->
<!--
        <table id="tabla_historico_{$r[ate_id]}" style="width:400px;display:none;" class="table table-striped table-condensed table-hover">
        <tbody id="valores_historicos_{$r[ate_id]}">
EOT;

        /*
        $sql = ("
            SELECT *
            , concat(
                vae_texto
                ,vae_numero
                ,to_char(vae_fecha, 'yyyy-MM-DD hh:mm')
            ) AS valor
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
            FROM sai_campo_extra
            ,sai_paso_atencion
            ,sai_valor_extra
            ,sai_usuario
            ,sai_transicion_estado_atencion
            ,sai_estado_atencion
            WHERE cae_borrado IS NULL
            AND vae_borrado IS NULL
            AND paa_borrado IS NULL
            AND usu_borrado IS NULL
            AND tea_borrado IS NULL
            AND esa_borrado IS NULL
            AND vae_campo_extra = cae_id
            AND vae_paso_atencion = paa_id
            AND paa_creado_por = usu_id
            AND paa_transicion_estado_atencion = tea_id
            AND tea_estado_atencion_actual = esa_id
            AND paa_atencion={$r[ate_id]}
            AND NOT paa_confirmado IS NULL
            ORDER BY paa_id DESC, cae_orden
        ");
            //AND NOT paa_paso_anterior IS NULL//reemplazado por confirmado

            //AND paa_borrado IS NULL // ya agregado...
        //$result_campos = q($sql);
        if ($result_campos) {
            $paa = null;
            foreach($result_campos as $rdato){
                if ($paa != $rdato['paa_id']) {
                    $paa = $rdato['paa_id'];
                    $usuario = $rdato['usu_nombres'] . ' ' . $rdato['usu_apellidos'];
                    $estado = $rdato['esa_nombre'];
                    $fecha_formateada = p_formatear_fecha($rdato['paa_creado']);
                    echo <<<EOT
            <tr>
              <td class="bg-info" colspan=2>
                <strong>$estado</strong> por $usuario
                <br>
                {$fecha_formateada}
            </td>
            </tr>
EOT;
                }
                $label = ucfirst($rdato['cae_texto']);
                //$dato = $rdato['vae_texto'] . $rdato['vae_numero'] . $rdato['vae_fecha'] . $rdato['nodo'] .$rdato['ciudad'];
                if (empty($rdato['nodo'])) {
                    $dato = $rdato['valor'] . $rdato['ciudad'] ;
                } else {
                    $nod_id = $rdato['vae_nodo'];
                    $dato = <<<EOT
                    <a href="#" onclick="p_abrir_detalle_nodo($nod_id);return false;">{$rdato['nodo']}</a>
EOT;
                }
                echo <<<EOT
            <tr>
              <th style="width:50%;text-align:right;">$label:</th>
              <td style="text-align:center;" id="campo_historico_{$r[ate_id]}_{$rdato[cae_id]}">$dato</td>
            </tr>
EOT;
            }
        }
         */
        //echo "$sql";
        echo <<<EOT
        </tbody></table>
-->
      <div>&nbsp;</div>
    </div>
  </div>
</div>
EOT;
    }
    echo '</div>';
}
?>


    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane active" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:;">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:;">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="pull-right-container">
                    <span class="label label-danger pull-right">70%</span>
                  </span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->
      <!-- Stats tab content -->
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="pull-right" checked="">
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
  immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<?php /*if (isset($mostrar_nuevo) && $mostrar_nuevo && !$_solo_lectura): ?>
<!--a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a-->
<?php endif;*/ ?>
<!-- REQUIRED JS SCRIPTS -->

<!-- AdminLTE App -->
<script src="/js/adminlte.min.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. -->

<div id="modal_detalle_precio" class="modal fade autoalto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title"><span id="detalle_precio_titulo"></span></h4>
      </div>
      <div class="modal-body">
        <div class="form-horizontal" id="detalle_precio_contenido">
<div class="form-group">
          <?php $col1=2;$col2=4; ?>
		  
            <label class="col-sm-<?=$col1?> control-label">Descripción:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_nombre"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Valor:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_precio"></span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Fecha de vigencia:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_fecha_vigencia"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Fecha de creación:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_creado"></span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Servicio:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_servicio"></span>
            </div>
            <label class="col-sm-<?=$col1?> control-label">Empresa:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_cliente"></span>
            </div>

          </div>
		  
		  <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Creado por:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_precio_creado_por"></span>
            </div>
          </div>

          
        </div>
		</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div id="modal_historial" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Historial de <?=isset($titulo_proceso_singular)?$titulo_proceso_singular:'atención'?> <span id="historial_titulo"></span></h4>
      </div>
      <div class="modal-body">
        <div class="form-horizontal" id="contenido_historial">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<style>
.autoalto .form-control{
height:auto;
min-height: 30px;
overflow: hidden;
}
</style>


<div id="modal_division" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"> Facturacion <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
<!--input type="hidden" id="cue_id" name="cue_id" value=""-->
<input type="hidden" id="ate_id" name="ate_id" value="">
<input type="hidden" id="bw_disponible" name="bw_disponible">
  <!--div class="form-group">
    <label for="codigo" class="col-sm-4 control-label">Descripción:</label>
    <div class="col-sm-8">
      <input type="text" required class="form-control" id="codigo" name="codigo" placeholder="Codigo">
    </div>
  </div-->

  <div class="form-group">
    <label for="cliente" class="col-sm-4 control-label">Empresa:</label>
    <div class="col-sm-8">
      <select required id="cliente" name="cliente" class="form-control combo-select2" style="width:50%" onchange="" disabled=true>
        <option value="">&nbsp;</option>
      </select>
    </div>
	 </div>
	 <div class="form-group">
    <label for="txt_proveedor" class="col-sm-4 control-label">Proveedor:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_proveedor" disabled=true>
    </div>
	<label for="txt_servicio" class="col-sm-4 control-label">Servicio:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_servicio" disabled=true>
    </div>
	<label for="txt_ciudad" class="col-sm-4 control-label">Ciudad:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_ciudad" disabled=true>
    </div>
	<label for="txt_capacidad_facturada" class="col-sm-4 control-label">Capacidad facturada:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_capacidad_facturada" disabled=true>
    </div>
	<label for="txt_codigo" class="col-sm-4 control-label">Código:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_codigo" disabled=true>
    </div>
  </div>
 
 <div class="form-group">
    <label for="orden" class="col-sm-4 control-label">ID Orden:</label>
    <div class="col-sm-8">
      <select id="orden" name="orden" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
      </select>
    </div>
  </div>
 
  <div class="form-group">
    <label for="hijo" class="col-sm-4 control-label">Dependiencia a facturar:</label>
    <div class="col-sm-8">
      <select id="hijo" name="hijo" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label for="modo_facturacion" class="col-sm-4 control-label">Modalidad:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" onchange="toggle_campos(this)" style="width: 50%" id="modo_facturacion" name="modo_facturacion" tabindex="-1" aria-hidden="true">
        <option value="bw_proporcion" selected=true>Porcentaje(%)</option>
		<option value="bw_cantidad">BW(Mbps)</option>
      </select> 
    </div>
  </div>
  
      <div id="div_bw_proporcion" class="form-group" >
    <label for="proporcion" class="col-sm-4 control-label">% Facturado:</label>
    <div class="col-sm-8">
      <input type="number" required class="form-control" id="bw_proporcion" name="bw_proporcion" placeholder="Porcentaje facturado" value=0>
    </div>
  </div>
  
  <div id="div_bw_cantidad" class="form-group" hidden=true>
    <label for="peso" class="col-sm-4 control-label">Mbps Facturado:</label>
    <div class="col-sm-8">
      <input type="number" required class="form-control" id="bw_cantidad" name="bw_cantidad" placeholder="Ancho de banda" value=0 min=0 step=0.1>
    </div>
  </div>
  
	<div class="panel-group">
	<div class="panel">
		<div class="">
	  <a data-toggle="collapse" href="#collapse_campos">Mas campos</a>
	  </div>
	<div id="collapse_campos" class="panel-collapse collapse">
	 <div class="panel-body">
  <div id="div_ciudad" class="form-group">
  <label for="txt_ciudad_nueva" class="col-sm-4 control-label">Ciudad facturación:</label>
    <div class="col-sm-8" >
      <input type="text" required class="form-control" id="txt_ciudad_nueva"  name="txt_ciudad_nueva">
    </div>
	<label for="txt_direccion" class="col-sm-4 control-label">Direccion:</label>
    <div class="col-sm-8" >
      <input type="text" class="form-control" id="txt_direccion"  name="txt_direccion" value="">
    </div>
	</div>
  </div>
  </div>
  </div>
</div>
<!--
  <div class="form-group">
    <label for="usuario_tecnico" class="col-sm-4 control-label">Usuario Técnico:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="usuario_tecnico" name="usuario_tecnico" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_usuario
    ,sai_rol
    WHERE 
    usu_borrado IS NULL
    AND rol_id = usu_rol
    AND rol_codigo = 'tecnico'
");
if ($result) {
    foreach($result as $r) {
        $value = $r['usu_id'];
        $label = $r['usu_nombres'] . ' ' .$r['usu_apellidos'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>

      </select> 
    </div>
  </div>
  <div class="form-group">
    <label for="contacto" class="col-sm-4 control-label">Contacto:</label>
    <div class="col-sm-8">
      <select required id="contacto" name="contacto" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
        <?php $contactos = q("SELECT * FROM sai_contacto ORDER BY con_apellidos"); ?>
        <?php foreach($contactos as $contacto): ?>
            <option value="<?=$contacto['con_id']?>"><?=$contacto['con_nombres'].' '.$contacto['con_apellidos']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
-->

</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar()" id="formulario_eliminar">Eliminar registro</button>
        <button type="button" class="btn btn-success" onclick="p_recuperar()" id="formulario_recuperar">Recuperar registro</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


</body></html>


<script src="/js/ckeditor/ckeditor.js"></script>
<script src="/js/bootstrap3-typeahead.min.js"></script>

  <link rel="stylesheet" href="/css/bootstrap-toggle.min.css">
  <script src="/js/bootstrap-toggle.min.js"></script>

<script>
var tipos_contactos = <?=json_encode($tipos_contactos)?>;
$(document).ready(function() {
    $('.panel-atencion').on('shown.bs.collapse', function() {
        console.log("shown", $(this).prop('id'));
    }).on('show.bs.collapse', function() {
        console.log("show", $(this).prop('id'));
		//$('#txt_proveedor').val($('#pro_nombre_comercial').val());
		//alert($('#txt_proveedor').prop('value','TEST'));
        ate_id = parseInt($(this).prop('id').replace('panel_atencion_', ''));
        p_desplegar_divisiones(ate_id);
/*
        var cue_id = $('#cuenta_' + ate_id).attr('data-cue-id');
        $.get('/_obtenerCuenta/' + cue_id, function(data){
            console.log('Resultado /_obtenerCuenta/' + cue_id, data);
            data = JSON.parse(data);
            console.log('data', data);
            data = data[0];
            $('#cuenta_' + ate_id).text('Cuenta ' + data['tipo'] + ' de ' + data['cli_razon_social']);
			
        });*/
		//attr('value',$('#pro_nombre_comercial' + ate_id).val());
		$('#ate_id').val(ate_id);
		$('#ate_id').attr('value',ate_id);
		//alert($('#pro_nombre_comercial').val());
		
    });

    $('.combo-select2').select2({
        language: "es"
        ,width: '100%'
    });
    $('textarea').each(function(){
         CKEDITOR.replace(this);
    });
    $('.datetimepicker').datetimepicker({
        locale: 'es',
        format: 'YYYY-MM-DD'
    });
    var hash = window.location.hash.substr(1);

    console.log('HASH:', "["+hash+"]");
    if (hash) {
        //hace el scroll hasta el elemento:
        var $anchor = $(':target'),
            fixedElementHeight = 50;

        if ($anchor.length > 0) {

            $('html, body')
                .stop()
                .animate({
                scrollTop: $anchor.offset().top - fixedElementHeight
            }, 200);
        }

        // abre el acordeon adecuado:
        ate_id = parseInt(hash.replace('atencion_', ''));
        console.log('ate_id', ate_id);
        $('#collapse_' + ate_id).collapse('show');
    }
    $("#modal").on("shown.bs.modal", function () {
        //google.maps.event.trigger(map, "resize");
        $("#modal").off("shown.bs.modal");
    });
  $('#busqueda_query').select2({
        language: "es"
        ,width: '100%'
        ,ajax: {
            url: function (params) {
                console.log('SELECT2 URL params:', params);
                var busqueda = (params.term) ? params.term : '';
                return '/_listar/cliente/borrado/null/razon_social/ilike-' + busqueda + '/';
            }
            ,data:function(){return '';}
            ,processResults: function (data) {
                console.log('Respuesta /_listar/cliente/borrado/null/razon_social/ilike-', data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = [];
                data.forEach(function(opcion){
                    opciones.push( {
                        "id": opcion['razon_social']
                        ,"text":opcion['razon_social']
                    });
                });
                return {
                    results: opciones
                };
            }
        }
    });
 $('#cliente').select2({
        language: "es"
        ,width: '100%'
        ,ajax: {
            url: function (params) {
                console.log('SELECT2 URL params:', params);
                var busqueda = (params.term) ? params.term : '';
                return '/_listar/cliente/borrado/null/razon_social/ilike-' + busqueda + '/';
            }
            ,data:function(){return '';}
            ,processResults: function (data) {
                console.log('Respuesta /_listar/cliente/borrado/null/razon_social/ilike-', data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = [];
                data.forEach(function(opcion){
                    opciones.push( {
                        "id": opcion['id']
                        ,"text":opcion['razon_social']
                    });
                });
                return {
                    results: opciones
                };
            }
        }
    });


});

function p_desplegar_divisiones(ate_id){
	
	$.get('/_obtenerValoresDivision/'+ate_id, function(data){
            console.log('/_obtenerValoresDivision/'+ate_id, data);
            data = JSON.parse(data);
            console.log('data', data);
			var campos_total = 0;
            var campos_estado_vigente = '';
			
            campos_estado_vigente = '<table style="width:550px;" class="table table-striped table-condensed table-hover"><tbody>';
			campos_estado_vigente += ''
                    + '<tr>'
                    + '<th style="width:25%;text-align:right;">BW ASIGNADO</th>'
			+ '<th style="text-align:center;">DEPENDENCIA</th>'
			+ '<th style="text-align:center;">CIUDAD</th>'
			+ '<th style="text-align:center;">PRECIO</th>'
			+ '<td style="text-align:center;"></td>'
                    + '</tr>'
                    ;
			if(data){
            data.forEach(function(campo){
                var valor_detallado = (campo['valor_detallado']  == null) ? '' : campo['valor_detallado'];
                console.log('CAMPO', campo);
				var bw_asignado = 0;
				valor_detallado_precio ="";
				
                if (campo['dif_id']) {
                    var dif_id = campo['dif_id'];
                    valor_detallado = '<a href="#" onclick="p_abrir('+dif_id+');return false;">'+valor_detallado+'</a>';
                }
				if (campo['prc_id']) {
				var prc_id = campo['prc_id'];
                    valor_detallado_precio = '<a href="#" onclick="p_abrir_detalle_precio_cliente('+prc_id+');return false;">'+campo['prc_nombre']+'</a>';
				}
				bw_asignado=(campo['dif_bw']>0 ? parseFloat(campo['dif_bw']): parseFloat((parseInt(campo['dif_porcentaje']) * parseInt($('#ate_capacidad_facturada' + ate_id).val()) )/100) );
				campos_total += bw_asignado;
						
                campos_estado_vigente += ''
                    + '<tr>'
                    + '<th style="width:25%;text-align:right;">' + ((campo['dif_bw']>0)? campo['dif_bw_asignado'] : campo['dif_bw_asignado']+'('+ bw_asignado+' Mbps)') + '</th>'
			+ '<td style="text-align:center;">' + campo['cue_codigo'] + '</td>'
			+ '<td style="text-align:center;">' + campo['dif_ciudad'] + '</td>'
			+ '<td style="text-align:center;">'+valor_detallado_precio+'</td>'
			+ '<td style="text-align:center;">' + '<button type="button" class="btn btn-warning boton-quitar" id="campo_extra_quitar_'+campo['cae_id']+'" onclick="p_abrir('+dif_id+');return false;"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>' + '</td>'
                    + '</tr>'
                    ;
            });
			}
			campos_estado_vigente += ''
			+ '<tr>'
                + '<th style="width:25%;text-align:right;"><input type=hidden id="campos_total_'+ate_id+'" value="'+ campos_total+'"/> Total:  ' + campos_total + ' Mbps</th>'
			+ '<td style="text-align:center;"></td>'
			+ '<td style="text-align:center;"></td>'
			+ '<td style="text-align:center;"></td>'
                    + '</tr>'
            campos_estado_vigente += '</tbody></table>';
            $('#campos_estado_vigente_'+ate_id).html(campos_estado_vigente);

        });
		
		$('#hijo').select2({
        language: "es"
        ,width: '100%'
        ,allowClear: true
        ,placeholder: "Seleccione la cuenta para asignacion de factura"
        ,ajax: {
            url: function (params) {
                console.log('SELECT2 URL params:', params);
                var busqueda = (params.term) ? params.term : 0;
                var cue_id_padre = $('#cue_id').val() ? $('#cue_id').val() : 0;
				cue_id_excluido=0;
                console.log('URL', '/_listarCuentas/' + cue_id_excluido + '/0/'+ busqueda + '/' + cue_id_padre );
                return '/_listarCuentas/' + cue_id_excluido + '/0/'+ busqueda + '/' + cue_id_padre ;
            }
            ,data:function(){return '';}
            ,processResults: function (data) {
                console.log('Respuesta /_listarCuentas/', data);
                data = JSON.parse(data);
                console.log('data',data);
                return data;
            }
        }
    });
		$('#modo_facturacion').select2({
        language: "es"
        ,width: '100%'
        ,allowClear: true
        ,placeholder: "Seleccione el tipo de asignacion de bw"
		});
		
		$('#orden').select2({
        language: "es"
        ,width: '100%'
        ,allowClear: true
        ,placeholder: "Seleccione la orden para asociar"
        ,ajax: {
            url: function (params) {
                console.log('SELECT2 URL params:', params);
                var busqueda = (params.term) ? params.term : 0;
                var cue_id_padre = $('#cue_id').val() ? $('#cue_id').val() : 0;
				cue_id_excluido=0;
                console.log('URL', '/_listarOrdenes/' + ate_id );
                return '/_listarOrdenes/' + ate_id ;
            }
            ,data:function(){return '';}
            ,processResults: function (data) {
                console.log('Respuesta /_listarCuentas/', data);
                data = JSON.parse(data);
                console.log('data',data);
                return data;
            }
        }
    });
}

function toggle_campos(obj){
	if(obj.value=='bw_cantidad'){
		$('#div_bw_cantidad').prop('hidden', false);
		$('#bw_proporcion').val(0);
	}else{
		$('#div_bw_cantidad').prop('hidden', true);
	}
	
	if (obj.value=='bw_proporcion'){
		$('#div_bw_proporcion').prop('hidden', false);
		$('#bw_cantidad').val(0);
	}else{
		$('#div_bw_proporcion').prop('hidden', true);
	}
}


function p_enviar_busqueda(target) {
    var busqueda = $('#busqueda_query').val();
    $(target).prop('action', '/proceso_facturacion/' + busqueda.replace(/&/g,';'));
}



function p_validar(target){
    var id = $(target).attr('id');
    console.log('validando', target, id, $(target)[0].checkValidity());
    var resultado = true;
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...', target);
        $(target).popover('hide');
        $(target).popover('destroy');
        $(target).popover({
            placement:'auto top',
            trigger:'manual',
            html:true,
            content:target.validationMessage
        });
        $(target).popover('show');
        setTimeout(function () {
            $(target).popover('hide');
            $(target).popover('destroy');
        }, 4000);

        //$('<input type="submit">').hide().appendTo('#' + id).click().remove();
        resultado = false;
    }

    if ($(target).prop("tagName") == 'FORM') {
        try {
            $(target)[0].reportValidity();
        } catch(e){
            console.error('CATCH del Error en reportValidity del formulario');
        }
    }
    return resultado;
}

function p_guardar(){

	ate_id=$('#ate_id').val();
	console.log('BW DISPONIBLE :' , parseFloat($('#bw_disponible').val()));
	console.log('BW ASIGNADO :' , $('#bw_cantidad').val());
	console.log('BW ASIGNADO :' , parseFloat((parseInt($('#bw_proporcion').val()) * parseFloat($('#ate_capacidad_facturada' + ate_id).val()) )/100));
	console.log('ATE ID :' , ate_id);
	console.log('hijo :' , $('#hijo').val());
	console.log('bw_proporcion :' , $('#bw_proporcion').val());
	
    if (p_validar($('#formulario')) && (parseFloat($('#bw_disponible').val()) >= $('#bw_cantidad').val() && parseFloat($('#bw_disponible').val()) >= parseFloat((parseInt($('#bw_proporcion').val()) * parseFloat($('#ate_capacidad_facturada' + ate_id).val()) )/100))) {
        if ($('#ate_id').val() !== '' && $('#hijo').val() !== '' && $('#bw_proporcion').val() !== '' && $('#bw_cantidad').val() !== '' ) {
            $('#formulario_guardar').prop('disabled', true);
                var respuestas_json = $('#formulario').serializeArray();
                console.log('respuestas json', respuestas_json);
                dataset_json = {};
                respuestas_json.forEach(function(respuesta_json){
                    var name =  respuesta_json['name'];
                    var value = respuesta_json['value'];
                    dataset_json[name] = value;

                });
                //dataset_json['codigo'] = $('#codigo').val();

                console.log('dataset_json', dataset_json);
                $.ajax({
                url: '/_guardarDivision',
                    type: 'POST',
                    //dataType: 'json',
                    data: JSON.stringify(dataset_json),
                    //contentType: 'application/json'
                }).done(function(data){
                    console.log('Guardado OK, data:', data);
                    //data = eval(data)[0];
                    data = JSON.parse(data);
                    data = data[0];
					error = data['ERROR'];
					
                    console.log('eval data:', data);
                    if (error) {
                        alert(error);
                        $('#formulario_guardar').prop('disabled', false);
                    } else {

                        if ($("#nombre_" + data['id']).length) { // 0 == false; >0 == true
                            //ya existe:
                            console.log('CUENTA ya existe');
                        } else {
                            //nuevo:
                            console.log(' GUARDAR DIVISION');
                        }
                        //location.reload();
						p_desplegar_divisiones($('#ate_id').val());
                        $('#modal_division').modal('hide');
                    }
                }).fail(function(xhr, err){
                    console.error('ERROR AL GUARDAR', xhr, err);
                    alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                    //$('#modal').modal('hide');
    $('#formulario_guardar').prop('disabled', false);
                });
        } else {
            alert ('Ingrese los datos del formulario'); 
        }
    } else {
            alert ('Verificar el BW asignado'); 
        }
}

function p_nuevo(id){
	
	$('#formulario_titulo').text('nueva');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#formulario_eliminar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
    $('#formulario_guardar').prop('disabled', false);
	
	var ate_id = $('#ate_id').val();
	
	/* $('#formulario').find(':input').each(function() {
        switch(this.type) {

        case 'select-one':
        case 'select-multiple':
        case 'email':
            $(this).val('');
            $(this).prop('disabled', false);
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            $(this).prop('disabled', false);
            break;
        }
        $(this).trigger('change');
    });  */

        //$('#cue_id').val($('#cue_id').val());
		//$('#id').val(data['dif_id']);


		$('#cliente').select2("trigger", "select", {
                    data: { id: $('#cue_cliente').val(), text: $('#cli_razon_social').val() }
                });
				
		
	
		var max_orden = 0;
        $.get('/_obtenerMaximaOrden/' + ate_id, function(data){
            console.log('Resultado /_obtenerMaximaOrden/' + ate_id, data);
            data = JSON.parse(data);
            console.log('data', data);
            data = data['results'];
			max_orden=data[0]['max']? parseInt(data[0]['max']) + 1 : 1;
            $('#orden').select2("trigger", "select", {
                    data: { id: max_orden, text: "OP:"+max_orden }
                });
			
        });
		

		
		
		$('#txt_proveedor').attr('value',$('#pro_nombre_comercial' + ate_id).val());
		$('#txt_ciudad').attr('value',$('#ate_ciudad' + ate_id).val());
		$('#txt_ciudad_nueva').attr('value',$('#ate_ciudad' + ate_id).val());
		$('#txt_servicio').attr('value',$('#ate_servicio' + ate_id).val());
		$('#txt_codigo').attr('value',$('#ate_codigo' + ate_id).val());
		$('#txt_capacidad_facturada').attr('value',$('#ate_capacidad_facturada' + ate_id).val()+' Mbps');
		$('#bw_disponible').val($('#ate_capacidad_facturada' + ate_id).val()-$('#campos_total_' + ate_id).val());
		
		
        $('#modal_division').modal('show');
 



}

function p_abrir(id){
	
	var ate_id = $('#ate_id').val();
    $.ajax({
        'url':'/_obtenerValoresDivision/'+ate_id+'/'+id
    }).done(function(data){
        //data = eval(data);
        console.log('/_obtenerValoresDivision/'+ate_id+'/'+id, data);
        data = JSON.parse(data);
        data = data[0];
        console.log('ABRIENDO DIVISION FACTURA', data);

        var badge = '';
        var disabled = false;
        if (data['dif_borrado'] == null) {
            $('#formulario_eliminar').show();
            //$('#formulario_eliminar').hide();
            $('#formulario_guardar').show();
            $('#formulario_guardar').prop('disabled', false);
            $('#formulario_recuperar').hide();
            disabled = false;
            //p_abrir_permiso_ingreso(data['id']);
        } else {
            badge = '<span class="badge">ELIMINADO</span>';
            $('#formulario_eliminar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_guardar').prop('disabled', true);
            $('#formulario_recuperar').show();
            disabled = true;
        }
        $('#formulario_titulo').html(' de ' + data['cli_razon_social'] + ' ' + badge);
        /*
        for (key in data){
            $('#' + key).val(data[key]);
            $('#' + key).trigger('change');
            $('#' + key).prop('disabled', disabled);
        }
         */
        //$('#cue_id').val(data['cue_id']);
		$('#id').val(data['dif_id']);

		$('#cliente').select2("trigger", "select", {
                    data: { id: $('#cue_cliente').val(), text: $('#cli_razon_social').val() }
                });

            if (data['cue_id']) {
                $('#hijo').select2("trigger", "select", {
                    data: { id: data['cue_id'], text: data['cue_codigo'] }
                });
            } else {
                $('#hijo').val('').change();
            }
			if (data['dif_codigo']) {
                $('#orden').select2("trigger", "select", {
                    data: { id: data['dif_codigo'], text: "OP:"+data['dif_codigo'] }
                });
            }else {
                $('#orden').val('').change();
            }
			
			if (data['dif_porcentaje']) {
                $('#bw_proporcion').val(data['dif_porcentaje']);
            } else {
                $('#bw_proporcion').val(0);
            }
			if (data['dif_bw']) {
                $('#bw_cantidad').val(data['dif_bw']);
            } else {
                $('#bw_cantidad').val(0);
            }
			if (data['dif_ciudad']) {
                $('#txt_ciudad_nueva').val(data['dif_ciudad']);
            } else {
                $('#txt_ciudad_nueva').val('');
            }
			if (data['dif_direccion']) {
                $('#txt_direccion').val(data['dif_direccion']);
            } else {
                $('#txt_direccion').val('');
            }
			
			if (data['dif_bw']>0){
				$('#modo_facturacion').select2("trigger", "select", {
                    data: { id: 'bw_cantidad', text: 'BW(Mbps)' }
                });
				$('#bw_disponible').val((parseInt($('#ate_capacidad_facturada' + ate_id).val())-parseFloat($('#campos_total_' + ate_id).val())) + parseFloat(data['dif_bw']));
				//$('#bw_disponible').val($('#ate_capacidad_facturada' + ate_id).val()-$('#campos_total_' + ate_id).val());
				
			} else if (data['dif_porcentaje']>0){
				$('#modo_facturacion').select2("trigger", "select", {
                    data: { id: 'bw_proporcion', text: 'Porcentaje(%)' }
                });
				$('#bw_disponible').val((parseInt($('#ate_capacidad_facturada' + ate_id).val())-parseFloat($('#campos_total_' + ate_id).val()))+parseFloat($('#ate_capacidad_facturada' + ate_id).val()*((data['dif_porcentaje'])/100)));
				
			}
			

            /*if (data['cue_responsable_cobranzas']) {
                $('#responsable_cobranzas').select2("trigger", "select", {
                    data: { id: data['cue_responsable_cobranzas'], text: data['usu_nombres'] + ' ' + data['usu_apellidos'] }
                });
            } else {
                $('#responsable_cobranzas').val('').change();
            }*/

        //$("#codigo").prop('disabled', true);
        
		
		$('#txt_proveedor').attr('value',$('#pro_nombre_comercial' + ate_id).val());
		$('#txt_ciudad').attr('value',$('#ate_ciudad' + ate_id).val());
		$('#txt_servicio').attr('value',$('#ate_servicio' + ate_id).val());
		$('#txt_codigo').attr('value',$('#ate_codigo' + ate_id).val());
		$('#txt_capacidad_facturada').attr('value',$('#ate_capacidad_facturada' + ate_id).val()+' Mbps');
		//alert($('#txt_proveedorn').val());
		
        $('#modal_division').modal('show');
		
		//$('#formulario_titulo').text('nueva');
		
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_borrar(){
	
	if (confirm('Seguro desea eliminar la Facturación a dependiencia ')) {
    if (p_validar($('#formulario'))) {	
        if ($('#ate_id').val() !== '' && $('#hijo').val() !== '' && $('#bw_proporcion').val() !== '' && $('#bw_cantidad').val() !== '' ) {
            $('#formulario_guardar').prop('disabled', true);
                var respuestas_json = $('#formulario').serializeArray();
                console.log('respuestas json', respuestas_json);
                dataset_json = {};
				//dataset_json['id'] = $('#id').val();
				dataset_json['borrar'] = 'borrar';
                respuestas_json.forEach(function(respuesta_json){
                    var name =  respuesta_json['name'];
                    var value = respuesta_json['value'];
                    dataset_json[name] = value;

                });
                //dataset_json['codigo'] = $('#codigo').val();

                console.log('dataset_json', dataset_json);
                $.ajax({
                url: '/_guardarDivision',
                    type: 'POST',
                    //dataType: 'json',
                    data: JSON.stringify(dataset_json),
                    //contentType: 'application/json'
                }).done(function(data){
                    console.log('Borrado OK, data:', data);
                    //data = eval(data)[0];
                    data = JSON.parse(data);
                    data = data[0];
					error = data['ERROR'];
					
                    console.log('eval data:', data);
                    if (error) {
                        alert(error);
                        $('#formulario_eliminar').prop('disabled', false);
                    } else {

                        //location.reload();
						p_desplegar_divisiones($('#ate_id').val());
                        $('#modal_division').modal('hide');
                    }
                }).fail(function(xhr, err){
                    console.error('ERROR AL BORRAR', xhr, err);
                    alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                    //$('#modal').modal('hide');
				$('#formulario_eliminar').prop('disabled', false);
                });
        } else {
            alert ('Ingrese los datos del formulario'); 
        }
    }
	}
}

function p_borrar2(){

    if (confirm('Seguro desea eliminar la Facturación a dependiencia ')) {
        dataset_json = {};
        dataset_json['id'] = $('#id').val();
		//alert($('#id').val());
        //dataset_json['codigo'] = $('#codigo').val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '/_guardarDivision',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            //$('#nombre_' + data['id']).parent().parent().remove();
            if (data['ERROR']){
                alert(data['ERROR']);
            } else {
                //location.reload();
				p_desplegar_divisiones($('#ate_id').val());
                $('#modal_division').modal('hide');
            }

        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_abrir_detalle_precio_cliente(prc_id){
    console.log('En p_abrir_detalle_precio', prc_id);
    $.get('/_obtenerPrecio/' + prc_id, function(data){
        console.log('/_obtenerPrecio/' + prc_id, data);
        data = JSON.parse(data);
        console.log('data:', data);
        if (data) {
            var precio = data[0];
            var contenido = '';
            contenido += '<>';
            var titulo = '';
                titulo = 'Detalle de ' + precio['prc_nombre'];
                $('#detalle_precio_titulo').text(titulo);
                $('#detalle_precio_nombre').text(precio['prc_nombre']);
                $('#detalle_precio_precio').text(precio['prc_precio_mb']);
                $('#detalle_precio_cliente').text(precio['cli_razon_social']);
                $('#detalle_precio_servicio').text(precio['ser_nombre']);
                $('#detalle_precio_fecha_vigencia').text(precio['prc_fecha_ejecucion']);
                $('#detalle_precio_creado').text(precio['prc_creado']);
				$('#detalle_precio_creado_por').text(precio['prc_creado_por']);
                $('#modal_detalle_precio').modal('show');
        }
    });
}
</script>
<!--
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8FSFiwwDeuKMWXOZpPuL1v6s9PnWNsFQ&callback=initMap"></script>
-->
