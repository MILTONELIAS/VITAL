<?php
   require_once('conexion.php');
   
   class Cajas{   
    
    function ListarLogo(){
      $ocado=new cado();
      $sql="select * from sucursal";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
    function ListarModulos($idgrupo){
            $ocado = new cado();
            $sql = "select m.id,m.nombre,p.id_grupo,m.url_imagen,m.orden from conf_permisos p inner join conf_menu m on p.id_menu=m.id where p.id_grupo=$idgrupo
                    group by m.id,m.nombre,p.id_grupo,m.url_imagen,m.orden order by m.orden asc";
            $ejecutar=$ocado->ejecutar($sql);
            return $ejecutar;
        }
    function DetalleMenu($idmenu,$idgrupo){
            $ocado = new cado();
            $sql = "select d.id,d.nombre,p.id_grupo,d.url_imagen,d.url_link from conf_permisos p 
			                                       inner join conf_menu_detalle d on p.id_menu_detalle=d.id 
                     where p.id_grupo=$idgrupo and p.id_menu=$idmenu  order by d.id asc";
            $ejecutar=$ocado->ejecutar($sql);
            return $ejecutar;
    }
	function Listar($nombre){
	  $ocado=new cado();
	  $sql="select * from caja  where nom_caja like '%$nombre%' order by id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarNoActivas(){
	  $ocado=new cado();
	  $sql="select id,nom_caja from caja c inner join caja_series cs on c.id=cs.id_caja where activa=0 and estado=0
	         group by id,nom_caja order by id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarNoActivasCajaChica(){
	  $ocado=new cado();
	  $sql="select id,nom_caja from caja where activa=0 and estado=0 and tipo=1 order by id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarDoc($nombre){
	  $ocado=new cado();
	  $sql="select * from serie order by serie asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValSer($doc, $id){
	  $ocado=new cado();
	  $sql="select count(*)	from serie where id=$id and serie='$doc'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function Registrar($familia,$est,$user,$tipo){
		  $ocado=new cado();
          $sql="insert into caja(nom_caja,fec_crea,user_crea,estado,activa,tipo) values('$familia',getdate(),'$user','$est',0,$tipo)";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 } 
	function Modificar($id,$familia,$est){
		  $ocado=new cado();
		  $sql="update caja set nom_caja='$familia',estado=$est where id=$id";
		        
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	 }
	function ValCaja(){
	  $ocado=new cado();
	  $sql="select count(*) from caja where estado=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCaja(){
	  $ocado=new cado();
	  $sql="select * from caja where estado=0 order by id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	/*function LisDocumento($id){
	  $ocado=new cado();
	  $sql="select f.* from serie f inner join ut_sucursal u on f.id=u.id_documento where u.id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}*/
	function ActDoc($id){
		  $ocado=new cado();
		  $sql="update serie set correlativo=0  where id = $id";    
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function AsignarSeriesCaja($idcaja,$idserie){
	  $ocado=new cado();
	  $sql="insert into caja_series(id_caja,id_serie) values($idcaja,$idserie)";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function SeriesXCaja($idcaja){
	  $ocado=new cado();
	  $sql="select c.id_caja,c.id_serie,case s.tipo_doc when 'BV' then 'BOLETA DE VENTA' when 'FA' then 'FACTURA' when 'NC' then 'NOTA DE CREDITO' when 'TK' then 'NOTA DE VENTA' END,CASE WHEN empresa='P' then 'VITAL DIAGNOSTIC' ELSE '' END emp
	   ,s.serie,REPLICATE('0',8-LEN(s.correlativo))+CAST(s.correlativo AS VARCHAR) cor
	  from caja_series c inner join serie s on c.id_serie=s.id where c.id_caja=$idcaja";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function AperturarCaja($idcaja,$nom_caja,$codigo,$fondo,$user){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_user="select id from usuario where userr='$user';";
		  $cmd=$cn->prepare($sql_user);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $id_user=$col[0];
		  $sql_apertura="insert into caja_fondos (codigo_ingreso,fec_ingreso,id_caja,id_user,nom_caja,nom_user,fondo_inicial,fondo_inicial_in,monto_efectivo,monto_tarjeta,total_egresos,total_ingresos,total_cierre_caja,fec_cierra,activo,billete_200,billete_100,billete_50,billete_20,billete_10,moneda_5,moneda_2,moneda_1,moneda_050,moneda_020,moneda_010)		                    values('$codigo',getdate(),$idcaja,$id_user,'$nom_caja','$user',$fondo,0,0,0,0,0,0,getdate(),1,0,0,0,0,0,0,0,0,0,0,0)";
		  //die($sql_apertura);exit;
		  $cn->prepare($sql_apertura)->execute();
		  $sql_caja="update caja set activa=1 where id=$idcaja";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update usuario set user_activo=1 where id=$id_user";
		  $cn->prepare($sql_usuario)->execute();
		  $_SESSION['S_cod_ingreso']=$codigo;
		  $_SESSION['S_idcaja']=$idcaja;
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
	function AperturarCajaChica($idcaja,$nom_caja,$codigo,$fondo,$user,$fondoIn){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_user="select id from usuario where userr='$user'";
		  $cmd=$cn->prepare($sql_user);
		  $cmd->execute();
		  $col=$cmd->fetch();
		  $id_user=$col[0];
		  $sql_apertura="insert into caja_fondos (codigo_ingreso,fec_ingreso,id_caja,id_user,nom_caja,nom_user,fondo_inicial,fondo_inicial_in,monto_efectivo,monto_tarjeta,total_egresos,total_ingresos,total_cierre_caja,fec_cierra,activo,billete_200,billete_100,billete_50,billete_20,billete_10,moneda_5,moneda_2,moneda_1,moneda_050,moneda_020,moneda_010)	values('$codigo',getdate(),$idcaja,$id_user,'$nom_caja','$user',$fondo,$fondoIn,0,0,0,0,0,getdate(),1,0,0,0,0,0,0,0,0,0,0,0)";
		  $cn->prepare($sql_apertura)->execute();
		  $sql_caja="update caja set activa=1 where id=$idcaja;";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update usuario set user_activo=1 where id=$id_user;";
		  $cn->prepare($sql_usuario)->execute();
		  $_SESSION['S_cod_ingreso']=$codigo;
		  $_SESSION['S_idcaja']=$idcaja;
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
	function CerrarCaja($idcaja,$cod_ingreso,$iduser,$efectivo,$tarjeta,$egresos,$total,$bi200,$bi100,$bi50,$bi20,$bi10,$mo5,$mo2,$mo1,$mo050,$mo020,$mo010){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_cierre="update caja_fondos set activo=0,monto_efectivo=$efectivo,monto_tarjeta=$tarjeta,total_egresos=$egresos,total_cierre_caja=$total,
		               fec_cierra=getdate(),billete_200=$bi200,billete_100=$bi100,billete_50=$bi50,billete_20=$bi20,billete_10=$bi10,    
					   moneda_5=$mo5,moneda_2=$mo2,moneda_1=$mo1,moneda_050=$mo050,moneda_020=$mo020,moneda_010=$mo010
		               where codigo_ingreso='$cod_ingreso';";
		  //die($sql_cierre);
		  $cn->prepare($sql_cierre)->execute();
		  $sql_caja="update caja set activa=0 where id=$idcaja;";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update usuario set user_activo=0 where id=$iduser;";
		  $cn->prepare($sql_usuario)->execute();
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
	function CerrarCajaChica($idcaja,$cod_ingreso,$iduser,$egresos,$ingresos,$total){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_cierre="update caja_fondos set activo=0,total_egresos='$egresos',total_cierre_caja='$total',
		               fec_cierra=getdate(),total_ingresos='$ingresos'
		               where codigo_ingreso='$cod_ingreso';";
		  //die($sql_cierre);
		  $cn->prepare($sql_cierre)->execute();
		  $sql_caja="update caja set activa=0 where id=$idcaja;";
		  $cn->prepare($sql_caja)->execute();
		  $sql_usuario="update usuario set user_activo=0 where id=$iduser;";
		  $cn->prepare($sql_usuario)->execute();
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
	function ValidarUserActivo($user){
	  $ocado=new cado();
	  $sql="select count(*) from usuario where userr='$user' and user_activo=1";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCajaFondo($cod_ingreso){
	  $ocado=new cado();
	  $sql="select c.codigo_ingreso,fondo_inicial,sum(isnull(d.monto_efectivo,0)) efectivo,sum(isnull(d.monto_tarjeta,0)) tarjeta, 
	        sum(isnull(d.monto_egreso,0)) egreso
	        from caja_fondos c left join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			where c.codigo_ingreso='$cod_ingreso' and d.estado=0
			group by c.codigo_ingreso,fondo_inicial";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarFondoCajaChica($cod_ingreso){
	  $ocado=new cado();
	  $sql="select codigo_ingreso,fondo_inicial from caja_fondos where codigo_ingreso='$cod_ingreso' ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCajaFondoIngreso($cod_ingreso){
	  $ocado=new cado();
	  $sql="select sum(isnull(d.monto_egreso,0))ingreso
	        from caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			where c.codigo_ingreso='$cod_ingreso' and d.estado=0 and tipo_doc='IG'
			group by c.codigo_ingreso";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCajaFondoEgreso($cod_ingreso){
	  $ocado=new cado();
	  $sql="select sum(isnull(d.monto_egreso,0))egreso
	        from caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			where c.codigo_ingreso='$cod_ingreso' and d.estado=0 and tipo_doc='EG'
			group by c.codigo_ingreso";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	
function ListarDetalleCaja($cod_ingreso){
	  $ocado=new cado();
	  $sql="select d.nro_orden,movimiento,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,tipo_doc,(nro_serie+nro_documento)doc,d.tipo_pago,
	        monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,
			case r.estado when 0 then 'PENDIENTE' when 1 then 'GENERADO' when 2 then 'FINALIZADO' END est,
			case r.doc_emitido when 0 then 'PENDIENTE' WHEN 1 THEN 'EMITIDO' end doc_emi,monto_deposito+monto_yape monto_deposito 
	         from  caja_fondos_detalle d inner join receta r on d.nro_orden=r.nro_orden
	                                     inner join paciente p on r.id_paciente=p.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 
		union all
		select '',movimiento, left(m.nombre,32) as pac,tipo_doc,nro_documento ,d.tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi,monto_deposito+monto_yape monto_deposito  
	         from  caja_fondos_detalle d inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 
		union all
		select '',movimiento, persona_egreso as per,tipo_doc,nro_documento ,tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi,monto_deposito+monto_yape  monto_deposito
	   from  caja_fondos_detalle where codigo_ingreso='$cod_ingreso' and estado=0 and left(nro_documento,2) in('EG','IG') ";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarDetalleCajaInnova($cod_ingreso){
	  $ocado=new cado();
	  $sql="select d.nro_orden,movimiento,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,tipo_doc,concat(nro_serie,nro_documento)doc,d.tipo_pago,
	        monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa
	         from  caja_fondos_detalle d inner join receta r on d.nro_orden=r.nro_orden
	                                     inner join paciente p on r.id_paciente=p.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='I'
		union all
		select '',movimiento, left(m.nombre,32) as pac,tipo_doc,nro_documento ,d.tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa
	         from  caja_fondos_detalle d inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='I'
		union all
		select '',movimiento, persona_egreso as per,tipo_doc,nro_documento ,tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa
	 from  caja_fondos_detalle   where codigo_ingreso='$cod_ingreso' and estado=0 and left(nro_documento,2) in('EG','IG') and empresa='I'";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarDetalleCajaChica($cod_ingreso){
	  $ocado=new cado();
	  $sql="select  case when empresa='P' then 'VITAL' ELSE '' END emp,movimiento, left(m.nombre,32) as pac,
	  tipo_doc,docref_egre ,tipodocref_egre, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi,
	  m.dni,descripcion,format(d.fec_emision,'yyyy/MM/dd')fecha
	         from  caja_fondos_detalle d inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 
		union all
		select  case when empresa='P' then 'VITAL' ELSE 'INNOVA' END emp,movimiento, persona_egreso as per,tipo_doc,
		docref_egre ,tipodocref_egre, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi,dni_egre_ingre,
		descripcion,format(fec_emision,'yyyy/MM/dd')fecha
	from  caja_fondos_detalle   where  codigo_ingreso='$cod_ingreso' and estado=0 and left(nro_documento,2) in('EG','IG')";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarDetalleCajaSemanal($cod_ingreso){
	  $ocado=new cado();
	  $sql="select d.nro_orden,movimiento,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,tipo_doc,concat(nro_serie,nro_documento)doc,
	  case d.tipo_pago when 'E' then '1' when 'T' then 2 when 'M' then 3 else 4 end pago,
	        d.monto_efectivo,d.monto_tarjeta,d.monto_egreso,d.total_venta,d.empresa,
			case r.estado when 0 then 'PENDIENTE' when 1 then 'GENERADO' when 2 then 'FINALIZADO' END est,
			case r.doc_emitido when 0 then 'PENDIENTE' WHEN 1 THEN 'EMITIDO' end doc_emi,
			case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,
			FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha,(visa+' '+mastercard+' '+otros+' '+isnull(nrodeposito,''))ref
	         from  caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			        inner join receta r on d.nro_orden=r.nro_orden
	                                     inner join paciente p on r.id_paciente=p.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='P'
		union all
		select '',movimiento, left(m.nombre,32) as pac,tipo_doc,nro_documento ,
		case d.tipo_pago when 'E' then '1' when 'T' then 2 when 'M' then 3 end pago,
		 d.monto_efectivo,d.monto_tarjeta,d.monto_egreso,d.monto_egreso,d.empresa,'GENERADO' est,''doc_emi,
		case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,
		FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha,'' ref
	         from caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso 
			 inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='P'
		union all
		select '',movimiento, persona_egreso as per,tipo_doc,nro_documento ,
		case d.tipo_pago when 'E' then '1' when 'T' then 2 when 'M' then 3 end pago,
		 d.monto_efectivo,d.monto_tarjeta,d.monto_egreso,d.monto_egreso,d.empresa,'GENERADO' est,''doc_emi,
		case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,
		FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha ,'' ref 
	         from  caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			  where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and left(d.nro_documento,2)='EG' and d.empresa='P'";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarDetalleCajaInnovaSemanal($cod_ingreso){
	  $ocado=new cado();
	  $sql="select nro_serie,nro_documento,FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha ,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,exa.nombre,r.user_finaliza,det.subtotal,
	 case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,d.empresa
	 
	         from  caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			    inner join receta r on d.nro_orden=r.nro_orden
				inner join receta_detalle det on r.id=det.id_receta
				inner join examen exa on det.id_examen=exa.id
	                                     inner join paciente p on r.id_paciente=p.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='I'
		union all
		select  '' serie,nro_documento ,FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha,
		left(m.nombre,32) as pac,movimiento,'' nom,d.monto_egreso,
		case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,
		d.empresa
	         from  caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso
			       inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and empresa='I'
		union all
		select '' serie,nro_documento , FORMAT(c.fec_ingreso,'dd/MM/yyyy')fecha,persona_egreso as per,movimiento,
		'' nom,d.monto_egreso,
		case when FORMAT(c.fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno,
		d.empresa
	         from caja_fondos c inner join caja_fondos_detalle d on c.codigo_ingreso=d.codigo_ingreso  
			   where d.codigo_ingreso='$cod_ingreso' and d.estado=0 and left(nro_documento,2)='EG' and empresa='I'";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function LisDetaCajaDia($cod_ingreso){
	  $ocado=new cado();
	  $sql="select * from (select r.nro_orden,CASE WHEN credito=1 and ingreso_dinero=0 then 'CREDITO' ELSE movimiento END movimiento,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,tipo_doc,concat(nro_serie,nro_documento)doc,d.tipo_pago,
	        monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,
		case when r.anulado=1 then 'ANULADO' ELSE case r.estado when 0 then 'PENDIENTE' when 1 then 'PROCESOS' when 2 then 'FINALIZADO' END END est,
			case when r.doc_emitido=0   and r.credito=0 then 'PENDIENTE' else 'EMITIDO' end doc_emi 
	         from  receta r left join caja_fondos_detalle d on r.codigo_ingreso=d.codigo_ingreso and r.nro_orden=d.nro_orden
	                                     inner join paciente p on r.id_paciente=p.id
			where r.codigo_ingreso='$cod_ingreso' and emp='P'
		union all
		select '',movimiento, left(m.nombre,32) as pac,tipo_doc,nro_documento ,d.tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi
	         from  caja_fondos_detalle d inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and empresa='P'
		union all
		select '',movimiento, persona_egreso as per,tipo_doc,nro_documento ,tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,case when estado=0 then 'EMITIDO' ELSE 'ANULADO' END doc_emi
	       from  caja_fondos_detalle   where codigo_ingreso='$cod_ingreso' and left(nro_documento,2)in('EG','IG') and empresa='P') as t order by nro_orden desc";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function LisDetaCajaDiaInnova($cod_ingreso){
	  $ocado=new cado();
	  $sql="select * from (select r.nro_orden,movimiento,
	  left(ape_pat+' '+ape_mat+' '+preNombres,32)pac,tipo_doc,concat(nro_serie,nro_documento)doc,d.tipo_pago,
	        monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,
		case when r.anulado=1 then 'ANULADO' ELSE case r.estado when 0 then 'PENDIENTE' when 1 then 'PROCESOS' when 2 then 'FINALIZADO' END END est,
			case when r.doc_emitido=0   and r.credito=0 then 'PENDIENTE' else 'EMITIDO' end doc_emi 
	         from  receta r left join caja_fondos_detalle d on r.codigo_ingreso=d.codigo_ingreso and r.nro_orden=d.nro_orden
	                                     inner join paciente p on r.id_paciente=p.id
			where r.codigo_ingreso='$cod_ingreso' and emp='I'
		union all
		select '',movimiento, left(m.nombre,32) as pac,tipo_doc,nro_documento ,d.tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,''doc_emi
	         from  caja_fondos_detalle d inner join ticket_pago t on d.nro_orden=t.id
	                                     inner join medico m on t.id_medico=m.id
			where d.codigo_ingreso='$cod_ingreso' and empresa='I'
		union all
		select '',movimiento, persona_egreso as per,tipo_doc,nro_documento ,tipo_pago, monto_efectivo,monto_tarjeta,monto_egreso,total_venta,empresa,'GENERADO' est,case when estado=0 then 'EMITIDO' ELSE 'ANULADO' END doc_emi
	       from  caja_fondos_detalle   where codigo_ingreso='$cod_ingreso'  and left(nro_documento,2) in('EG','IG') and empresa='I') as t order by nro_orden desc";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	
	function ListarDinero($cod_ingreso){
	  $ocado=new cado();
	  $sql="select *,case when FORMAT(fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'MAÑANA' ELSE 'TARDE' end turno 
	         from  caja_fondos 
			where codigo_ingreso='$cod_ingreso'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function LisCajaFondoDatos($cod_ingreso){
	  $ocado=new cado();
	  $sql="select nom_caja,nom_user from caja_fondos
			where codigo_ingreso='$cod_ingreso'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function DocElectronico($iddoc){
	  $ocado=new cado();
	  $sql="select d.serie,d.correlativo,d.fecha_emision,d.hora_emision, case when d.tipo_documento in('03','TB','TF') then nomcli 
	        else (select (ape_pat+' '+ape_mat+' '+preNombres) from paciente p where p.id=d.id_cliente ) end paciente,
			case when tipo_documento='01' then nomcli else '' end razon,case when tipo_documento='01' then dircliente else '' end direccion,
			valor_venta,total_igv,importe_total,tipo_documento,monto_pagado,vuelto,tipo_pago,doc_cliente,tipodoc_cli,dni,valor
	        ,forma_pago,fecha_pago,aleatorio
			from doc_electronicos d inner join caja_fondos_detalle c on concat(d.serie,d.correlativo)=concat(c.nro_serie,c.nro_documento)
			inner join paciente p on d.id_cliente=p.id 
			where d.id=$iddoc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function DocDetalleElectronico($iddoc){
	  $ocado=new cado();
	  $sql="select descripcion_item,preuni  from doc_electronico_items where doc_electronico_id=$iddoc";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function RegistrarEgreso($cod_ingreso,$monto_egreso,$descripcion,$empresa,$persona,$dni,$tipo,$docref){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  
		  $sql_serie="select correlativo+1 from serie where tipo_doc='EG' ";
		  $cmd=$cn->prepare($sql_serie);
		  $cmd->execute();
		  $data=$cmd->fetch();
		  $corre=$data[0];
		  $correlativo='EG-'.$corre;
		  $sql="insert into caja_fondos_detalle (codigo_ingreso,tipo_doc,nro_documento,tipo_pago,fec_emision,hora_emision,estado,monto_egreso,movimiento,descripcion,empresa,persona_egreso,dni_egre_ingre,tipodocref_egre,docref_egre)
		                                  values('$cod_ingreso','EG','$correlativo','E',getdate(),getdate(),0,'$monto_egreso','EGRESO','$descripcion','$empresa','$persona','$dni','$tipo','$docref')";
		  $cn->prepare($sql)->execute();
		  
		  $sql_caja="update serie set correlativo=$corre where tipo_doc='EG';";
		  $cn->prepare($sql_caja)->execute();
		  
		  $cn->commit();
		  $cn=null;
	      $return=1;
	 
	  }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
        }
		  return $return;
	}
	function ListarEgresos($cod_ingreso){
	  $ocado=new cado();
	  $sql="select d.id,d.nro_documento,case when  isnull(t.id, '0')='0' then empresa else emp end empresa,
	  FORMAT(d.fec_emision,'dd/MM/yyyy')fecha,d.descripcion,d.movimiento,d.dni_egre_ingre,d.persona_egreso,d.monto_egreso,d.estado,
	  d.tipodocref_egre,d.docref_egre
	         from caja_fondos_detalle d left join ticket_pago t on d.nro_orden=t.id
		    where d.codigo_ingreso='$cod_ingreso' and d.tipo_doc='EG'  order by d.id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function AnularEgresos($id){
	  $ocado=new cado();
	  $sql="update caja_fondos_detalle set estado=1 where id=$id";
	       
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function CabeceraCierreCaja($inicio,$fin){
	  $ocado=new cado();
	  $sql=" SELECT c.codigo_ingreso,u.nombre,case when FORMAT(fec_ingreso, 'hh:mm tt') BETWEEN '06:00 AM' and '12:00 AM' then 'M' ELSE 'T' end turno,
	  FORMAT(fec_ingreso, 'dd/MM/yyyy'),monto_efectivo,monto_tarjeta,total_egresos,total_cierre_caja
              FROM caja_fondos c inner join usuario u on c.id_user=u.id                 
             where (cast(fec_ingreso as date)>=cast('$inicio' as date) and cast(fec_ingreso as date)<=cast('$fin' as date)) ";
 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function DetalleCierreCaja($codigo,$emp){
	  $ocado=new cado();
	  $sql=" SELECT sum(monto_efectivo),sum(monto_tarjeta),sum(monto_egreso) from caja_fondos_detalle                
             where codigo_ingreso='$codigo' and empresa='$emp' and estado=0 ";
 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ValOrdPendientes($cod_ingreso){
	  $ocado=new cado();
	  $sql="select count(*) from receta where codigo_ingreso='$cod_ingreso' and estado=0 and anulado=0 and domicilio=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValOrdSinDoc($cod_ingreso){
	  $ocado=new cado();
	  $sql="select count(*) from receta where codigo_ingreso='$cod_ingreso' and doc_emitido=0 and credito=0 and anulado=0 and domicilio=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function IngresosCajaDia($cod_ingreso){
	  $ocado=new cado();
	  $sql="select 
(SELECT sum(monto_efectivo) FROM caja_fondos_detalle
WHERE codigo_ingreso='$cod_ingreso' and tipo_doc in('BV','FA') and empresa='P' and estado=0) efectivo_p,
(SELECT sum(monto_efectivo) FROM caja_fondos_detalle
WHERE codigo_ingreso='$cod_ingreso' and tipo_doc in('TB','TF','BV','FA') and empresa='I' and estado=0)efectivo_i,
isnull((SELECT sum(total_venta) FROM caja_fondos_detalle
WHERE codigo_ingreso='$cod_ingreso' and movimiento='PAGO CREDITO' and estado=0),0)pago_credito";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function GastosCajaDia($cod_ingreso){
	  $ocado=new cado();
	  $sql="SELECT movimiento,sum(monto_egreso) monto
             FROM caja_fondos_detalle WHERE codigo_ingreso='$cod_ingreso' and estado=0 and tipo_doc='EG'
            group by movimiento";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarIngresos($cod_ingreso){
	  $ocado=new cado();
	  $sql="select id,nro_documento,empresa ,FORMAT(fec_emision,'dd/MM/yyyy')fecha,descripcion,movimiento,dni_egre_ingre,persona_egreso
	  ,monto_egreso,estado,tipodocref_egre,docref_egre
	         from caja_fondos_detalle where codigo_ingreso='$cod_ingreso' and tipo_doc='IG'  order by id desc ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteIngresos($ini,$fin){
	  $ocado=new cado();
	  $sql="select id,nro_documento,empresa ,FORMAT(fec_emision,'dd/MM/yyyy')fecha,descripcion,movimiento,dni_egre_ingre,persona_egreso
	  ,monto_egreso,estado,tipo_doc,tipodocref_egre,docref_egre
	  from caja_fondos_detalle
			where ( cast(fec_emision as date)>=cast('$ini' as date) and cast(fec_emision as date)<=cast('$fin' as date)) and tipo_doc='IG' order by id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ReporteEgresos($ini,$fin){
	  $ocado=new cado();
	  $sql="select d.id,d.nro_documento,case when  isnull(t.id, '0')='0' then empresa else emp end empresa,
	  FORMAT(d.fec_emision,'dd/MM/yyyy')fecha,d.descripcion,d.movimiento,d.dni_egre_ingre,d.persona_egreso,d.monto_egreso,d.estado,tipo_doc
	  ,tipodocref_egre,docref_egre
	         from caja_fondos_detalle d left join ticket_pago t on d.nro_orden=t.id
			where ( cast(d.fec_emision as date)>=cast('$ini' as date) and cast(d.fec_emision as date)<=cast('$fin' as date)) and d.tipo_doc='EG' order by d.id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function RepCajaChicaXCod($cod){
	  $ocado=new cado();
	  $sql="select d.id,d.nro_documento,case when  isnull(t.id, '0')='0' then empresa else emp end empresa,
	  FORMAT(d.fec_emision,'dd/MM/yyyy')fecha,d.descripcion,d.movimiento,d.dni_egre_ingre,d.persona_egreso,d.monto_egreso,d.estado,tipo_doc,
	  tipodocref_egre,docref_egre
	         from caja_fondos_detalle d left join ticket_pago t on d.nro_orden=t.id
			where  codigo_ingreso='$cod' and d.empresa='P'
			 order by d.id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function RepCajaChicaXCodIn($cod){
	  $ocado=new cado();
	  $sql="select d.id,d.nro_documento,case when  isnull(t.id, '0')='0' then empresa else emp end empresa,
	  FORMAT(d.fec_emision,'dd/MM/yyyy')fecha,d.descripcion,d.movimiento,d.dni_egre_ingre,d.persona_egreso,d.monto_egreso,d.estado,tipo_doc,
	  tipodocref_egre,docref_egre
	         from caja_fondos_detalle d left join ticket_pago t on d.nro_orden=t.id
			where  codigo_ingreso='$cod' and d.empresa='I'
			 order by d.id asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function RegistrarIngreso($cod_ingreso,$monto_egreso,$descripcion,$empresa,$persona,$dni,$tipo,$docref){
	  try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  
		  $sql_serie="select correlativo+1 from serie where tipo_doc='IG'";
		  $cmd=$cn->prepare($sql_serie);
		  $cmd->execute();
		  $data=$cmd->fetch();
		  $corre=$data[0];
		  $correlativo='IG-'.$corre;
		  $sql="insert into caja_fondos_detalle (codigo_ingreso,tipo_doc,nro_documento,tipo_pago,fec_emision,hora_emision,estado,monto_egreso,movimiento,descripcion,empresa,persona_egreso,
		  dni_egre_ingre,tipodocref_egre,docref_egre)	                                  values('$cod_ingreso','IG','$correlativo','E',getdate(),getdate(),0,'$monto_egreso','INGRESO','$descripcion','$empresa','$persona','$dni','$tipo','$docref')";
		  $cn->prepare($sql)->execute();
		  
		  $sql_caja="update serie set correlativo=$corre where tipo_doc='IG';";
		  $cn->prepare($sql_caja)->execute();
		  
		  $cn->commit();
		  $cn=null;
	      $return=1;
	 
	  }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
        }
		  return $return;
	}
	function LisIngresosXId($id){
	  $ocado=new cado();
	  $sql="select id,nro_documento,persona_egreso,monto_egreso,descripcion,empresa,estado,dni_egre_ingre,
	  FORMAT(fec_emision,'dd/MM/yyyy')fecha,tipodocref_egre,docref_egre
	        from caja_fondos_detalle where id=$id";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function AnularIngresos($id){
	  $ocado=new cado();
	  $sql="update caja_fondos_detalle set estado=1 where id=$id";   
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function SaldoAnterior($id){
	  $ocado=new cado();
	  $sql="select top 1 id,total_cierre_caja from caja_fondos where id_caja=$id
		    order by id desc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ValidarFondo($idcaja){
	  $ocado=new cado();
	  $sql="select count(*) from caja_fondos where id_caja=$idcaja";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
    function LisGrupoUsuario(){
		  $ocado=new cado();
		  $sql="select * from usuario_grupo order by nombre asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
     }
     function LisModulos(){
		  $ocado=new cado();
		  $sql="select * from conf_menu where estado=0  order by orden asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	    }
		function DetalleMenuPermisos($idgrupo,$idmenu){
            $ocado = new cado();
            $sql="select d.id,d.nombre,p.id_grupo,d.url_imagen,d.url_link,case when p.id_grupo is null then 0 else 1 end permiso,d.idmenu,'$idgrupo' idgru
			      from conf_menu_detalle d left join conf_permisos p on d.id=p.id_menu_detalle  and p.id_grupo=$idgrupo and p.id_menu=$idmenu
                     where  d.idmenu=$idmenu  order by d.id asc";
            $ejecutar=$ocado->ejecutar($sql);
            return $ejecutar;
        }
		function GrabarPermisos($carrito,$idgru,$idme){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->beginTransaction();//inicia una transacción
		  $sql_delete="delete from conf_permisos where id_grupo=$idgru and id_menu=$idme";	
		  $cn->prepare($sql_delete)->execute();
		  for($i=0;$i<count($carrito);$i++){
			  $idgrupo=$carrito[$i][0];$idmenu=$carrito[$i][1];$iddetalle=$carrito[$i][2];
		      $sql="INSERT INTO conf_permisos (id_grupo,id_menu,id_menu_detalle) VALUES ($idgrupo,$idmenu,$iddetalle)";		 
		      $cn->prepare($sql)->execute(); 
		   }
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
       function DocCortesia($nro_orden){
	  $ocado=new cado();
	  $sql="select codigo_ingreso,total,round(total/1.18,2) subtotal,total-round(total/1.18,2) igv,convert(varchar(10),r.fec_crea,103) fecha,
	  convert(varchar(10),r.fec_crea,108) hora,concat(ape_pat,' ',ape_mat,' ',preNombres) pac,r.id id_receta
	  from receta r inner join paciente p on r.id_paciente=p.id
			where nro_orden='$nro_orden'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
    function DocDetalleCortesia($id){
	  $ocado=new cado();
	  $sql="select nombre,subtotal  
	        from receta_detalle d inner join examen e on d.id_examen=e.id where id_receta=$id";	
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
   }
?>
