<?php
   require_once('conexion.php');
   
   class Personal{
	   
     function Listar($buscar){
	  $ocado=new cado();
	  $sql="select t.*,a.nombre,a.id from trabajador_ca t inner join area_ca a on t.id_area=a.id
	         where persona like '%$buscar%'
	        order by id_biometrico asc, a.nombre asc";
	  $ejecutar=$ocado->EjecutarSql($sql);
	  return $ejecutar;
	 }
	 function ListarAreas(){
	  $ocado=new cado();
	  $sql="select * from area_ca order by nombre asc";
	  $ejecutar=$ocado->EjecutarSql($sql);
	  return $ejecutar;
	 }
	 function ListarPerXAreas($id){
	  $ocado=new cado();
	  $sql="select id,persona,dni from trabajador_ca where id_area=$id order by persona asc";
	  $ejecutar=$ocado->EjecutarSql($sql);
	  return $ejecutar;
	 }
	 function ListarPerXAreasLider(){
	  $ocado=new cado();
	  $sql="select id,nombre,dni_tra from usuario where dni_tra<>'' order by nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	  /*function Valida_Bio($idbio){
	  $ocado=new cado();
	  $sql="select * from trabajador_ca where id_biometrico=$idbio and estado=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }*/
	  function Insertar_Personal($apepat,$apemat,$nom,$persona,$dni,$sexo,$contrato,$sueldo,$can_horas,$rem_x_hora,$extra_diurna,
	                             $extra_nocturna,$asig_fam,$bonificacion,$estado,$id_area){
	  try{ 
		  $ocado=new cado();
		  $cn=$ocado->ConectarSql();
		  $cn->beginTransaction();
		  $sql="select count(*) from trabajador_ca where dni='$dni'";
		  $cmd=$cn->prepare($sql);
		  $cmd->execute();
		  $datos=$cmd->fetch();
		  $can=$datos[0];
	      if($can==0){
			  $sql_insert=" INSERT INTO trabajador_ca
(id_biometrico,ape_pat,ape_mat,nombres,persona,dni,sexo,contrato,sueldo,can_horas,rem_x_hora,extra_diurna,extra_nocturna,asig_fam,bonificacion,estado,id_area)		  
    VALUES
    (0,'$apepat','$apemat','$nom','$persona','$dni','$sexo','$contrato','$sueldo','$can_horas','$rem_x_hora','$extra_diurna',
	'$extra_nocturna','$asig_fam','$bonificacion','$estado','$id_area')";
	          $cn->prepare($sql_insert)->execute();
			  $cn->commit();
		      $cn=null;
		      $return=0;
		   }
		  else{return $return=2;}
		     
		  }catch (PDOException $ex){
                    $cn->rollBack();
			  $return=1;
              //return $ex->getMessage();
          }
		return $return;
	 }
	 function Valida_Mod_DNI($dni,$id){
	  $ocado=new cado();
	  $sql="select * from trabajador_ca where dni=$dni and id<>$id and estado=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function Eliminar($id){
	  $ocado=new cado();
	  $sql="delete from Trabajador_ca where id='$id'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }

	 /*function Insertar_Personal($id_biometrico,$persona,$dni,$sexo,$contrato,$sueldo,$can_horas,$rem_x_hora,$extra_diurna,$extra_nocturna,$asig_fam,$bonificacion,$estado,$id_area){
         $ocado=new cado();  	
         $sql=" INSERT INTO trabajador_ca
           (id_biometrico,persona,dni,sexo,contrato,sueldo,can_horas,rem_x_hora,extra_diurna,extra_nocturna,asig_fam,bonificacion,estado,id_area)
     VALUES
      ('$id_biometrico','$persona','$dni','$sexo','$contrato','$sueldo','$can_horas','$rem_x_hora','$extra_diurna','$extra_nocturna','$asig_fam',
	  '$bonificacion','$estado','$id_area')";
		 //echo $sql; exit;
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	   }*/
	   
	   function Modificar_Personal($id,$apepat,$apemat,$nom,$persona,$dni,$sexo,$contrato,$sueldo,$can_horas,$rem_x_hora,$extra_diurna,
	                               $extra_nocturna,$asig_fam,$bonificacion,$estado,$id_area){
		 try{
		  
		  $ocado=new cado();
		  $cn=$ocado->ConectarSql();
		  $cn->beginTransaction(); 	
          $sql=" update trabajador_ca
			    set ape_pat='$apepat', ape_mat='$apemat',nombres='$nom',
				  persona = '$persona',
				  dni = '$dni',
				  sexo = '$sexo',
				  contrato = '$contrato',
				  sueldo = '$sueldo',
				  can_horas = '$can_horas',
				  rem_x_hora = '$rem_x_hora',
				  extra_diurna = '$extra_diurna',
				  extra_nocturna = '$extra_nocturna',
				  asig_fam=$asig_fam, 
				  bonificacion=$bonificacion,
				  estado=$estado,
				  id_area=$id_area
			where id = $id";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=0;
		  }catch (PDOException $ex){
                    $cn->rollBack();
		   $return=1;
          }
		return $return;
	   }
	   
	   function Eliminar_Personal($Id){
         $ocado=new cado();  	
         $sql=" delete from trabajador_ca where id = '".$Id."' ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	   }

   }
?>
