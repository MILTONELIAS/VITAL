<?php
   require_once('conexion.php');
   
   class CC{
	   
    function ListarEntidadFinanciera($t){
	  if($t==0){$where="";}
	  if($t==1){$where=" and codsunat='18'";}
	  $ocado=new cado();
	  $sql="select codsunat,nombre from cc_entidad_financiera where mostrar=1 $where";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function LisDocumentos($idconv,$ini,$fin,$estado){
		  if($idconv>0 and $estado=='T'){$where=" and c.id=$idconv";}
		  if($idconv==0 and $estado<>'T'){$where=" and pagado=$estado";}
		  if($idconv>0 and $estado<>'T'){$where=" and c.id=$idconv and pagado='$estado'";}

		  if($estado==1){$where1="cast(cu.fec_pago as date)>=cast('$ini' as date) and cast(cu.fec_pago as date)<=cast('$fin' as date)";}
		  else{$where1="cast(cu.fec_emision as date)>=cast('$ini' as date) and cast(cu.fec_emision as date)<=cast('$fin' as date)";}
		  
		  $ocado=new cado();
		  $sql="select c.id,c.empresa,concat(doc.serie,'-',doc.correlativo) docu,cast(doc.importe_total as decimal(10,2)),pagado,
		          FORMAT(doc.fecha_emision,'dd/MM/yyyy') fecha,doc.id doc_elec,doc.serie,doc.correlativo,cu.id,monto_detraccion,
				  acuenta,saldo_total,c.ruc,
				  (select sum(monto_pago) from cc_pagos p where p.id_cc=cu.id and p.anulado=0 and p.ingreso=1) monto_ing,monto_nc
		          ,FORMAT(cu.fec_pago,'dd/MM/yyyy') fecha_pago
				  from doc_electronicos doc 
                  inner join convenio c on doc.doc_cliente=c.ruc
                  inner join cuenta_corriente cu on doc.id=cu.doc_electronico_id
			    where  $where1 and cu.estado=0 $where
				order by doc.fecha_emision desc,doc.correlativo desc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	
	function LisDocEmp($idconv,$ini,$fin,$estado){
		  if($idconv>0 and $estado=='T'){$where=" and c.id=$idconv";}
		  if($idconv==0 and $estado<>'T'){$where=" and pagado=$estado";}
		  if($idconv>0 and $estado<>'T'){$where=" and c.id=$idconv and pagado='$estado'";}
		  
		  $ocado=new cado();
		  $sql="select c.id,c.empresa
		          from doc_electronicos doc 
                  inner join convenio c on doc.doc_cliente=c.ruc
                  inner join cuenta_corriente cu on doc.id=cu.doc_electronico_id
			    where cast(cu.fec_emision as date)>=cast('$ini' as date) and cast(cu.fec_emision as date)<=cast('$fin' as date)and cu.estado=0 $where
				group by c.id,c.empresa
				order by c.empresa asc";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	
	function AgregarCobro($idcc,$tipo_pago,$pago,$codbanco,$idcuenta,$fecpago,$fecdeposito,$monpago,$mondetra,$acuenta,$referencia,
	$user,$mon_real,$redondeo,$ingreso){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  
		  $sql="INSERT INTO cc_pagos(id_cc,tipo_pago,pago,cod_entidad_fin,id_nro_cuenta,fecha_pago,fecha_deposito,monto_real,monto_pago,
		  redondeo,referencia, user_crea,fecha_crea,anulado,ingreso) 
          VALUES ($idcc,'$tipo_pago','$pago','$codbanco','$idcuenta','$fecpago','$fecdeposito','$mon_real','$monpago','$redondeo',
		  '$referencia','$user',getdate(),0,$ingreso)";	
		  //die($sql);
		  $cn->prepare($sql)->execute();
		  /*if($pago=='TOT'){
		$sql_up="update cuenta_corriente set monto_detraccion='$mondetra',acuenta='$acuenta',saldo_total=0 
		                             where id=$idcc ";}*/
		 if($ingreso==0){
		     if($pago=='ACU'){$sql_up="update cuenta_corriente set acuenta=acuenta+'$acuenta',saldo_total=saldo_total-'$acuenta' 
		                             where id=$idcc ";}
		     if($pago=='DET'){$sql_up="update cuenta_corriente set monto_detraccion='$mondetra',saldo_total=saldo_total-'$mondetra' 
		                             where id=$idcc ";}
			 $cn->prepare($sql_up)->execute();
		  }
		  $cn->commit();
		  $cn=null;
		  $return=1;
	    }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
          }
		  return $return;
	 }
	 function Anular($id,$user,$ingreso){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql_pago="select id_cc,pago,monto_pago from cc_pagos where id=$id";
		  $cmd=$cn->prepare($sql_pago);
		  $cmd->execute();
		  $dato=$cmd->fetch();
		  $pago=$dato[1];$monto=$dato[2];$idcc=$dato[0];
		  /*if($pago=='TOT'){
		    $sql_up="update cuenta_corriente set monto_detraccion=0,acuenta=0,saldo_total='$monto' where id=$idcc ";	
		    $cn->prepare($sql_up)->execute();
		  }*/
		  if($ingreso==0){	
		    if($pago=='DET'){//$acuenta=0;$detraccion=0;$saldo=$monto;
		       $sql_up="update cuenta_corriente set monto_detraccion=0,saldo_total=saldo_total+$monto where id=$idcc ";	
		       $cn->prepare($sql_up)->execute();
		     }	
		    if($pago=='ACU'){//$acuenta=0;$detraccion=0;$saldo=$monto;
		       $sql_up="update cuenta_corriente set saldo_total=saldo_total+$monto, acuenta=acuenta-$monto where id=$idcc ";	
		       $cn->prepare($sql_up)->execute();
			}
		  }
		  $sql="update cc_pagos set anulado=1,user_anula='$user',fecha_anula=getdate() where id=$id";	
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
	    }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
          }
		  return $return;
	 }
	 function ListarCuentasBancarias($cod){
	  $ocado=new cado();
	  $sql="select id,concat(numero,' ',nom_cuenta,' - ',tipo_cuenta)cuenta from cc_nro_cuenta where cod_entidad_fin='$cod' and estado=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function TasaDetraccion(){
	  $ocado=new cado();
	  $sql="select id,valor from tasas where nombre='DETRACCION'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	  
	  function LisDetPagos($idcc,$doc){
	  $ocado=new cado();
	  $sql="select cc.id,id_cc,m.descripcion,pago,ent.nombre,concat(numero,' ',nom_cuenta,' - ',tipo_cuenta)cuenta,
	  FORMAT(fecha_pago,'dd/MM/yyyy')fecha_pago,FORMAT(fecha_deposito,'dd/MM/yyyy')fecha_deposito,
	        monto_pago,referencia,fecha_deposito,case when anulado=0 then 'EMITIDO' ELSE 'ANULADO' END est,cc.user_anula,ingreso
	        from cc_pagos cc inner join cc_entidad_financiera ent on cc.cod_entidad_fin=ent.codsunat
			left join cc_nro_cuenta cue on cc.id_nro_cuenta=cue.id
			inner join conta_mediospago m on cc.tipo_pago=m.cod_sunat
		    where id_cc=$idcc and anulado=0
			union ALL
select d.id,0 id_cc,motivo_nota,'-'pago,'-' ent,'-' cuenta,FORMAT(fecha_emision,'dd/MM/yyyy')fecha_pago,'-' fecha_deposito,importe_total,concat(serie,'-',correlativo) referencia,
'' fecha_deposito,case when estado=0 then 'EMITIDO' else 'ANULADO' end est,'' user_anula,'' ingreso
from doc_electronicos d where nrodoc_relacionado='$doc' ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 
	 function ValPagos($idcc){
	  $ocado=new cado();
	  $sql="select (select count(*) from cc_pagos where id_cc=$idcc and anulado=0 and pago='DET')can_det,
	               (select count(*) from cc_pagos where id_cc=$idcc and anulado=0 and pago='ACU')can_acu,
				   (select count(*) from cc_pagos where id_cc=$idcc and anulado=0 and pago='TOT')can_tot,
				   isnull((select cast(saldo_total*0.12 as decimal(10,2)) from cuenta_corriente where id=$idcc and total_venta>700 and estado=0 ),0) detraccion,
		isnull((select saldo_total from cuenta_corriente where id=$idcc and total_venta>700 and estado=0 ),0) saldo";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 } 
	 function MediosPago(){
	  $ocado=new cado();
	  $sql="select cod_sunat,descripcion from conta_mediospago where vista=1";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	  function MediosPagoCC(){
	  $ocado=new cado();
	  $sql="select cod_sunat,descripcion from conta_mediospago where vista=1 and cod_sunat<>'008'";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	 
	 function ModFecDep($id,$fec){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql="update cc_pagos set fecha_deposito='$fec' where id=$id ";
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
	    }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
          }
		  return $return;
	 }
	 function CcxId($idcc){
	  $ocado=new cado();
	  $sql="select c.id,(nro_serie+' - '+nro_documento)doc,doc_cliente,nomcli,FORMAT(d.fecha_emision,'dd/MM/yyyy')fec,total_venta
	        from cuenta_corriente c inner join doc_electronicos d on c.doc_electronico_id=d.id
	        where c.id=$idcc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	 function CcxIdDet($idcc){
	  $ocado=new cado();
	  $sql="select m.descripcion,pago,ent.nombre,(numero+' '+nom_cuenta+' - '+tipo_cuenta)cuenta,FORMAT(fecha_pago,'dd/MM/yyyy')fecha_pago
	  ,FORMAT(fecha_deposito,'dd/MM/yyyy')fecha_deposito, monto_pago,referencia
	        from cc_pagos cc inner join cc_entidad_financiera ent on cc.cod_entidad_fin=ent.codsunat
			left join cc_nro_cuenta cue on cc.id_nro_cuenta=cue.id
			inner join conta_mediospago m on cc.tipo_pago=m.cod_sunat
		    where id_cc=$idcc and anulado=0 and ingreso=0";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }	
	 
	 function CancelarFact($id,$user){
		try{
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  $sql="update cuenta_corriente set pagado=1, user_pago='$user',fec_pago=getdate() where id=$id";	
		  $cn->prepare($sql)->execute();
		  $cn->commit();
		  $cn=null;
		  $return=1;
	    }catch (PDOException $ex){
              $cn->rollBack();
			  $return=0;
          }
		  return $return;
	 }
	 function SegTipoDoc(){
	  $ocado=new cado();
	  $sql="select distinct(tipo_doc) tipo from cuenta_corriente";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	 function SegSerie(){
	  $ocado=new cado();
	  $sql="select distinct(nro_serie) tipo from cuenta_corriente";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	  
	 function CabSegPago($serie,$correlativo){
	  $ocado=new cado();
	  $sql="select top 1 c.id,(nro_serie+' - '+nro_documento)doc,doc_cliente,nomcli,FORMAT(d.fecha_emision,'dd/MM/yyyy')fec,total_venta,saldo_total,
	   case when c.estado=1 then 'DOCUMENTO ANULADO' else case when pagado=0 then 'DOCUMENTO PENDIENTE' else 'DOCUMENTO CANCELADO' end end est,d.id 
	        from cuenta_corriente c inner join doc_electronicos d on c.doc_electronico_id=d.id
	        where c.nro_serie='$serie' and nro_documento=$correlativo";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	  }
	  
	 function SegDetPagos($idcc){
	  $ocado=new cado();
	  $sql="select cc.id,id_cc,m.descripcion,pago,ent.nombre,(numero+' '+nom_cuenta+' - '+tipo_cuenta)cuenta,
	  FORMAT(fecha_pago,'%d/MM/yyyy')fecha_pago,FORMAT(fecha_deposito,'dd/MM/yyyy')fecha_deposito,
	        monto_pago,referencia,fecha_deposito,case when cc.anulado=0 then 'EMITIDO' ELSE 'ANULADO' END EST,user_anula,ingreso
	        from cc_pagos cc inner join cc_entidad_financiera ent on cc.cod_entidad_fin=ent.codsunat
			left join cc_nro_cuenta cue on cc.id_nro_cuenta=cue.id
			inner join conta_mediospago m on cc.tipo_pago=m.cod_sunat
		    where id_cc=$idcc order by ingreso asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	 function ResumenDocCancelados($idconv,$ini,$fin,$estado){
		  if($idconv>0 and $estado=='T'){$where=" and c.id=$idconv";}
		  if($idconv==0 and $estado<>'T'){$where=" and pagado=$estado";}
		  if($idconv>0 and $estado<>'T'){$where=" and c.id=$idconv and pagado='$estado'";}
		  
		  $ocado=new cado();
		  $sql="select c.ruc,c.empresa,cast(sum(doc.importe_total) as decimal(10,2)) total,cast(sum(acuenta) as decimal(10,2))acuenta
		  ,cast(sum(monto_detraccion) as decimal(10,2))detra,cast(sum(saldo_total) as decimal(10,2) )saldo
		          from doc_electronicos doc 
                  inner join convenio c on doc.doc_cliente=c.ruc
                  inner join cuenta_corriente cu on doc.id=cu.doc_electronico_id
			    where cast(cu.fec_emision as date)>=cast('$ini' as date) and cast(cu.fec_emision as date)<=cast('$fin' as date)and cu.estado=0 $where
				group by c.ruc,c.empresa";
		  $ejecutar=$ocado->ejecutar($sql);
		  return $ejecutar;
	}
	function LisDetraccion($ini,$fin,$est){
	  $ocado=new cado();
	  $sql="select d.nomcli,concat(c.nro_serie,'-',c.nro_documento)documento,FORMAT(c.fec_emision,'dd/MM/yyyy') fecha,c.total_venta,
	        (select valor from tasas where id=2)valor
	        from cuenta_corriente c inner join doc_electronicos d on c.doc_electronico_id=d.id
		    where c.total_venta>700 and cast(c.fec_emision as date)>=cast('$ini' as date) and cast(c.fec_emision as date)<=cast('$fin' as date) and c.estado=0 and c.pagado=$est
			order by d.nomcli asc";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	 }
	function ListarSerieDoc(){
	  $ocado=new cado();
	  $sql="select tipo_doc,(tipo_doc+' - '+serie) ser,serie from serie where tipo_doc in('FA','BV') and empresa='P' and serie not in ('FP01')"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function CorrelativoSerie($tipo_doc){
		if($tipo_doc=='BV'){$serie='BN01';}
		if($tipo_doc=='FA'){$serie='FN03';}
	  $ocado=new cado();
	  $sql="select serie,correlativo+1 from serie where serie='$serie' and empresa='P' and tipo_doc='NC'"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function ListarDetalleDoc($serie,$corre){
	  $ocado=new cado();
	  $sql="select nro_orden,descripcion_item,cantidad,preuni
	      from doc_electronicos d inner join doc_electronico_items de on d.id=de.doc_electronico_id where d.serie='$serie' and d.correlativo=$corre"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	function ListarCabeceraDoc($serie,$corre){
	  $ocado=new cado();
	  $sql="select valor_venta,total_igv,importe_total,tipodoc_cli,doc_cliente,nomcli,id,dircliente,moneda,tipo_documento,id_cliente,serie,correlativo,
	  (select sum(importe_total) from doc_electronicos doc where doc.doc_relacionado_id=d.id)total_nc
	    from doc_electronicos d where serie='$serie' and correlativo=$corre"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function RegistrarFacturacionElectronicaNC($serie_nc,$corre_nc,$serie,$corre,$total_venta,$tipodoc_cli,$doc_cliente,$nomcli,
			$dircliente,$doc_afec,$doc_elec_relacionado,$tipo_documento,$user,$idcliente,$motivo){
		try{
		  
		  $ocado=new cado();
		  $cn=$ocado->conectar();
		  $cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $cn->beginTransaction();
		  //$sql_validar="select count(*) from doc_electronicos where serie";
		  $op_gravadas=round($total_venta/1.18,2);
		  $total_igv=$total_venta-$op_gravadas;
		  
		  $emp='P';
		  $sql_corre="select correlativo from serie where serie='$serie_nc' and tipo_doc='NC' and empresa='P'";
		  $cn->prepare($sql_corre)->execute();
		 //die($sql_corre);exit;
		  $sql="insert into doc_electronicos(cod_operacion,fecha_emision,hora_emision,cod_dom_fiscal,tipo_documento,serie,correlativo,
		  tipodoc_cli,doc_cliente,nomcli,dircliente,moneda,op_gravadas,op_gratuitas,op_inafecta,op_exoneradas,total_descuento,total_igv,	total_isc,total_otrtri,total_otrcar,total_glosa,valor_venta,importe_total,doc_relacionado_id,nrodoc_relacionado,tipo_nota,codmot_nota,motivo_nota,
	created_at,id_cliente,grupal,emp,motivo_nota_interno,titulo_gratuito)
values('0101',getdate(),getdate(),'0000','07','$serie_nc',REPLICATE('0',8-LEN('$corre_nc'))+CAST('$corre_nc' AS VARCHAR),
'$tipodoc_cli','$doc_cliente','$nomcli','$dircliente','PEN','$op_gravadas',	'0.00','0.00','0.00','0.00','$total_igv','0.00','0.00','0.00','0.00','$op_gravadas','$total_venta',$doc_elec_relacionado,'$doc_afec',1,'04',
'DESCUENTO GLOBAL',getdate(),$idcliente,0,'P','$motivo',0);";
	      //die($sql);
		  $cn->prepare($sql)->execute();
		  $doc_electronico_id= $cn->lastInsertId();
		  //die($doc_electronico_id);exit;
		  $sql_detalle="INSERT INTO doc_electronico_items (doc_electronico_id,nro_orden,tipo_item,cod_afecta_igv,unidad_medida,cantidad, descripcion_item,cod_interno,valuni,valigv,preuni,valven,created_at) 
VALUES ('$doc_electronico_id',1,2,'10','ZZ',1,'POR CONCEPTO DE ANALISIS CLINICOS','','$op_gravadas','$total_igv', '$total_venta','$op_gravadas',
getdate());";
           $cn->prepare($sql_detalle)->execute();
		      
		  //$corre=(int)$correlativo;
		  $sql_update="update serie set correlativo=$corre_nc where serie='$serie_nc' and tipo_doc='NC' and empresa='P'";
		  $cn->prepare($sql_update)->execute();
		  $cn->commit();
		  $cn=null;
		  
		  file_get_contents("http://localhost/Lab/FactElect/firmar/".$doc_electronico_id); //}
		  //if($emp=='I'){file_get_contents("http://localhost/Innova/FactElect/firmar/".$doc_electronico_id);}		   		  
		  $return=1;
		  
		 }catch (PDOException $ex){
                    $cn->rollBack();
			  $return=0;
              //return $ex->getMessage();
          }
		  return $return;
	 }
	 function ValidarDoc($serie,$corre){
	  $ocado=new cado();
	  $sql="select count(*) from doc_electronicos where serie='$serie' and correlativo=$corre and estado=0"; 
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}	
}
?>