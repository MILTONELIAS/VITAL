<?php
   require_once('conexion.php');
   
   class Grupos{
	   
    function Listar(){
		  $ocado=new cado();
		  $sql="select id, nombre, nro_orden, mostrar from grupo order by nro_orden asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}

	 function Registrar($nombre){
		  $ocado=new cado();
		  $sql="insert into grupo(nombre,nro_orden,mostrar) values('$nombre',isnull((select max(nro_orden) from grupo),0)+1,0);";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function Modificar($id,$nombre){
		  $ocado=new cado();
		  $sql="update grupo set nombre = '$nombre' where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ListarMuestra($nombre){
		  $ocado=new cado();
		  $sql="select * from muestra where nombre like '%$nombre%'  order by id desc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	 function RegistrarMuestra($nombre){
		  $ocado=new cado();
		  $sql="insert into muestra(nombre) values('$nombre');";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ModificarMuestra($id,$nombre){
		  $ocado=new cado();
		  $sql="update muestra set nombre = '$nombre' where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }	
     function RegistrarConvenio($nombre,$ruc,$dire,$tipo_conv,$tipo_tarifario,$factor,$pass){
		  $ocado=new cado();
		  $sql="insert into convenio(empresa,ruc,direccion,tipo,tarifario,factor,estado,pass,sucursal)			 						  values('$nombre','$ruc','$dire','$tipo_conv','$tipo_tarifario','$factor',0,ENCRYPTBYPASSPHRASE('PassUsuario','$pass'),0)";
	      //die($sql);exit;
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function MaxIdConvenio(){
		  $ocado=new cado();
		  $sql="select max(id) from convenio";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function RegistrarPreciosConvenio($idconvenio,$tipo){
		  $ocado=new cado(); //
		  if($tipo=='E'){
		  $sql="insert into examen_precio (id_examen,id_convenio,precio)
		        select id,'$idconvenio',
				  isnull((select top 1 precio from examen_precio where id_examen=e.id  group by precio order by count(*) desc ),0) pre
				 from examen e where estado=0";
		  }else{
		  $sql="insert into examen_precio (id_examen,id_convenio,precio)
		        select id,'$idconvenio',
				  isnull((select e.unidad*factor from convenio where id=$idconvenio),0)pre
				 from examen e where estado=0";
			  }
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function MaxRucCorrelativoPart(){
		  $ocado=new cado();
		  $sql="select replicate('0',11-len(MAX(ruc)+1))+cast((MAX(ruc)+1 ) as varchar) from convenio where tipo='P'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ModificarConvenio($id,$nombre,$ruc,$dire,$tipo_conv,$tipo_tarifario,$factor,$pass){
		  $ocado=new cado();
		  $sql="update convenio set empresa='$nombre',ruc='$ruc',direccion='$dire',tipo='$tipo_conv',tarifario='$tipo_tarifario',
		  factor='$factor' $pass where id = $id";
		  //die($sql);exit;
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }	
	 	
	 function ActualizarOrden($idgrupo,$orden){
		  $ocado=new cado();
		  $sql="update grupo set nro_orden = $orden where id = $idgrupo";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 } 
	 	 
	 function Eliminar($id){
		$ocado=new cado();
		$sql="delete from grupo where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ListarConv($nombre){
		  $ocado=new cado();
		  $sql="select top 200 id, case when sucursal=1 then empresa+' - '+nom_ciudad else empresa end empresa, ruc, direccion, tipo, 
		  tarifario, factor, estado, Cast(DecryptByPassPhrase('PassUsuario', pass) As varchar(100))pass, sucursal, nom_ciudad
		       from convenio where estado=0 and empresa like '%$nombre%'  order by empresa asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function ListarConveniosExcel(){
		$ocado=new cado();
		$sql="select id, case when sucursal=1 then empresa+' - '+nom_ciudad else empresa end empresa, ruc, direccion, tipo, 
		tarifario, factor, estado, Cast(DecryptByPassPhrase('PassUsuario', pass) As varchar(100))pass, sucursal, nom_ciudad
			 from convenio where estado=0 order by empresa asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
  }
	function ListarConvOrden(){
		  $ocado=new cado();
		  $sql="select id, case when sucursal=1 then (empresa+' - '+nom_ciudad) else empresa end empresa ,ruc,direccion,tipo,tarifario,
		        factor,estado,pass from convenio where estado=0 order by id asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function ListarConvOrdenLider(){
		  $ocado=new cado();
		  $sql="select * from convenio where estado=0  AND ruc in('20601443091','20103269319','20101260373','20178922581')  order by id desc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function LisEmp(){
		  $ocado=new cado();
		  $sql="select id, case when sucursal=1 then concat(empresa,' - ',nom_ciudad) else empresa end empresa,ruc,direccion,tipo, 
		       tarifario,factor,estado,pass
		  from convenio where estado=0 and tipo='C'   order by empresa asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function ListarMedico($nombre){
		  $ocado=new cado();
		  $sql="select top 200 * from medico where estado=0 and nombre like '%$nombre%'  order by nombre asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function ListarMedicoExcel(){
		$ocado=new cado();
		$sql="select  * from medico where estado=0 order by nombre asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
  } 
	function ListarMedicoAuto(){
		  $ocado=new cado();
		  $sql="select id,nombre,porcentaje_comision,por_comision_in from medico where estado=0 order by nombre asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function RegistrarMedico($dni,$nombre,$comision,$foto,$fn,$s,$comision_in,$ruc,$telmed,$emailmed){
		  $ocado=new cado();
		  $sql="insert into medico(dni,nombre,porcentaje_comision,estado,foto,fec_nac,sexo,por_comision_in,ruc,telmed,emailmed) 
		  values('$dni','$nombre','$comision',0,'$foto','$fn','$s','$comision_in','$ruc','$telmed','$emailmed')";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ModificarMedico($id,$dni,$nombre,$comision,$fn,$s,$comision_in,$ruc,$telmed,$emailmed){
		  $ocado=new cado();
		  $sql="update medico set dni='$dni', nombre = '$nombre', porcentaje_comision='$comision' ,fec_nac='$fn',
		  sexo='$s',por_comision_in='$comision_in',ruc='$ruc',telmed='$telmed',emailmed='$emailmed'
		   where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }	
	 function EliminarConv($id){
		$ocado=new cado();
		$sql="update convenio set estado=1 where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	 function EliminarMed($id){
		$ocado=new cado();
		$sql="update medico set estado=1 where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function EliminarConvPrecios($id){
		$ocado=new cado();
		$sql="delete from examen_precio where id_convenio=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	 function ListarArea(){
		  $ocado=new cado();
		  $sql="select * from area_ca order by nombre asc";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	}
	function RegistrarArea($nombre){
		  $ocado=new cado();
		  $sql="insert into area_ca(nombre,estado) values('$nombre',0)";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	 }
	function ModificarArea($id,$nombre){
		  $ocado=new cado();
		  $sql="update area_ca set nombre = '$nombre' where id = $id";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	 }
	 function ListarModulos(){
		  $ocado=new cado();
		  $sql="select * from per_modulos";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ValidarPerso($dni){
		  $ocado=new cado();
		  $sql="select dni,concat(ape_pat,' ',ape_mat,' ',preNombres) pac from paciente where dni='$dni'
                 union
                select dni,concat(ape_pat,' ',ape_mat,' ',preNombres) pac from paciente_ant where dni='$dni'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
   }
?>