<?php
$us_listado = array(
array('usu_cedula' => '01','usu_nombres' => 'Router', 'usu_correo_electronico' => '4'),
array('usu_cedula'=>'02','usu_nombres' => 'Switch','usu_correo_electronico' => '2'),
array('usu_cedula'=>'03','usu_nombres' => 'Equipo de limpieza de equipos electrónicos','usu_correo_electronico' => '0'),
array('usu_cedula'=>'04','usu_nombres' => 'Rollo de cable', 'usu_correo_electronico' => '7'),
);
//q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_cedula");
?>

<h2>Productos</h2>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:10px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Producto <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="cedula" class="col-sm-2 control-label">Código:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="cedula" name="cedula" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="nombres" class="col-sm-2 control-label">Descripción:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nombres" name="nombres" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Proveedor:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Marca:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Modelo:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Número de serie:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Comentario:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Cantidad:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-4 control-label">Costo del producto:</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-4 control-label">Precio al público recomendado:</label>
    <div class="col-sm-8">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Factura:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="">
    </div>
  </div>
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_eliminar()" id="formulario_eliminar">Eliminar usuario</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<table class="table table-striped">
  <tr>
    <th></th>
    <th>Codigo</th>
    <th>Descripción</th>
    <th>Cantidad</th>
  </tr>
<tbody id="antiguos">
<?php foreach($us_listado as $i=>$us): ?>
  <tr>
    <th><?php echo ($i+1).'.&nbsp;'; ?></th>
    <td><span id=""><a href="#" onclick="p_abrir('<?=$us['usu_id']?>');return false;"><?=$us['usu_cedula']?></a></span></td>
    <td><span id="nombre_<?=$us['usu_id']?>"><?php echo $us['usu_nombres'].' '.$us['usu_apellidos']; ?></span></td>
    <td><span id="correo_electronico_<?=$us['usu_id']?>"><?=$us['usu_correo_electronico']?></span></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>


<!--script src="/js/bootstrap3-typeahead.min.js"></script-->
<script src="/js/md5.min.js"></script>
<script>
function p_abrir(id){
    $.ajax({
        'url':'/_listar/usuario/'+id
    }).done(function(data){
        data = eval(data);
        usu = data[0];
        console.log(usu);
        $('#formulario_titulo').text(usu['cedula'] + ' "' + usu['nombres'] + ' ' + usu['apellidos'] + '"');
        $('#formulario_eliminar').show();
        $("#cedula").prop('disabled', true);
        for (key in usu){
            $('#' + key).val(usu[key]);
        }
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_guardar(){
    if ($('#nombres').val() !== '' && $('#apellidos').val() !== '' && $('#cedula').val() !== '' && $('#correo_electronico').val() !== '') {
    var respuestas_json = $('#formulario').serializeArray();
    console.log(respuestas_json);
    dataset_json = [];
    dataset_json[0] = {};
    respuestas_json.forEach(function(respuesta_json){
        var name =  respuesta_json['name'];
        var value = respuesta_json['value'];
        dataset_json[0][name]=value;

    });

    dataset_json[0]['username'] = dataset_json[0]['cedula'];
    dataset_json[0]['password'] = md5(dataset_json[0]['cedula']);

    console.log('dataset_json', dataset_json);
    $.ajax({
        url: '_guardar/usuario',
        type: 'POST',
        dataType: 'json',
        data: JSON.stringify(dataset_json),
        //data: dataset_json,
        contentType: 'application/json'
    }).done(function(data){
        console.log('Guardado OK', data)
        data = eval(data);

        if($("#nombre_" + data[0]['id']).length) { // 0 == false; >0 == true
            //ya existe:
            $('#cedula_' + data[0]['id']).text(data[0]['cedula']);
            $('#nombre_' + data[0]['id']).text(data[0]['nombres'] + ' ' + data[0]['apellidos']);
            $('#correo_electronico_' + data[0]['id']).text(data[0]['correo_electronico']);
        } else {
            //nuevo:
            console.log('nuevo USUARIO');
            var numero = $('#antiguos').children().length + 1;
            $('#antiguos').append('<tr><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data[0]['id']+'\')">'+data[0]['cedula']+'</a></td><td><span id="nombre_' + data[0]['id'] + '">' + data[0]['nombres'] + ' ' + data[0]['apellidos'] + '</span></td><td><span id="correo_electronico_'+data[0]['id']+'">'+data[0]['correo_electronico'] + '</span></td></tr>');
        }
        $('#modal').modal('hide');
    }).fail(function(xhr, err){
        console.error('ERROR AL GUARDAR', xhr, err);
        $('#modal').modal('hide');
    });
    } else {
        alert ('Ingrese al menos el número de cédula, nombres, apellidos y correo electrónico');
    }
}

function p_nuevo(){

    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#modal').modal('show');
    $('#formulario_eliminar').hide();
 
    $('#cedula').prop('disabled', false);

}

function p_eliminar(cedula, nombre){
    if (confirm('Seguro desea eliminar el Usuario ' + $('#cedula').val() + ' "' + $('#nombres').val() + ' ' + $('#apellidos').val() + '"')) {
        var dataset_json = [{id:$('#id').val()}];
        $.ajax({
            url: '_borrar/usuario',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(dataset_json),
            //data: dataset_json,
            contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK', data)
                data = eval(data);

            $('#nombre_' + data[0]['id']).parent().parent().remove();
        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
        });
        $('#modal').modal('hide');
    }
}
</script>
<script>
/*
function p_abrir(usu_id){
    $.ajax({
        'url':'/_listar/usuario/'+usu_id
    }).done(function(data){
        data = eval(data);
        usuario = data[0];
        console.log(usuario);
        $('#formulario_titulo').text(usuario['cedula']);
        for (key in usuario){
            $('#' + key).val(usuario[key]);
        }
    }).fail(function(){
        console.error('ERROR AL ABRIR');
    });
    $('#modal').modal('show');
}
 */
</script>
