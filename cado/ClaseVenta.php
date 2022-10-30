<?php
   require_once('conexion.php');
   
   class Ventas{
	
	function ListarContrato($buscar){
	  $ocado=new cado();
	  $sql="select top 20 c.id,nro_contrato,conv.empresa,fecha_inicio,fecha_fin,etapa,tipo_pago,userr,conv.id id_convenio,u.id id_usuario,curier from contrato c left join convenio conv on c.id_convenio=conv.id INNER JOIN usuario u on c.id_usuar=u.id
	      where c.estado=0 and (nro_contrato like '%$buscar%' or conv.empresa like '%$buscar%') order by fecha_inicio desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarEmpresa($nombre){
		  $ocado=new cado();
		  $sql="select * from convenio where empresa like '%$nombre%' order by empresa asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function ListarUsuario($nombre){
		  $ocado=new cado();
		  $sql="select * from usuario where user like '%$nombre%' and estado=0 order by user asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}

	function ValidarContrato($nro){
	  $ocado=new cado();
	  $sql="select count(*) from contrato where nro_contrato='$nro'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}	 
	
	 function RegistrarNuevo($CboEmpresa,$txtNroContrato,$CboEtapa,$txtFechaIni,$txtFechaFin,$txtUsuario,$CboTipoPago,$CboCurier){
		$ocado=new cado();
		$sql="insert into contrato(id_convenio,nro_contrato,etapa,fecha_inicio,fecha_fin,id_usuar,tipo_pago,estado,curier)
	   values('$CboEmpresa','$txtNroContrato','$CboEtapa','$txtFechaIni','$txtFechaFin','$txtUsuario','$CboTipoPago',0,'$CboCurier')";
		  
	    $ejecutar=$ocado->ejecutar($sql);
	    return $ejecutar;
	 }	  
	 
	 function Modificar($id,$CboEmpresa,$txtNroContrato,$CboEtapa,$txtFechaIni,$txtFechaFin,$txtUsuario,$CboTipoPago,$CboCurier){		  
		  $ocado=new cado();
		  $sql="update contrato set nro_contrato = '$txtNroContrato',id_convenio='$CboEmpresa',fecha_inicio='$txtFechaIni', fecha_fin='$txtFechaFin',id_usuar='$txtUsuario',etapa='$CboEtapa',tipo_pago='$CboTipoPago', curier='$CboCurier'
		        where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }		 
	 function ReporteDocumentosDeclarado( $inicio, $fin ) {
    $ocado = new cado();
    $sql = "SELECT d.fecha_emision,
case when grupal=0 then d.fecha_emision else (select top 1 fec_vencimiento from cuenta_corriente c where c.nro_serie=d.serie and c.nro_documento=d.correlativo and c.estado=0) end ,
tipo_documento,serie,correlativo,tipodoc_cli,case when tipodoc_cli=0 then '' else doc_cliente end,
case when tipodoc_cli=0 then 'VENTAS DEL DIA' else nomcli end ,case when d.estado=0 then valor_venta else 0.00 end ,
case when d.estado=0 then total_igv else 0.00 end ,case when d.estado=0 then importe_total else 0.00 end ,
case f.tipo_pago when 'E' then 1 when 'T' then 2 when 'M' then  3 end,case when  tipo_documento='07' then 
(select det.fecha_emision from doc_electronicos det where det.id=d.doc_relacionado_id) else '' end as fec_emision_doc_afecto,
nrodoc_relacionado,concat(f.visa,' ',f.mastercard,' ',f.otros,' ',coalesce(f.nrodeposito,''))ref
FROM doc_electronicos d 
     left join caja_fondos_detalle f on d.serie=f.nro_serie and d.correlativo=f.nro_documento 
WHERE (cast(d.fecha_emision as date)>=cast('$inicio' as date) and cast(d.fecha_emision as date)<=cast('$fin' as date)) and d.tipo_documento in('01','03','07','08') and len(serie)>0 
order by d.serie,d.correlativo asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }
	 
	/* function Eliminar($id){
		$ocado=new cado();
		$sql="delete from paciente where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/

   }
?>