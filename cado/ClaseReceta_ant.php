<?php
   require_once('conexion.php');
   date_default_timezone_set('America/Lima');	 
   class Recetas{
	function ListarRe($idpac){
	  $ocado=new cado();
	  $sql="select fec_crea,subtotal,descuento,total,estado,
	        medico,id,examen,id_paciente,id_medico,coalesce(acuenta,0.00),
			total-coalesce(acuenta,0.00)
	        from receta r where id_paciente=$idpac order by fec_crea desc limit 50";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarExa($id){
	  $ocado=new cado();
	  $sql="select id,nombre,precio from examen where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarMedico(){
	  $ocado=new cado();
	  $sql="select * from medico ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDetalle($id){
	  $ocado=new cado();
	  $sql="select id,nombre,precio from examen where id in($id) ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function MaxOrden($user){
       $ocado=new cado();
	   $sql="select coalesce(correlativo,0),(select codigo_ingreso from caja_fondos where activo=1 and nom_user='$user') from serie where tipo_doc='OR' ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function VerActivo($user){
       $ocado=new cado();
	   $sql="select codigo_ingreso from caja_fondos where activo=1 and nom_user='$user' ";
	   $ejecutar=$ocado->ejecutar($sql);
	   return $ejecutar;
	 }
	function RegistrarReceta($subtotal,$descuento,$total,$id_pac,$id_med,$medico,$porcentaje,$monto_med,$user,$idconv,$carrito,$emp,$domicilio,$fec_dom,$lugar,$tipo_pago,$turno,$fecemision,$nrocarta,$nrosolicitud,$montocarta){
		 try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		 // $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_max_orden="select coalesce(correlativo,0),
		  coalesce((select rtrim(ltrim(codigo_ingreso)) from caja_fondos where activo=1 and nom_user='$user'),0)
		                  from serie where tipo_doc='OR' ";
		  $cmd=$cn->prepare($sql_max_orden);
		  $cmd->execute();
		  $datos=$cmd->fetch();
		  //print_r($datos);die('ok');
		  $sucursal=$datos[2];$codigo_ingreso=$datos[1];
		  $nro_orden=$datos[0]+1;
		  $credito=0;
		  if($codigo_ingreso==0){
			    $credito=1;
			   if($idconv==130){$codigo_ingreso="0".date('dmY').'CLIPAC';}
			   if($idconv==145){$codigo_ingreso="0".date('dmY').'LIDER';}
			   if($idconv==161){$codigo_ingreso="0".date('dmY').'TECNOFARMA';}
			   if($idconv==9){$codigo_ingreso="0".date('dmY').'SANIDAD';}
		  }
		 // if($sucursal==0){$codigo_ingreso=$datos[1];}else{$codigo_ingreso="0".date('dmY').'LIDER';}
		  
		  
		  $sql_convenio="select tipo,tarifario from convenio where id=$idconv";
		  $cmd_conv=$cn->prepare($sql_convenio);
		  $cmd_conv->execute();
		  $datos_conv=$cmd_conv->fetch();
		  $tipo=$datos_conv[0];$tarifario=$datos_conv[1];
		  
		  if(strlen($codigo_ingreso)>0){
			   if($emp=='P'){$estado=0;}
		       if($emp=='I'){$estado=0;}
			  // primero insertamos la cabecera
			   $sql="insert into receta(fec_crea,examen,subtotal,descuento,total,estado,id_paciente,id_medico,medico,
		      porcentaje_med,monto_medico,codigo_ingreso,nro_orden,id_convenio,emp,credito,domicilio,fec_domicilio,lugar_dom,tipo_pago_dom,turno)  
	            values(now(),'$id_examen',$subtotal,$descuento,$total,$estado,$id_pac,$id_med,'$medico',$porcentaje,$monto_med,'$codigo_ingreso',
					LPAD('$nro_orden',8,0),$idconv,'$emp','$credito',$domicilio,'$fec_dom','$lugar','$tipo_pago','$turno');";
		       $cn->prepare($sql)->execute();
			   $receta_id= $cn->lastInsertId();
			   
			     for($i=0;$i<count($carrito);$i++){
				   $precio=$carrito[$i][2]; $examen_id=$carrito[$i][0]; 
				   if($i==0){$id_examen=$carrito[$i][0];$exa_precio=$carrito[$i][0].'-'.$carrito[$i][2];}
				   else{$id_examen=$id_examen.','.$carrito[$i][0];$exa_precio=$exa_precio.';'.$carrito[$i][0].'-'.$carrito[$i][2];}
				   
				   if($tipo=='P'){$precio_part=$precio;$precio_conv=0.00;$precio_part_desc=0.00;$pago_paciente=$precio;$pago_conv=0.00;}
				   if($tipo=='C'){$precio_part=0.00;$precio_conv=$precio;$precio_part_desc=0.00;$pago_paciente=0.00;$pago_conv=$precio;}
				   if($tipo=='PD'){$precio_part=0.00;$precio_conv=0.00;$precio_part_desc=$precio;$pago_paciente=$precio;$pago_conv=0.00;}
				   $subtotal_detalle=$pago_paciente+$pago_conv;
				   $sql_detalle="INSERT INTO `receta_detalle`(`id_receta`, `id_examen`, `precio_part`, `precio_conv`, `precio_part_desc`, `cantidad`, `pago_paciente`, `pago_convenio`, `subtotal`) 
				               VALUES ('$receta_id','$examen_id','$precio_part','$precio_conv','$precio_part_desc',1,'$pago_paciente','$pago_conv','$subtotal_detalle')";
				   $cn->prepare($sql_detalle)->execute();
			     }
			 
		      $sql_receta="update receta set examen_precio='$exa_precio',examen='$id_examen' where id=$receta_id;";
			  $cn->prepare($sql_receta)->execute();
			  
		      $sql_correlativo="update serie set correlativo='$nro_orden' where tipo_doc='OR';";
		      $cn->prepare($sql_correlativo)->execute();
			  
			  if($credito==0){
			     $sql_cola="select max(nro_ticket) from cola_ticket where date(fecha)=date(now())";
		         $cmd_cola=$cn->prepare($sql_cola);$cmd_cola->execute();
		         $dato_cola=$cmd_cola->fetch();
		         $nroticket=$dato_cola[0]+1;
				 $sql_cola_insert="insert into cola_ticket (id_receta,id_paciente,fecha,nro_ticket) values($receta_id,$id_pac,now(),$nroticket)";
				 $cn->prepare($sql_cola_insert)->execute();
			  }
			  if($idconv==9){
			   $sql_salupol="INSERT INTO atencion_salupol(fec_emision,nro_carta,nro_solicitud,nro_ht,monto_carta,nro_orden,id_paciente, monto_orden,nro_documento,situacion,estado) VALUES ('$fecemision','$nrocarta','$nrosolicitud','','$montocarta','$nro_orden',$id_pac,'$total','','POR FACTURAR',0)";
			   $cn->prepare($sql_salupol)->execute();
			  }
		      $cn->commit();
		      $cn=null;
		      $return='3**++'.$nro_orden;
		    } else{$return='4**++';}
		  }catch (PDOException $ex){
                    $cn->rollBack();
			  $return='2**++';
              //return $ex->getMessage();
          }
		  return $return;
	 }
	function RegistrarCuentaCorriente($cod,$orden,$tipo_doc,$nro_serie,$nro_documento,$total_venta,$fecha,$hora){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql="insert into caja_fondos_detalle(codigo_ingreso,nro_orden,tipo_doc,nro_serie,nro_documento,total_venta,fec_emision,
		  hora_emision,saldo_total,estado)  
		  values('$cod','$orden','$tipo_doc','$nro_serie','$nro_documento','$total_venta',now(),now(),'$total_venta',0);";
		  $cn->prepare($sql)->execute();
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
	function RegistrarCredito($cod_ingreso,$orden,$total_venta,$empresa,$chek){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $in=0;// ingreso de dinero por defecto cero
		  if($chek==1){
		   $in=1;// si es que en la venta al credito hay ingreso de dinero ventas especiales
		   $sql_caja="insert into caja_fondos_detalle (codigo_ingreso,nro_orden,total_venta,tipo_pago,monto_efectivo,movimiento,
		              fec_emision,hora_emision,empresa)
					  values('$cod_ingreso','$orden','$total_venta','E','$total_venta','PAGO CREDITO',now(),now(),'$empresa')";
		   $cn->prepare($sql_caja)->execute();
		  }
		  $sql="update receta set credito=1,ingreso_dinero=$in where nro_orden='$orden'";
		  $cn->prepare($sql)->execute();
		  
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
	/*function ListarRecetaExamen($orden){
		  $ocado=new cado();
		  $sql="select examen_precio from  receta  where nro_orden='$orden'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/
	function ListarRecetaExamen($orden){
		  $ocado=new cado();
		  $sql="SELECT GROUP_CONCAT(concat(id_examen,'-',d.subtotal) SEPARATOR ';') examen_precio 
		     FROM receta r inner join receta_detalle d on r.id=d.id_receta where nro_orden='$orden'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function TraerDatosExamen($id){
		  $ocado=new cado();
		  $sql="select id,nombre,(select cast(valor/100 as decimal(18,2)) from tasas where nombre='IGV') igv from examen where id=$id"; 
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	/*function RegistrarDocumento($cod,$orden,$tipo_doc,$nro_serie,$nro_documento,$ruc,$razon,$dir,$total_venta,$tipo_pago, 
		 $monto_efectivo,$monto_tarjeta,$monto_pagado,$vuelto,$visa,$mastercard,$otros){
		  $ocado=new cado();
		  $sql="insert into caja_fondos_detalle(codigo_ingreso,nro_orden,tipo_doc,nro_serie,nro_documento,ruc,razon,direccion,total_venta,tipo_pago,monto_efectivo,monto_tarjeta,
		  monto_pagado,vuelto,visa,mastercard,otros,fec_emision,hora_emision,estado)  
		  values('$cod','$orden','$tipo_doc','$nro_serie','$nro_documento','$ruc','$razon','$dir','$total_venta','$tipo_pago', 
		 '$monto_efectivo','$monto_tarjeta','$monto_pagado','$vuelto','$visa','$mastercard','$otros',now(),now(),0);";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }*/
	function RegistrarFacturacionElectronica($cod_operacion,$cod_dom_fiscal,$tipo_documento,$serie,$correlativo,$tipodoc_cli,$doc_cliente,$nomcli,$dircliente,$moneda,$op_gravadas,$op_gratuitas,$op_inafecta,$op_exoneradas,$total_descuento,$total_igv,$total_isc
	,$total_otrtri,$total_otrcar,$total_glosa,$valor_venta,$importe_total,$idcliente,$array_examen,$cod,$orden,$tipo_doc,$nro_serie,$nro_documento,$ruc,$razon,$dir,$total_venta,$tipo_pago, 
		 $monto_efectivo,$monto_tarjeta,$monto_pagado,$vuelto,$visa,$mastercard,$otros,$emp,$nrodeposito){
		try{
		  
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  //$sql_validar="select count(*) from doc_electronicos where serie";
		  $sql="insert into doc_electronicos(cod_operacion,fecha_emision,hora_emision,cod_dom_fiscal,tipo_documento,serie,correlativo,
		  tipodoc_cli,doc_cliente,nomcli,dircliente,moneda,op_gravadas,op_gratuitas,op_inafecta,op_exoneradas,total_descuento,total_igv,
	total_isc,total_otrtri,total_otrcar,total_glosa,valor_venta,importe_total,created_at,id_cliente,grupal,emp)  	  values('$cod_operacion',now(),now(),'$cod_dom_fiscal','$tipo_documento','$serie','$correlativo','$tipodoc_cli','$doc_cliente','$nomcli',
	'$dircliente','$moneda','$op_gravadas','$op_gratuitas','$op_inafecta','$op_exoneradas','$total_descuento','$total_igv','$total_isc',
'$total_otrtri','$total_otrcar','$total_glosa','$valor_venta','$importe_total',now(),$idcliente,0,'$emp');";
	      //die($sql);
		  $cn->prepare($sql)->execute();
		  $doc_electronico_id= $cn->lastInsertId();
		  
		   //die($doc_electronico_id);
		  // 1- AUTOINCREMENTAL, 2- $doc_electronico_id ,3- Orden de Item (De acuerdo al orden en que se reciben),4-tipo_item 1(PRODUCTO ') 2(SERVICIO) NOSOTROS UTILIZAREMOS 2
		  // 5- cod_afecta_igv(UTILIZAREMOS 10), 6- unidad_medida(ZZ=SERVICIO), 7- cantidad (POR DEFECTO 1), 8- descripcion_item (NOMBRE DEL SERVICIO), 9- cod_interno (IDEXAMEN)
		  // 10- COD_SUNAT (POR EL MOMENTO NULL), 11- valuni(VALOR UNITARIO SIN DESCUENTO ni impuestos), 12- VALDES (VALOR DEL DESCUENTO POR DEFECTO NULL), 13- VALIGV(VALOR DEL IGV)
		  // 14- PREUNI(precio unitario (con dscto e impuestos)), 15- valven(Valor de Venta de Item (cantidad * valor uni))
		  // 16- tipo_isc (null por defecto) , 17- valisc(null por defecto) , 18- created_at(fecha hora del sistema), 19- updated_at(null por defecto)
		    $incremental=0;$igv_total=0;
		  for($i=0;$i<count($array_examen);$i++){$incremental++;
		    // die($array_examen[$i]);
			 $datos=explode('-',$array_examen[$i]);
			 $preuni=$datos[1];
			// die($preuni);
			 //$ver=$this->TraerDatosExamen($datos[$i][0]);
			 //$data=$ver->fetch();
			 $cod_interno=$data[0];$descripcion_item=$data[1];$tasa_igv=$data[2];
			 $sql_examen="select id,nombre,(select cast(valor/100 as decimal(18,2)) from tasas where nombre='IGV') igv
			       from examen where id=".$datos[0];
			 $cmd_examen= $cn->prepare($sql_examen);
		     $cmd_examen->execute();
			 $data=$cmd_examen->fetch();
			 $cod_interno=$data[0];$descripcion_item=$data[1];$tasa_igv=$data[2];
			
			  $valuni=round($preuni/(1+$tasa_igv),2);
			  $valigv=$preuni-$valuni;$valven=$valuni;
			  $igv_total=$igv_total+$valigv;
			  $sql_detalle="INSERT INTO `doc_electronico_items` (`doc_electronico_id`, `nro_orden`, `tipo_item`, `cod_afecta_igv`, `unidad_medida`, `cantidad`, `descripcion_item`,
		                               `cod_interno`, `valuni`, `valigv`, `preuni`,`valven`, `created_at`) 
		                    VALUES ('$doc_electronico_id', $incremental, 2, '10', 'ZZ', 1, '$descripcion_item',LPAD($cod_interno,5,0),'$valuni','$valigv', '$preuni', '$valven', now());";
             $cn->prepare($sql_detalle)->execute();
		      
		   }
		  
		  $sql_caja_fondos="insert into caja_fondos_detalle(codigo_ingreso,nro_orden,tipo_doc,nro_serie,nro_documento,ruc,razon,direccion,total_venta,tipo_pago,monto_efectivo,
		  monto_tarjeta,monto_pagado,vuelto,visa,mastercard,otros,fec_emision,hora_emision,estado,movimiento,empresa,nrodeposito)  
		  values('$cod','$orden','$tipo_doc','$nro_serie','$nro_documento','$ruc','$razon','$dir','$total_venta','$tipo_pago', 
 '$monto_efectivo','$monto_tarjeta','$monto_pagado','$vuelto','$visa','$mastercard','$otros',now(),now(),0,'VENTA','$emp','$nrodeposito');";
		  $cn->prepare($sql_caja_fondos)->execute();

		  //$corre=(int)$correlativo;
		  $sql_update="update serie set correlativo='$correlativo' where serie='$serie' and tipo_doc='$tipo_doc'";
		  $cn->prepare($sql_update)->execute();
		  $sql_doc="update receta set doc_emitido=1 where nro_orden='$orden';";
		  $cn->prepare($sql_doc)->execute();
		  $subtotal=$importe_total-$igv_total;
		  $sql_totales="update doc_electronicos set total_igv=$igv_total,op_gravadas=$subtotal,valor_venta=$subtotal
		               where id=$doc_electronico_id;";
		  $cn->prepare($sql_totales)->execute();
		  $cn->commit();
		  $cn=null;
		  if($tipo_documento=='01' || $tipo_documento=='03'){
			  if($emp=='P'){file_get_contents("http://localhost/Lab/FactElect/firmar/".$doc_electronico_id);}
			  if($emp=='I'){file_get_contents("http://localhost/Innova/FactElect/firmar/".$doc_electronico_id);}
		   }		  
		  $return=$doc_electronico_id.'*****'.$cod.'*****'.$orden;
		  
		 }catch (PDOException $ex){
                    $cn->rollBack();
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }
	
	function ActOrdenDocEmitido($nro_orden){
       $ocado=new cado();
	   $sql="update receta set doc_emitido=1 where nro_orden='$nro_orden';";
	   $ejecutar=$ocado->ejecutar($sql);
	   return $ejecutar;
	 }
	/*function ActCorrelativoOrden($nro_orden){
       $ocado=new cado();
	   $sql="update serie set correlativo='$nro_orden' where tipo_doc='OR';";
	   $ejecutar=$ocado->ejecutar($sql);
	   return $ejecutar;
	 }*/
	function ValidarReceta($id_pac){
		  $ocado=new cado();
		  $sql="select id,nro_orden from receta where estado=0 and anulado=0 and id_paciente=$id_pac";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ValidarPac($id_pac){
		  $ocado=new cado();
		  $sql="select id from paciente where  id=$id_pac";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 
	function Eliminar($nro_orden,$user){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="update receta set anulado=1,user_anula='$user',fecha_anula=now() where nro_orden = '$nro_orden' ";
		  $cn->prepare($sql)->execute();
		  $sql_caja="update caja_fondos_detalle set estado=1 where nro_orden = '$nro_orden' ";
		  $cn->prepare($sql_caja)->execute();
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
	 
	/*function Eliminar($nro_orden,$user){
		$ocado=new cado();
		$sql="update receta set anulado=1,user_anula='$user',fecha_anula=now() where nro_orden = '$nro_orden' ";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/
	
	function BuscarOrden($orden){
		$ocado=new cado();
		$sql="select r.id,r.nro_orden,r.examen,r.porcentaje_med,r.monto_medico,r.subtotal,r.descuento,r.total,r.estado,r.id_convenio,
		concat(p.ape_pat,' ',p.ape_mat,' ',coalesce(p.preNombres,''))as paciente,r.id_medico,r.anulado,r.examen_precio,p.dni,p.id idpac,
		r.doc_emitido,r.emp,r.credito
		      from receta r inner join paciente p on r.id_paciente=p.id
			   where r.nro_orden =  LPAD($orden,8,0) ";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	/*function ListarDetalleOrden($examenes,$idconv){
		$ocado=new cado();
		$sql="select  e.id,e.nombre as value,coalesce(p.precio,0) as precio,m.nombre as muestra
	           from examen e inner join muestra m on e.muestra=m.id
			      left join examen_precio p on e.id=p.id_examen and p.id_convenio=$idconv 
				where e.id in($examenes)";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/
	function ListarDetalleOrden($examen){
		$ocado=new cado();
		$sql="select e.nombre,m.nombre as muestra,g.nombre gru,frecuencia
	           from examen e inner join muestra m on e.muestra=m.id
			   inner join grupo g on e.id_grupo=g.id
				where e.id =$examen";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ListarDetalleOrdenNuevo($idreceta){
		$ocado=new cado();
		$sql="select e.nombre,d.subtotal,m.nombre as muestra,g.nombre gru,frecuencia
	           from receta r inner join receta_detalle d on r.id=d.id_receta
			                 inner join examen e on d.id_examen=e.id
							 inner join muestra m on e.muestra=m.id
							 inner join grupo g on e.id_grupo=g.id
				where r.id =$idreceta";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function Modificar($id,$examen,$subtotal,$total){
		$ocado=new cado();
		$sql="update receta set examen='$examen',subtotal='$subtotal',total='$total' where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ListarReId($id){
		$ocado=new cado();
		$sql="select * from receta where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function Editar($id,$descuento,$total){
		$ocado=new cado();
		$sql="update receta set descuento='$descuento',total='$total' where id = $id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function VerPaquete($examen){
		$ocado=new cado();
		$sql="select id,case when tipo=1 then paquete else id end final from examen where id in($examen)";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function InsertarResultado($orden){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_listar="select id,id_paciente,examen from receta where nro_orden='$orden'";
		  //die($sql_listar);
		  $cmd=$cn->prepare($sql_listar);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $idreceta=$col[0];$examen=$col[2];$idpac=$col[1];	  
		  $sql=" insert into resultado(id_receta,id_examen,examen,id_caracteristica,caracteristica,titulo,resultado,unidad,rango,metodo)  
		         select t.id_receta,t.id_exa,t.nombre,c.id,c.nombre,c.titulo,c.resultado,c.unidad,c.referencia,c.metodo
                 from (
                  select d.id_receta,case when e.paquete=1 then p.id_exa_detalle else e.id end id_exa, e.nombre,e.id_grupo
                   from receta_detalle d inner join examen e on d.id_examen=e.id
                   left join examen_paquete p on e.id=p.id_examen
                   where id_receta=$idreceta
                  ) as t  left join caracteristica c on t.id_exa=c.id_examen
                   inner join grupo g on t.id_grupo=g.id
                   order by g.nro_orden asc,t.id_exa asc,c.orden asc";
		 // die($sql);
		  $cn->prepare($sql)->execute();
		  $sql_act="update receta set estado=1 where id=$idreceta;";
		  $cn->prepare($sql_act)->execute();
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
	 
	 function InsertarResultadoImagenes($orden){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_listar="select id,id_paciente,examen from receta where nro_orden='$orden'";
		  $cmd=$cn->prepare($sql_listar);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $idreceta=$col[0];$examen=$col[2];$idpac=$col[1];	  
		  $sql=" insert into resultado_informe(id_receta,id_examen,id_detalle_examen)  
                  select d.id_receta,case when e.paquete=1 then p.id_exa_detalle else e.id end id_exa, inf.id
                   from receta_detalle d inner join examen e on d.id_examen=e.id
				                         inner join examen_informe inf on e.id=inf.id_examen
                                         left join examen_paquete p on e.id=p.id_examen
                   where id_receta=$idreceta";
                  
		  //die($sql);
		  $cn->prepare($sql)->execute();
		  $sql_act="update receta set estado=1 where id=$idreceta;";
		  $cn->prepare($sql_act)->execute();
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
	 /*function ActReceta($idreceta){
		$ocado=new cado();
		$sql="update receta set estado=1 where id=$idreceta;";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/
	function LisRes(){
		$ocado=new cado();
		$sql="select r.id,fec_crea,(select nom_pac from paciente p where p.id=r.id_paciente )paciente,estado,id_paciente,
		    (select nombre from medico m where m.id=r.id_medico )medico,muestra
	        from receta r where estado=1  order by fec_crea desc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisRes1($bus,$p){
		if($p==0){
		  if($bus==''){$where="";}else{$where=" and concat(rtrim(ltrim(ape_pat)),' ',rtrim(ltrim(ape_mat)),' ',rtrim(ltrim(preNombres))) like '%$bus%'";}
		}
		if($p==1){
		  if($bus==''){$where=" and r.estado=1 ";}else{$where=" and r.estado=1 and concat(rtrim(ltrim(ape_pat)),' ',rtrim(ltrim(ape_mat)),' ',rtrim(ltrim(preNombres))) like '%$bus%'";}
		}
		
		$ocado=new cado();
		$sql="select r.id,nro_orden,r.fec_crea,
		concat(coalesce(ape_pat,''),' ',coalesce(ape_mat,''),' ',coalesce(preNombres,''))paciente
		,r.estado,r.id_paciente,
		    m.nombre,' ' examen,user_finaliza,r.user_anula,r.anulado,conv.ruc ruc,
			(select count(*) from resultado re inner join examen e on re.id_examen=e.id
		      where re.id_receta=r.id and  resul_externo=0) can,p.telefono,domicilio,obs_dom,
			  DATE_FORMAT(fecha_entrega_dom,'%d/%m/%Y %h:%i %p') fec_ent_dom,
			 case when domicilio=1 then  DATE_FORMAT(fec_domicilio,'%d/%m/%Y') else '' end fec_dom ,fec_domicilio,lugar_dom,tipoentregadom 
	        from receta r left join medico m on r.id_medico=m.id
			              inner join paciente p on r.id_paciente=p.id
						  inner join convenio conv on r.id_convenio=conv.id
			where r.estado>0 and r.emp='P'  $where  order by r.fec_crea desc
			limit 0,200";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisResOrden($bus,$p,$tipoorden){
		
		/*if($p==1){
		  if($bus==''){$where=" and r.estado=1 ";}else{$where=" and r.estado=1 and concat(rtrim(ltrim(ape_pat)),' ',rtrim(ltrim(ape_mat)),' ',rtrim(ltrim(preNombres))) like '%$bus%'";}
		}*/
		if($tipoorden==1){$ordenar=" r.fec_crea desc ";}
		if($tipoorden==2){$ordenar=" r.fecha_entrega_dom desc, r.estado asc";}
		$ocado=new cado();
		$sql="select r.id,nro_orden,r.fec_crea,
		concat(coalesce(ape_pat,''),' ',coalesce(ape_mat,''),' ',coalesce(preNombres,''))paciente
		,r.estado,r.id_paciente,
		    m.nombre,' ' examen,user_finaliza,r.user_anula,r.anulado,conv.ruc ruc,
			(select count(*) from resultado re inner join examen e on re.id_examen=e.id
		      where re.id_receta=r.id and  resul_externo=0) can,p.telefono,domicilio,obs_dom,
			  DATE_FORMAT(fecha_entrega_dom,'%d/%m/%Y %h:%i %p') fec_ent_dom,
			 case when domicilio=1 then  DATE_FORMAT(fec_domicilio,'%d/%m/%Y') else '' end fec_dom ,fec_domicilio,lugar_dom,tipoentregadom 
	        from receta r left join medico m on r.id_medico=m.id
			              inner join paciente p on r.id_paciente=p.id
						  inner join convenio conv on r.id_convenio=conv.id
			where r.estado>0 and r.emp='P'  and concat(rtrim(ltrim(ape_pat)),' ',rtrim(ltrim(ape_mat)),' ',rtrim(ltrim(preNombres))) like '%$bus%'  order by $ordenar 
			limit 0,200";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function LisGraResulCantidad($idreceta){
		$ocado=new cado();
		$sql="select count(*) from resultado r inner join examen e on r.id_examen=e.id
		      where id_receta=$idreceta and  resul_externo=0";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function LisInforme($bus){
		$ocado=new cado();
		$sql="select r.id,nro_orden,r.fec_crea,
		concat(coalesce(ape_pat,''),' ',coalesce(ape_mat,''),' ',coalesce(preNombres,''))paciente
		,r.estado,r.id_paciente,
		    m.nombre,(select nombre from examen e where e.id=r.examen )examen,user_finaliza,r.user_anula,r.anulado
	        from receta r left join medico m on r.id_medico=m.id
			              inner join paciente p on r.id_paciente=p.id
			where r.estado>0 and r.emp='I' and concat(rtrim(ltrim(ape_pat)),' ',rtrim(ltrim(ape_mat)),' ',rtrim(ltrim(preNombres))) like '%$bus%' 
			  order by r.fec_crea desc
			limit 0,200";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisReporte($pac,$inicio,$fin){
		if($pac==""){$where="";}else {$where=" and nom_pac like '%$pac%'";}
		$ocado=new cado();
		$sql="select r.id,r.fec_crea,p.nom_pac,r.estado,p.id,
		    (select nombre from medico m where m.id=r.id_medico )medico,muestra,r.total,coalesce(acuenta,0.00),total-coalesce(acuenta,0.00)
	        ,(select nombre from examen e where e.id=r.examen )examen
			from receta r inner join paciente p on r.id_paciente=p.id
			 where r.estado>0 and (cast(r.fec_crea as date)>='$inicio' and cast(r.fec_crea as date)<='$fin')  $where
			    order by fec_crea desc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisCarRes($id,$arreglo){
		$ocado=new cado();
		$sql="select r.*,e.muestra from resultado r inner join examen e on r.id_examen=e.id inner join grupo g on e.id_grupo=g.id
		      where id_receta=$id and r.id_examen in($arreglo)
			  order by g.nro_orden asc,r.id_examen asc,r.id_caracteristica asc";
		$ejecutar=$ocado->ejecutar2($sql);
		return $ejecutar;
	}
	function LisPdfRes($id,$arreglo){
		//echo $id.'-->'.$arreglo;exit;
		$ocado=new cado();
		$sql="select r.*,m.nombre muestra,case when g.mostrar=0 then g.nombre else '' end nom_grupo,g.id id_gru,e.nombre nombre_examen,e.historico
	   from resultado r inner join examen e on r.id_examen=e.id 
		                inner join grupo g on e.id_grupo=g.id
		                inner join muestra m on e.muestra=m.id
		      where id_receta=$id and r.id_examen in($arreglo)
			   order by g.nro_orden asc,e.nombre asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisPdfResGrupal($id,$arreglo){
		//echo $id.'-->'.$arreglo;exit;
		$ocado=new cado();
		$sql="select r.*,m.nombre muestra,case when g.mostrar=0 then g.nombre else '' end nom_grupo,g.id id_gru,e.nombre nombre_examen,e.historico
	   from resultado r inner join examen e on r.id_examen=e.id 
		                inner join grupo g on e.id_grupo=g.id
		                inner join muestra m on e.muestra=m.id
		      where id_receta=$id and r.id_examen in($arreglo) and e.especial=0
			   order by g.nro_orden asc,e.nombre asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function LisPdfResIndividual($id,$arreglo){
		$ocado=new cado();
		$sql="select r.*,m.nombre muestra,case when g.mostrar=0 then g.nombre else '' end nom_grupo,g.id id_gru,e.nombre nombre_examen,e.historico
	   from resultado r inner join examen e on r.id_examen=e.id 
		                inner join grupo g on e.id_grupo=g.id
		                inner join muestra m on e.muestra=m.id
		      where id_receta=$id and r.id_examen in($arreglo) and e.especial=1
			   order by g.nro_orden asc,e.nombre asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function LisPdfResCovid($id){
		//echo $id.'-->'.$arreglo;exit;
		$ocado=new cado();
		$sql="select r.*,m.nombre muestra,case when g.mostrar=0 then g.nombre else '' end nom_grupo,g.id id_gru,e.nombre nombre_examen,e.historico
	   from resultado r inner join examen e on r.id_examen=e.id 
		                inner join grupo g on e.id_grupo=g.id
		                inner join muestra m on e.muestra=m.id
			  where id_receta=$id and r.id_examen in( '616')
		order by g.nro_orden asc,e.nombre asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function VerHistorico($idreceta,$arreglo){
		$ocado=new cado();
		$sql="
		      select rec.id,rec.id_paciente,resul.id_examen,e.nombre,(select count(*) from resultado rst where rst.id_receta=resul.id_receta and rst.id_examen=resul.id_examen and length(firma)>5) cant 
	         from  receta rec inner join resultado resul on rec.id=resul.id_receta
	                   inner join examen e on resul.id_examen=e.id
		       where rec.id=$idreceta and e.historico=1 and resul.id_examen in($arreglo) group by rec.id,rec.id_paciente,resul.id_examen,e.nombre ";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	/*function LisPdfResHistorico($idreceta,$idpac,$idexamen){
		$ocado=new cado();
		$sql="select  year(fec_crea)anio,DATE_FORMAT(re.fec_crea, '%d/%m/%Y')fecha,r.resultado,
		replace(replace(replace(r.resultado,'<',''),'>',''),' ','')  resultado
	   from receta re inner join resultado r on re.id=r.id_receta
		     where id_paciente=$idpac and id_receta<=$idreceta and r.id_examen=$idexamen and resultado<>''  and re.anulado=0
	   order by re.id asc limit 0,24";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}*/
	function LisPdfResHistorico($idreceta,$idpac,$idexamen){
		$ocado=new cado();
		$sql="select * from 
		(select  year(fec_crea)anio,DATE_FORMAT(re.fec_crea, '%d/%m/%Y')fecha,r.resultado,
		replace(replace(replace(r.resultado,'<',''),'>',''),' ','')  resultado1,re.id
	   from receta re inner join resultado r on re.id=r.id_receta
		     where id_paciente=$idpac and id_receta<=$idreceta and r.id_examen=$idexamen and resultado<>''  and re.anulado=0
	   order by re.id desc limit 0,24 ) as t order by id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function CanResulExamen($idreceta,$idexamen){
		$ocado=new cado();
		$sql="select count(*) from resultado where id_receta=$idreceta and id_examen=$idexamen";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function CanResulGrupo($idreceta,$idgrupo){
		$ocado=new cado();
		$sql="select count(*) from resultado r inner join examen e on r.id_examen=e.id
		                                       inner join grupo g on e.id_grupo=g.id
		       where id_receta=$idreceta and g.id=$idgrupo";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisCaractExamen($idexa){
		$ocado=new cado();
		$sql="select r.*,m.nombre,e.nombre examen from caracteristica r inner join examen e on r.id_examen=e.id
		              inner join muestra m on e.muestra=m.id 
		      where id_examen=$idexa order by r.id_examen asc,r.orden asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function LisCarRes1($id){
		$ocado=new cado();
		$sql="select r.*,m.nombre,e.resul_externo ext,e.especial esp,r.user_ingresa,case when r.examen<>e.nombre then concat(e.nombre,' (',r.examen,')') else e.nombre end nom,de.estado
		      from resultado r inner join examen e on r.id_examen=e.id 
		           inner join grupo g on e.id_grupo=g.id
		           inner join muestra m on e.muestra=m.id
				   inner join receta_detalle de on r.id_receta=de.id_receta and r.id_examen=de.id_examen
		       where r.id_receta=$id order by g.nro_orden asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisCarResPaquete($id){
		$ocado=new cado();
		$sql="select r.*,m.nombre,e.resul_externo ext,e.especial esp,r.user_ingresa,case when r.examen<>e.nombre then concat(e.nombre,' (',r.examen,')') else e.nombre end nom,de.estado
		      from resultado r inner join examen e on r.id_examen=e.id 
		           inner join grupo g on e.id_grupo=g.id
		           left join muestra m on e.muestra=m.id
				   left join receta_detalle de on r.id_receta=de.id_receta and r.id_examen=de.id_examen
		       where r.id_receta=$id order by g.nro_orden asc,r.id asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ContarPaquete($id){
		$ocado=new cado();
		$sql="select count(*) from receta_detalle de inner join examen e on de.id_examen=e.id and e.paquete=1
		       where de.id_receta=$id ";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisCarResInformes($id){
		$ocado=new cado();
		$sql="select r.id,r.id_examen,inf.id,e.nombre,case when LENGTH(rtrim(ltrim(r.titulo)))>0 then r.titulo else inf.titulo end titu ,
		      case when LENGTH(rtrim(ltrim(r.contenido)))>0 then r.contenido  else inf.contenido end conte,case when LENGTH(rtrim(ltrim(r.contenido)))>0 then 1 else 0 end informado
		      from resultado_informe r inner join examen e on r.id_examen=e.id 
			                           inner join examen_informe inf on r.id_detalle_examen=inf.id
			  where r.id_receta=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ContarDetalleInforme($idreceta,$idexamen){
		$ocado=new cado();
		$sql="select count(*) from resultado_informe where id_receta=$idreceta and id_examen=$idexamen ";     
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ContarExamenEspecial($id){
		$ocado=new cado();
		$sql="select count(*) from resultado r inner join examen e on r.id_examen=e.id
               where r.id_receta=$id and e.especial=1 ";     
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisCarResUro($id){
		$ocado=new cado();
		$sql="select * from
                   (select *,case resultado when 'Sensible' then '1' 
                                            when 'Intermedio' then '2'
                                            when 'Resistente' then '3' else '0' end orden from resultado 
                     where id_receta=$id ) as t
              order by orden asc,id_examen asc,id_caracteristica asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function VerRes($id){
		$ocado=new cado();
		$sql="select resultado from resultado where id_receta=$id and caracteristica='RESULTADO'";               
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	/*function GrabarRes($array,$user){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  for($i=0;$i<count($array);$i++){
			$id=$array[$i][0];$resultado=$array[$i][1];
		    $sql="update resultado set resultado='$resultado' where id=$id";
			$cmd=$cn->prepare($sql)->execute();
			if($cmd->rowCount()==1){$sql_user="update resultado set user_ingresa='$user',fec_hora_ingresa=now() where id=$id";$cn->prepare($sql_user)->execute();}
		  }
		  $return=1;
		}catch (PDOException $ex){
                    $cn->rollBack();
		  $return=0;
              //return $ex->getMessage();
         }
		  return $return;
	}*/
	function GrabarRes($id,$resultado,$user){
		  $ocado=new cado();
		  $sql="update resultado set resultado='$resultado' where id=$id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function GrabarResUser($id,$user){
		  $ocado=new cado();
		  $sql="update resultado set user_ingresa='$user',fec_hora_ingresa=now() where id=$id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function LisGraResul($idreceta){
		$ocado=new cado();
		$sql="select id from resultado where id_receta=$idreceta";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	
	function ListarPaciente($idpac){
	  $ocado=new cado();
	  $sql="select id,concat(ape_pat,' ',ape_mat,' ',coalesce(preNombres,''))pacien,dni,sexo,fec_nac,telefono,fec_crea,crea_user,
	        TIMESTAMPDIFF(YEAR,fec_nac, CURDATE())edad
	        from paciente where id=$idpac ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarPaciente1($idreceta){
	  $ocado=new cado();
	  $sql="select p.id,concat(ape_pat,' ',ape_mat,' ',coalesce(preNombres,''))pacien,dni,sexo,fec_nac,telefono,
	  case when domicilio=0 then r.fec_crea else r.fec_domicilio end fec_crea ,p.crea_user,
	        TIMESTAMPDIFF(YEAR,fec_nac, CURDATE())edad,medico,nro_orden
	        from receta r inner join  paciente p on r.id_paciente=p.id where r.id=$idreceta ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function FinalizarRes($id,$user){
		$ocado=new cado();
		$sql="update receta set estado=2,user_finaliza='$user' where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function DesfinalizarRes($id,$user){
		$ocado=new cado();
		$sql="update receta set estado=1,user_desfinaliza='$user',user_finaliza='' where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ActualizarRuta($id,$ruta){
		//echo $id."-->".$ruta."-->".$idexamen;
		$ocado=new cado();
		$sql="update resultado set firma='$ruta' 
		      where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function ActualizarFirma($id){
		//echo $id."-->".$ruta."-->".$idexamen;
		$ocado=new cado();
		$sql="update resultado set firma='' 
		      where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function MaxIdRuta($id,$idexamen){
		//echo $id."-->".$ruta."-->".$idexamen;
		$ocado=new cado();
		$sql="select max(id) from resultado where id_receta=$id and id_examen=$idexamen";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function UpdaAcuenta($id,$acuenta){
		$ocado=new cado();
		$sql="update receta set acuenta='$acuenta' where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
    function LisPacEmp($empresa,$pac,$ini,$fin){
		//echo $empresa.'-->'.$pac.'-->'.$ini.'-->'.$fin;exit;
		if($pac==""){$where="";}else {$where=" and nom_pac like '%$pac%'";}
	  $ocado=new cado();
	  $sql="select r.id,r.fec_crea,r.examen,nom_pac,(select m.nombre from medico m where m.id=r.id_medico)doctor,muestra,id_paciente
	       from receta r inner join paciente p on r.id_paciente=p.id 
		   where cen_dialisis='$empresa' and r.estado=2 and ((cast(fec_crea as date)>='$ini' and cast(fec_crea as date)<='$fin'))  $where
		   order by fec_crea desc ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ver($examen){
	  $ocado=new cado();
	  $sql="select group_concat(case when tipo=0 then cast(id as char) else paquete end ) as general
	        from examen where id in($examen) and estado=0 ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ver1($examen){
	  $ocado=new cado();
	  $sql="select group_concat(nombre)general
	        from examen where id in($examen) and estado=0 ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function LisPacConv($cen_dia,$conv,$mes,$anio,$abrev){
	  $ocado=new cado();
	  $sql=" select id,nom_pac,
	  (select count(*) from tbl_sis t 
	    where t.id_pac=p.id and t.cen_dialisis='$cen_dia' and t.convenio='$conv' and anio=$anio and mes=$mes) can,
	  (select tipo_examen from tbl_sis t 
	    where t.id_pac=p.id and t.cen_dialisis='$cen_dia' and t.convenio='$conv' and anio=$anio and mes=$mes) tipo_exa,
	  (select $abrev from he_cronograma c  where  p.id=c.id_paciente and anio=$anio)tipo 
from paciente p
where cen_dialisis='$cen_dia' and tipo_seguro='$conv' and estado=0
	        order by nom_pac asc ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 
	 function LisPacConvSal($cen_dia,$conv,$mes,$anio){
	  $ocado=new cado();
	  $sql=" select id,nom_pac,
	  (select count(*) from tbl_salupol t 
	    where t.id_pac=p.id and t.cen_dialisis='$cen_dia' and t.convenio='$conv' and anio=$anio and mes=$mes) can,
	  (select tipo_examen from tbl_sis t 
	    where t.id_pac=p.id and t.cen_dialisis='$cen_dia' and t.convenio='$conv' and anio=$anio and mes=$mes) tipo_exa
from paciente p
where cen_dialisis='$cen_dia' and tipo_seguro='$conv' and estado=0
	        order by nom_pac asc ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function ListarFinal($tipo,$anio,$mes,$cen_dia,$conv){
	   if($tipo=='M'){$where=" tb.tipo_examen in('M','T','S')";}
	   if($tipo=='T'){$where=" tb.tipo_examen in('T','S')";}
	   if($tipo=='S'){$where=" tb.tipo_examen in('S')";}
	  $ocado=new cado();
	  $sql="select tb.id,p.nom_pac,tb.tipo_examen,
	  (select m_hto from tbl_sis t where t.id_pac=tb.id_pac and t.anio=$anio and t.mes=$mes-1 and t.convenio='$conv' and 
	  t.cen_dialisis='$cen_dia') hto_ant,
	  m_hto,m_hb,m_ureapre,m_ureapost,m_pesoseco,m_pesopre,m_pesopost,fec_muestra,m_horashd,
	  m_urr,m_ktv,m_creatpre,m_elecna,m_eleck,m_eleccl,m_calcio,m_fosforo,m_nuo,nro_orden,t_fralbum,t_fraalfa1,t_fraalfa2,t_frbeta,
	  t_frgamma,t_prot,t_fosfatasa,t_tgo,t_tgp,s_creat24h,s_vih,s_vdrl_rpr,s_pth,s_hbs,s_detecc_hbs, s_detc_tothb,s_det_anthep,
	  s_ferritina,s_indice,s_hierro,s_transferrina,s_dep_creat,vol_orina,estatura      
	        from tbl_sis tb inner join paciente p on tb.id_pac=p.id
			where  tb.anio=$anio and tb.mes=$mes and tb.convenio='$conv' and tb.cen_dialisis='$cen_dia' and $where
			order by tb.nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarFinalSalupol($anio,$mes,$cen_dia,$conv){  
	  $ocado=new cado();
	  $sql="select tb.id,tb.nro_orden,p.nom_pac,tb.convenio,
	  (select hto from tbl_salupol t where t.id_pac=tb.id_pac and t.anio=$anio and t.mes=$mes-1 and t.convenio='$conv' and 
	  t.cen_dialisis='$cen_dia') hto_ant,hto,hg,ureapre,ureapost,pesopre,pesopost,hrashd,urr,ktv
	   from tbl_salupol tb inner join paciente p on tb.id_pac=p.id
	   where  tb.anio=$anio and tb.mes=$mes and tb.convenio='$conv' and tb.cen_dialisis='$cen_dia' 
	   order by tb.nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	 function InsertarSis($id_pac,$pac,$orden,$cen_dia,$conv,$anio,$mes,$tipo_examen){
	  $ocado=new cado();
	  $sql="insert  into tbl_sis(id_pac,paciente,nro_orden,cen_dialisis,convenio,anio,mes,tipo_examen) 
	           values ($id_pac,'$pac',$orden,'$cen_dia','$conv',$anio,$mes,'$tipo_examen')";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function InsertarSalupol($id_pac,$pac,$orden,$cen_dia,$conv,$anio,$mes){
	  $ocado=new cado();
	  $sql="insert  into tbl_salupol(id_pac,paciente,nro_orden,cen_dialisis,convenio,anio,mes) 
	           values ($id_pac,'$pac',$orden,'$cen_dia','$conv',$anio,$mes)";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function Deshacer($idpac,$anio,$mes){
	  $ocado=new cado();
	  $sql="delete from tbl_sis where id_pac=$idpac and anio=$anio and mes=$mes";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function GrabarResulConv($id,$m_hto_ant,$m_hto,$m_hb,$m_ureapre,$m_ureapost,$m_pesoseco,$m_pesopre,$m_pesopost,$fec_muestra,$m_horashd,
	  $m_urr,$m_ktv,$m_creatpre,$m_elecna,$m_eleck,$m_eleccl,$m_calcio,$m_fosforo,$m_nuo){
	  $ocado=new cado();
	  $sql="update tbl_sis set m_hto_ant='$m_hto_ant',m_hto='$m_hto',m_hb='$m_hb',m_ureapre='$m_ureapre',
	          m_ureapost='$m_ureapost',m_pesoseco='$m_pesoseco',m_pesopre='$m_pesopre',m_pesopost='$m_pesopost',fec_muestra='$fec_muestra',
	          m_horashd='$m_horashd',m_urr='$m_urr',m_ktv='$m_ktv',m_creatpre='$m_creatpre',m_elecna='$m_elecna',m_eleck='$m_eleck',
	          m_eleccl='$m_eleccl',m_calcio='$m_calcio',m_fosforo='$m_fosforo',m_nuo='$m_nuo'
	         where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GrabarResulConvT($id,$t_fralbum,$t_fraalfa1,$t_fraalfa2,$t_frbeta,$t_frgamma,$t_prot,$t_fosfatasa,$t_tgo,$t_tgp){
	  $ocado=new cado();
	  $sql="update tbl_sis set t_fralbum='$t_fralbum',t_fraalfa1='$t_fraalfa1',t_fraalfa2='$t_fraalfa2',t_frbeta='$t_frbeta',
	          t_frgamma='$t_frgamma',t_prot='$t_prot',t_fosfatasa='$t_fosfatasa',t_tgo='$t_tgo',t_tgp='$t_tgp'
	         where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GrabarResulConvS($id,$s_creat24h,$s_vih,$s_vdrl_rpr,$s_pth,$s_hbs,$s_detecc_hbs,$s_detc_tothb,$s_det_anthep,
	                           $s_ferritina,$s_indice,$s_hierro,$s_transferrina,$s_dep_creat,$estatura,$vol_orina){
	  $ocado=new cado();
	  $sql="update tbl_sis set s_creat24h='$s_creat24h',s_vih='$s_vih',s_vdrl_rpr='$s_vdrl_rpr',s_pth='$s_pth',s_hbs='$s_hbs',
	                s_detecc_hbs='$s_detecc_hbs',s_detc_tothb='$s_detc_tothb',s_det_anthep='$s_det_anthep',
	                s_ferritina='$s_ferritina',s_indice='$s_indice',s_hierro='$s_hierro',s_transferrina='$s_transferrina',
					s_dep_creat='$s_dep_creat',estatura='$estatura',vol_orina='$vol_orina'
	         where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GrabarResulConvSalupol($id,$hto_ant,$hto,$hg,$ureapre,$ureapost,$pesopre,$pesopost,$hrashd,$urr,$ktv){
	  $ocado=new cado();
	  $sql="update tbl_salupol set hto_ant='$hto_ant',hto='$hto',hg='$hg',ureapre='$ureapre',
	          ureapost='$ureapost',pesopre='$pesopre',pesopost='$pesopost',
	          hrashd='$hrashd',urr='$urr',ktv='$ktv'
	         where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConv($anio,$mes,$cen_dia,$conv){   
	  $ocado=new cado();
	  if($conv=="SIS"){
	  $sql="select t.id,t.nro_orden,p.nom_pac,t.tipo_examen,t.m_nuo,t.cen_dialisis,t.observacion 
	         from tbl_sis t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and t.cen_dialisis='$cen_dia' and t.convenio='$conv'
			order by t.nro_orden asc";
	  }else{
		 $sql="select t.id,t.nro_orden,p.nom_pac,t.observacion 
		    from tbl_salupol t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and t.cen_dialisis='$cen_dia' and t.convenio='$conv'
			order by t.nro_orden asc";
		 }
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConvXId($id){   
	  $ocado=new cado();
	  $sql="select t.*,p.nom_pac,p.nro_historia,dni,sexo,p.cen_dialisis,tipo_seguro,p.fec_nac 
	         from tbl_sis  t inner join paciente p on t.id_pac=p.id
	        where  t.id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConsolidadoXId($idpac,$anio){   
	  $ocado=new cado();
	  $sql="select t.*,p.nom_pac,p.nro_historia,dni,sexo,p.cen_dialisis,tipo_seguro
	   from tbl_sis  t inner join paciente p
	         on t.id_pac=p.id
	        where  p.id=$idpac and anio=$anio";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConsolidadoXId2($idpac,$anio,$mes){   
	  $ocado=new cado();
	  $sql="select m_hto,m_hb,m_ureapre,m_ureapost,m_urr,m_ktv,m_creatpre,m_elecna,m_eleck,m_eleccl,m_calcio,m_fosforo,m_nuo,
	      t_prot,t_fralbum,t_fosfatasa,t_tgo,t_tgp,s_creat24h,s_dep_creat,s_ferritina,s_hierro,s_transferrina,s_indice,s_pth,s_vih,
		  s_det_anthep,s_vdrl_rpr,s_hbs,s_detecc_hbs,s_detc_tothb,
		  (select eva_hierro from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes) hierro
	  ,(select eva_eritropoyetina from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes)eritropoyetina,
	   (select eva_b12 from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes) b12
	   from tbl_sis  t inner join paciente p
	         on t.id_pac=p.id
	        where  p.id=$idpac and anio=$anio and mes=$mes";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConsolidadoXId3($idpac,$anio,$mes){   
	  $ocado=new cado();
	  $sql="select hto,hg,ureapre,ureapost,urr,ktv,
	         (select eva_hierro from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes) hierro
	  ,(select eva_eritropoyetina from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes)eritropoyetina,
	   (select eva_b12 from medicacion m where p.id=m.id_pac and m.anio=$anio and m.mes=$mes) b12
       	     from tbl_salupol  t inner join paciente p on t.id_pac=p.id     
	        where  p.id=$idpac and anio=$anio and mes=$mes";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConsolidadoXIdSalupol($idpac,$anio){   
	  $ocado=new cado();
	  $sql="select t.*,p.nom_pac,p.nro_historia,dni,sexo,p.cen_dialisis,tipo_seguro from tbl_salupol  t inner join paciente p
	         on t.id_pac=p.id
	        where  p.id=$idpac and anio=$anio";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function DatosPac($idpac){   
	  $ocado=new cado();
	  $sql="select p.nom_pac,p.nro_historia,dni,sexo,p.cen_dialisis,tipo_seguro,fec_nac from  paciente p
	         where  p.id=$idpac ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ResMensual($anio,$mes,$cen_dia){   
	  //echo $mes;
	  $ocado=new cado();
	  $sql="select left(p.nom_pac,35),m_hto,m_hb,m_ureapre,m_ureapost,m_pesoseco,m_pesopre,m_pesopost,m_horashd,m_urr,m_ktv,m_creatpre,
	        m_elecna,m_eleck,m_eleccl,m_calcio,m_fosforo,m_nuo,observacion,fec_muestra,dni,p.fec_nac
	        from tbl_sis  t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and p.cen_dialisis='$cen_dia'
			order by nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ResTrimestral($anio,$mes,$cen_dia){   
	  $ocado=new cado();
	  $sql="select left(p.nom_pac,35),t_fralbum,t_fraalfa1,t_fraalfa2,t_frbeta,t_frgamma,t_prot,t_fosfatasa,t_tgo,t_tgp,observacion,
	  p.fec_nac
	        from tbl_sis  t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and p.cen_dialisis='$cen_dia' and tipo_examen in('T','S')
			order by nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ResSemestral($anio,$mes,$cen_dia){   
	  $ocado=new cado();
	  $sql="select left(p.nom_pac,30),s_creat24h,s_vih,s_vdrl_rpr,s_pth,s_hbs,s_detecc_hbs,s_detc_tothb,s_det_anthep,s_ferritina,
	        s_indice,s_hierro,s_transferrina,s_dep_creat,observacion,p.fec_nac
	        from tbl_sis  t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and p.cen_dialisis='$cen_dia' and tipo_examen ='S'
			order by nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ResSalupol($anio,$mes,$cen_dia){   
	  $ocado=new cado();
	  $sql="select left(p.nom_pac,30),'SALUPOL',hto,hg,ureapre,ureapost,pesopre,pesopost,hrashd,urr,ktv,observacion
	        from tbl_salupol  t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and p.cen_dialisis='$cen_dia'
			order by nro_orden asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteConvXIdAdicional($id){   
	  $ocado=new cado();
	  $sql="select t.id,p.nom_pac,p.nro_historia,dni,sexo,p.cen_dialisis,tipo_seguro,t.fec_muestra,t.tipo_examen,estatura,
	  cast(replace(vol_orina,',','') as decimal(10,2)) vol_orina,s_creat24h,
	       m_creatpre,m_pesoseco,m_pesopre
	         from tbl_sis  t inner join paciente p on t.id_pac=p.id
	        where  t.id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GenerarOrden($anio,$mes,$cen_dia,$conv){   
	  $ocado=new cado();
	  if($conv=="SIS"){
	    $sql="select max(nro_orden) from tbl_sis  where  anio=$anio and mes=$mes and convenio='SIS'";     
	  }else{$sql="select max(nro_orden) from tbl_salupol  where  anio=$anio and mes=$mes and cen_dialisis='$cen_dia' and convenio='SALUPOL'";     }
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function FinalizarConv($anio,$mes,$cen_dia,$conv){	
		  $ocado=new cado();
		  $sql="insert into cerrar_convenio(cen_dialisis,convenio,anio,mes) values('$cen_dia','$conv',$anio,$mes) ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ValidarButton($anio,$mes,$cen_dia,$conv){	
		  $ocado=new cado();
		  $sql="select count(*) from cerrar_convenio where anio=$anio and mes=$mes and cen_dialisis='$cen_dia' and convenio='$conv' "; 
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 
	function VerMes($num){
		 switch ($num){
			 case 0:
			   $mes='TODOS'; 
			   return $mes;
			   break;
			 case 1:
			   $mes='ENERO'; 
			   return $mes;
			   break;
			 case 2:
			   $mes='FEBRERO'; 
			   return $mes;
			   break;
			 case 3:
			   $mes='MARZO'; 
			   return $mes;
			   break;
			 case 4:
			   $mes='ABRIL'; 
			   return $mes;
			   break;
			 case 5:
			   $mes='MAYO'; 
			   return $mes;
			   break;
			 case 6:
			   $mes='JUNIO'; 
			   return $mes;
			   break;
			 case 7:
			   $mes='JULIO'; 
			   return $mes;
			   break;          
			 case 8:
			   $mes='AGOSTO'; 
			   return $mes;
			   break;
			 case 9:
			   $mes='SEPTIEMBRE'; 
			   return $mes;
			   break;
			 case 10:
			   $mes='OCTUBRE'; 
			   return $mes;
			   break;
			 case 11:
			   $mes='NOVIEMBRE'; 
			   return $mes;
			   break;
			 case 12:
			   $mes='DICIEMBRE'; 
			   return $mes;
			   break;           
			 }
		 
		 }
	function VerMesAbr($num){
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
	function EspacioX($num){
		 switch ($num){
			 case 1:
			   $mes=50; 
			   return $mes;
			   break;
			 case 2:
			   $mes=69; 
			   return $mes;
			   break;
			 case 3:
			   $mes=88; 
			   return $mes;
			   break;
			 case 4:
			   $mes=107; 
			   return $mes;
			   break;
			 case 5:
			   $mes=126; 
			   return $mes;
			   break;
			 case 6:
			   $mes=145; 
			   return $mes;
			   break;
			 case 7:
			   $mes=164; 
			   return $mes;
			   break;          
			 case 8:
			   $mes=183; 
			   return $mes;
			   break;
			 case 9:
			   $mes=202; 
			   return $mes;
			   break;
			 case 10:
			   $mes=221; 
			   return $mes;
			   break;
			 case 11:
			   $mes=240; 
			   return $mes;
			   break;
			 case 12:
			   $mes=258; 
			   return $mes;
			   break;           
			 }
		 
		 }
	function ReporteConsolidado($cen_dialisis,$conv){
		//echo $cen_dia,$conv;exit;
		$ocado=new cado();   
		$sql="select id,nom_pac  from paciente where cen_dialisis='$cen_dialisis' and tipo_seguro='$conv' and estado=0     
		       order by nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function ReporteConsolidadoExt($idsucu,$conv){
		//echo $cen_dia,$conv;exit;
		$ocado=new cado();   
		$sql="select id,nom_pac  from paciente where id_sucursal='$idsucu' and tipo_seguro='$conv' and estado=0     
		       order by nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function ProgramacionLista($idsucu,$conv,$anio){
		//echo $cen_dia,$conv;exit;
		$ocado=new cado();   
		$sql="select id,nom_pac,(select ene from he_cronograma c where c.id_paciente=p.id and anio=$anio )enero,
		      (select feb from he_cronograma c where c.id_paciente=p.id and anio=$anio )Febrero,
			  (select mar from he_cronograma c where c.id_paciente=p.id and anio=$anio )Marzo,
			  (select abr from he_cronograma c where c.id_paciente=p.id and anio=$anio )Abril,
			  (select may from he_cronograma c where c.id_paciente=p.id and anio=$anio )Mayo,
			  (select jun from he_cronograma c where c.id_paciente=p.id and anio=$anio )Junio,
			  (select jul from he_cronograma c where c.id_paciente=p.id and anio=$anio )Julio,
			  (select ago from he_cronograma c where c.id_paciente=p.id and anio=$anio )Agosto,
			  (select sep from he_cronograma c where c.id_paciente=p.id and anio=$anio )Septiembre,
			  (select oct from he_cronograma c where c.id_paciente=p.id and anio=$anio )Octubre,
			  (select nov from he_cronograma c where c.id_paciente=p.id and anio=$anio )Noviembre,
			  (select dic from he_cronograma c where c.id_paciente=p.id and anio=$anio )Diciembre,
			  (select count(*) from he_cronograma c where c.id_paciente=p.id and anio=$anio )cantidad
		       from paciente p where id_sucursal='$idsucu' and tipo_seguro='$conv' and estado=0     
		       order by nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function LisPacReceta($idsucu,$conv){
		$ocado=new cado();
		$sql="select id,nom_pac from paciente where id_sucursal='$idsucu' and tipo_seguro='$conv' and estado=0  
		       order by nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function LisPacFua($idsucu,$turno,$modulo,$fecha){
		//echo $fecha;exit;
		$nom_dia=$this->DiasSemana($fecha);
		$ocado=new cado();
		$sql="select * from 
		(select p.id,CASE DAYOFWEEK('$fecha') WHEN 1 THEN 'Domingo' WHEN 2 THEN 'Lunes' WHEN 3 THEN 'Martes' WHEN 4 THEN 'Miércoles'
    WHEN 5 THEN 'Jueves' WHEN 6 THEN 'Viernes' WHEN 7 THEN 'Sábado' end dia,
		p.nom_pac,p.tipo_seguro,t.turno,m.modulo,lunes,martes,miercoles,jueves,viernes,sabado,domingo ,
		(select count(*) from he_fua h where h.tipo=1 and h.fecha='$fecha' and p.id=h.id_paciente) can,0 fua,
		COALESCE((select count(*) from he_fua h where h.fecha='$fecha' and h.id_paciente=p.id),0) can2,
		(select generado from he_fua h where  h.fecha='$fecha' and h.id_paciente=p.id)generado,
		COALESCE((select h.id from he_fua h where h.fecha='$fecha' and p.id=h.id_paciente),0) fua_pro,fi.id ficha
		from paciente p inner join ut_turno t on p.id_turno=t.id
		                inner join he_modulo m on p.id_modulo=m.id
                        inner join he_ficha_atencion fi on p.id=fi.id_paciente
		      where p.id_sucursal='$idsucu' and p.estado=0 and p.id_turno=$turno and p.id_modulo=$modulo and $nom_dia=1
			      and (p.id not in (select id_paciente from he_fua h where h.tipo=1 and h.fecha='$fecha'))
		       
			   
			   union
			
			select p.id,CASE DAYOFWEEK('$fecha') WHEN 1 THEN 'Domingo' WHEN 2 THEN 'Lunes' WHEN 3 THEN 'Martes' WHEN 4 THEN 'Miércoles'
    WHEN 5 THEN 'Jueves' WHEN 6 THEN 'Viernes' WHEN 7 THEN 'Sábado' end dia,
		p.nom_pac,p.tipo_seguro,t.turno,m.modulo,lunes,martes,miercoles,jueves,viernes,sabado,domingo ,
		(select count(*) from he_fua h where h.tipo=1 and h.fecha_rep='$fecha' and p.id=h.id_paciente) can,fu.id fua,
		(select count(*) from he_fua h where h.fecha_rep='$fecha' and p.id=h.id_paciente) can2,
		fu.generado,fu.id fua_pro,fi.id ficha
		from paciente p inner join he_fua fu on p.id=fu.id_paciente
		                inner join ut_turno t on fu.id_turno=t.id
		                inner join he_modulo m on fu.id_modulo=m.id
                        inner join he_ficha_atencion fi on p.id=fi.id_paciente
		      where fu.id_sucursal='$idsucu' and p.estado=0 and fu.id_turno='$turno' and fu.id_modulo='$modulo' and fu.tipo=1 and fecha_rep='$fecha') as t
			  
			  order by t.nom_pac asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function DiasSemana($fec){
	    $fe= strtotime($fec);	
        switch (date('w', $fe)){ 
          case 0: $dia="domingo"; break; 
          case 1: $dia="lunes"; break; 
          case 2: $dia="martes"; break; 
          case 3: $dia="miercoles"; break; 
          case 4: $dia="jueves"; break; 
          case 5: $dia="viernes"; break; 
          case 6: $dia="sabado"; break; 
         } 
      return $dia; 
	 }
	function DiasSemanaAbrev($num){	
        switch ($num){ 
          case 0: $dia="DO"; break; 
          case 1: $dia="LU"; break; 
          case 2: $dia="MA"; break; 
          case 3: $dia="MI"; break; 
          case 4: $dia="JU"; break; 
          case 5: $dia="VI"; break; 
          case 6: $dia="SA"; break; 
         } 
      return $dia; 
	 }
	function ReporteTodos($cen_dia,$anio,$mes){
		$ocado=new cado();
		$sql="select id from tbl_sis where cen_dialisis='$cen_dia' and anio=$anio and mes=$mes  
		       order by paciente asc";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function Observacion($id,$obs,$conv){
		$ocado=new cado();
		if($conv=="SIS"){
	       $sql="update tbl_sis set observacion='$obs' where id = $id";
	  }else{
		   $sql="update tbl_salupol set observacion='$obs' where id = $id";
		 }
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	 function calcular_edad($fecha){
    $dias = explode("/", $fecha, 3);
    $dias = mktime(0,0,0,$dias[1],$dias[0],$dias[2]);
    $edad = (int)((time()-$dias)/31556926 );
    return $edad;
	}
	function Llenado($emp,$anio,$mes){   
	  $ocado=new cado();
	  $sql="select t.id,p.nom_pac,t.tipo_examen,t.m_hb,t.eva_hierro,t.eva_eritropoyetina,t.eva_b12 
	         from tbl_sis t inner join paciente p on t.id_pac=p.id
	        where  t.anio=$anio and t.mes=$mes and t.cen_dialisis='$emp'
			order by p.nom_pac asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function Act($id,$anio,$mes,$hierro,$conv,$cantidad,$emp){
	  if($cantidad==0){
	     $sql="insert into medicacion(eva_hierro,id_pac,mes,anio,convenio,empresa)values('$hierro',$id,$mes,$anio,'$conv','$emp')";
	   }else{$sql="update medicacion set eva_hierro='$hierro' where id_pac=$id and anio=$anio and mes=$mes and convenio='$conv'";}
	  $ocado=new cado();
	  
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;	
	}
	function ActEpo($id,$anio,$mes,$epo,$conv,$cantidad,$emp){
		echo $cantidad;exit;
	  if($cantidad==0){
	     $sql="insert into medicacion(eva_eritropoyetina,id_pac,mes,anio,convenio,empresa)values('$epo',$id,$mes,$anio,'$conv','$emp')";
	   }else{$sql="update medicacion set eva_eritropoyetina='$epo' where id_pac=$id and anio=$anio and mes=$mes and convenio='$conv'";}
	  $ocado=new cado();
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;	
	}
	function ActB12($id,$anio,$mes,$b12,$conv,$cantidad,$emp){
	   if($cantidad==0){
	     $sql="insert into medicacion(eva_b12,id_pac,mes,anio,convenio,empresa)values('$b12',$id,$mes,$anio,'$conv','$emp')";
	   }else{$sql="update medicacion set eva_b12='$b12' where id_pac=$id and anio=$anio and mes=$mes and convenio='$conv'";}
	  $ocado=new cado();
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function Contar($id,$anio,$mes,$conv){
	  $ocado=new cado();
	  $sql="select count(*) from medicacion where id_pac=$id and anio=$anio and mes=$mes and convenio='$conv'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValidarAnular2($orden,$credito){
		  if($credito==0){
		   $sql="select r.id,case when tipo_doc in('TB','TF') then  concat(tipo_doc,' - ',nro_serie,nro_documento) 
		        else concat(nro_serie,nro_documento) end doc 
		        from receta r inner join caja_fondos_detalle c on 
		        r.nro_orden=c.nro_orden
		      where r.nro_orden='$orden' and r.doc_emitido=1 and r.anulado=0";
		   }
		   if($credito==1){
		   $sql="select id from receta where nro_orden='$orden' and doc_emitido=1 and anulado=0";
		         
		   }
		  $ocado=new cado();
		  
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ValidarAnular($orden){
		  $ocado=new cado();
		  $sql="select id from receta r where nro_orden='$orden' and r.doc_emitido=1 and r.anulado=0";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function VerDatosReceta($orden){
		  $ocado=new cado();
		  $sql="select id,id_paciente,examen from receta where nro_orden='$orden'";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function EliResulExt($id){
		$ocado=new cado();
		$sql="update resultado set resultado='' where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	 function RegistrarResultadoExt($id,$ruta){
		$ocado=new cado();
		$sql="update resultado set resultado='$ruta' where id=$id";
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function BuscarUrl($id){
		$ocado=new cado();
		$sql="select archivo_resultado from examen  where id =$id and especial=1 ";           
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function LisConvenios(){
		$ocado=new cado();
		$sql="select id from convenio  where estado=0 and tarifario='S' ";           
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	 }
	function ActPreciosPrueba($idexa,$idconv,$precio){
		$ocado=new cado();
		$sql="insert into examen_precio (id_examen,id_convenio,precio) value('$idexa','$idconv','$precio') ";           
		$ejecutar=$ocado->ejecutar($sql);
		return $ejecutar;
	}
	function RepitePrueba($idexa,$idconvenio){
		  $ocado=new cado();
		  $sql="select count(*) from examen_precio where id_examen=$idexa and id_convenio=$idconvenio ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
    function ListarExamenPrueba($idconvenio){
		  $ocado=new cado(); 
		  $sql="select id,COALESCE((select e.unidad*factor from convenio where id=$idconvenio),0)pre
				 from examen e where estado=0 "; 
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function MigrandoDatos(){
		  $ocado=new cado(); 
		  $sql="SELECT r.id,r.nro_orden,r.examen_precio,c.tipo FROM receta r inner join convenio c on r.id_convenio=c.id 
		         where fec_crea>'2019-08-01'
		        order by r.id asc ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function IngresarMigracion($receta_id,$examen_id,$precio,$tipo){
		  if($tipo=='P'){$precio_part=$precio;$precio_conv=0.00;$precio_part_desc=0.00;$pago_paciente=$precio;$pago_conv=0.00;}
		  if($tipo=='C'){$precio_part=0.00;$precio_conv=$precio;$precio_part_desc=0.00;$pago_paciente=0.00;$pago_conv=$precio;}
		  if($tipo=='PD'){$precio_part=0.00;$precio_conv=0.00;$precio_part_desc=$precio;$pago_paciente=$precio;$pago_conv=0.00;}
		  $subtotal_detalle=$pago_paciente+$pago_conv;
		  $ocado=new cado(); 
		  $sql="INSERT INTO `receta_detalle`(`id_receta`, `id_examen`, `precio_part`, `precio_conv`, `precio_part_desc`, `cantidad`, `pago_paciente`, `pago_convenio`, `subtotal`) 
				 VALUES ('$receta_id','$examen_id','$precio_part','$precio_conv','$precio_part_desc',1,'$pago_paciente','$pago_conv','$subtotal_detalle') ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function GuardarInforme($id,$titulo,$contenido){
		  $ocado=new cado();
		  $sql="update resultado_informe set titulo = '$titulo',contenido='$contenido' where id = $id";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ResultadoInforme($idreceta,$idexamen){
		  $ocado=new cado();
		  $sql="select r.id,e.nombre,e.id,r.titulo,r.contenido from resultado_informe r inner join examen e on r.id_examen=e.id where r.id_receta= $idreceta and r.id_examen=$idexamen";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function FinDetalle($idreceta,$idexamen){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="update receta_detalle set estado=1 where id_receta=$idreceta and id_examen=$idexamen";
		  $cn->prepare($sql)->execute();
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
	 
	 function ListarCanCar($idexamen){
		  $ocado=new cado();
		  $sql="select count(*) from caracteristica where id_examen=$idexamen ";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	 function ValDoc($serie,$corre){
	  $ocado=new cado();
	  $sql="select count(*) from doc_electronicos where serie='$serie' and correlativo=$corre and estado=0"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDomicilios($ini){
	  $ocado=new cado();
	  $sql="select r.id,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,p.dni,telefono,direccion,nro_orden,total,m.nombre,r.nro_orden,
	  (select count(*) from receta_detalle d inner join examen e on d.id_examen=e.id where d.id_receta=r.id and e.condiciones='AYUNAS')can,
	  r.estado_dom,r.estado,r.obs_dom,r.fecha_entrega_dom,tipo_pago_dom,lugar_dom,tipoentregadom,turno
	   from receta r inner join paciente p on r.id_paciente=p.id 
	                 inner join medico m on r.id_medico=m.id
	   where domicilio=1 and anulado=0 and estado_dom=0 and date(fec_domicilio)=date('$ini')
	   order by turno asc, r.fec_crea asc"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function DetalleDomicilio($idreceta){
	  $ocado=new cado();
	  $sql="select e.nombre,e.id,toma_muestra,d.id,d.id_receta 
	        from receta_detalle d inner join examen e on d.id_examen=e.id where d.id_receta=$idreceta";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function InsertarResultadoDom($orden,$obs,$fec,$tipopago,$idreceta,$te){
		  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_listar="select id,id_paciente,examen from receta where nro_orden='$orden'";
		  //die($sql_listar);
		  $cmd=$cn->prepare($sql_listar);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $idreceta=$col[0];$examen=$col[2];$idpac=$col[1];	  
		  $sql=" insert into resultado(id_receta,id_examen,examen,id_caracteristica,caracteristica,titulo,resultado,unidad,rango,metodo)  
		         select t.id_receta,t.id_exa,t.nombre,c.id,c.nombre,c.titulo,c.resultado,c.unidad,c.referencia,c.metodo
                 from (
                  select d.id_receta,case when e.paquete=1 then p.id_exa_detalle else e.id end id_exa, e.nombre,e.id_grupo
                   from receta_detalle d inner join examen e on d.id_examen=e.id
                   left join examen_paquete p on e.id=p.id_examen
                   where id_receta=$idreceta
                  ) as t  left join caracteristica c on t.id_exa=c.id_examen
                   inner join grupo g on t.id_grupo=g.id
                   order by g.nro_orden asc,t.id_exa asc,c.orden asc";
		  
		  $cn->prepare($sql)->execute();
		  $sql_act="update receta set estado=1,tipo_pago_dom='$tipopago',fecha_entrega_dom='$fec',obs_dom='$obs', estado_dom=1,
		  fec_toma_muestra=now() , tipoentregadom='$te'
		  where id=$idreceta;";
		  $cn->prepare($sql_act)->execute();
		  
		  /*for($i=0;$i<count($detalle);$i++){
			  $idexamen=$detalle[0];*/
		  $sql_act="update receta_detalle set toma_muestra=1 where id_receta=$idreceta;";
		  $cn->prepare($sql_act)->execute(); 
		  //}
		  
		  
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
	
	function FinalizarToma($idreceta,$obs,$fec,$tipopago){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="update receta set estado_dom=1,tipo_pago_dom='$tipopago',fecha_entrega_dom='$fec',obs_dom='$obs' where id=$idreceta";
		  $cn->prepare($sql)->execute();
		  $sql="update receta_detalle set toma_muestra=1 where id_receta=$idreceta";
		  $cn->prepare($sql)->execute();
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
	function EliminarDom($id,$idreceta){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();
		  $sql="delete from  receta_detalle where id=$id";
		  $cn->prepare($sql)->execute();
		 $sql_total="select sum(subtotal)tot,(select porcentaje_med from receta where id=$idreceta)por from receta_detalle where id_receta=$idreceta";
		  $cmd=$cn->prepare($sql_total);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $total=$col['tot'];$porcentaje=$col[1];$monto_med=($total*$porcentaje)/100;
		  $sql_up="update receta set subtotal=$total,total=$total,monto_medico=$monto_med where id=$idreceta";
		  $cn->prepare($sql_up)->execute();
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
	
	function DetalleMuestra($id){
	  $ocado=new cado();
	  $sql="select r.id,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,p.dni,telefono,direccion,nro_orden,total,m.nombre,r.nro_orden,r.estado_dom,
	  r.estado,r.obs_dom
	   from receta r inner join paciente p on r.id_paciente=p.id 
	                 inner join medico m on r.id_medico=m.id
	   where r.id=$id"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	
	 
   }
?>