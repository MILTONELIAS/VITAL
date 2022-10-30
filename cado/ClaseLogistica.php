<?php

require_once('conexion.php');

class Logistica
{
    public $IGV = 0.18;

    function redondear_dos_decimal($valor){
        $redondeado = round(($valor * 100)) / 100;
        return $redondeado;
    }
    // FUNCIONES PARA EL MANTENEDOR ALMACÉN	   
    function ListarAlmacen($q){
        $ocado = new cado();
        $sql = "select top 10 a.*,s.nombre as sucursal from log_almacen a inner join cont_sucursal s on s.id=a.id_sucursal where  
        a.estado=0  and (a.nombre like '%$q%' or a.responsable like '%$q%' ) order by a.nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function TotalAlmacen($q){
        $ocado = new cado();

        $sql = "select count(*) from log_almacen a inner join cont_sucursal s on s.id=a.id_sucursal where  
        a.estado=0  and (a.nombre like '%$q%' or a.responsable like '%$q%' )";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarAlmacenxid($id){
        $ocado = new cado();
        $sql = "select * from log_almacen where id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarAlmacenSucursalxid($id){
        $ocado = new cado();
        $sql = "select *,a.nombre as nombre_almacen,s.nombre as nombre_sucursal from log_almacen a inner join cont_sucursal s on s.id=a.id_sucursal where  a.id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
	function ListarAlmacenxSucursal($sucursal){
        $ocado = new cado();
        $sql = "select * from log_almacen where  id_sucursal='$sucursal' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarPersonalxArea($area){
        $ocado = new cado();
        $sql = "select * from log_trabajador t where  id_area='$area' and t.estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarAlmacenGeneral(){
        $ocado = new cado();
        $sql = "select * from log_almacen where tipo='0' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarAlmacenGeneralxSucursal($sucursal){
        $ocado = new cado();
        $sql = "select * from log_almacen where id_sucursal='$sucursal' and tipo='0' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarAlmacenSubxSucursal($sucursal){
        $ocado = new cado();
        $sql = "select * from log_almacen where  id_sucursal='$sucursal'  and tipo='1' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ValidarAlmacen($nombre){
        $ocado = new cado();
        $sql = "select count(*) from log_almacen where nombre='$nombre' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function RegistrarAlmacen($nombre, $responsable, $correo, $sucursal, $tipo){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_almacen(nombre,responsable,correo,id_sucursal,estado,tipo) values"
                . "('$nombre','$responsable','$correo','$sucursal',0,'$tipo')";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function ModificarAlmacen($id, $nombre, $responsable, $correo, $sucursal, $tipo){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_almacen set nombre = '$nombre' , responsable='$responsable',correo='$correo' ,"
                . "id_sucursal='$sucursal',tipo='$tipo' where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function EliminarAlmacen($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_almacen set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    // FUNCIONES PARA EL MANTENEDOR PRODUCTO	
    
    function ListarExamenProducto(){
        $ocado = new cado();
        $sql = "select * from examen  where estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarUnidades(){
        $ocado = new cado();
        $sql = "select * from log_codigo_unidad_medida  where estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarUnidadxid($id){
        $ocado = new cado();
        $sql = "select * from log_codigo_unidad_medida  where id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarProductoLog($q, $id_categoria){
        $ocado = new cado();
        $sql = "select top 500 p.*,c.nombre as categoria,pf.nombre  as nombre_producto_fraccion,IIF(p.tipo_producto='0','PRODUCTO','SERVICIO') as tipo ,u.codigo as codigo_unidad,u.descripcion as descripcion_unidad from log_producto p 
            inner join log_categoria_producto c on c.id=p.id_categoria 
            left join log_producto pf on p.id_producto_fraccion=pf.id 
            left join log_codigo_unidad_medida u on p.unidad=u.id
         where (p.nombre like '%$q%' or p.equipo like '%$q%' or p.examen like '%$q%') and p.estado=0";

        if ($id_categoria != '' && $id_categoria != 0) {
            $sql .= " and p.id_categoria=$id_categoria";
        }
        $sql .= " order by p.id desc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
	function ListarSoloProductos(){
        $ocado = new cado();
        $sql = "select p.*,c.nombre as categoria,IIF(p.tipo_producto=0,'PRODUCTO','SERVICIO') as tipo from log_producto p inner join log_categoria_producto c on c.id=p.id_categoria
         where p.estado=0 and p.tipo_producto=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarReactivos(){
        $ocado = new cado();
        $sql = "select p.*,c.nombre as categoria,IIF(p.tipo_producto='0','PRODUCTO','SERVICIO') as tipo ,u.codigo as codigo_unidad,u.descripcion as descripcion_unidad from log_producto p 
        inner join log_categoria_producto c on c.id=p.id_categoria inner join log_codigo_unidad_medida u on p.unidad=u.id
         where c.id=13 and p.estado=0  order by p.nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarProductoTipo(){
        $ocado = new cado();
        $sql = "select p.*,c.nombre as categoria from log_producto p inner join log_categoria_producto c on c.id=p.id_categoria
         where p.estado=0 order by p.nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarProductoLogxid($id){
        $ocado = new cado();
        $sql = "select p.*,lcu.descripcion as unidad,p.unidad as id_unidad from log_producto p inner join log_codigo_unidad_medida lcu on lcu.id=p.unidad where  p.id=$id and p.estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarProductosFraccion(){
        $ocado = new cado();
        $sql = "select * from log_producto where id not in(select id_producto_fraccion from log_producto where estado='0' and tipo_producto='0') and tipo_producto='0' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ValidarProductoLog($nombre){
        $ocado = new cado();
        $sql = "select count(*) from log_producto where nombre='$nombre' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ValidarProductoFraccion($id){
        $ocado = new cado();
        $sql = "select count(*) from log_producto where id_producto_fraccion=$id  and estado=0 ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
	function RegistrarProductoLog($nombre,$equipo,$examen,$categoria,$unidad,$stock_min,$stock_max,$tipo_producto,$id_producto_fraccion, $cantidad_fraccion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_producto(nombre,equipo,examen,id_categoria,unidad,stock_min,stock_max,tipo_producto,id_producto_fraccion,cantidad_fraccion,estado) "
                . "values('$nombre','$equipo','$examen','$categoria','$unidad','$stock_min','$stock_max','$tipo_producto','$id_producto_fraccion','$cantidad_fraccion','0')";
           
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }
	function ModificarProductoLog($id, $nombre, $equipo, $examen, $categoria, $unidad, $stock_min, $stock_max, $tipo_producto, $id_producto_fraccion, $cantidad_fraccion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_producto set nombre = '$nombre',unidad= '$unidad',id_categoria='$categoria',stock_min='$stock_min',stock_max='$stock_max',          tipo_producto='$tipo_producto',id_producto_fraccion='$id_producto_fraccion',cantidad_fraccion='$cantidad_fraccion',equipo='$equipo', examen='$examen'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function EliminarProductoLog($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_producto set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    // FUNCIONES PARA EL MANTENEDOR CATEGORÍA	   
    function ListarCategoriaProducto($q){
        $ocado = new cado();
        $sql = "select top 50 * from log_categoria_producto where nombre like '%$q%' and estado=0 order by nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function TotalCategoria($q){
        $ocado = new cado();

        $sql = "select count(*) from log_categoria_producto where  nombre like '%$q%' and estado=0 ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
	function ListarCategoriaProductoxid($id){
        $ocado = new cado();
        $sql = "select * from log_categoria_producto where  id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
	function ValidarCategoriaProducto($nombre){
        $ocado = new cado();
        $sql = "select count(*) from log_categoria_producto where nombre='$nombre' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function RegistrarCategoriaProducto($nombre){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_categoria_producto(nombre,estado) values('$nombre',0)";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }

        return $return;
    }
    function ModificarCategoriaProducto($id, $nombre){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_categoria_producto set nombre = '$nombre'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function EliminarCategoriaProducto($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_categoria_producto set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    //FUNCIONES PARA ORDEN DE COMPRA

    function NroOrdCompra($tipo){
        $year = date('Y'); // función para obtener el año actual
        $ocado = new cado();
        $sql = "select count(*) from log_orden_compra where tipo='$tipo' and YEAR(fecha) = '$year'";
        $ejecutar = $ocado->ejecutar($sql);       

        return $year . '-' .($ejecutar->fetch()[0]+1);
    }
    
    function RegistrarOrdenCompra($detalles_orden_compra, $nro, $fecha, $sucursal, $almacen, $referencia, $tipo,$proveedor,$entrega,$anticipo){
        $ocado = new cado();
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "61";

            // $usuario = "1";
            $date = date('d-m-Y H:i:s');
            $nro = $this->NroOrdCompra($tipo);
            $sql = "insert into log_orden_compra(numero,fecha,id_sucursal,id_almacen,referencia,id_usuario,fecha_sistema,estado,tipo,id_proveedor,entrega,anticipo) values('$nro','$fecha','$sucursal','$almacen','$referencia','$usuario','$date','pendiente','$tipo','$proveedor','$entrega','$anticipo')";
            
            die($sql);exit;
            
            foreach ($detalles_orden_compra as $detalle) {

                $id_producto = $detalle['id_producto'];
                $cantidad = $detalle['cantidad'];
                $producto = $this->ListarProductoLogxid($id_producto)->fetch();
                $unidad = $producto['id_unidad'];
                $despachado = $detalle['despachado'];
                $pendiente = $detalle['pendiente'];
                $precio = $detalle['precio'];

                $sql .= "insert into log_orden_compra_detalle(id_orden_compra,id_producto,cantidad,despachado,pendiente,unidad,precio) values((select max(id) from log_orden_compra),'$id_producto','$cantidad','$despachado','$pendiente','$unidad','$precio');";
            }
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }
    function ModificarOrdenCompra($id, $detalles_orden_compra, $nro, $fecha, $sucursal,  $almacen, $referencia, $tipo,$proveedor,$entrega,$anticipo){
        $ocado = new cado();
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "61";
            $date = date('d-m-Y H:i:s');
            $sql = "update  log_orden_compra set numero='$nro' , fecha='$fecha', id_sucursal='$sucursal' , id_almacen='$almacen', referencia='$referencia',id_usuario='$usuario',fecha_sistema='$date',tipo='$tipo' ,id_proveedor='$proveedor', entrega='$entrega', anticipo='$anticipo' where id='$id';";

            $sql .= " delete from log_orden_compra_detalle where id_orden_compra='$id'; ";

            foreach ($detalles_orden_compra as $detalle) {

                $id_producto = $detalle['id_producto'];
                $cantidad = $detalle['cantidad'];

                $producto = $this->ListarProductoLogxid($id_producto)->fetch();
                $unidad = $producto['id_unidad'];
                $despachado = $detalle['despachado'];
                $pendiente = $detalle['pendiente'];
                $precio = $detalle['precio'];


                $sql .= "insert into log_orden_compra_detalle(id_orden_compra,id_producto,cantidad,unidad,despachado,pendiente,precio)"
                    . "values('$id','$id_producto','$cantidad','$unidad','$despachado','$cantidad','$precio');";
            }
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
             $return = $ex->getMessage();
        }
        return $return;
    }
	function ValidarOrdenCompra($numero){
        $ocado = new cado();
        $sql = "select count(*) from log_orden_compra where numero='$numero'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarOrdenCompra($where){
        $ocado = new cado();
        $sql = "select o.id,s.nombre,o.fecha,o.numero,a.nombre,o.estado,o.tipo,a.id,p.nombre from log_orden_compra o inner join cont_sucursal  s on s.id=o.id_sucursal  inner join log_almacen a on a.id=o.id_almacen INNER JOIN log_proveedor p ON o.id_proveedor=p.id $where order by o.id desc ;";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarOrdenCompraxId($id){
        $ocado = new cado();
        $sql = "select oc.*,prov.nombre, prov.documento,prov.direccion,prov.telefono,prov.contacto,prov.condicion_pago,prov.banco,prov.numero_cuenta from log_orden_compra oc inner join log_proveedor prov on oc.id_proveedor=prov.id where oc.id=$id;";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EstadoOrdenCompra($id, $estado){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_orden_compra set estado='$estado' where id='$id'";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function ListarOrdenComprDetalles($id){
        $ocado = new cado();
        $sql = "select o.*,p.nombre,p.tipo_producto,lu.descripcion nombre_unidad from log_orden_compra_detalle o inner join log_producto p on p.id=o.id_producto inner join log_codigo_unidad_medida lu on lu.id=p.unidad where id_orden_compra=$id ;";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    //FUNCIONES PARA EL MANTENEDOR PROVEEDOR

    function ListarProveedor($q){
        $ocado = new cado();
        $sql = "select * from log_proveedor where  (nombre like '%$q%' or documento like '%$q%' or nombre_comercial like '%$q%' ) and estado=0 order by id desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function TotalProveedor($q){
        $ocado = new cado();
        $sql = "select * from log_proveedor where  nombre like '%$q%' and estado='0' ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarProveedorxid($id){
        $ocado = new cado();
        $sql = "select * from log_proveedor where  id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ValidarProveedor($nombre){
        $ocado = new cado();
        $sql = "select count(*) from log_proveedor where nombre='$nombre' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function Registrarproveedor($nombre, $documento, $direccion, $contacto, $telefono, $email, $estado_contribuyente, $condicion, $nombre_comercial,$condicion_pago,$banco,$tipo_cuenta,$numero_cuenta){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_proveedor(nombre,documento,direccion,contacto,telefono,email,estado,condicion_contribuyente,nombre_comercial,condicion_pago,banco,
			tipo_cuenta,numero_cuenta) values('$nombre','$documento','$direccion','$contacto','$telefono','$email','0','$condicion','$nombre_comercial','$condicion_pago','$banco','$tipo_cuenta','$numero_cuenta')";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }
        return $return;
    }

    function Modificarproveedor($id, $nombre, $documento, $direccion, $contacto, $telefono, $email, $estado_contribuyente, $condicion, $nombre_comercial, $condicion_pago, $banco, $tipo_cuenta, $numero_cuenta){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_proveedor set nombre ='$nombre', direccion='$direccion', telefono='$telefono', email='$email', contacto='$contacto', documento='$documento', nombre_comercial='$nombre_comercial', estado='$estado_contribuyente', condicion_contribuyente='$condicion', condicion_pago='$condicion_pago', banco='$banco', tipo_cuenta='$tipo_cuenta', numero_cuenta='$numero_cuenta'  where id = '$id'";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //             $return= $ex->getMessage();
        }
        return $return;
    }

    function EliminarProveedor($id){
		$ocado=new cado();
		$sql="update log_proveedor set estado='1' where id = $id";		
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
       
    }
	
	/*function ValidarProveedor($ruc){
		  $ocado=new cado();
		  $sql="select documento,nombre from paciente where documento='$ruc'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/


    //FUNCIONES PARA COMPRAS

    function UltimoCostoFinalKardex($id_producto){
        $ocado = new cado();
        $sql = "select costo_total_final from log_kardex where  id_producto=$id_producto  order by id desc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar->fetch()['costo_total_final'];
    }

    function RegistrarCompra(
        $detalles_compra,
        $fecha,
        $proveedor,
        $tipo_documento,
        $tipo_afectacion,
        $nota_credito,
        $tipo_compra,
        $serie,
        $nro_documento,
        $nro_dias,
        $id_orden,
        $id_almacen,
        $igv_detalle,
        $redondeo
    ) {
        $ocado = new cado();
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "72";
            $date = date('d-m-Y H:i:s');

            //Si compra viene de orden 
            if ($id_orden != '0') {
                $id_almacen = $this->ListarOrdenCompraxId($id_orden)->fetch()['id_almacen'];
            }

            //CÁLCULO DEL DETALLE
            $sub_total = 0;
            $detalles = array();
            foreach ($detalles_compra as $detalle) {

                if ($igv_detalle == '1') { //FACT. AFECTA A IGV  
                    $detalle['monto_igv'] = ($detalle['precio'] * $this->IGV) / (1 + $this->IGV);
                    $detalle['precio_sin_igv'] = $detalle['precio'] - $detalle['monto_igv'];
                } else {
                    $detalle['monto_igv'] = $detalle['precio'] * $this->IGV;
                    $detalle['precio_sin_igv'] = $detalle['precio'];
                }
                $detalle['subtotal'] = (($detalle['precio']) * $detalle['cantidad']);
                if ($detalle['bonificacion'] == '0') {
                    $sub_total =  $sub_total + $detalle['subtotal'];
                } else {
                    $detalle['subtotal'] = 0;
                }

                $detalles[] = $detalle;
            }
            //CÁLCULO DEL TOTAL


            if ($tipo_afectacion == '1' && $igv_detalle == '1') {

                $total = $sub_total;
                $monto_igv = $total * $this->IGV / (1 + $this->IGV);
                $monto_sin_igv = $total - $monto_igv;
                $igv = $this->IGV;
            }

            if ($tipo_afectacion == '1' && $igv_detalle == '0') {
                $monto_sin_igv = $sub_total;
                $monto_igv = $monto_sin_igv * $this->IGV;
                $total = $monto_sin_igv + $monto_igv;
                $igv = $this->IGV;
            }

            if ($tipo_afectacion == '2') {
                $monto_sin_igv = $sub_total;
                $monto_igv = 0;
                $total = $monto_sin_igv + $monto_igv;
                $igv = $this->IGV;
            }

            //COMPRA

            $sql = "insert into log_compra(fecha,id_usuario,id_proveedor,tipo_documento,tipo_afectacion,monto_sin_igv,igv,monto_igv,"
                . "total,nota_credito,fecha_sistema,tipo_compra,serie,nro_documento,nro_dias) values('$fecha','$usuario','$proveedor',"
                . "'$tipo_documento','$tipo_afectacion',"
                . "'" . $this->redondear_dos_decimal($monto_sin_igv) . "','$igv','" . $this->redondear_dos_decimal($monto_igv) . "','" . ($this->redondear_dos_decimal($total + $redondeo)) . "','$nota_credito','$date','$tipo_compra','$serie','$nro_documento','$nro_dias');";
            //DETALLES
            foreach ($detalles as $detalle) {

                $id_producto = $detalle['id_producto'];
                $cantidad_orden = $detalle['cantidad_orden'];
                $orden = $detalle['orden'];
                $cantidad = $detalle['cantidad'];
                $fecha_vencimiento = $detalle['fecha_vencimiento'];
                $bonificacion = $detalle['bonificacion'];
                $nro_lote = $detalle['nro_lote'];
                $precio = $detalle['precio'];
                $precio_sin_igv = $detalle['precio_sin_igv'];
                $monto_igv = $detalle['monto_igv'];
                $subtotal = $detalle['subtotal'];
                $precio_anterior = $detalle['precio_anterior'];

                //Si es producto y no servicio
                $producto = $this->ListarProductoLogxid($id_producto)->fetch();
                $unidad = $producto['unidad'];
                $id_categoria = $producto['id_categoria'];

                $lote = '0';
                $nro_orden = '';

                //Cuando es producto, no servicio
                if ($producto['tipo_producto'] == '0') {

                    $id_lote = $this->ListarLotexNroProAlm($nro_lote, $id_producto, $id_almacen)->fetch();

                    //Si lote no existe insertamos sino lo actualizamos 
                    if ($id_lote[0] == '') {
                        $sql .= "insert into log_lote(nro,id_producto,cantidad,unidad,fecha_vencimiento,id_almacen)
                        values('$nro_lote','$id_producto','$cantidad','$unidad','$fecha_vencimiento','$id_almacen');";
                        $lote = "(select max(id) from log_lote)";
                    } else {
                        $lote = $id_lote[0];
                        $sql .= "update log_lote set cantidad=cantidad+$cantidad  where id=" . $id_lote[0] . ";";
                    }


                    //KARDEX
                    $costo_total = $precio_sin_igv * $cantidad;


                    $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                            fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                        . " values ('1','02','$id_producto','$id_categoria',$lote,'" . $this->redondear_dos_decimal($precio_sin_igv) . "','$fecha','$tipo_documento',
                        '$nro_documento','$producto[3]','$cantidad','$id_almacen',(select max(id) from log_compra),'$usuario',$costo_total);";
                }


                //SI DETALLE PERTENECE A ORDEN DE COMPRA
               // if ($orden == '1') {
                    $nro_orden = $this->ListarOrdenCompraxId($id_orden)->fetch()['numero'];

                    $sql .= "insert into log_orden_documento(id_orden_compra,id_producto,cant_orden,id_compra,cant_compra)
                            values('$id_orden','$id_producto','$cantidad_orden',(select max(id) from log_compra),'$cantidad');";

                    $sql .= "update log_orden_compra_detalle set despachado=despachado + $cantidad , pendiente=pendiente-$cantidad 
                        where id_orden_compra='$id_orden'  and id_producto='$id_producto' ;";
               //}

                //precio anterior
                // $precio_anterior = $this->UltimaPrecioCompra($id_producto);
                //if ($precio_anterior == '' || $precio_anterior == '0' || $precio_anterior == null) {
                //  $precio_anterior = '0.00';
                // }

                //Insertar detalles de compra
                $sql .= "insert into log_compra_detalle(id_compra,id_producto,bonificacion,id_lote,fecha_vencimiento,cantidad,precio,"
                    . "precio_sin_igv,monto_igv,subtotal,precio_compra_ant,nro_orden)values((select max(id) from log_compra),'$id_producto',"
                    . "'$bonificacion',$lote,'$fecha_vencimiento','$cantidad','$precio',"
                    . "'" . $this->redondear_dos_decimal($precio_sin_igv) . "','" . $this->redondear_dos_decimal($monto_igv) . "','" . $this->redondear_dos_decimal($subtotal) . "','$precio_anterior','$nro_orden');";
            }
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 'OK';
            //$return =$sql;
        } catch (PDOException $ex) {
            $cn->rollBack();

            $cn = null;
            //return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }


    public function TotalySubtotalCompra(
        $detalles_compra,
        $tipo_afectacion,
        $igv_detalle,
        $redondeo
    ) {


        //CÁLCULO DEL DETALLE
        $sub_total = 0;
        foreach ($detalles_compra as $detalle) {

            if ($igv_detalle == '1') { //FACT. AFECTA A IGV  
                $detalle['monto_igv'] = ($detalle['precio'] * $this->IGV) / (1 + $this->IGV);
                $detalle['precio_sin_igv'] = $detalle['precio'] - $detalle['monto_igv'];
            } else {
                $detalle['monto_igv'] = $detalle['precio'] * $this->IGV;
                $detalle['precio_sin_igv'] = $detalle['precio'];
            }
            $detalle['subtotal'] = (($detalle['precio']) * $detalle['cantidad']);
            if ($detalle['bonificacion'] == '0') {
                $sub_total =  $sub_total + $detalle['subtotal'];
            }
        }

        //CÁLCULO DEL TOTAL


        if ($tipo_afectacion == '1' && $igv_detalle == '1') {

            $total = $sub_total;
            $monto_igv = $total * $this->IGV / (1 + $this->IGV);
            $monto_sin_igv = $total - $monto_igv;
            $igv = $this->IGV;
        }

        if ($tipo_afectacion == '1' && $igv_detalle == '0') {
            $monto_sin_igv = $sub_total;
            $monto_igv = $monto_sin_igv * $this->IGV;
            $total = $monto_sin_igv + $monto_igv;
            $igv = $this->IGV;
        }

        if ($tipo_afectacion == '2') {
            $monto_sin_igv = $sub_total;
            $monto_igv = 0;
            $total = $monto_sin_igv + $monto_igv;
            $igv = $this->IGV;
        }

        $totales = array();
        $totales['total'] = $this->redondear_dos_decimal($total + $redondeo);
        $totales['monto_sin_igv'] = $this->redondear_dos_decimal($monto_sin_igv);
        $totales['monto_igv'] = $this->redondear_dos_decimal($monto_igv);

        return $totales;
    }

    function ListarCompra($q, $fecha_inicio, $fecha_fin)
    {
        $ocado = new cado();
        $where = '';
        if ($fecha_inicio != '' && $fecha_fin != '') {
            $where = "and c.fecha between '$fecha_inicio' and '$fecha_fin'";
        }

        $sql = "select top 200 c.fecha,u.nombre,prov.nombre,c.tipo_documento,ta.descripcion,CAST(c.monto_sin_igv as decimal(18,2))
		,CAST(c.igv as decimal(18,2)),CAST(c.monto_igv as decimal(18,2)),c.total,c.id,c.nota_credito,c.fecha_sistema,c.tipo_compra,
		c.nro_documento,c.nro_dias,c.serie 
		from log_compra c inner join usuario u on c.id_usuario=u.id inner join admin_tipo_afectacion_igv ta on 	
			 ta.id=c.tipo_afectacion inner join log_proveedor prov on prov.id=c.id_proveedor
		where c.nro_documento like '%$q%' $where order by c.fecha desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function TotalCompra($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from log_compra c inner join usuario u on c.id_usuario=u.id inner join"
            . " admin_tipo_afectacion_igv ta on ta.id=c.tipo_afectacion where c.nro_documento like '%$q%' ";


        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    // FUNCIONES PARA EL MANTENEDOR COMPRA_ETALLES

    function ListarCompraDetalles($compra)
    {
        $ocado = new cado();
        $sql = "select p.nombre,c.*,l.nro,c.nro_orden from log_compra_detalle c inner join log_producto p on c.id_producto=p.id 
         left join log_lote l on l.id=c.id_lote where id_compra=$compra";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function UltimaPrecioCompra($id)
    {
        $ocado = new cado();
        $sql = "SELECT top 1 * from  log_compra_detalle where id_producto=$id order by id desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar->fetch()['precio_sin_igv'];
    }
    // FUNCIONES PARA EL MANTENEDOR LOTE
    function ListarLote($nombre, $id_almacen)
    {
        $where = "";
        $limit = "";
        if ($id_almacen != "") { $where .= " l.id_almacen=$id_almacen and"; }
        if ($nombre == "" && $id_almacen == "") { $limit = "top 9"; }
        $ocado = new cado();
        $sql = "SELECT $limit l.nro,p.nombre,l.cantidad,l.fecha_vencimiento as fecha_vencimiento,lcu.descripcion as unidad from log_lote l
         inner join log_producto p on l.id_producto=p.id  inner join log_codigo_unidad_medida lcu on lcu.id=p.unidad where $where (l.nro like '%$nombre%' or p.nombre like '%$nombre%' or l.fecha_vencimiento like '%$nombre%') order by p.id desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarLotexCategoriaAlmacen($id_categoria, $id_almacen)
    {
        $ocado = new cado();
        $sql = "SELECT l.nro,p.nombre,l.cantidad,l.fecha_vencimiento as fecha_vencimiento,lcu.descripcion as unidad from log_lote l
         inner join log_producto p on l.id_producto=p.id  inner join log_codigo_unidad_medida lcu on lcu.id=p.unidad where l.id_almacen=$id_almacen 
         and p.id_categoria=$id_categoria order by p.id desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarLoteReactivos()
    {
        $ocado = new cado();
        $sql = "SELECT l.id,p.nombre,l.nro,l.cantidad,l.fecha_vencimiento as fecha_vencimiento,a.nombre as almacen,s.nombre as sucursal,lcu.descripcion as unidad from log_lote l
        inner join log_producto p on l.id_producto=p.id inner join log_almacen a on a.id=l.id_almacen inner join cont_sucursal s on s.id=a.id_sucursal inner join log_codigo_unidad_medida lcu on lcu.id=p.unidad  "
            . " where l.cantidad >0 order by l.fecha_vencimiento desc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarLotesxProductoFechaAsc($id_producto)
    {
        $ocado = new cado();
        $sql = "SELECT * from log_lote "
            . " where cantidad >0 and id_producto=$id_producto order by fecha_vencimiento asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ListarLotexAlmacen($almacen)
    {
        $ocado = new cado();
        $sql = "SELECT l.id,l.nro,p.nombre,l.cantidad,l.fecha_vencimiento as fecha_vencimiento,a.nombre as almacen from log_lote l
         inner join log_producto p on l.id_producto=p.id inner join log_almacen a on a.id=l.id_almacen where a.id=$almacen order by p.id desc  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function TotalLote($nombre)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from log_lote l inner join log_producto p on l.id_producto=p.id "
            . "where l.nro like '%$nombre%' or p.nombre like '%$nombre%' or l.fecha_vencimiento like '%$nombre%'  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarLotexid($id)
    {
        $ocado = new cado();
        $sql = "select * from log_lote where  id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarLotexNroProAlm($nro, $id_producto, $id_almacen)
    {
        $ocado = new cado();
        $sql = "select * from log_lote where  id_producto=$id_producto and nro='$nro' and id_almacen=$id_almacen  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function ProductoxLote($id)
    {
        $ocado = new cado();
        $sql = "select p.*,l.cantidad as stock,u.descripcion as nombre_unidad from log_lote l 
        inner join log_producto p on l.id_producto=p.id 
        inner join log_codigo_unidad_medida u on p.unidad=u.id
        where  l.id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function StockxProducto($id_producto)
    {
        $ocado = new cado();
        $sql = "SELECT SUM(cantidad) as stock FROM `log_lote` WHERE id_producto=$id_producto ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ListarLoteFraccionxAlmacen($almacen)
    {
        $ocado = new cado();
        $sql = "SELECT l.id,l.nro,p.nombre,l.cantidad,l.fecha_vencimiento  as fecha_vencimiento,a.nombre as almacen from log_lote l
         inner join log_producto p on l.id_producto=p.id inner join log_almacen a on a.id=l.id_almacen inner join log_producto pf on p.id_producto_fraccion=pf.id where a.id=$almacen order by p.id desc  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ProductoFraccionxLote($id)
    {
        $ocado = new cado();
        $sql = "select p.*,l.cantidad as stock,lcu.descripcion as nombre_unidad,pf.id as id_producto_fraccion from log_lote l inner join log_producto p on l.id_producto=p.id
        inner join log_producto pf on p.id_producto_fraccion=pf.id inner join log_codigo_unidad_medida lcu on lcu.id=p.unidad 
         where  l.id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function LoteXnro_lote($nro_lote, $id_producto)
    {
        $ocado = new cado();
        $sql = "select id from log_lote where  nro='$nro_lote' and id_producto='$id_producto' ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ValidarLote($nombre)
    {
        $ocado = new cado();
        $sql = "select count(*) from log_producto where nombre='$nombre' and estado='0'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    // FUNCIONES PARA EL MANTENEDOR ORDEN DOCUMENTO
    function ListarOrdDoc($nombre)
    {
        $ocado = new cado();
        $sql = "SELECT p.nombre,oc.numero,l.cant_orden,c.tipo_documento,concat(td.descripcion, ' - ',c.nro_documento)nro_documento,l.cant_compra from log_orden_documento l 
      inner join log_producto p on l.id_producto=p.id inner join log_orden_compra oc on l.id_orden_compra=oc.id
       inner join log_compra c on l.id_compra=c.id inner join log_tipo_documento td on  td.id=c.tipo_documento
       where  p.nombre like '%$nombre%' or c.nro_documento like '%$nombre%' or oc.numero like '%$nombre' order by l.id desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function TotalOrdDoc($nombre)
    {
        $ocado = new cado();

        $sql = "SELECT count(*) from log_orden_documento l inner join log_producto p on l.id_producto=p.id 
      inner join log_orden_compra oc on l.id_orden_compra=oc.id inner join log_compra c on l.id_compra=c.id "
            . "where  p.nombre like '%$nombre%' or c.nro_documento like '%$nombre%' or oc.numero like '%$nombre%'  ";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    //FUNCIONES PARA KARDEX

    function ListarKardex($id_producto, $inicio, $fin)
    {
        $ocado = new cado();
        $sql = "SELECT k.fecha,IIF(td.codigo!='',td.codigo,'-')codigo,IIF(c.serie!='',c.serie,'-')serie,k.nro_doc,k.id_tipo_operacion,IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0,k.cantidad,'0') as cantidad_entrada,
        IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0 ,cast(precio as decimal(18,2)),'0.00') as precio_entrada, IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0,cast(costo_total as decimal(18,2)),'0.00') as costo_total_entrada, 
        IIF(k.tipo_movimiento=2,k.cantidad,'0') as cantidad_salida,IIF(k.tipo_movimiento=2,cast(precio as decimal(18,2)),'0.00') as precio_salida, 
        IIF(k.tipo_movimiento=2,cast(costo_total as decimal(18,2)),'0.00') as costo_total_salida ,k.tipo_movimiento,k.cantidad,k.costo_total       
       FROM log_kardex k
       LEFT JOIN log_tipo_documento td on k.id_tipo_documento=td.id
	   LEFT JOIN log_compra c on k.nro_doc=c.nro_documento
       where k.id_producto=$id_producto and cast(k.fecha as date)>=cast('$inicio' as date) and cast(k.fecha as date)<=cast('$fin' as date) order by k.fecha asc ,k.id asc";        
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }


    function TotalKardex($id_producto)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from log_kardex where id_producto='$id_producto';";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarKardexAlmacen($id_producto, $id_almacen, $inicio, $numero_filas)
    {
        $ocado = new cado();
        $sql = "SELECT k.fecha,IIF(td.descripcion!='',td.descripcion,'-'),k.nro_doc,k.id_tipo_operacion,IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0,k.cantidad,'0') as cantidad_entrada,
        IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0,cast(precio as decimal(18,2)),'0.00') as precio_entrada, IIF(k.tipo_movimiento=1 or k.tipo_movimiento=0,cast(costo_total as decimal(18,2)),'0.00') as costo_total_entrada, 
        IIF(k.tipo_movimiento=2,k.cantidad,'0') as cantidad_salida,IIF(k.tipo_movimiento=2,cast(precio as decimal(18,2)),'0.00') as precio_salida, 
        IIF(k.tipo_movimiento=2,cast(costo_total as decimal(18,2)),'0.00') as costo_total_salida ,k.tipo_movimiento,k.cantidad,k.costo_total 
       FROM log_kardex k
       LEFT JOIN log_tipo_documento td on k.id_tipo_documento=td.id
       JOIN log_lote l ON l.id=k.id_lote 
       where k.id_producto=$id_producto and l.id_almacen=$id_almacen  order by k.fecha asc,k.id asc     ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }


    function TotalKardexAlmacen($id_producto, $id_almacen)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from log_kardex k JOIN log_lote l ON l.id=k.id_lote  where k.id_producto='$id_producto' and l.id_almacen=$id_almacen ;  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function TransferenciaAlmacen($almacen_origen, $almacen_destino, $transferencias)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "61";
            //$usuario = "1";
            $fecha = date('Y-m-d');
            $sql = '';
            foreach ($transferencias as $t) {



                //Lote de almacen de origen
                $lote = $this->ListarLotexid($t['id_lote'])->fetch();

                //DISMINUYE  CANTIDAD EN LOTE DE ALMACEN DE ORIGEN
                $sql .= "update log_lote set cantidad=cantidad-" . $t['cantidad'] . " where id=" . $t['id_lote'] . ";";



                //AUMENTA CANTIDAD EN LOTE DE ALMACEN DE DESTINO

                //SI NO EXISTE INSERTA  SI EXISTE ACTUALIZA  LOTE EN ALMACEN

                $lote_destino = $this->ListarLotexNroProAlm($lote['nro'], $lote['id_producto'], $almacen_destino)->fetch();
                if ($lote_destino[0] == '') {
                    $sql .= "insert into log_lote(nro,id_producto,cantidad,unidad,fecha_vencimiento,id_almacen)
     values('$lote[1]','$lote[2]','" . $t['cantidad'] . "','" . $t['unidad'] . "','$lote[5]','$almacen_destino');";
                    $id_lote_destino = "(select max(id) from log_lote)";
                } else {

                    $sql .= "update log_lote set cantidad=cantidad+" . $t['cantidad'] . " where id='$lote_destino[0]';";
                    $id_lote_destino = $lote_destino[0];
                }


                //KARDEX 


                $id_producto = $lote['id_producto'];
                $producto = $this->ListarProductoLogxid($id_producto)->fetch();
                $id_categoria = $producto['id_categoria'];


                //KARDEX
                $precio = $this->UltimaPrecioCompra($lote['id_producto']);

                $costo_total = $precio * $t['cantidad'];

                //SALIDA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
       fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('2','11','$id_producto','$id_categoria'," . $t['id_lote'] . ",'$precio','$fecha','',
     '','$producto[3]','" . $t['cantidad'] . "','$almacen_origen','0','$usuario',$costo_total);";
                //ENTRADA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
       fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('1','21','$id_producto','$id_categoria',$id_lote_destino,'$precio','$fecha','',
     '','$producto[3]','" . $t['cantidad'] . "','$almacen_destino','0','$usuario',$costo_total);";
            }

            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }


    function FraccionarLote($almacen, $cantidad_origen, $cantidad_destino, $unidad_origen, $unidad_destino, $id_producto_origen, $id_producto_destino, $id_lote_origen)
    {
        //$ocado = new cado();

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "61";
            //$usuario = "1";
            $fecha = date('Y-m-d');

            //Lote de almacen de origen
            $lote = $this->ListarLotexid($id_lote_origen)->fetch();

            //DISMINUYE  CANTIDAD EN LOTE DE ALMACEN DE ORIGEN
            $sql = "update log_lote set cantidad=cantidad-$cantidad_origen where id=$id_lote_origen;";



            //AUMENTA CANTIDAD EN LOTE DE ALMACEN DE DESTINO

            //SI NO EXISTE INSERTA  SI EXISTE ACTUALIZA  LOTE EN ALMACEN

            $lote_destino = $this->ListarLotexNroProAlm($lote['nro'], $id_producto_destino, $almacen)->fetch();
            if ($lote_destino[0] == '') {
                $sql .= "insert into log_lote(nro,id_producto,cantidad,unidad,fecha_vencimiento,id_almacen)
                values('$lote[1]','$id_producto_destino','$cantidad_destino','$unidad_destino','$lote[5]','$almacen');";
                $id_lote_destino = "(select max(id) from log_lote)";
            } else {

                $sql .= "update log_lote set cantidad=cantidad+$cantidad_destino where id='$lote_destino[0]';";
                $id_lote_destino = $lote_destino[0];
            }


            //KARDEX
            $producto_origen = $this->ListarProductoLogxid($id_producto_origen)->fetch();
            $producto_destino = $this->ListarProductoLogxid($id_producto_destino)->fetch();

            $id_categoria_producto_origen = $producto_origen['id_categoria'];
            $id_categoria_producto_destino = $producto_destino['id_categoria'];


            $precio_origen = $this->UltimaPrecioCompra($id_producto_origen);
            $precio_destino = $precio_origen / $cantidad_destino;
            if ($precio_destino == '') {
                $precio_destino = '0';
            }




            $costo_total_origen = $precio_origen * $cantidad_origen;
            $costo_total_destino = $precio_destino * $cantidad_destino;

            //SALIDA
            $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                  fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('2','11','$id_producto_origen',$id_categoria_producto_origen,$id_lote_origen,'$precio_origen','$fecha','',
                '','$unidad_origen','$cantidad_origen','$almacen','','$usuario',$costo_total_origen);";
            //ENTRADA
            $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                  fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('1','21','$id_producto_destino',$id_categoria_producto_destino,$id_lote_destino,'$precio_destino','$fecha','',
                '','$unidad_destino','$cantidad_destino','$almacen','','$usuario',$costo_total_destino);";


            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }

    //CONTROL DE REACTIVOS


    function RegistrarMaquina($nombre,$area)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "insert into log_maquina(nombre,estado,id_area) values('$nombre','0','$area') ";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function ModificarMaquina($id, $nombre,$area)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_maquina set nombre='$nombre', id_area='$area' where id='$id'  ";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function EliminarMaquina($id)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_maquina set estado='1' where id='$id'  ";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function ListarEquipos(){
        $ocado= new cado();
        $sql="select * from log_maquina where estado=0";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function ListarMaquinas($nombre)
    {
        $ocado = new cado();
        $sql = "SELECT m.id,m.nombre equipo,a.nombre area from log_maquina m inner join log_area a on m.id_area=a.id
        where m.nombre like '%%' and m.estado='0' order by m.nombre desc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function TotalMaquina($nombre)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from log_maquina where nombre like '%$nombre%' and estado='0';  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarExamenReactivo($id_examen)
    {
        $ocado = new cado();
        $sql = "SELECT exr.id,r.nombre,exr.cantidad ,exr.id_examen from examen_reactivo exr inner join log_producto r on r.id=exr.id_reactivo where exr.id_examen=$id_examen and exr.estado='0'  ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ListarReactivosxExamen($id_examen)
    {
        $ocado = new cado();
        $sql = "SELECT r.id,exr.cantidad  from examen_reactivo exr inner join log_producto r on r.id=exr.id_reactivo where exr.id_examen=$id_examen and exr.estado='0'  ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function RegistrarExamenReactivo($id_reactivo, $cantidad, $id_examen)
    {
        $ocado = new cado();
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "INSERT INTO examen_reactivo(id,id_examen,id_reactivo,cantidad,estado) values($id_examen" . "$id_reactivo,$id_examen,$id_reactivo,$cantidad,'0');";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
            //$return =$sql;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }

    function EliminarExamenReactivo($id)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            //$sql = "update examen_reactivo set estado='1' where id='$id'  ";
            $sql = "DELETE from examen_reactivo where id= $id;";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function RegistrarCalibracion($id_producto, $fecha, $cantidad, $id_maquina)
    {
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $usuario = '1';
            $lote = $this->ListarLotesxProductoFechaAsc($id_producto)->fetch(); // lote más antiguo
            $producto = $this->ListarProductoLogxid($id_producto)->fetch();
            $id_categoria = $producto['id_categoria'];
            $id_unidad = $producto['id_unidad'];

            $precio = $this->UltimaPrecioCompra($id_producto)->fetch();
            $costo_total = $precio * $cantidad;

            $id_almacen = $lote['id_almacen'];

            $sql = "update log_lote set cantidad=cantidad-$cantidad where id=" . $lote['id'] . ";";

            $sql .= "INSERT INTO calibracion(id_reactivo,id_lote,fecha,cantidad,id_maquina,estado) values($id_producto," . $lote['id'] . ",'$fecha',$cantidad,$id_maquina,'0');";

            //SALIDA
            $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                  fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('2','',$id_producto,$id_categoria,$lote[0],$precio,'$fecha','',
                '','$id_unidad','$cantidad','$id_almacen','','$usuario',$costo_total);";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function ModificarCalibracion($id, $id_producto, $fecha, $cantidad, $id_maquina)
    {


        try {

            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $usuario = '1';

            //REESTABLECER :
            $calibracion = $this->CalibracionXId($id)->fetch();

            //RETORNAR LOTE 
            $sql = "update log_lote set cantidad=cantidad+" . $calibracion['cantidad'] . " where id=" . $calibracion['id_lote'] . ";";


            $lote = $this->ListarLotexid($calibracion['id_lote'])->fetch();
            $producto = $this->ListarProductoLogxid($calibracion['id_reactivo'])->fetch();
            $id_categoria = $producto['id_categoria'];
            $id_unidad = $producto['id_unidad'];

            $precio = $this->UltimaPrecioCompra($calibracion['id_reactivo']);
            $costo_total = $precio * $calibracion['cantidad'];

            $id_almacen = $lote['id_almacen'];

            //ENTRADA KARDEX
            $sql .= "INSERT into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
          fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('1',''," . $calibracion['id_reactivo'] . ",$id_categoria," . $calibracion['id_lote'] . ",$precio,'" . $calibracion['fecha'] . "','',
        '','$id_unidad'," . $calibracion['cantidad'] . ",'$id_almacen','','$usuario',$costo_total);";



            //NUEVO :

            $lote = $this->ListarLotesxProductoFechaAsc($id_producto)->fetch();
            $producto = $this->ListarProductoLogxid($id_producto)->fetch();
            $id_categoria = $producto['id_categoria'];
            $id_unidad = $producto['id_unidad'];

            $precio = $this->UltimaPrecioCompra($id_producto);
            $costo_total = $precio * $cantidad;

            $id_almacen = $lote['id_almacen'];

            //ACTUALIZAR CALIBRACION
            $sql .= "UPDATE calibracion set id_reactivo=$id_producto,id_lote=$lote[0], fecha='$fecha',cantidad='$cantidad',id_maquina=$id_maquina where id=$id  ;";

            // NUEVO LOTE
            $sql .= "update log_lote set cantidad=cantidad-$cantidad where id=" . $lote['id'] . ";";

            //SALIDA KARDEX
            $sql .= "INSERT into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
          fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('2','',$id_producto,$id_categoria, $lote[0],$precio,'$fecha','',
        '','$id_unidad','$cantidad','$id_almacen','','$usuario',$costo_total);";


            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function EliminarCalibracion($id)
    {
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "UPDATE calibracion set estado='1' where id=$id;";

            $usuario = '1';

            //REESTABLECER :
            $calibracion = $this->CalibracionXId($id)->fetch();

            //RETORNAR LOTE 
            $sql .= "UPDATE log_lote set cantidad=cantidad+" . $calibracion['cantidad'] . " where id=" . $calibracion['id_lote'] . ";";


            $lote = $this->ListarLotexid($calibracion['id_lote'])->fetch();
            $producto = $this->ListarProductoLogxid($calibracion['id_reactivo'])->fetch();
            $id_categoria = $producto['id_categoria'];
            $id_unidad = $producto['id_unidad'];

            $precio = $this->UltimaPrecioCompra($calibracion['id_reactivo']);
            $costo_total = $precio * $calibracion['cantidad'];

            $id_almacen = $lote['id_almacen'];

            //ENTRADA KARDEX
            $sql .= "INSERT into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                        fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('1',''," . $calibracion['id_reactivo'] . ",$id_categoria," . $calibracion['id_lote'] . ",$precio,'" . $calibracion['fecha'] . "','',
                        '','$id_unidad'," . $calibracion['cantidad'] . ",'$id_almacen','','$usuario',$costo_total);";




            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    function CalibracionXId($id)
    {
        $ocado = new cado();
        $sql = "SELECT * from calibracion where id=$id ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ListarCalibraciones($fecha, $inicio, $numero_filas)
    {
        $ocado = new cado();
        $sql = "SELECT top $numero_filas c.id,c.fecha,p.nombre as nombre_reactivo,c.cantidad,m.nombre as nombre_maquina,p.id as id_reactivo,m.id as id_maquina 
          from calibracion c  
         inner join log_producto p on c.id_reactivo=p.id
         inner join log_maquina m on m.id=c.id_maquina  where c.fecha like '%$fecha%' and c.estado='0' order by c.fecha desc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function TotalCalibracion($fecha)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from calibracion c  
         inner join log_producto p on c.id_reactivo=p.id
         inner join log_maquina m on m.id=c.id_maquina  where c.fecha like '%$fecha%' ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarCalibracionesxPeriodo($id_reactivo, $fecha1, $fecha2)
    {
        $ocado = new cado();
        $sql = "SELECT c.fecha,m.nombre as nombre_maquina,c.cantidad from calibracion c  inner join log_producto r on r.id=c.id_reactivo
         inner join log_maquina m on m.id=c.id_maquina  where c.fecha between '$fecha1' and DATEADD(DAY,-1,'$fecha2') and id_reactivo='$id_reactivo' and c.estado='0' ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function TotalCalibracionesxPeriodo($id_reactivo, $fecha1, $fecha2)
    {
        $ocado = new cado();
        $sql = "SELECT sum(c.cantidad) from calibracion c  inner join log_producto r on r.id=c.id_reactivo
         inner join log_maquina m on m.id=c.id_maquina  where c.fecha between '$fecha1' and DATEADD(DAY,-1,'$fecha2') and id_reactivo='$id_reactivo' and c.estado='0' ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ListarExamenes($nombre, $inicio, $numero_filas)
    {
        $ocado = new cado();
        $sql = "SELECT top $numero_filas id,nombre from examen where nombre like '%$nombre%' and estado='0' order by nombre asc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }


    function TotalExamen($nombre)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from examen where nombre like '%$nombre%' and estado='0';  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }




    function RegistrarClienteExamen($id_cliente, $id_examen, $fecha)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "INSERT INTO cliente_examen(id_cliente,id_examen,fecha,estado) values($id_cliente,$id_examen,'$fecha','0');";


            $lista_reactivos = $this->ListarReactivosxExamen($id_examen);
            foreach ($lista_reactivos as $reactivo) {
                $usuario = '1';
                $lote = $this->ListarLotesxProductoFechaAsc($reactivo[0])->fetch(); // lote más antiguo
                $producto = $this->ListarProductoLogxid($reactivo[0])->fetch();
                $id_categoria = $producto['id_categoria'];
                $id_unidad = $producto['id_unidad'];

                $precio = $this->UltimaPrecioCompra($reactivo[0]);
                $costo_total = $precio * $reactivo[0];

                $id_almacen = $lote['id_almacen'];

                $sql .= "update log_lote set cantidad=cantidad-$reactivo[1] where id=" . $lote['id'] . ";";


                //SALIDA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                      fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('2','',$reactivo[0],$id_categoria,$lote[0],$precio,'$fecha','',
                    '','$id_unidad','$reactivo[1]','$id_almacen','','$usuario',$costo_total);";
            }


            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function ModificarClienteExamen($id, $id_cliente, $id_examen, $fecha)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción

            //DEVOLVER A LOTE INGRESAR A KARDEX
            $cliente_examen = $this->ClienteExamenxId($id)->fetch();
            $lista_reactivos = $this->ListarReactivosxExamen($cliente_examen['id_examen']);
            $sql = "";
            foreach ($lista_reactivos as $reactivo) {
                $usuario = '1';
                $lote = $this->ListarLotesxProductoFechaAsc($reactivo[0])->fetch(); // lote más antiguo
                $producto = $this->ListarProductoLogxid($reactivo[0])->fetch();
                $id_categoria = $producto['id_categoria'];
                $id_unidad = $producto['id_unidad'];

                $precio = $this->UltimaPrecioCompra($reactivo[0]);
                $costo_total = $precio * $reactivo[0];

                $id_almacen = $lote['id_almacen'];

                $sql .= "update log_lote set cantidad=cantidad+$reactivo[1] where id=" . $lote['id'] . ";";


                //ENTRADA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                      fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('1','',$reactivo[0],$id_categoria,$lote[0],$precio,'" . $cliente_examen['fecha'] . "','',
                    '','$id_unidad','$reactivo[1]','$id_almacen','','$usuario',$costo_total);";
            }

            //ACTUALIZAR CLIENTE EXAMEN

            $sql .= "update cliente_examen set id_cliente=$id_cliente,id_examen=$id_examen,fecha='$fecha' where id=$id;";

            //INGRESAR NUEVO 

            $lista_reactivos = $this->ListarReactivosxExamen($id_examen);
            foreach ($lista_reactivos as $reactivo) {
                $usuario = '1';
                $lote = $this->ListarLotesxProductoFechaAsc($reactivo[0])->fetch(); // lote más antiguo
                $producto = $this->ListarProductoLogxid($reactivo[0])->fetch();
                $id_categoria = $producto['id_categoria'];
                $id_unidad = $producto['id_unidad'];

                $precio = $this->UltimaPrecioCompra($reactivo[0]);
                $costo_total = $precio * $reactivo[0];

                $id_almacen = $lote['id_almacen'];

                $sql .= "update log_lote set cantidad=cantidad-$reactivo[1] where id=" . $lote['id'] . ";";


                //SALIDA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
          fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('2','',$reactivo[0],$id_categoria,$lote[0],$precio,'$fecha','',
        '','$id_unidad','$reactivo[1]','$id_almacen','','$usuario',$costo_total);";
            }



            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    function EliminarClienteExamen($id)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción

            //DEVOLVER A LOTE INGRESAR A KARDEX
            $cliente_examen = $this->ClienteExamenxId($id)->fetch();
            $lista_reactivos = $this->ListarReactivosxExamen($cliente_examen['id_examen']);
            $sql = "";
            foreach ($lista_reactivos as $reactivo) {
                $usuario = '1';
                $lote = $this->ListarLotesxProductoFechaAsc($reactivo[0])->fetch(); // lote más antiguo
                $producto = $this->ListarProductoLogxid($reactivo[0])->fetch();
                $id_categoria = $producto['id_categoria'];
                $id_unidad = $producto['id_unidad'];

                $precio = $this->UltimaPrecioCompra($reactivo[0]);
                $costo_total = $precio * $reactivo[0];

                $id_almacen = $lote['id_almacen'];

                $sql .= "update log_lote set cantidad=cantidad+$reactivo[1] where id=" . $lote['id'] . ";";


                //ENTRADA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                      fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('1','',$reactivo[0],$id_categoria,$lote[0],$precio,'" . $cliente_examen['fecha'] . "','',
                    '','$id_unidad','$reactivo[1]','$id_almacen','','$usuario',$costo_total);";
            }

            //ACTUALIZAR CLIENTE EXAMEN

            $sql .= "update cliente_examen set estado='1' where id=$id;";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }


    function ListarClienteExamen($fecha, $inicio, $numero_filas)
    {
        $ocado = new cado();
        $sql = "SELECT top $numero_filas ce.id,ce.fecha,c.nombre,e.nombre,c.id,e.id from cliente_examen ce inner join cliente c on c.id=ce.id_cliente
        inner join examen e on e.id=ce.id_examen where ce.fecha like '%$fecha%'  and ce.estado='0' order by ce.fecha";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }


    function TotalClienteExamen($fecha)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from cliente_examen ce inner join cliente c on c.id=ce.id_cliente
        inner join examen e on e.id=ce.id_examen where ce.fecha like '%$fecha%'  and ce.estado='0' ;  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ClienteExamenxId($id)
    {
        $ocado = new cado();
        $sql = "SELECT * from cliente_examen where id=$id;";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ListarClientes()
    {
        $ocado = new cado();
        $sql = "SELECT * from  cliente where estado='0' ;";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function ReporteReactivoxPeriodo($id, $fecha1, $fecha2)
    {
        $ocado = new cado();
        $sql = "SELECT ce.fecha,e.nombre,er.cantidad FROM cliente_examen ce 
        inner join examen_reactivo er on ce.id_examen=er.id_examen 
        inner join examen e on e.id=ce.id_examen 
        
        where ce.fecha BETWEEN '$fecha1' and DATEADD(DAY,-1,'$fecha2') and er.id_reactivo=$id and e.estado='0' and er.estado='0' and ce.estado='0'";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ReporteTotalReactivoxPeriodo($id, $fecha1, $fecha2)
    {
        $ocado = new cado();
        $sql = "SELECT sum(er.cantidad) FROM cliente_examen ce 
        inner join examen_reactivo er on ce.id_examen=er.id_examen 
        inner join examen e on e.id=ce.id_examen 
        where ce.fecha BETWEEN '$fecha1' and DATEADD(DAY,-1,'$fecha2') and er.id_reactivo=$id and e.estado='0' and er.estado='0' and ce.estado='0'";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function FechaIngresoReactivo($id)
    {
        $ocado = new cado();
        $sql = "select fecha from ingreso_reactivo where id=$id and estado='0'";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function FechaAnteriorIngresoReactivo($fecha, $id_reactivo)
    {
        $ocado = new cado();
        $sql = "SELECT top 1 c.fecha from log_compra_detalle cd inner join log_compra c on c.id=cd.id_compra where c.fecha<'$fecha' and cd.id_producto=$id_reactivo  GROUP BY c.fecha ORDER BY c.fecha DESC ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function FechaPosteriorIngresoReactivo($fecha, $id_reactivo)
    {
        $ocado = new cado();
        $sql = "SELECT top 1 c.fecha from log_compra_detalle cd inner join log_compra c on c.id=cd.id_compra where c.fecha>'$fecha' and cd.id_producto=$id_reactivo  GROUP BY c.fecha ORDER BY c.fecha ASC ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    function ListarIngresosxReactivo($id, $inicio, $numero_filas)
    {
        $ocado = new cado();
        $sql = " SELECT top $numero_filas c.fecha,sum(cd.cantidad) from log_compra_detalle cd inner join log_compra c on c.id=cd.id_compra where cd.id_producto=$id GROUP BY c.fecha ORDER BY c.fecha DESC ";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }

    function TotalIngresosxReactivo($id)
    {
        $ocado = new cado();
        $sql = "SELECT count(*) from log_compra_detalle cd inner join log_compra c on c.id=cd.id_compra where cd.id_producto=$id GROUP BY c.fecha ;  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ListarAreas()
    {
        $ocado = new cado();
        $sql = "SELECT *  from log_area where estado='0' order by nombre asc;";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTrasferirArea($id_area, $nombre_producto)
    {
        $ocado = new cado();
        $where = "";
        $limit = "";
        if ($id_area != "") {
            $where .= " t.id_area=$id_area and";
        }

        if ($nombre_producto == "" && $id_area == "") {
            $limit = "";
        }


        $sql = "SELECT $limit t.fecha,l.nro as lote ,p.nombre as producto,u.descripcion as unidad,a.nombre as area,t.cantidad,al.nombre as almacen  from log_transferir_area t inner join log_producto p on p.id=t.id_producto
        inner join log_lote l on l.id=t.id_lote inner join log_area a on a.id=t.id_area
         inner join log_almacen al on al.id=t.id_almacen_origen inner join log_codigo_unidad_medida u on p.unidad=u.id
         where $where ( p.nombre like '%$nombre_producto%' or l.nro  like  '%$nombre_producto%' ) order by t.fecha desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function TransferenciaArea($almacen_origen, $area, $transferencias,$fecha)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            //$usuario = $_SESSION['S_iduser'];
            $usuario = "61";
           // $fecha = date('Y-m-d');
            $sql = '';


            foreach ($transferencias as $t) {


                //Lote de almacen de origen
                $lote = $this->ListarLotexid($t['id_lote'])->fetch();

                //DISMINUYE  CANTIDAD EN LOTE DE ALMACEN DE ORIGEN
                $sql .= "update log_lote set cantidad=cantidad-" . $t['cantidad'] . " where id=" . $t['id_lote'] . ";";


                //KARDEX 


                $id_producto = $lote['id_producto'];
                $producto = $this->ListarProductoLogxid($id_producto)->fetch();
                $id_categoria = $producto['id_categoria'];


                //KARDEX
                $precio = $this->UltimaPrecioCompra($lote['id_producto']);

                $costo_total = $precio * $t['cantidad'];

                //SALIDA
                $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                  fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                    . " values ('2','11','$id_producto','$id_categoria'," . $t['id_lote'] . ",'$precio','$fecha','',
                '','$producto[3]','" . $t['cantidad'] . "','$almacen_origen','0','$usuario',$costo_total);";

                //Lista de transferencias a alamacén

                $sql .= "insert into log_transferir_area(fecha,id_producto,id_lote,cantidad,id_almacen_origen,id_usuario,id_area)"
                    . " values ('$fecha','$id_producto','" . $t['id_lote'] . "','" . $t['cantidad'] . "','$almacen_origen','$usuario','$area');";
            }



            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            // $return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }

    function RegistrarInventarioInicial($id_producto, $fecha, $almacen, $nro_lote, $cantidad, $precio)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $usuario = "61";
            //$fecha = date('Y-m-d');
            $sql = '';
            $id_lote = 0;


            $producto = $this->ListarProductoLogxid($id_producto)->fetch();
            $id_categoria = $producto['id_categoria'];
            $unidad = $producto['unidad'];

            //SI NO EXISTE INSERTA  SI EXISTE ACTUALIZA  LOTE EN ALMACEN

            $lote = $this->ListarLotexNroProAlm($nro_lote, $id_producto, $almacen)->fetch();
            if ($lote[0] == '') {
                $sql .= "insert into log_lote(nro,id_producto,cantidad,unidad,fecha_vencimiento,id_almacen)
                     values('$nro_lote','$id_producto','$cantidad','$unidad','$fecha','$almacen');";
                $id_lote = "(select max(id) from log_lote)";
            } else {

                $sql .= "update log_lote set cantidad=cantidad+$cantidad where id='$lote[0]';";
                $id_lote = $lote[0];
            }

           
            $costo_total = $precio * $cantidad;

            //SALIDA
            $sql .= "insert into log_kardex(tipo_movimiento,id_tipo_operacion,id_producto,id_categoria_producto,id_lote,precio,
                  fecha,id_tipo_documento,nro_doc,unidad,cantidad,id_almacen,id_referencia,id_usuario,costo_total)"
                . " values ('0','16','$id_producto','$id_categoria',$id_lote,'$precio','2020-01-01','',
                '','$producto[3]','$cantidad','$almacen','0','$usuario',$costo_total);";


            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            //$return = 0;
            $return = $ex->getMessage();
        }

        return $return;
    }


    function ListarInventarioInicial($nombre_producto, $nro_lote)
    {
        $ocado = new cado();

        $sql = "SELECT p.nombre,l.nro,k.cantidad,u.descripcion,k.precio,l.fecha_vencimiento,a.nombre from log_kardex k
        inner join log_producto p on p.id=k.id_producto inner join log_lote l on l.id=k.id_lote inner join log_codigo_unidad_medida u on u.id=p.unidad
        inner join log_almacen a on a.id=k.id_almacen where k.tipo_movimiento=0  and p.nombre like '%$nombre_producto%' and l.nro  like '%$nro_lote%' order by p.nombre asc ;  ";

        $listar = $ocado->ejecutar($sql);
        return $listar;
    }


    function ProductoInventarioInicial($id_producto)
    {
        $ocado = new cado();

        $sql = "SELECT count(*) from log_kardex k
        inner join log_producto p on p.id=k.id_producto 
        where k.tipo_movimiento=0  and p.id=$id_producto ;  ";

        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    
     //EXISTENCIAS
    function ListarExistencias($q)
    {
        $ocado = new cado();
        $sql = "select * from tipo_existencia te where (codigo like '%$q%' or descripcion like '%$q%') and te.estado=0 order by te.codigo asc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    
    function ListarExistenciaxId($id){
        $ocado = new cado();
        $sql = "select * from tipo_existencia where  id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function ValidarExistencia($descripcion){
        $ocado = new cado();
        $sql = "select count(*) from tipo_existencia where descripcion='$descripcion' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function RegistrarExistencia($codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into tipo_existencia(codigo,descripcion,estado) values('$codigo','$descripcion',0)";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }
        return $return;
    }
    
    function ModificarExistencia($id,$codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update tipo_existencia set codigo='$codigo', descripcion = '$descripcion'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function EliminarExistencia($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update tipo_existencia set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    //CODIGO DE UNIDAD DE MEDIDA
    function ListarUnidadMedida($q)
    {
        $ocado = new cado();
        $sql = "select * from log_codigo_unidad_medida cum where (codigo like '%$q%' or descripcion like '%$q%') and cum.estado=0 order by cum.descripcion asc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    
    function ListarUnidadMedidaxId($id){
        $ocado = new cado();
        $sql = "select * from log_codigo_unidad_medida where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function ValidarUnidadMedida($descripcion){
        $ocado = new cado();
        $sql = "select count(*) from log_codigo_unidad_medida where descripcion='$descripcion' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function RegistrarUnidadMedida($codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_codigo_unidad_medida(codigo,descripcion,estado) values('$codigo','$descripcion',0)";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }
        return $return;
    }
    
    function ModificarUnidadMedida($id,$codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_codigo_unidad_medida set codigo='$codigo', descripcion = '$descripcion'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function EliminarUnidadMedida($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_codigo_unidad_medida set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    //TIPO DE DOCUMENTO
    function ListarComprobante($q)
    {
        $ocado = new cado();
        $sql = "select * from log_tipo_documento td where (codigo like '%$q%' or descripcion like '%$q%') and td.estado=0 order by td.codigo asc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    
    function ListarComprobantexId($id){
        $ocado = new cado();
        $sql = "select * from log_tipo_documento where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function ValidarComprobante($descripcion){
        $ocado = new cado();
        $sql = "select count(*) from log_tipo_documento where descripcion='$descripcion' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function RegistrarComprobante($codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_tipo_documento(codigo,descripcion,estado) values('$codigo','$descripcion',0)";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }
        return $return;
    }
    
    function ModificarComprobante($id,$codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_tipo_documento set codigo='$codigo', descripcion = '$descripcion'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function EliminarComprobante($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_tipo_documento set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    //TIPO DE OPERACIÓN
    function ListarOperacion($q)
    {
        $ocado = new cado();
        $sql = "select * from log_tipo_operacion lto where (codigo like '%$q%' or descripcion like '%$q%') and lto.estado=0 order by lto.codigo asc";
        $listar = $ocado->ejecutar($sql);
        return $listar;
    }
    
    function ListarOperacionxId($id){
        $ocado = new cado();
        $sql = "select * from log_tipo_operacion where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function ValidarOperacion($descripcion){
        $ocado = new cado();
        $sql = "select count(*) from log_tipo_operacion where descripcion='$descripcion' and estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function RegistrarOperacion($codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction();
            $sql = "insert into log_tipo_operacion(codigo,descripcion,estado) values('$codigo','$descripcion',0)";
            $cn->prepare($sql)->execute();
            $cn->commit();
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //            $return= $ex->getMessage();
        }
        return $return;
    }
    
    function ModificarOperacion($id,$codigo,$descripcion){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_tipo_operacion set codigo='$codigo', descripcion = '$descripcion'  where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function EliminarOperacion($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_tipo_operacion set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }

    //AREAS
    function ListarArea($q)
    {
        $ocado = new cado();
        $sql = "Select * from log_area where estado='0' and nombre like '%$q%' order by nombre asc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    
    function RegistrarArea($nombre,$responsable)
    {

        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "insert into log_area(nombre,responsable,estado) values('$nombre','$responsable','0') ";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function ModificarArea($id,$nombre,$responsable){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una transacción
            $sql = "update log_area set nombre='$nombre', responsable = '$responsable' where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $cn = null;
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
    
    function EliminarArea($id){
        try {
            $ocado = new cado();
            $cn = $ocado->conectar();
            $cn->beginTransaction(); //inicia una trasacción
            $sql = "update log_area set estado=1 where id = $id";
            $cn->prepare($sql)->execute();
            $cn->commit(); //Consignar cambios
            $cn = null;
            $return = 1;
        } catch (PDOException $ex) {
            $cn->rollBack();
            $return = 0;
            //return $ex->getMessage();
        }
        return $return;
    }
}
