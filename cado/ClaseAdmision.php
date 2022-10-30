<?php session_start();
   require_once('conexion.php');
   
   class Admision{
	   
  // FUNCIONES PARA ABRIR CAJA   
 /* function ListarNoActivas(){
	  $ocado=new cado();
	  $sql="select id,nom_caja from conf_caja c inner join conf_caja_series cs on c.id=cs.id_caja where activa=0 and estado=0
	         group by id,nom_caja
		    order by id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
   function TipoServicio(){
	  $ocado=new cado();
	  $sql="select id, nombre,cod_tipo from conf_tiposervicio where vista_adm=1 and estado=0 order by id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
   /*function Especialidades(){
	  $ocado=new cado();
	  $sql="select id, nombre from conf_especialidad where estado=0 order by nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
   function MedicosXEsp($id){
	  $ocado=new cado();
	  $sql="select me.id,web.apePaterno+' '+web.apeMaterno+' '+web.preNombres medico from conf_medico me inner join conf_medico_especialidad med_esp on me.id=med_esp.id_medico 
                             inner join conf_especialidad esp on med_esp.id_especialidad=esp.id
							 inner join webservice_persona web on me.id_persona=web.id
       where esp.id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
   function LisMedicos(){
	  $ocado=new cado();
	  $sql="select me.id,web.apePaterno+' '+web.apeMaterno+' '+web.preNombres medico from conf_medico me
					inner join webservice_persona web on me.id_persona=web.id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
   function BuscarPer($dni){
	  $ocado=new cado();
	  $sql="select id,nuDni,apePaterno+' '+apeMaterno+' '+preNombres from webservice_persona 
             where nuDni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function BuscarPerNom($nom){
	  $ocado=new cado();
	  $sql="select top 200 id,nuDni,apePaterno+' '+apeMaterno+' '+preNombres from webservice_persona 
             where apePaterno+' '+apeMaterno+' '+preNombres like '%$nom%' ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}   
    function ListarSerie(){
	  $ocado=new cado();
	  $sql="select cod_sunat,tipo_doc+' - '+serie ser from conf_serie where tipo_doc in('FA','BV','NC')"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCie(){
	  $ocado=new cado();
	  $sql="select TOP 10 codigo,descripcion AS value from susalud_cie10 where titulo=0 "; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function LisServicioXTipoServ($id){
      if($id==3){$sql="select id, nombre,precio_part from lab_examen  order by nombre asc";}
	  else{$sql="select id,nomenclador,
cast(case when porcentaje>0 then porcentaje*(select precio from conf_tarifario where codigo='101')/100 else precio end as decimal(18,2)) precio
          from conf_tarifario order by id asc";}
	  $ocado=new cado();
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
	
	/*function GrabarAM($dni,$idtiposerv,$turno,$idesp,$idmed,$cod_tipo,$carrito,$user,$codigo_ingreso){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();//inicia una transacción
		  
		  $sql_validar="select count(*) from webservice_persona where nuDni='$dni' ";
		  $cmd=$cn->prepare($sql_validar);
		  $cmd->execute();
		  $val=$cmd->fetch();
		   
		if($val[0]>0){
		  $sql_val_pac="select id from conf_paciente where id_persona=(select id from webservice_persona where nuDni='$dni') ";
		  $cmd=$cn->prepare($sql_val_pac);
		  $cmd->execute();
		  $validar=$cmd->fetch();
		  if($validar[0]==''){
			 $sql_per="select id from webservice_persona where nuDni='$dni' ";
		     $cmd=$cn->prepare($sql_per);
		     $cmd->execute();
		     $per=$cmd->fetch();$idper=$per[0];
			 $sql_pac="insert into conf_paciente(id_persona) values($idper)"; 
		     $cn->prepare($sql_pac)->execute();
			 $idpac= $cn->lastInsertId();
		  }else{ $idpac=$validar[0];}
		  
		  if($cod_tipo=='C' or $cod_tipo='E'){
		    $sql_aten="select isnull(max(correlativo),0)+1 from acto_medico 
      where fecha_crea=cast(getdate()as date) and tiposervicio='$cod_tipo' and id_especialidad=$idesp and id_medico=$idmed and turno='$turno'";
		    $cmd=$cn->prepare($sql_aten);
		    $cmd->execute();
		    $corr=$cmd->fetch();
			$correlativo=$corr[0];
		  }else{$correlativo=0;}
		  
		  $sql_nroacto="select correlativo+1 from conf_correlativo_tabla where codigo='AM'";
		  $cmd=$cn->prepare($sql_nroacto);
		  $cmd->execute();
		  $corre_acto=$cmd->fetch();
		  $nro_acto=$corre_acto[0];
		  //die($nro_acto);exit;
		  $sql_am="INSERT INTO acto_medico (nro_acto,id_paciente,fecha_crea,hora_crea,tiposervicio,id_especialidad,id_medico,turno,
		           correlativo,estado,user_crea,codigo_ingreso) 
				   VALUES ($nro_acto,$idpac,getdate(),getdate(),'$cod_tipo',$idesp,$idmed,'$turno',$correlativo,0,'$user','$codigo_ingreso') ";
		  $cn->prepare($sql_am)->execute();
		  $idactomedico=$cn->lastInsertId();
		  $pago_pac=0;$pago_conv=0;$pago_total=0;
		  for($i=0;$i<count($carrito);$i++){
			$pago_pac=$pago_pac+$carrito[$i][4];$pago_conv=$pago_conv+$carrito[$i][5];$pago_total=$pago_total+$carrito[$i][6];
		    $idservicio=$carrito[$i][0];$servicio=$carrito[$i][1];$can=$carrito[$i][2];$precio=$carrito[$i][3];$subtotal=$carrito[$i][6];
			$sql_am_detalle="insert into acto_medico_detalle (id_actomedico,id_servicio,servicio,precio,cantidad,subtotal)
			                 values($idactomedico,$idservicio,'$servicio',$precio,$can,$subtotal)";
			//die($sql_am_detalle);  exit;
			$cn->prepare($sql_am_detalle)->execute();				 
		  }
		  
		  $sql_update_am="update acto_medico set monto_pac=$pago_pac,monto_conv=$pago_conv,monto_total=$pago_total where id=$idactomedico;";
		  $cn->prepare($sql_update_am)->execute();
		  
		  $sql_corre_tabla="update conf_correlativo_tabla set correlativo=$nro_acto where codigo='AM';";
		  $cn->prepare($sql_corre_tabla)->execute();
		  
		  $cn->commit(); //consignar cambios
		  $cn=null;
		  $return=1;
		}else{
		  $cn->commit(); //consignar cambios
		  $cn=null;
		  $return=2; 
		 }
	  }catch (PDOException $ex){
              $cn->rollBack();
			  $cn=null;
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }*/
	 
	 /*function ValidarUserActivo($user){
	  $ocado=new cado();
	  $sql="select count(*) from conf_usuario where usuario='$user' and user_activo=1";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function AperturarCaja($idcaja,$nom_caja,$codigo,$fondo,$user){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql_user="select id from conf_usuario where usuario='$user'";
		  $cmd=$cn->prepare($sql_user);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $id_user=$col[0];
		  $sql_apertura=" insert into caja_fondos (codigo_ingreso,fec_ingreso,id_caja,id_user,nom_caja,nom_user,fondo_inicial,activo) 
		                    values('$codigo',getdate(),$idcaja,$id_user,'$nom_caja','$user',$fondo,1)";
		  $cn->prepare($sql_apertura)->execute();
		  $sql_caja="update conf_caja set activa=1 where id=$idcaja;";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update conf_usuario set user_activo=1 where id=$id_user;";
		  $cn->prepare($sql_usuario)->execute();
		  $_SESSION['S_cod_ingreso']=$codigo;
		  $cn->commit();
		  $cn=null;
	      $return=1;
	 
	  }catch (PDOException $ex){
              $cn->rollBack();
			  $cn=null;
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	}
	 */
	 function ListarCajaFondo($cod_ingreso){
	  $ocado=new cado();
	  $sql="select c.codigo_ingreso,fondo_inicial,cast(sum(isnull(a.monto_pac,0)) as decimal(18,2)) efectivo
	        from caja_fondos c inner join acto_medico a on c.codigo_ingreso=a.codigo_ingreso
			where c.codigo_ingreso='$cod_ingreso' and a.estado=0
			group by c.codigo_ingreso,fondo_inicial";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function CerrarCaja($idcaja,$cod_ingreso,$iduser,$efectivo,$tarjeta,$egresos,$total){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql_cierre="update caja_fondos set activo=0,monto_efectivo=$efectivo,monto_tarjeta=$tarjeta,total_egresos=$egresos,total_cierre_caja=$total,fec_cierra=getdate()
		               where codigo_ingreso='$cod_ingreso';";
		  //die($sql_cierre);
		  $cn->prepare($sql_cierre)->execute();
		  $sql_caja="update conf_caja set activa=0 where id=$idcaja;";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update conf_usuario set user_activo=0 where id=$iduser";
		  $cn->prepare($sql_usuario)->execute();
		  $cn->commit();
		  $cn=null;
	      $return=1;	 
	  }catch (PDOException $ex){
          $cn->rollBack();
		  $cn=null;
		  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	}	
 }

?>