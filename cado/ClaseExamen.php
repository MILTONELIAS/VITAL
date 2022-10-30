<?php
   require_once('conexion.php');
   
   class Examenes{
	   
    /*function Listar($nombre){
	  $ocado=new cado();
	  $sql="select e.id,e.nombre,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,m.id,e.unidad,(select count(*) from caracteristica c where c.id_examen=e.id)contador,
		precio_part,precio_conv,precio_part_desc,e.archivo_resultado,cod_tipo
	     from examen e left join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
	        where e.nombre like '$nombre%' and e.estado=0 order by e.nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
	function Listar($nombre){
	  $ocado=new cado();
	  $sql="select top 100 e.id,e.nombre,e.recipiente,m.nombre nom,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,m.id,e.unidad, 
		precio_part,precio_conv,precio_part_desc,e.archivo_resultado,cod_tipo,count(c.id) contador
	     from examen e left join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
					   left join caracteristica c on e.id=c.id_examen
					    where e.nombre like '%$nombre%' and e.estado=0
			group by e.id,e.nombre,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,m.id,e.unidad,precio_part,precio_conv,precio_part_desc,e.archivo_resultado,cod_tipo
			order by e.nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
       
    function ListarExamen(){
	  $ocado=new cado();
	  $sql="select e.id,e.nombre,e.recipiente,m.nombre nom,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,m.id,e.unidad, 
		precio_part,precio_conv,precio_part_desc,e.archivo_resultado,cod_tipo,count(c.id) contador
	     from examen e left join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
					   left join caracteristica c on e.id=c.id_examen
					    where e.estado=0
			group by e.id,e.nombre,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,m.id,e.unidad,precio_part,precio_conv,precio_part_desc,e.archivo_resultado,cod_tipo
			order by e.nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	} 
       
	function ListarWeb($nombre,$ruc){
	  $ocado=new cado();
	  $sql="select id_exa,nom,  case when precio_esp >0 then precio_esp  else case when '$ruc'='20601443091' then  precio_part else precio_conv end end  pre,
	  recipiente,nombre,frecuencia,condiciones,volumen,temp_traslado,tiempo_resultado,nom_grupo,id_grupo,id,unidad
from (
select e.id id_exa,e.nombre nom,precio_conv,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre nom_grupo,e.id_grupo,m.id,e.unidad,COALESCE((SELECT precio FROM precio_especial pre inner join convenio con on pre.id_convenio=con.id and con.ruc='$ruc' where pre.id_examen=e.id),0)precio_esp,precio_part
	     from examen e left join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
	        where e.nombre like '$nombre%' and e.estado=0 
  ) as t 
  order by nom asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarWebPac($nombre){
	  $ocado=new cado();
	  $sql="
select e.id id_exa,e.nombre nom,precio_part,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre nom_grupo,e.id_grupo,m.id,e.unidad
	     from examen e left join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
	        where e.nombre like '$nombre%' and e.estado=0 
  order by e.nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPreciosPdf(){
	  $ocado=new cado();
	  $sql="select id,nombre,precio_part,precio_conv,precio_part_desc from examen where estado=0 order by nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPreciosRPMPdf(){
	  $ocado=new cado();
	  $sql="select e.id,e.nombre,e.precio_part,e.precio_conv,e.precio_part_desc,g.nombre from examen e INNER JOIN grupo g on e.id_grupo=g.id where estado=0 AND id_grupo IN(7,14,18) order by g.nombre ASC";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPrecios($id){
	  $ocado=new cado();
	  $sql="select c.*,e.id,e.id_examen,case when tarifario='E' then coalesce(e.precio,NULL,0) else 
	  (select unidad from examen where id=$id)*factor*(select (valor/100)+1 from tasas where nombre='IGV') end as precio 
       from convenio c left join examen_precio e on c.id=e.id_convenio and e.id_examen=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarXArea($id){
	  $ocado=new cado();
	  $sql="select e.id,e.nombre,e.recipiente,m.nombre,e.frecuencia,e.condiciones,e.volumen,e.temp_traslado,
	  	e.tiempo_resultado,g.nombre,e.id_grupo,(select count(*) from caracteristica c where c.id_examen=e.id)contador
	     from examen e inner join muestra m on e.muestra=m.id
	                   inner join grupo g on e.id_grupo=g.id
	        where e.id_grupo=$id and e.estado=0  order by e.id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPaq(){
	  $ocado=new cado();
	  $sql="select id,nombre,precio_part from examen where paquete=1 and estado=0 order by id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetalle($paquete){
	  $ocado=new cado();
	  $sql="select  id,nombre value from examen where estado=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	/*function ListarDetalle1($idconv){
		//if($paquete!=''){$where=" where id not in ($paquete)";}else{$where='';}
	  $ocado=new cado();
	  $sql="select * from (select  e.id,e.nombre as value,coalesce(p.precio,0) as precio,m.nombre as muestra,
	           case when resul_externo=1 then 1 else (select count(*) from caracteristica c where c.id_examen=e.id) end  can_car
	           from examen e inner join muestra m on e.muestra=m.id
			      left join examen_precio p on e.id=p.id_examen and p.id_convenio=$idconv where e.estado=0 ) as t
		     where precio>0 and can_car>0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
	function DatosConv($idconv){
		//if($paquete!=''){$where=" where id not in ($paquete)";}else{$where='';}
	  $ocado=new cado();
	  $sql="select id,tipo,tarifario,factor,ruc from convenio where id=$idconv ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetalle1($tipo,$tari,$factor,$id_empresa,$cod_tipo,$ruc){
		//if($paquete!=''){$where=" where id not in ($paquete)";}else{$where='';}
		if($tipo=='P'){$precio='precio_part';}
		if($tipo=='PD'){$precio='precio_part_desc';}
  if($tipo=='C'){if($ruc=='20601443091'){$precio='precio_part';}else{if($tari=='E'){$precio='precio_conv';}else{ $precio="unidad*$factor";}} }

	  $ocado=new cado();
      $sql="select * from (
            select e.id, e.nombre as value,case when COALESCE(pre.precio,0)=0 then  $precio else pre.precio end  as precio,COALESCE(m.nombre,'') as muestra,g.nombre gru,tiempo_resultado,g.id idgrupo
from examen e  inner join grupo g on e.id_grupo=g.id
               left join muestra m on e.muestra=m.id
               left join precio_especial pre on e.id=pre.id_examen and pre.id_convenio=$id_empresa
    where e.estado=0 and e.cod_tipo='$cod_tipo') as t
           where t.precio>0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetalleImagenes($tipo,$tari,$factor,$id_empresa,$cod_tipo){
		//if($paquete!=''){$where=" where id not in ($paquete)";}else{$where='';}
		if($tipo=='P'){$precio='precio_part';}
		if($tipo=='PD'){$precio='precio_part_desc';}
		if($tipo=='C'){if($tari=='E'){$precio='precio_conv';}else{ $precio="unidad*$factor";}  }

	  $ocado=new cado();
      $sql="select * from (
            select e.id, e.nombre as value,case when COALESCE(pre.precio,0)=0 then  $precio else pre.precio end  as precio,COALESCE(m.nombre,'') as muestra,
    (select count(*) from examen_informe exa where exa.id_examen=e.id) as can_car,g.nombre gru,tiempo_resultado
from examen e left join muestra m on e.muestra=m.id
              left join precio_especial pre on e.id=pre.id_examen and pre.id_convenio=$id_empresa
			  inner join grupo g on e.id_grupo=g.id
    where e.estado=0 and e.cod_tipo='$cod_tipo') as t
           where t.precio>0 and t.can_car>0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetaRe($deta){
	  $ocado=new cado();
	  $sql="select top 8 id,nombre value from examen where nombre like '%$deta%'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function MostrarPaquete($idpaq){  
	  $ocado=new cado();
	  $sql="select paquete from examen where id =$idpaq";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetalleXId($idpaq){  
	  $ocado=new cado();
	  $sql="select  p.*,e.nombre  from examen e inner join examen_paquete p on e.id=p.id_exa_detalle
	       where p.id_examen =$idpaq";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function Registrar($nombre,$recipiente,$muestra,$frecuencia,$condiciones,$volumen,$temp_traslado,$tiempo_resultado,$id_grupo,$uni,$pp,$pc
	,$ppd,$cod_tipo){
		  $ocado=new cado();
		  $sql="insert into examen (nombre,recipiente,muestra,frecuencia,condiciones,volumen,temp_traslado,tiempo_resultado,id_grupo,unidad,
		  estado,precio_part,precio_conv,precio_part_desc,cod_tipo)
     values ('$nombre','$recipiente','$muestra','$frecuencia','$condiciones','$volumen','$temp_traslado','$tiempo_resultado',$id_grupo,'$uni',0,$pp,
	 $pc,$ppd,'$cod_tipo')";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 } 
	function ModPaquete($idpaquete,$idexamen){
		  $ocado=new cado();
		  $sql="INSERT INTO examen_paquete(id_examen, id_exa_detalle) VALUES ('$idpaquete','$idexamen')";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 } 
	function ValidarDuplicadoExa($idexamen,$id_detalle){
		  $ocado=new cado();
		  $sql="select count(*) from examen_paquete where id_examen=$idexamen and id_exa_detalle=$id_detalle";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 } 
	function EliDetPaquete($id){
		  $ocado=new cado();
		  $sql="delete from examen_paquete where id=$id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function Modificar($id,$nombre,$recipiente,$muestra,$frecuencia,$condiciones,$volumen,$temp_traslado,$tiempo_resultado,$id_grupo,$uni
	,$pp,$pc,$ppd,$cod_tipo){
		  $ocado=new cado();
		  $sql="update examen 
                  set nombre='$nombre',recipiente='$recipiente',muestra='$muestra',frecuencia='$frecuencia',condiciones='$condiciones',
                  volumen='$volumen',temp_traslado='$temp_traslado',tiempo_resultado='$tiempo_resultado',id_grupo=$id_grupo,unidad='$uni',
				  precio_part=$pp,precio_conv=$pc,precio_part_desc=$ppd,cod_tipo='$cod_tipo'
                where id=$id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }	 
	 	 
	 function Eliminar($id){
		$ocado=new cado();
		$sql="delete from examen where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function EliminarExa($id){
		$ocado=new cado();
		$sql="update examen set estado=1 where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function RegistrarPaquete($nombre,$precio,$grupo){
		  $ocado=new cado();
		  $sql="INSERT INTO examen(nombre, recipiente, muestra,frecuencia,condiciones,volumen,temp_traslado, tiempo_resultado,id_grupo,paquete,unidad,estado,resul_externo,especial,archivo_resultado,precio_part,precio_conv,precio_part_desc,can_resultado,cod_tipo,historico) 
		  VALUES ('$nombre','-','-','-','-','-','-','-',$grupo,1,'0.00',0,0,0,NULL,'$precio','$precio','$precio',0,'02',0)";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 
	function ListarCar($idexamen){
	  $ocado=new cado();
	  $sql="select * from caracteristica where id_examen=$idexamen order by orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function RegistrarCar($nombre,$titulo,$id_examen,$referencia,$unidad,$resul,$metodo){
		  $ocado=new cado();
		  $sql="insert into caracteristica(nombre,titulo,id_examen,unidad,referencia,metodo,resultado,orden)
		        values('$nombre','$titulo',$id_examen,'$unidad','$referencia','$metodo','$resul',(select coalesce(max(orden),0) from caracteristica)+1);
				COMMIT;";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }	
	function QuitarCar($id){
		$ocado=new cado();
		$sql="delete from caracteristica where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ActualizarOrdenCa($idcar,$orden){
		  $ocado=new cado();
		  $sql="update caracteristica set orden = $orden where id = $idcar";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ValidarPrecio($idconvenio,$idexamen){
		  $ocado=new cado();
		  $sql="select count(*) from examen_precio where id_examen=$idexamen and id_convenio=$idconvenio";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function InsertarPrecio($idexa,$idconv,$precio){
		  $ocado=new cado();
		  $sql="insert into examen_precio (id_examen,id_convenio,precio) values ($idexa,$idconv,$precio)";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
    function ActualizarPrecio($idexaprecio,$precio){
		  $ocado=new cado();
		  $sql="update examen_precio set precio = $precio where id = $idexaprecio";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ActualizarPrecio2($idexa,$idconv,$precio){
		  $ocado=new cado();
		  $sql="update examen_precio set precio = $precio where id_examen = $idexa and id_convenio=$idconv";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ListarConv(){
		  $ocado=new cado();
		  $sql="select id from convenio where tarifario<>'S'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function RegistrarPreciosConvenio($idexa){
		  $ocado=new cado(); //
		  
		  $sql="insert into examen_precio (id_examen,id_convenio,precio)
		        select '$idexa',id,case when tarifario='E' then 0.00 else (select e.unidad*c.factor from examen e where e.id=$idexa)end pre
				 from convenio c where estado=0";
		 
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function MaxExamen(){
		  $ocado=new cado();
		  $sql="select max(id) from examen where estado=0";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ListarInformeExamen($idexamen){
		  $ocado=new cado();
		  $sql="select * from examen_informe where id_examen= $idexamen";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function FormatoInformeExamen($idexamen){
		  $ocado=new cado();
		  $sql="select e.id,e.nombre,d.id,d.titulo,d.contenido from examen e inner join examen_informe d on e.id=d.id_examen where id_examen= $idexamen";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function GuardarPagina($id,$titulo,$contenido){
		  $ocado=new cado();
		  $sql="update examen_informe set titulo = '$titulo',contenido='$contenido' where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function AgregarPagina($idexamen){
		  $ocado=new cado();
		  $sql="insert into examen_informe (id_examen) values($idexamen)";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function QuitarPlantilla($id){
		  $ocado=new cado();
		  $sql="delete from examen_informe where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ListarCotizacion(){
	  $ocado=new cado();
	  $sql="select e.id,e.nombre,e.precio_part,e.precio_conv,precio_part_desc from examen e where e.estado=0 and e.precio_part>0 and cod_tipo='02' order by e.nombre asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ModificarCar($id,$nombre,$titulo,$referencia,$unidad,$resul,$metodo){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql="update caracteristica set nombre='$nombre',titulo='$titulo',referencia='$referencia',unidad='$unidad',resultado='$resul',
		         metodo='$metodo' where id=$id;";
		  $cn->prepare($sql)->execute();
		  $sql1="update resultado set caracteristica='$nombre',titulo='$titulo',resultado='$resul',unidad='$unidad',rango='$referencia',
				 metodo='$metodo' where id_caracteristica=$id and id_receta in(select id from receta where estado<2 );";
		  $cn->prepare($sql1)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
		  }catch (PDOException $ex){
                    $cn->rollBack();
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }
	   /*function ModificarCar($id,$nombre,$titulo,$referencia,$unidad,$resul,$metodo){
		  $ocado=new cado();
		  $sql="start transaction;
		         update caracteristica set nombre='$nombre',titulo='$titulo',referencia='$referencia',unidad='$unidad',resultado='$resul',
		         metodo='$metodo'
		        where id=$id;
				update resultado set caracteristica='$nombre',titulo='$titulo',resultado='$resul',unidad='$unidad',rango='$referencia',
				 metodo='$metodo'
				where id_caracteristica=$id and id_receta in(select id from receta where estado<2 );
				commit;";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/
	/* function AbrirPlantilla($idexa){
		  $ocado=new cado();
		  $sql="select id,archivo_resultado from examen where id = $idexa";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function GuardarInforme($id,$ruta){
		  $ocado=new cado();
		  $sql="update receta_detalle set informe_cargado = 1, ruta_informe='$ruta' where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function AbrirInforme($id){
		  $ocado=new cado();
		  $sql="select id,informe_cargado,ruta_informe from receta_detalle where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	  function QuitarInforme($id){
		  $ocado=new cado();
		  $sql="update receta_detalle set informe_cargado = 0, ruta_informe = NULL where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/
	/*function InsertarPreciosTotales($idexa,$idconv,$precio){
		  $ocado=new cado();
		  $sql="insert into examen_precio (id_examen,id_convenio,precio) values('$idxa','$idconv','$precio')";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ActPreciosTotales($idexa,$precio){
		  $ocado=new cado();
		  $sql="update examen_precio set precio = $precio where id_examen = $idexa and id_convenio=$idconv";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/
   }
?>