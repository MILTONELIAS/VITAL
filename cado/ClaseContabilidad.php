<?php
require_once("conexion.php");

class Contabilidad
{

    function ListarAuxiliar($q, $inicio, $fin)
    {
        $ocado = new cado();
        $sql = "select a.*,td.nombre as documento,ta.nombre as auxiliar from cont_auxiliar a inner join cont_tipo_documentos td on 
          a.tipo_documento=td.id inner join cont_tipo_auxiliares ta on a.tipo_auxiliar=ta.id where a.estado='0' and (a.ruc like '%$q%' or a.razon_social like '%$q%'  ) limit $inicio,$fin   ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalAuxiliar($q)
    {
        $ocado = new cado();
        $sql = "select count(*) from cont_auxiliar a inner join cont_tipo_documentos td on 
          a.tipo_documento=td.id inner join cont_tipo_auxiliares ta on a.tipo_auxiliar=ta.id where a.estado='0' and (a.ruc like '%$q%' or a.razon_social like '%$q%'  )   ";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarAuxiliar($ruc, $razon_social, $direccion, $telefono, $estado_auxiliar, $condicion, $tipo_documento, $tipo_auxiliar)
    {
        $ocado = new cado();
        $sql = "insert into cont_auxiliar(ruc,razon_social,direccion,telefono,estado_auxiliar,condicion,tipo_documento,tipo_auxiliar,estado) 
                    values('$ruc','$razon_social','$direccion','$telefono','$estado_auxiliar','$condicion','$tipo_documento','$tipo_auxiliar','0')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarAuxiliar($id, $ruc, $razon_social, $direccion, $telefono, $estado_auxiliar, $condicion, $tipo_documento, $tipo_auxiliar)
    {
        $ocado = new cado();
        $sql = "update cont_auxiliar set razon_social='$razon_social' ,ruc='$ruc' ,estado_auxiliar='$estado_auxiliar',direccion='$direccion', 
              telefono='$telefono',condicion='$condicion',tipo_documento='$tipo_documento',tipo_auxiliar='$tipo_auxiliar' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarAuxiliar($id)
    {
        $ocado = new cado();
        $sql = "update cont_auxiliar set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarCentroCostos($q, $inicio, $fin)
    {
        $ocado = new cado();
        $sql = "select top 100 * from cont_centro_costos where estado=0  and (codigo like '%$q%' or descripcion like '%$q%' ) order by codigo asc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalCentroCostos($q)
    {
        $ocado = new cado();

        $sql = " select count(*) from cont_centro_costos where estado=0  and (codigo like '%$q%' or descripcion like '%$q%' )";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarCentroCostosxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_centro_costos where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarCentroCostos($codigo, $descripcion, $cuenta, $activo)
    {
        $ocado = new cado();
        $sql = "insert into cont_centro_costos (codigo,descripcion,cuenta,activo,estado) 
              values('$codigo','$descripcion','$cuenta','$activo','0')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarCentroCostos($id, $codigo, $descripcion, $cuenta, $activo)
    {
        $ocado = new cado();
        $sql = "update cont_centro_costos set descripcion='$descripcion',codigo='$codigo',cuenta='$cuenta',activo='$activo'  where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarCentroCostos($id)
    {
        $ocado = new cado();
        $sql = "update cont_centro_costos set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ListarEmpresa($q, $inicio, $fin)
    {
        $ocado = new cado();

        $sql = "select top 100 * from cont_empresa where  estado=0  and (nombre like '%$q%' or ruc like '%$q%' ) order by nombre asc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function totalEmpresa($q)
    {
        $ocado = new cado();

        $sql = " select count(*) from cont_empresa where estado=0  and (nombre like '%$q%' or ruc like '%$q%' )";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function RegistrarEmpresa($nombre, $ruc, $direccion, $telefono, $pass, $representante, $dni, $cell)
    {
        $ocado = new cado();
        $sql = "insert into cont_empresa(nombre,ruc,estado,direccion,telefono,pass,representante,dni,cell) 
                  values('$nombre','$ruc','0','$direccion','$telefono','$pass','$representante','$dni','$cell')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarEmpresa($id, $ruc, $nombre, $direccion, $telefono, $pass, $representante, $dni, $cell)
    {
        $ocado = new cado();
        $sql = "update cont_empresa set nombre='$nombre' ,ruc='$ruc' ,pass='$pass', estado='0',direccion='$direccion', 
            telefono='$telefono',representante='$representante',dni='$dni',cell='$cell' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarEmpresa($id)
    {
        $ocado = new cado();
        $sql = "update cont_empresa set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }



    function ListarPLanCuentas($q, $inicio, $fin)
    {
        $ocado = new cado();
        $sql = "select top 100 p.id,p.codigo,p.nombre,p.haber,p.debe,p.tipo,p.id_tipo_auxiliar,p.tasa_cambio,p.estado,p.movimiento,p.estado_financiero,a.nombre as
           tipo_auxiliar from cont_plan_cuentas p INNER JOIN cont_tipo_auxiliares a on a.id=p.id_tipo_auxiliar
            where p.estado=0  and (p.codigo like '%$q%' or p.nombre like '%$q%'  ) order by p.codigo asc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalPLanCuentas($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_plan_cuentas p INNER JOIN cont_tipo_auxiliares a on a.id=p.id_tipo_auxiliar
            where p.estado=0  and (p.codigo like '%$q%'  or p.nombre like '%$q%' ) ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarPLanCuentasxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_plan_cuentas where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarPLanCuentas($codigo, $nombre, $tipo, $movimiento, $haber, $debe, $id_tipo_auxiliar, $tasa_cambio, $estado_financiero)
    {
        $ocado = new cado();
        $sql = "insert into cont_plan_cuentas (codigo,nombre,tipo,movimiento,haber,debe,id_tipo_auxiliar,tasa_cambio,estado,estado_financiero) 
              values('$codigo','$nombre','$tipo','$movimiento','$haber','$debe','$id_tipo_auxiliar','$tasa_cambio','0','$estado_financiero')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarPLanCuentas($id, $codigo, $nombre, $tipo, $movimiento, $haber, $debe, $id_tipo_auxiliar, $tasa_cambio, $estado_financiero)
    {
        $ocado = new cado();
        $sql = "update cont_plan_cuentas set nombre='$nombre' ,codigo='$codigo' ,tipo='$tipo', movimiento='$movimiento' ,
              haber='$haber',debe='$debe',id_tipo_auxiliar='$id_tipo_auxiliar',tasa_cambio='$tasa_cambio',estado_financiero='$estado_financiero' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarPLanCuentas($id)
    {
        $ocado = new cado();
        $sql = "update cont_plan_cuentas set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ListarSucursal($q, $inicio, $fin, $id_empresa)
    {
        $ocado = new cado();
        $sql = "select top 100 s.*,e.nombre as nombre_empresa from cont_sucursal s inner join cont_empresa e on e.id=s.empresa 
         where s.empresa=$id_empresa and s.estado=0  and (s.nombre like '%$q%' ) order by nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTodoSucursal()
    {
        $ocado = new cado();
        $sql = "select s.*,e.nombre as nombre_empresa from cont_sucursal s inner join cont_empresa e on e.id=s.empresa 
         where s.estado=0";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalSucursal($q, $id_empresa)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_sucursal s inner join cont_empresa e on e.id=s.empresa  where  s.empresa=$id_empresa and s.estado=0  and (s.nombre like '%$q%')  ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarSucursal($nombre, $departamento, $provincia, $distrito, $direccion, $telefono, $representante, $dni, $cell, $empresa)
    {
        $ocado = new cado();
        $sql = "insert into cont_sucursal(nombre,estado,logo,departamento,provincia,distrito,direccion,telefono,representante,dni,cell,empresa) 
                values('$nombre','0',null,'$departamento','$provincia','$distrito','$direccion','$telefono','$representante','$dni','$cell','$empresa')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarSucursal($id, $nombre, $departamento, $provincia, $distrito, $direccion, $telefono, $representante, $dni, $cell)
    {
        $ocado = new cado();
        $sql = "update cont_sucursal set nombre='$nombre' , estado='0', departamento='$departamento',provincia='$provincia',distrito='$distrito',direccion='$direccion', 
          telefono='$telefono',representante='$representante',dni='$dni',cell='$cell' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EliminarSucursal($id)
    {
        $ocado = new cado();
        $sql = "update cont_sucursal set estado=1 where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoAsientos($q, $inicio, $fin)
    {
        $ocado = new cado();
        $sql = "select * from cont_tipo_asientos  where estado=0  and (codigo like '%$q%' or nombre like '%$q%'  ) order by codigo asc limit $inicio,$fin ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalTipoAsientos($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_tipo_asientos  where estado=0  and (codigo like '%$q%' or nombre like '%$q%'  )  ";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoAsientosxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_tipo_asientos where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarTipoAsientos(
        $codigo,$nombre,$tipo,$apertura,$enero,$febrero,$marzo,$abril,$mayo,$junio,$julio,$agosto,$setiembre,$octubre,$noviembre,
        $diciembre,$cierre1,$cierre2,$cierre3) {
        $ocado = new cado();
        $sql = "insert into cont_tipo_asientos (codigo,nombre,tipo,apertura,enero,febrero,marzo,abril,mayo,junio,julio,agosto,
            setiembre,octubre,noviembre,diciembre,estado,cierre1,cierre2,cierre3) 
            values('$codigo','$nombre','$tipo','$apertura','$enero','$febrero','$marzo','$abril','$mayo','$junio','$julio',
            '$agosto','$setiembre','$octubre','$noviembre','$diciembre','0','$cierre1','$cierre2','$cierre3')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarTipoAsientos(
        $id,$codigo,$nombre,$tipo,$apertura,$enero,$febrero,$marzo,$abril,$mayo,$junio,$julio,$agosto,$setiembre,$octubre,
		$noviembre,$diciembre,$cierre1,$cierre2,$cierre3
    ) {
        $ocado = new cado();
        $sql = "update cont_tipo_asientos set nombre='$nombre' ,codigo='$codigo' ,tipo='$tipo', apertura='$apertura' ,
            enero='$enero',febrero='$febrero',marzo='$marzo',abril='$abril', mayo='$mayo', junio='$junio',julio='$julio',
            agosto='$agosto',setiembre='$setiembre',octubre='$octubre',noviembre='$noviembre',diciembre='$diciembre',
            cierre1='$cierre1',cierre2='$cierre2',cierre3='$cierre3' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarTipoAsientos($id)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_asientos set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoAuxiliares($q)
    {
        $ocado = new cado();
        $sql = "select top 100 * from cont_tipo_auxiliares where estado=0 and nombre like '%$q%'";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalTipoAuxiliares($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_tipo_auxiliares where estado=0 and nombre like '%$q%' ";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoAuxiliaresxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_tipo_auxiliares where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarTipoAuxiliares($codigo, $nombre)
    {
        $ocado = new cado();
        $sql = "insert into cont_tipo_auxiliares (codigo,nombre,estado) 
              values('$codigo','$nombre','0')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarTipoAuxiliares($id, $codigo, $nombre)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_auxiliares set nombre='$nombre' ,codigo='$codigo'  where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarTipoAuxiliares($id)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_auxiliares set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoCambio($q)
    {
        $ocado = new cado();
        $sql = "select top 100 * from cont_tipo_cambio where estado=0 and fecha like '%$q%' order by fecha desc";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function totalTipoCambio($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_tipo_cambio where estado=0 and fecha like '%$q%' ";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoCambioxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_tipo_cambio where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarTipoCambio($fecha, $compra_sunat, $venta_sunat, $compra_sbs, $venta_sbs)
    {
        $ocado = new cado();
        $sql = "insert into cont_tipo_cambio (fecha,compra_sunat,venta_sunat,compra_sbs,venta_sbs,estado) 
              values('$fecha','$compra_sunat','$venta_sunat','$compra_sbs','$venta_sbs','0')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarTipoCambio($id, $fecha, $compra_sunat, $venta_sunat, $compra_sbs, $venta_sbs)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_cambio set fecha='$fecha' ,compra_sunat='$compra_sunat' ,venta_sunat='$venta_sunat', compra_sbs='$compra_sbs' ,
              venta_sbs='$venta_sbs' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarTipoCambio($id)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_cambio set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }


    function ListarTipoDocumentos($q)
    {
        $ocado = new cado();
        $sql = "select top 100 * from cont_tipo_documentos where estado=0 and (codigo like '%$q%' or nombre like '%$q%'  ) order by nombre asc ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function totalTipoDocumentos($q)
    {
        $ocado = new cado();

        $sql = "select count(*) from cont_tipo_documentos where estado=0 and (codigo like '%$q%' or nombre like '%$q%'  )";

        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function ListarTipoDocumentosxId($id)
    {
        $ocado = new cado();
        $sql = "select * from cont_tipo_documentos where estado=0 and id=$id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function RegistrarTipoDocumentos($codigo, $nombre, $tipo, $abreviatura, $serie, $correlativo, $igv, $anticipo, $cuenta_ventas, $suma)
    {
        $ocado = new cado();
        $sql = "insert into cont_tipo_documentos (codigo,nombre,tipo,abreviatura,serie,correlativo,igv,anticipo,cuenta_ventas,suma,estado) 
            values('$codigo','$nombre','$tipo','$abreviatura','$serie','$correlativo','$igv','$anticipo','$cuenta_ventas','$suma','0')";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }

    function EditarTipoDocumentos($id, $codigo, $nombre, $tipo, $abreviatura, $serie, $correlativo, $igv, $anticipo, $cuenta_ventas, $suma)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_documentos set nombre='$nombre' ,codigo='$codigo' ,tipo='$tipo', abreviatura='$abreviatura' ,
            serie='$serie',correlativo='$correlativo',igv='$igv',anticipo='$anticipo', cuenta_ventas='$cuenta_ventas', suma='$suma' where id=$id ";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
    function EliminarTipoDocumentos($id)
    {
        $ocado = new cado();
        $sql = "update cont_tipo_documentos set estado='1' where id = $id";
        $ejecutar = $ocado->ejecutar($sql);
        return $ejecutar;
    }
}
