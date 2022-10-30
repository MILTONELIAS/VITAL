
<?php
   require_once('conexion.php');
   
   class Horarios{
	   
     /*function ListarAreas(){
	  $ocado=new cado();
	  $sql="select * from area_ca where estado <> 1 order by nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }*/
	 //claudia
	 /*function ListarHorarioCombo($id_area){
		$ocado=new cado() ;
		
		if ($id_area>0 ){
			
			$sql="select ho.id,turno,convert(varchar(15),ent_man,100)ent_man,convert(varchar(15),sal_man,100)sal_man,tolerancia,nombre,ho.estado,
	          ent_man_inicio,ent_man_fin,sal_man_inicio,sal_man_fin,ent_man,sal_man,id_area
            from horario_ca ho inner join area_ca ar on ho.id_area = ar.id where ar.id=$id_area
			order by ho.id_area ,ho.ent_man asc";
			$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar; }
	 
		 if ($id_area=='T'){
			$sql="select ho.id,turno,convert(varchar(15),ent_man,100)ent_man,convert(varchar(15),sal_man,100)sal_man,tolerancia,nombre,ho.estado,
	          ent_man_inicio,ent_man_fin,sal_man_inicio,sal_man_fin,ent_man,sal_man,id_area
            from horario_ca ho inner join area_ca ar on ho.id_area = ar.id 
			order by ho.id_area ,ho.ent_man asc";
			$ejecutar=$ocado->ejecutar($sql);
			return $ejecutar; 
		}
	  
	 }*/
	 function Listar($buscar){
	  $ocado=new cado();
	  $sql="select ho.id,turno,convert(varchar(15),ent_man,100)ent_man,convert(varchar(15),sal_man,100)sal_man,tolerancia,nombre,ho.estado,
	          cast(ent_man_inicio as varchar(8))ent_man_inicio,cast(ent_man_fin as varchar(8))ent_man_fin,cast(sal_man_inicio as varchar(8)) sal_man_inicio,cast(sal_man_fin as varchar(8))sal_man_fin,
			  cast(ent_man as varchar(8))ent_man, cast(sal_man  as varchar(8))sal_man,id_area
            from horario_ca ho inner join area_ca ar on ho.id_area = ar.id
			 where nombre like '%$buscar%'
			order by ho.id_area ,ho.ent_man asc";
	  $ejecutar=$ocado->EjecutarSql($sql);
	  return $ejecutar;
	 }
	 function ListarMarcaciones($idarea,$idpersonal,$inicio,$fin){
		 if($idpersonal==0){$where=" id_area=$idarea ";}else{$where=" t.id=$idpersonal ";}
	  $ocado=new cado();
	  $sql="select t.*,case when fila % 2 = 0 then 'S' else 'E' end tipo_mar,USERID,CHECKTIME
  from (
  select ROW_NUMBER() OVER ( PARTITION BY t.id,convert(varchar(10),CHECKTIME,103) ORDER BY CHECKTIME asc)fila,t.id,
  t.persona,convert(varchar(10),CHECKTIME,103)fecha,convert(varchar(15),cast(CHECKTIME as time),100)hora,datename(weekday,CHECKTIME)dia,m.USERID,m.CHECKTIME
   from CHECKINOUT m inner join USERINFO u on m.USERID=u.USERID
                     inner join trabajador_ca t on u.SSN=t.dni
  where $where and (cast(CHECKTIME as date)>=cast('$inicio' as date) and cast(CHECKTIME as date)<=cast('$fin' as date)   ) and m.estado is null
  ) as t
  ORDER BY t.CHECKTIME ASC";
	  $ejecutar=$ocado->EjecutarSql($sql);
	  return $ejecutar;
	 }
	 function ListarHoras($idarea,$idpersonal,$inicio,$fin){
	  $sql="exec AdmReporteHoras ?,?,?,? ";
	  $ocado=new cado();  
	  $ejecutar=$ocado->EjecutarPA($sql,array($idarea,$idpersonal,$inicio,$fin));
	  return $ejecutar;
	 }
	 function ListarHorasLider($idpersonal,$inicio,$fin){
	  if($idpersonal==0){$where='';}else{$where=" and id_user='$idpersonal'";}
	  $sql="select persona,turno,date_format(entrada,'%d/%m/%Y %h:%i %p') entrada,date_format(salida,'%d/%m/%Y %h:%i %p') salida,idper,
TIMESTAMPDIFF(MINUTE, entrada,salida)diferencia 
from (
select nombre persona,'turno' turno,fecha_marcacion entrada,
(select case when a2.tipo='S' then fecha_marcacion else 'ERROR DE MARCACION' end  
from asistencia a2 where a2.id_user=a.id_user and a2.id>a.id limit 1 )salida,
id_user idper
from asistencia a
where tipo='E' and date(fecha_marcacion)>=date('$inicio') and date(fecha_marcacion)<=date('$fin') $where
order by id_user asc,fecha_marcacion asc
) as t";
	  $ocado=new cado();  
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function Insertar_Horario($Nom,$Entrada,$Salida,$Tolerancia,$Area,$Estado,$en_inicio,$en_fin,$sa_inicio,$sa_fin){
         $ocado=new cado();  	
         $sql=" Insert into horario_ca
		 (turno,ent_man,sal_man,ent_man_inicio,ent_man_fin,sal_man_inicio,sal_man_fin,tipo_ho,tolerancia,id_area,estado)
		 values('$Nom','$Entrada','$Salida','$en_inicio','$en_fin','$sa_inicio','$sa_fin',2,'$Tolerancia','$Area','$Estado') ";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	   }
	   
	   function Modificar_Horario($Estado,$id){
         $ocado=new cado();  	
         $sql=" update horario_ca
			set estado = $Estado	
			where id =$id ";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	   }
	   
	   function Eliminar_Horario($id){
         $ocado=new cado();  	
         $sql=" update horario_ca set estado=1		
			    where id = $id ";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	   }
	   function EliminarMarcacion($id,$marcacion){
         $ocado=new cado();  	
         $sql=" update CHECKINOUT set estado=1		
			    where USERID =$id AND convert(varchar(25),CHECKTIME,121)='$marcacion'";
		  $ejecutar=$ocado->EjecutarSql($sql);
		  return $ejecutar;
	   }
	   function BusTrabajador($dni){
         $ocado=new cado();  	
         $sql="select id,nombre from usuario where dni_tra='$dni' ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	   }
	   function RegistrarMarcaLider($idtra,$dni,$tra,$tipo){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql="INSERT INTO asistencia( id_user, dni,nombre, tipo, fecha_marcacion)
                VALUES($idtra,'$dni','$tra','$tipo',getdate())";
          //die($sql);
		  $cn->prepare($sql)->execute();
		  $idmarca= $cn->lastInsertId();
		  //die($idmarca);
		  $sql_consulta="select nombre,  FORMAT (getdate(), 'yyyy-MM-dd hh:mm:ss tt')fecha,
		  case when tipo='E' then 'ENTRADA' else 'SALIDA' end tip 
		                 from asistencia where id=$idmarca";
		  $cmd=$cn->prepare($sql_consulta);
		  $cmd->execute();
		  $datos=$cmd->fetch();
		  $resultado=$datos[2].' : '.$datos[1];
		  $cn->commit();
		  $cn=null;
		  $return=$resultado;
		  }catch (PDOException $ex){
                    $cn->rollBack();
			  $return=0;
          }
		  return $return;
	 }
   }
?>