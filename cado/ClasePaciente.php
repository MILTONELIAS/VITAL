<?php
   require_once('conexion.php');
   
   class Pacientes{
	   
    function Listar($nombre){
	  $ocado=new cado();
	  $sql="select *,TIMESTAMPDIFF(YEAR, fec_nac, CURDATE())edad from paciente 
	  where estado_pac=0 and nom_pac like '%$nombre%' order by id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPaciente($nombre){
	  $ocado=new cado();
	 $sql="select Top 200 * from paciente
	      where estado_pac=0 and ape_pat+' '+ape_mat+' '+preNombres like '%$nombre%' order by ape_pat,ape_mat asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPac(){
	  $ocado=new cado();
	  $sql="select id,trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') AS value,dni,telefono 
	        from paciente where estado_pac=0 ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPacNom($pac){
	  $ocado=new cado();
	  $sql="select top 200 id,trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') AS value,dni,telefono 
	        from paciente where estado_pac=0 and trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') like '%$pac%'
			 order by ape_pat,ape_mat,preNombres asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarPacDni($dni){
	  $ocado=new cado();
	  $sql="select id,trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') AS value,dni,telefono 
	        from paciente where estado_pac=0 and dni='$dni' ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarPac1($nombre){
	  $ocado=new cado();
	  $sql="select top 50 id,trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') AS value,dni,trim(telefono)telefono 
	      from paciente
	        where estado_pac=0 and trim(ape_pat)+' '+trim(ape_mat)+' '+isnull(trim(preNombres),'') like '$nombre%'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarLab(){
	  $ocado=new cado();
	  $sql="select empresa AS value,ruc,direccion from convenio";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GenDni(){
	  $ocado=new cado();
	  $sql="select replicate('0',9-len(correlativo+1))+cast(correlativo+1 as varchar) from serie where tipo_doc='PA'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValidarCanConsultas($anio,$mes){
	  $ocado=new cado();
	  $sql="select count(*) from consultas_reniec where ejercicio=$anio and mes=$mes";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function InsertarCanConsultas($anio,$mes){
	  $ocado=new cado();
	  $sql="insert into consultas_reniec (ejercicio,mes,can) values($anio,$mes,1)";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function UpdateCanConsultas($anio,$mes){
	  $ocado=new cado();
	  $sql="update consultas_reniec 
	         set can=can+1
	        where ejercicio=$anio and mes=$mes";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	/*function ListarPac1($nombre){
	  $ocado=new cado();
	  //and cen_dialisis='$empresa'
	  $sql="select * from 
	         (select id,CONCAT(CONCAT(concat(ape_pat,' ',ape_mat),' ',pri_nom),' ',seg_nom) value from paciente ) as t
	       where t.value like '%$nombre%' limit 0,10";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
	function ListarMed(){
	  $ocado=new cado();
	  $sql="select id,nombre value,porcentaje_comision from medico where estado=0 ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarEnf(){
	  $ocado=new cado();
	  $sql="select id,nombre value from medico where tipo=20";//la columna tipo no está dentro de la tabla medico
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarMedXId($id){
	  $ocado=new cado();
	  $sql="select * from medico where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValidarPaciente($dni){
	  $ocado=new cado();
	  $sql="select count(*) from paciente where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
    function ValidarPacData($dni){
	  $ocado=new cado();
	  $sql="select * from paciente_ant where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	function ValidarMedico($dni){
	  $ocado=new cado();
	  $sql="select count(*) from medico where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	function ValidarPaciente2($dni){
	  $ocado=new cado();
	  $sql="select id,(ape_pat+' '+ape_mat+' '+preNombres) pac from paciente where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	   
	function RegistrarNuevo($ape_pat,$ape_mat,$preNombres,$dni,$estatura,$sexo,$fec_nac,$estadoCivil,$telefono,
		$depaDireccion,$provDireccion,$distDireccion,$direccion,$foto,$crea_user,$tipo_ingreso,$dni_inicial){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  
		  $sql_validar="select count(*) from paciente where dni='$dni'";
		  $cmd_validar= $cn->prepare($sql_validar);
		  $cmd_validar->execute();
		  $data=$cmd_validar->fetch();
		  $validar=$data[0];
		  
		  if($validar==0){
           if($tipo_ingreso=='M'){
		    if($dni==$dni_inicial){$actualizar=1;}else{$actualizar=0;}
		    $sql="insert into paciente(ape_pat,ape_mat,preNombres,dni,estatura,sexo,estadoCivil,fec_nac,telefono,
		depaDireccion,provDireccion,distDireccion,direccion,foto,fec_crea,crea_user,tipo_ingreso)
		        values('$ape_pat','$ape_mat','$preNombres','$dni','$estatura','$sexo','$estadoCivil','$fec_nac','$telefono',
		'$depaDireccion','$provDireccion','$distDireccion','$direccion','$foto',getdate(),'$crea_user','$tipo_ingreso')";
		    //die($sql);exit;
		    $cn->prepare($sql)->execute();
		     if($actualizar==1){
		      $sql_update="update serie set correlativo=$dni where tipo_doc='PA' ";
		      $cn->prepare($sql_update)->execute();
			 }
		   }else{
			 $sql="insert into paciente(ape_pat,ape_mat,preNombres,dni,estatura,sexo,estadoCivil,fec_nac,telefono,
		depaDireccion,provDireccion,distDireccion,direccion,foto,fec_crea,crea_user,tipo_ingreso)
		        values('$ape_pat','$ape_mat','$preNombres','$dni','$estatura','$sexo','$estadoCivil','$fec_nac','$telefono',
		'$depaDireccion','$provDireccion','$distDireccion','$direccion','$foto',getdate(),'$crea_user','$tipo_ingreso')";
		    //die($sql);exit; 
		    $cn->prepare($sql)->execute();
			 }
		  $idpac= $cn->lastInsertId();
		  $aleatorio=substr($ape_mat, 1, 1).substr($ape_pat, 0, 1).$idpac.substr($preNombres, 0, 2);
		  $sql_act="update paciente set pass=ENCRYPTBYPASSPHRASE('PassUsuario','$aleatorio'),aleatorio='$aleatorio' where id=$idpac";
		  $cn->prepare($sql_act)->execute();
		  //die($sql_act);exit;
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  }else{
			$cn->rollBack();
			$cn=null;
			$return=2;
		  }
		  
		  }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }
	 
	 function RegistrarNuevoPacOrden($ape_pat,$ape_mat,$preNombres,$dni,$estatura,$sexo,$fec_nac,$estadoCivil,$telefono,
		$depaDireccion,$provDireccion,$distDireccion,$direccion,$foto,$crea_user,$tipo_ingreso,$dni_inicial){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql_validar="select count(*) from paciente where dni='$dni' ";
		  $cmd_validar= $cn->prepare($sql_validar);
		  $cmd_validar->execute();
		  $data=$cmd_validar->fetch();
		  $validar=$data[0];
		  if($validar==0){
           if($tipo_ingreso=='M'){
		    if($dni==$dni_inicial){$actualizar=1;}else{$actualizar=0;}
		    $sql="insert into paciente(ape_pat,ape_mat,preNombres,dni,estatura,sexo,estadoCivil,fec_nac,telefono,
		depaDireccion,provDireccion,distDireccion,direccion,foto,fec_crea,crea_user,tipo_ingreso)
		        values('$ape_pat','$ape_mat','$preNombres','$dni','$estatura','$sexo','$estadoCivil','$fec_nac','$telefono',
		'$depaDireccion','$provDireccion','$distDireccion','$direccion','$foto',getdate(),'$crea_user','$tipo_ingreso')";
			   //die($sql);exit;
		    $cn->prepare($sql)->execute();
		     if($actualizar==1){
		      $sql_update="update serie set correlativo=$dni where tipo_doc='PA' ";
		      $cn->prepare($sql_update)->execute();
			 }
		   }else{
			$sql="insert into paciente(ape_pat,ape_mat,preNombres,dni,estatura,sexo,estadoCivil,fec_nac,telefono,
			depaDireccion,provDireccion,distDireccion,direccion,foto,fec_crea,crea_user,tipo_ingreso)
					values('$ape_pat','$ape_mat','$preNombres','$dni','$estatura','$sexo','$estadoCivil','$fec_nac','$telefono',
			'$depaDireccion','$provDireccion','$distDireccion','$direccion','$foto',getdate(),'$crea_user','$tipo_ingreso')";
			//die($sql);
		    $cn->prepare($sql)->execute();
			 }
		  $pac_id= $cn->lastInsertId();
		  $aleatorio=substr($ape_mat, 1, 1).substr($ape_pat, 0, 1).$pac_id.substr($preNombres, 0, 2);
		  $sql_pac="update paciente set pass=ENCRYPTBYPASSPHRASE('PassUsuario','$aleatorio'),aleatorio='$aleatorio' where id=$pac_id";
		  $cn->prepare($sql_pac)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=$pac_id;
		  }else{
			$cn->rollBack();
			$cn=null;
			$return=2;
		  }
		  
		  }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }
	 
	 function Registrar($ape_pat,$ape_mat,$preNombres,$dni,$estatura,$sexo,$fec_nac,$estadoCivil,$telefono,
		$depaDireccion,$provDireccion,$distDireccion,$direccion,$foto,$crea_user,$tipo_ingreso){
		
		$sql="insert into paciente(ape_pat,ape_mat,preNombres,dni,estatura,sexo,estadoCivil,fec_nac,telefono,
		depaDireccion,provDireccion,distDireccion,direccion,foto,fec_crea,crea_user,tipo_ingreso,pass)
		        values('$ape_pat','$ape_mat','$preNombres','$dni','$estatura','$sexo','$estadoCivil','$fec_nac','$telefono',
		'$depaDireccion','$provDireccion','$distDireccion','$direccion','$foto',getdate(),'$crea_user','$tipo_ingreso',md5(dni))";
		  $ocado=new cado();
		  $ultimo_id=$ocado->ObtenerUltimoId($sql);
		  return $ultimo_id;
	 }
	  
	 
	 function Modificar($id,$ape_pat,$ape_mat,$preNombres,$dni,$estatura,$sexo,$fec_nac,$estadoCivil,$telefono,$depaDireccion,
		$provDireccion,$distDireccion,$direccion,$foto,$crea_user,$dni_inicial){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql_validar="select count(*) from paciente where dni='$dni' ";
		  $cmd_validar= $cn->prepare($sql_validar);
		  $cmd_validar->execute();
		  $data=$cmd_validar->fetch();
		  if($dni==$dni_inicial){$validar=0;}else{$validar=$data[0];}
		  if(strlen($foto)>100){$tipo_ingreso='R';}else{$tipo_ingreso='M';}
		  
		  if($validar==0){
		   $sql="update paciente set ape_pat='$ape_pat',ape_mat='$ape_mat',preNombres='$preNombres',dni='$dni',estatura='$estatura',
		       sexo='$sexo',fec_nac='$fec_nac',estadoCivil='$estadoCivil',telefono='$telefono',depaDireccion='$depaDireccion',
			   provDireccion='$provDireccion',distDireccion='$distDireccion',direccion='$direccion',foto='$foto',crea_user='$crea_user',
			   tipo_ingreso='$tipo_ingreso'
		        where id = $id";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  }else{
			$cn->rollBack();
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
			
		  /*$ocado=new cado();
		 
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;*/
	 }	
	 
	 function CrearApartado($mensaje,$user){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="insert into apartado(descripcion,fecha,user_crea) values ('$mensaje',getdate(),'$user')";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  
		  }catch (PDOException $ex){
              $cn->rollBack();
			  $cn=null;
			  $return=0;
          }
		  return $return;
	 } 
	 	 
	 function Eliminar($id){
		$ocado=new cado();
		$sql="delete from paciente where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ValHisEmp($nro_his,$emp,$tipo){ 
		$ocado=new cado();
		if($tipo=='PARTICULAR'){$sql="select 0 ";}else{
		$sql="select count(*) from paciente where nro_historia='$nro_his' and cen_dialisis='$emp'";}// no existe la columna nro_historia
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function GenRotulo($tipo){
		if($tipo=='P'){$where=" tipo_seguro='PARTICULAR'";}
		if($tipo=='C'){$where=" cen_dialisis='CEDIMA'";}
		if($tipo=='I'){$where=" cen_dialisis='IDR'";}
		//return $where ; exit;
		$ocado=new cado();
		$sql="select max(cast(substring(rotulo,2,length(rotulo)) as decimal(10,0))) from paciente where $where";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	 
	function calcular_edad($fecha){
    $dias = explode("/", $fecha, 3);
    $dias = mktime(0,0,0,$dias[1],$dias[0],$dias[2]);
    $edad = (int)((time()-$dias)/31556926 );
    return $edad;
	} 
	/*
	 function Cie($des){
		$ocado=new cado();
		if($tipo=='PARTICULAR'){$sql="select 0 ";}else{
		$sql="select dx_codigo,dx_des value from he_cie10 where dx_des like '$des%' limit 0,10";}
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/
	/*function InsertarPaciente( $nom_pac,$dni,$nro_historia,$telefono,$fec_ingreso,$fec_nac,$tipo_seguro,$cen_dialisis,$sexo,
	   $estado,$ape_pat,$ape_mat,$pri_nom,$seg_nom,$regimen,$gruposanguineo,$factorsanguineo,
	 $autogenerado,$crea_user,$id_sucursal,$id_turno,$id_modulo,
	  $edad,$di_actual,$contact_emerg,$telef_emerg,$fecha_ini_dia,$fecha_ini_dia_ri,$diag_ini,$cie10,$pesoseco,$lunes,
	  $martes,$miercoles,$jueves,$viernes,$sabado,$domingo, $alergias,$est_civil,$dir_contacto,$rela_pac,$profesion,
	  $con_actual,$fec_ult_emp,$hos_proc,$diali_proc,$fec_pri_hemo,$con_comorbidas,$int_quirur,$num_trans,
      $diu_resi_24h,$med_recibe,$mar_hepatitis,$sintomas,$presion_art,$fc,$fr,$peso,$talla,$piel,
      $linfaticos,$acceso_vas,$ubicacion,$thrill,$observ,$cardiovas,$corazon,
      $pul_perife,$apa_resp,$abdomen,$neurologico,$osteomuscular,$est_nutricional,
      $ind_karnofski,$tiempo_hemo,$heparina_total,$qb,$qd,$area_diali,$tipo_concen,
      $na_dia_inicial,$na_dia_final,$sectorizacion,$aislamiento,
	  $s1,$s2,$s3,$c1,$c2,$c3,$i1,$i2,$i3,$ir1,$ir2,$ir3){
		
	 $sql="
	 INSERT paciente (nom_pac,dni,nro_historia,telefono,fec_ingreso,fec_nac,tipo_seguro,cen_dialisis,sexo,estado,ape_pat,ape_mat,pri_nom,seg_nom,regimen,gruposanguineo,
	 factorsanguineo,autogenerado,fec_crea,crea_user,id_sucursal,id_turno,id_modulo)
	   VALUES ('$nom_pac','$dni','$nro_historia','$telefono','$fec_ingreso','$fec_nac','$tipo_seguro','$cen_dialisis','$sexo',
	   '$estado','$ape_pat','$ape_mat','$pri_nom','$seg_nom','$regimen','$gruposanguineo','$factorsanguineo',
	 '$autogenerado',getdate(),'$crea_user','$id_sucursal','$id_turno','$id_modulo');

 INSERT INTO  he_ficha_atencion  (id_paciente,nro_ficha,fecha_ing,fecha_ate,edad,di_actual,contac_emerg,telef_emerg,
 fecha_inicio_dialisis,fecha_inicio_dialisis_rinon,diagnostico_inicio,cie10,peso_seco,lunes,martes,miercoles,jueves,viernes,sabado,
 domingo,alergico_a,restante,obs,fec_crea,crea_user,fec_anul,anul_user,estado,est_civil,dir_contacto,rela_pac,profesion,con_actual,
 fec_ult_emp,hos_proc,diali_proc,fec_pri_hemo,con_comorbidas,int_quirur,num_trans,diu_resi_24h,med_recibe,mar_hepatitis,sintomas,
 presion_art,fc,fr,peso,talla,piel,linfaticos,acceso_vas,ubicacion,thrill,observ,cardiovas,corazon,pul_perife,apa_resp,abdomen,
 neurologico,osteomuscular,est_nutricional,ind_karnofski,tiempo_hemo,heparina_total,qb,qd,area_diali,tipo_concen,na_dia_inicial,
 na_dia_final,sectorizacion,aislamiento) 
 
 VALUES (LAST_INSERT_ID(),'','$fec_ingreso','','$edad','$di_actual','$contact_emerg','$telef_emerg','$fecha_ini_dia',  '$fecha_ini_dia_ri', '$diag_ini','$cie10','$pesoseco','$lunes','$martes','$miercoles','$jueves','$viernes','$sabado','$domingo', '$alergias' , NULL , NULL ,  getdate(),  '$crea_user', NULL , NULL ,  '0',
  '$est_civil','$dir_contacto','$rela_pac','$profesion','$con_actual','$fec_ult_emp'
 ,'$hos_proc','$diali_proc','$fec_pri_hemo','$con_comorbidas','$int_quirur','$num_trans',
'$diu_resi_24h','$med_recibe','$mar_hepatitis','$sintomas','$presion_art','$fc','$fr','$peso','$talla','$piel',
'$linfaticos','$acceso_vas','$ubicacion','$thrill','$observ','$cardiovas','$corazon',
'$pul_perife','$apa_resp','$abdomen','$neurologico','$osteomuscular','$est_nutricional',
'$ind_karnofski','$tiempo_hemo','$heparina_total','$qb','$qd','$area_diali','$tipo_concen',
'$na_dia_inicial','$na_dia_final','$sectorizacion','$aislamiento');
  
   INSERT INTO  he_serologia (id_ficha_atencion,s_hiv,s_hvc,s_ag_hbs,con_n,con_p,con_pp,inmunizacion_fecha_1,inmunizacion_responsable_1,
   inmunizacion_fecha_2,inmunizacion_responsable_2,inmunizacion_fecha_3,inmunizacion_responsable_3)
    VALUES ( LAST_INSERT_ID(),'$s1' , '$s2' , '$s3', '$c1' , '$c2' , '$c3' , '$i1','$ir1','$i2','$ir2','$i3' ,'$ir3'); ";	
   
$ocado=new cado();		  
	  $ejecutar=$ocado->ejecutar($sql);
	 
	  return $ejecutar; 
		 
		 } */
	 /*function BuscarPac($nombre,$idsucu){
	  $ocado=new cado();
	  $sql="select p.id,p.nom_pac,fi.id,se.id from paciente p inner join he_ficha_atencion fi on p.id=fi.id_paciente
	                                                          inner join he_serologia se on fi.id=se.id_ficha_atencion
	        where nom_pac like '%$nombre%' and id_sucursal='$idsucu' order by nom_pac asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	 function SeleccionarDatos($idpac){
	  $ocado=new cado();
	  $sql="select p.*,fi.*,se.*,cie.dx_codigo,cie.dx_des,p.estado est from paciente p inner join he_ficha_atencion fi on p.id=fi.id_paciente
	                                                          inner join he_serologia se on fi.id=se.id_ficha_atencion
															  inner join he_cie10 cie on fi.cie10=cie.dx_codigo
	        where p.id=$idpac";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	 function UpdatePaciente($idpac,$idficha,$nom_pac,$dni,$nro_historia,$telefono,$fec_ingreso,$fec_nac,$tipo_seguro,$cen_dialisis,$sexo,
	   $estado,$ape_pat,$ape_mat,$pri_nom,$seg_nom,$regimen,$gruposanguineo,$factorsanguineo,
	 $autogenerado,$crea_user,$id_sucursal,$id_turno,$id_modulo,
	  $edad,$di_actual,$contact_emerg,$telef_emerg,$fecha_ini_dia,$fecha_ini_dia_ri,$diag_ini,$cie10,$pesoseco,$lunes,
	  $martes,$miercoles,$jueves,$viernes,$sabado,$domingo, $alergias,$est_civil,$dir_contacto,$rela_pac,$profesion,
	  $con_actual,$fec_ult_emp,$hos_proc,$diali_proc,$fec_pri_hemo,$con_comorbidas,$int_quirur,$num_trans,
      $diu_resi_24h,$med_recibe,$mar_hepatitis,$sintomas,$presion_art,$fc,$fr,$peso,$talla,$piel,
      $linfaticos,$acceso_vas,$ubicacion,$thrill,$observ,$cardiovas,$corazon,
      $pul_perife,$apa_resp,$abdomen,$neurologico,$osteomuscular,$est_nutricional,
      $ind_karnofski,$tiempo_hemo,$heparina_total,$qb,$qd,$area_diali,$tipo_concen,
      $na_dia_inicial,$na_dia_final,$sectorizacion,$aislamiento,
	  $s1,$s2,$s3,$c1,$c2,$c3,$i1,$i2,$i3,$ir1,$ir2,$ir3){
	 $sql="UPDATE   paciente SET 
	 nom_pac='$nom_pac',dni =  '$dni',ape_pat =  '$ape_pat',ape_mat =  '$ape_mat',pri_nom='$pri_nom',seg_nom='$seg_nom',
     fec_nac='$fec_nac',gruposanguineo ='$gruposanguineo',factorsanguineo='$factorsanguineo',autogenerado='$autogenerado',
	 crea_user='$crea_user',nro_historia='$nro_historia',telefono='$telefono',fec_ingreso='$fec_ingreso',tipo_seguro='$tipo_seguro',
	 estado='$estado',regimen='$regimen',id_turno='$id_turno',id_modulo='$id_modulo',sexo='$sexo'   WHERE  id='$idpac'  ;
	 
UPDATE   he_ficha_atencion SET  
fecha_ing='$fec_ingreso',edad='$edad',di_actual='$di_actual',contac_emerg='$contact_emerg',telef_emerg='$telef_emerg',crea_user='$crea_user',
fecha_inicio_dialisis=  '$fecha_ini_dia',fecha_inicio_dialisis_rinon='$fecha_ini_dia_ri',
diagnostico_inicio='$diag_ini',cie10='$cie10',peso_seco='$pesoseco',lunes='$lunes',martes='$martes',miercoles='$miercoles',
jueves='$jueves',viernes='$viernes',sabado='$sabado',domingo='$domingo',alergico_a='$alergias',est_civil='$est_civil',
dir_contacto='$dir_contacto',rela_pac='$rela_pac',profesion='$profesion',con_actual='$con_actual',fec_ult_emp='$fec_ult_emp'
,hos_proc='$hos_proc',diali_proc='$diali_proc',fec_pri_hemo='$fec_pri_hemo',con_comorbidas='$con_comorbidas',int_quirur='$int_quirur',
num_trans='$num_trans',diu_resi_24h='$diu_resi_24h',med_recibe='$med_recibe',mar_hepatitis='$mar_hepatitis',sintomas='$sintomas'
,presion_art='$presion_art',fc='$fc',fr='$fr',peso='$peso',talla='$talla',piel='$piel',
linfaticos='$linfaticos',acceso_vas='$acceso_vas',ubicacion='$ubicacion',thrill='$thrill',observ='$observ',cardiovas='$cardiovas',
corazon='$corazon',pul_perife='$pul_perife',apa_resp='$apa_resp',abdomen='$abdomen',neurologico='$neurologico',osteomuscular='$osteomuscular',
est_nutricional='$est_nutricional',ind_karnofski='$ind_karnofski',tiempo_hemo='$tiempo_hemo',heparina_total='$heparina_total',qb='$qb',qd='$qd',
area_diali='$area_diali',tipo_concen='$tipo_concen',na_dia_inicial='$na_dia_inicial',na_dia_final='$na_dia_final',sectorizacion='$sectorizacion',
aislamiento='$aislamiento'
 WHERE   id='$idficha'   ;
 
 UPDATE  he_serologia SET
s_hiv='$s1',s_hvc='$s2',s_ag_hbs ='$s3',con_n='$c1',con_p='$c2',con_pp='$c3',
inmunizacion_fecha_1='$i1',inmunizacion_responsable_1 ='$ir1',inmunizacion_fecha_2='$i2',
inmunizacion_responsable_2='$ir2',inmunizacion_fecha_3='$i3',inmunizacion_responsable_3='$ir3' 
 WHERE  id_ficha_atencion ='$idficha';";	
 $ocado=new cado();		  
	 $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar; 
 }
 function LisPac($id,$mes,$anio){
		$ocado=new cado();
		$sql="select p.id,left(nom_pac,29),nro_historia,ape_pat,ape_mat,pri_nom,seg_nom,dni,regimen,
		  (select $mes from he_cronograma c where c.id_paciente=p.id and anio=$anio) cronograma
		 from paciente p 
		       where p.id in($id)
			   order by nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
 function LisSuc($id){
		$ocado=new cado();
		$sql="select * from ut_sucursal  where id=$id";       
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
 function InsertarCronograma($ene,$feb,$mar,$abr,$may,$jun,$jul,$ago,$sep,$oct,$nov,$dic,$anio,$id){
		$ocado=new cado();
		$sql="insert he_cronograma (ene,feb,mar,abr,may,jun,jul,ago,sep,oct,nov,dic,anio,id_paciente) 
		     values ('$ene','$feb','$mar','$abr','$may','$jun','$jul','$ago','$sep','$oct','$nov','$dic','$anio','$id')";       
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
  function UpdateCronograma($id_pac,$ene,$feb,$mar,$abr,$may,$jun,$jul,$ago,$sep,$oct,$nov,$dic,$anio){
		$ocado=new cado();
		$sql="update he_cronograma set ene='$ene',feb='$feb',mar='$mar',abr='$abr',may='$may',jun='$jun',jul='$jul',ago='$ago',
		       sep='$sep',oct='$oct',nov='$nov',dic='$dic' where id_paciente=$id_pac and anio=$anio";       
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }*/
   function VerMesCorto($num){
		 switch ($num){
			 case 1:
			   $mes='ene'; 
			   return $mes;
			   break;
			 case 2:
			   $mes='feb'; 
			   return $mes;
			   break;
			 case 3:
			   $mes='mar'; 
			   return $mes;
			   break;
			 case 4:
			   $mes='abr'; 
			   return $mes;
			   break;
			 case 5:
			   $mes='may'; 
			   return $mes;
			   break;
			 case 6:
			   $mes='jun'; 
			   return $mes;
			   break;
			 case 7:
			   $mes='jul'; 
			   return $mes;
			   break;          
			 case 8:
			   $mes='ago'; 
			   return $mes;
			   break;
			 case 9:
			   $mes='sep'; 
			   return $mes;
			   break;
			 case 10:
			   $mes='oct'; 
			   return $mes;
			   break;
			 case 11:
			   $mes='nov'; 
			   return $mes;
			   break;
			 case 12:
			   $mes='dic'; 
			   return $mes;
			   break;           
			 }
		 
		 }

  /*function ListarPrograPac($conv,$anio,$emp){
		$ocado=new cado();
		$sql="select nom_pac,ene,feb,mar,abr,may,jun,jul,ago,sep,oct,nov,dic 
		      from paciente p left join he_cronograma cro on p.id=cro.id_paciente
		      where  id_sucursal='$emp' and tipo_seguro='$conv' and cro.anio=$anio and estado=0
			  order by nom_pac asc";       
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }*/
	 function ListarXDni($dni){
	  $ocado=new cado();
	  $sql="select id,concat(ape_pat,' ',ape_mat,' ',preNombres)as pac from paciente where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarApartadoMenu(){
	  $ocado=new cado();
	  $sql="select top 1 descripcion,case when cast(fecha as date)=getdate() then 0 else 1 end tipo
	       from apartado  order by fecha desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function UltimosApartados(){
	  $ocado=new cado();
	  $sql="select top 10 id,descripcion,FORMAT(fecha, 'dd/MM/yyyy')fec,FORMAT(fecha, 'hh:mm ss')hora,user_crea,
	  case when cast(fecha as date)=getdate() then 0 else 1 end tipo
	       from apartado  order by fecha desc ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ayu(){
	  $ocado=new cado();
	  $sql="SELECT dni FROM paciente WHERE cast(fec_crea as date)>'2020-06-18' AND tipo_ingreso='R'	";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function act_ayu($dni,$pat){
	  $ocado=new cado();
	  $sql="update paciente set ape_pat='$pat' where dni='$dni'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function act_cell($id,$cell){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="update paciente set telefono='$cell' where id=$id";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  
		  }catch (PDOException $ex){
              $cn->rollBack();
			  $cn=null;
			  $return=0;
          }
		  return $return;
	}
	function CambiarEstado($id,$user){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="update paciente set estado_pac=1,user_anula='$user',fec_anula=getdate() where id=$id";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  
		  }catch (PDOException $ex){
              $cn->rollBack();
			  $cn=null;
			  $return=0;
          }
		  return $return;
	}

   }
?>