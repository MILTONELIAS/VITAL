<?php
require_once( 'conexion.php' );

class Reportes {

  function CierresCaja( $tipo ) {
    $ocado = new cado();
    $sql = "select codigo_ingreso,u.nombre,f.nom_caja,day(f.fec_ingreso)dia,FORMAT(f.fec_ingreso,'dd/MM/yyyy hh:mm ss'),
	  f.fec_cierra,  f.nom_user,c.tipo
	       from caja_fondos f inner join usuario u on f.id_user=u.id 
		                      inner join caja c on f.id_caja=c.id
			where tipo=$tipo
	        order by fec_ingreso desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function CajaDelDia() {
    $ocado = new cado();
    $sql = "select codigo_ingreso,u.nombre,nom_caja,fec_ingreso,fec_cierra,nom_user
	       from caja_fondos f inner join usuario u on f.id_user=u.id 
		    where cast(f.fec_ingreso as date)=cast(getdate() as date)
	        order by fec_ingreso desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function DatosReimprimir( $orden ) {
    $ocado = new cado();
    $sql = "select d.id,f.codigo_ingreso,empresa
	       from caja_fondos_detalle f inner join doc_electronicos d on f.nro_serie=d.serie and f.nro_documento=d.correlativo
		   where f.nro_orden='$orden'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaOrdenes() {
    $ocado = new cado();
    $sql = "select d.id,f.codigo_ingreso,empresa
	       from caja_fondos_detalle f inner join doc_electronicos d on f.nro_serie=d.serie and f.nro_documento=d.correlativo
		   where f.nro_orden='$orden'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporOrden($emp,$med,$pac,$ini,$fin,$tp){
    if ($ini=='' and $fin==''){$fecha_where = '';} else {
      $fecha_where = "cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) and";}
    if ($tp==3) {$tp_where = "credito in(0,1,2)";} else {$tp_where = " credito='$tp' ";}
    if ($emp==0 and $med==0 and $pac==0){$where="";}
    if ($emp==0 and $med==0 and $pac>0){$where = " and r.id_paciente=$pac";}
    if ($emp==0 and $med>0 and $pac==0){$where = " and r.id_medico=$med";}
    if ($emp>0 and $med==0 and $pac==0){$where = " and r.id_convenio=$emp";}
    if ($emp>0 and $med>0 and $pac==0){$where = " and r.id_convenio=$emp and  r.id_medico=$med";}
    if ($emp>0 and $med==0 and $pac>0){$where = " and r.id_convenio=$emp and  r.id_paciente=$pac";}
    if ($emp==0 and $med>0 and $pac>0){$where = " and r.id_medico=$med and  r.id_paciente=$pac";}
    if ($emp>0 and $med>0 and $pac>0){$where = " and r.id_convenio=$emp and r.id_medico=$med and  r.id_paciente=$pac";}
    $ocado = new cado();
    $sql = "SELECT FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm ss'),r.nro_orden,case when r.anulado=0 then r.total else 0.00 end total,
	  (ape_pat+' '+ape_mat+' '+preNombres)pac,m.nombre,monto_medico,case credito when 0 then 'CONTADO' WHEN 1 then 'CREDITO' end 
	  tipo_pago,c.empresa,r.id,
		   case when r.anulado=1 then 'ANULADO' ELSE case r.estado when 0 then 'PENDIENTE' when 1 then 'PROCESOS' when 2 then 'FINALIZADO' end
		   END est,credito,nom_user,c.ruc,r.domicilio,m.porcentaje_comision
            FROM receta r inner join paciente p on r.id_paciente=p.id 
			              inner join convenio c on r.id_convenio=c.id
                left join medico m on r.id_medico=m.id
				left join caja_fondos fon on r.codigo_ingreso=fon.codigo_ingreso
             where  $fecha_where  emp='P' and $tp_where $where order by c.empresa asc";

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporOrdenWeb( $ini, $fin, $pac, $ruc ) {
    $ocado = new cado();
    $sql = "SELECT FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm ss'),r.nro_orden,r.total,(ape_pat+' '+ape_mat+' '+preNombres)pac
	  ,m.nombre,monto_medico,c.empresa,r.id,case when  r.estado = 2 then 'FINALIZADO' ELSE 'EN PROCESO' end  est,
	  (select count(*) from receta_detalle det where det.id_receta=r.id and det.estado=1)cant
            FROM receta r inner join paciente p on r.id_paciente=p.id 
			              inner join convenio c on r.id_convenio=c.id 
                left join medico m on r.id_medico=m.id
           where  c.ruc='$ruc' and cast(r.fec_crea as date)>=cast('$ini' as date)  and cast(r.fec_crea as date)<=cast('$fin' as date) and  emp='P' and r.anulado=0 and r.estado > 0 
			 and (ape_pat+' '+ape_mat+' '+preNombres)  like '%$pac%'
			  order by r.nro_orden desc";

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporOrdenFarias( $ini, $fin, $pac ) {
    $ocado = new cado();
    $sql = "SELECT FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm ss'),r.nro_orden,r.total,(ape_pat+' '+ape_mat+' '+preNombres)pac
		,m.nombre,monto_medico,c.empresa,r.id,case when  r.estado = 2 then 'FINALIZADO' ELSE 'EN PROCESO' end  est,
		(select count(*) from receta_detalle det where det.id_receta=r.id and det.estado=1)cant
			  FROM receta r inner join paciente p on r.id_paciente=p.id 
							inner join convenio c on r.id_convenio=c.id
				  left join medico m on r.id_medico=m.id
			   where cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) and  emp='P' and r.anulado=0 and r.estado > 0 and (ape_pat+' '+ape_mat+' '+preNombres)  like '%$pac%'
				order by r.nro_orden desc";

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporOrdenWebPac( $ini, $fin, $dni ) {
    $ocado = new cado();
    $sql = "SELECT FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm ss'),r.nro_orden,r.total,(ape_pat+' '+ape_mat+' '+preNombres)pac
	  ,m.nombre,monto_medico,c.empresa,r.id,case when  r.estado = 2 then 'FINALIZADO' ELSE 'EN PROCESO' end  est
            FROM receta r inner join paciente p on r.id_paciente=p.id 
			              inner join convenio c on r.id_convenio=c.id
                left join medico m on r.id_medico=m.id
             where p.dni='$dni' and cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) and  emp='P' and r.anulado=0 and r.estado > 0 
			  order by r.nro_orden desc";

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporOrdenInnova( $emp, $med, $pac, $ini, $fin ) {
    if ( $ini == ''
      and $fin == '' ) {
      $fecha_where = '';
    } else {
      $fecha_where = "cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) and";
    }
    if ( $emp == 0 and $med == 0 and $pac == 0 ) {
      $where = "";
    }
    if ( $emp == 0 and $med == 0 and $pac > 0 ) {
      $where = " and r.id_paciente=$pac";
    }
    if ( $emp == 0 and $med > 0 and $pac == 0 ) {
      $where = " and r.id_medico=$med";
    }
    if ( $emp > 0 and $med == 0 and $pac == 0 ) {
      $where = " and r.id_convenio=$emp";
    }
    if ( $emp > 0 and $med > 0 and $pac == 0 ) {
      $where = " and r.id_convenio=$emp and  r.id_medico=$med";
    }
    if ( $emp > 0 and $med == 0 and $pac > 0 ) {
      $where = " and r.id_convenio=$emp and  r.id_paciente=$pac";
    }
    if ( $emp == 0 and $med > 0 and $pac > 0 ) {
      $where = " and r.id_medico=$med and  r.id_paciente=$pac";
    }
    if ( $emp > 0 and $med > 0 and $pac > 0 ) {
      $where = " and r.id_convenio=$emp and r.id_medico=$med and  r.id_paciente=$pac";
    }
    $ocado = new cado();
    $sql = "SELECT FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm ss'),r.nro_orden,r.total,(ape_pat+' '+ape_mat+' '+preNombres)pac
	  ,m.nombre,monto_medico
           ,case credito when 0 then 'CONTADO' WHEN 1 then 'CREDITO' end tipo_pago,c.empresa,r.id,
		   case when r.anulado=1 then 'ANULADO' ELSE case r.estado when 0 then 'PENDIENTE' when 1 then 'GENERADO' when 2 then 'FINALIZADO' end
		   END est,credito,nom_user
            FROM receta r inner join paciente p on r.id_paciente=p.id 
			              inner join convenio c on r.id_convenio=c.id
                left join medico m on r.id_medico=m.id 
				inner join caja_fondos fon on r.codigo_ingreso=fon.codigo_ingreso
             where $fecha_where  emp='I' and r.anulado=0 $where order by r.nro_orden desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function VerOrden( $orden ) {
    $ocado = new cado();
    $sql = "select r.id,r.nro_orden,m.nombre,(ape_pat+' '+ape_mat+' '+preNombres)pac,p.dni,
		DATEDIFF(YEAR, p.fec_nac, getdate())edad,
		  FORMAT(r.fec_crea, 'dd/MM/yyyy hh:mm tt')fec,examen_precio detalle,total,nom_user,nom_caja,
		  (SELECT aten.nro_carta from atencion_salupol aten WHERE aten.nro_orden=cast(r.nro_orden as varchar)) nro_carta,r.id_convenio
		 from receta r inner join paciente p on r.id_paciente=p.id		   				
					   left join medico m  on r.id_medico=m.id  
					   left join caja_fondos fon on r.codigo_ingreso=fon.codigo_ingreso      
		 where r.nro_orden='$orden'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function VerOrdenDetalle( $idreceta ) {
    $ocado = new cado();
    $sql = "select id_examen,e.nombre,r.subtotal from receta_detalle r inner join examen e on r.id_examen=e.id 
	         where id_receta=$idreceta";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function VerExamen( $id ) {
    $ocado = new cado();
    $sql = "select nombre from examen where id=$id";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarSerie() {
    $ocado = new cado();
    $sql = "select tipo_doc,concat(tipo_doc,' - ',serie) ser from serie where tipo_doc in('FA','BV','NC','TK') and empresa='P'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarSerieInnova() {
    $ocado = new cado();
    $sql = "select tipo_doc,concat(tipo_doc,' - ',serie) ser from serie where tipo_doc in('FA','BV','NC','TB','TF') and empresa='I'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporDoc( $ini, $fin, $pac, $serie, $tipo ) {
    if ( $pac == 0 and $tipo == 0 ) {
      $where = "";
    }
    if ( $pac > 0 and $tipo == 0 ) {
      $where = " and p.id=$pac";
    }
    if ( $pac == 0 and $tipo > 0 ) {
      $where = " and d.tipo_documento='$tipo' and d.serie='$serie' ";
    }
    if ( $pac > 0 and $tipo > 0 ) {
      $where = " and p.id=$pac and d.tipo_documento and d.serie='$serie'";
    }

    $ocado = new cado();
    $sql = "select d.id,serie,correlativo, nomcli pac,importe_total,xml_generado,resp_cdr,
	        case when estado=1 then 'ANULADO' else case when envio_sunat=0 then 'PENDIENTE' else 'ENVIADO' end end est, FORMAT(fecha_emision, 'dd/MM/yyyy')fec,grupal,tipo_documento,descripcion_cdr
	        from doc_electronicos d left join paciente p on d.id_cliente=p.id 
	        where cast(fecha_emision as date) >=cast('$ini' as date) and cast(fecha_emision as date)<=cast('$fin' as date) and d.tipo_documento='$tipo'   $where 
			order by serie,correlativo desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporDocInnova( $ini, $fin, $pac, $serie, $tipo ) {
    if ( $pac == 0 and $tipo == 0 ) {
      $where = "";
    }
    if ( $pac > 0 and $tipo == 0 ) {
      $where = " and p.id=$pac";
    }
    if ( $pac == 0 and $tipo > 0 ) {
      $where = " and d.tipo_documento='$tipo' and d.serie='$serie' ";
    }
    if ( $pac > 0 and $tipo > 0 ) {
      $where = " and p.id=$pac and d.tipo_documento and d.serie='$serie'";
    }

    $ocado = new cado();
    $sql = "select d.id,serie,correlativo,case when d.tipo_documento='03' then  (ape_pat+' '+ape_mat+' '+preNombres) else nomcli end pac,importe_total,xml_generado,resp_cdr,
	        case when estado=1 then 'ANULADO' else 'GENERADO' end est,FORMAT(fecha_emision, 'dd/MM/yyyy')fec,grupal,tipo_documento,descripcion_cdr
	        from doc_electronicos d left join paciente p on d.id_cliente=p.id 
	        where cast(fecha_emision as date) >=cast('$ini' as date) and cast(fecha_emision as date)<=cast('$fin' as date) and d.tipo_documento in('01','03','07') and emp='I' $where 
			order by serie,correlativo desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporDocWeb( $ini, $fin, $pac, $ruc ) {
    $ocado = new cado();
    $sql = "select d.id,serie,correlativo,case when d.tipo_documento='03' then  concat(ape_pat,' ',ape_mat,' ',preNombres) else nomcli end pac,cast(importe_total as decimal(10,2)),xml_generado,resp_cdr,
	        case when estado=1 then 'ANULADO' else case when envio_sunat=0 then 'PENDIENTE' else 'ENVIADO' end end est, FORMAT(fecha_emision, 'dd/MM/yyyy')fec,grupal,tipo_documento
	        from doc_electronicos d left join paciente p on d.id_cliente=p.id 
	        where cast(fecha_emision as date) >=cast('$ini' as date) and cast(fecha_emision as date)<=cast('$fin' as date)  and d.tipo_documento in('01','03','07') and doc_cliente='$ruc'
			order by serie,correlativo desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporDocWebPac( $ini, $fin, $user ) {
    $ocado = new cado();
    $sql = "select d.id,serie,correlativo,case when d.tipo_documento='03' then  concat(ape_pat,' ',ape_mat,' ',preNombres) else nomcli end pac,cast(importe_total as decimal(10,2)),xml_generado,resp_cdr,
	        case when estado=1 then 'ANULADO' else case when envio_sunat=0 then 'PENDIENTE' else 'ENVIADO' end end est, FORMAT(fecha_emision, 'dd/MM/yyyy')fec,grupal,tipo_documento
	        from doc_electronicos d left join paciente p on d.id_cliente=p.id 
	        where cast(fecha_emision as date) >=cast('$ini' as date) and cast(fecha_emision as date)<=cast('$fin' as date)  and d.tipo_documento in('01','03','07') and nomcli='$user'
			order by serie,correlativo desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporResumenPrecisa( $ini, $fin ) {
    $ocado = new cado();
    $sql = "select id,fecha_generacion,fecha_resumen,cod_resumen,envio_sunat,fec_envio_sunat,resp_cdr,
			  case when envio_sunat=0 then 'PENDIENTE' else 'ENVIADO' end est,desc_cdr,emp
			  from doc_resumenes_electronicos 
			  where cast(fecha_generacion as date) >=cast('$ini' as date) and cast(fecha_generacion as date)<= cast('$fin' as date) and emp='P' 
			  order by id desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListaReporResumenInnova( $ini, $fin ) {
    $ocado = new cado();
    $sql = "select id,fecha_generacion,fecha_resumen,cod_resumen,envio_sunat,fec_envio_sunat,resp_cdr,
			  case when envio_sunat=0 then 'PENDIENTE' else 'ENVIADO' end est,desc_cdr,emp
			  from doc_resumenes_electronicos 
			  where cast(fecha_generacion as date) >= cast('$ini' as date) and cast(fecha_generacion as date)<= cast('$fin' as date) and emp='I' 
			  order by id desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function AnularDoc( $serie, $doc, $user, $grupal ) {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $cn->beginTransaction();
      if ( $grupal == 0 ) {
        $sql_det = "select nro_orden from caja_fondos_detalle where nro_serie='$serie' and nro_documento='$doc'";
        $cmd = $cn->prepare( $sql_det );
        $cmd->execute();
        $data = $cmd->fetch();
        $orden = $data[ 0 ];
        $sql = "update doc_electronicos set estado=1,user_anula='$user',fecha_anulacion=getdate() where serie = '$serie' and correlativo='$doc' ";
        $cn->prepare( $sql )->execute();
        $sql_fondo = "update caja_fondos_detalle set estado=1 where nro_serie = '$serie' and nro_documento='$doc' ";
        $cn->prepare( $sql_fondo )->execute();
        $sql_act_orden = "update receta set doc_emitido=0 where nro_orden='$orden' ";
        $cn->prepare( $sql_act_orden )->execute();
      }
      if ( $grupal == 1 ) {
        $sql_cuentas = "select id,id_receta from cuenta_corriente where nro_serie='$serie' and nro_documento='$doc'";
        $cmd = $cn->prepare( $sql_cuentas );
        $cmd->execute();
        $data = $cmd->fetch();
        $idcuentas = $data[ 0 ];
        $idre = $data[ 1 ];
        $sql = "update doc_electronicos set estado=1,user_anula='$user',fecha_anulacion=getdate() where serie = '$serie' and correlativo='$doc' ";
        $cn->prepare( $sql )->execute();
        $sql_fondo = "update caja_fondos_detalle set estado=1 where nro_serie = '$serie' and nro_documento='$doc' ";
        $cn->prepare( $sql_fondo )->execute();
        $sql_cc = "update cuenta_corriente set estado=1,user_anula='$user',fec_anula=getdate() where id=$idcuentas ";
        $cn->prepare( $sql_cc )->execute();
        $sql_orden = "update receta set doc_emitido=0 where id in($idre) ";
        $cn->prepare( $sql_orden )->execute();
      }
      $cn->commit();
      $cn = null;
      $return = 1;
    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $return = 0;
    }
    return $return;
  }

  function ListarMedicoPagos( $tipo ) {
    if ( $tipo == 1 ) {
      $pago = " pago_medico=0";
    }
    if ( $tipo == 2 ) {
      $pago = " pago_medico=1";
    }
    $ocado = new cado();
    $sql = "select m.id,m.nombre from medico m inner join receta r on m.id=r.id_medico
                 where m.estado=0 and monto_medico>0 and credito=0 and r.anulado=0 and $pago
                group by m.id,m.nombre order by m.nombre asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisEmpresaPagos( $est ) {
    $ocado = new cado();
    $sql = "select c.id,c.empresa from doc_electronicos doc 
                  inner join convenio c on doc.doc_cliente=c.ruc
                  inner join cuenta_corriente cu on doc.id=cu.doc_electronico_id
				  where cu.estado=0 and pagado=$est and c.estado=0
                group by c.id,c.empresa";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisDocumentos( $idconv, $ini, $fin, $estado ) {
    if ( $idconv > 0 and $estado == 'T' ) {
      $where = " and c.id=$idconv";
    }
    if ( $idconv == 0 and $estado <> 'T' ) {
      $where = " and pagado=$estado";
    }
    if ( $idconv > 0 and $estado <> 'T' ) {
      $where = " and c.id=$idconv and pagado='$estado'";
    }

    $ocado = new cado();
    $sql = "select c.id,c.empresa,concat(doc.serie,'-',doc.correlativo) docu,cast(doc.importe_total as decimal(10,2)),pagado,
		          FORMAT(doc.fecha_emision,'dd/MM/yyyy') fecha,doc.id doc_elec,doc.serie,doc.correlativo,cu.id
		          from doc_electronicos doc 
                  inner join convenio c on doc.doc_cliente=c.ruc
                  inner join cuenta_corriente cu on doc.id=cu.doc_electronico_id
			    where cast(cu.fec_emision as date)>=cast('$ini' as date) and cast(cu.fec_emision as date)<=cast('$fin' as date)and cu.estado=0 $where
				order by doc.fecha_emision desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarMedicoPagoscaja() {
    $ocado = new cado();
    $sql = "select m.id,m.nombre from medico m inner join ticket_pago t on m.id=t.id_medico
                 where t.entregado=0
                group by m.id,m.nombre order by m.nombre asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarPagos( $tipo, $ini, $fin, $idmed, $emp ) {
    if ( $idmed == 0 ) {
      $where = "";
    }
    if ( $idmed > 0 ) {
      $where = " and id_medico=$idmed";
    }
    if ( $tipo == 1 ) {
      $sql = "select m.id,m.nombre,r.emp,sum(r.monto_medico) from medico m inner join receta r on m.id=r.id_medico
                 where r.emp='$emp' and m.estado=0 and monto_medico>0 and credito=0 and r.anulado=0 and pago_medico=0 and 
				 cast(fec_crea as date)>=cast('$ini' as date) and cast(fec_crea as date)<=cast('$fin' as date) $where
                group by m.id,m.nombre,r.emp order by m.nombre asc";
    }
    if ( $tipo == 2 ) {
      $sql = "select m.nombre,t.monto,t.user_crea_ticket,FORMAT(fec_genera_ticket,'dd/MM/yyyy hh:mm tt'),user_pago_medico,
		                 case when user_pago_medico='' then '' else FORMAT(fec_user_pago,'dd/MM/yyyy hh:mm tt') end fec_anula
		 ,nro_ticket,t.id_receta,cast(round(monto_sis_anterior,1)as decimal(10,2))mon_ant,FORMAT(fec_inicio,'dd/MM/yyyy'),
		 FORMAT(fec_fin,'dd/MM/yyyy'),emp,
		 t.monto_redondeado,t.redondeo,t.monto_redondeado+round(monto_sis_anterior,1) mon_total
						 from ticket_pago t left join medico m on t.id_medico=m.id
		                     where t.emp='$emp' and cast(fec_genera_ticket as date)>=cast('$ini' as date) and cast(fec_genera_ticket as date)<=cast('$fin' as date) $where 
		 order by fec_genera_ticket desc ";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarPagosCaja( $idmed ) {
    if ( $idmed == 0 ) {
      $where = "";
    }
    if ( $idmed > 0 ) {
      $where = " and  id_medico=$idmed";
    }

    $sql = "select m.nombre,t.monto_redondeado+round(monto_sis_anterior,1),t.user_crea_ticket,
		  FORMAT(fec_genera_ticket,'dd/MM/yyyy hh:mm tt')fec,user_pago_medico,
		                 case when user_pago_medico='' then '' else FORMAT(fec_user_pago,'dd/MM/yyyy hh:mm tt') end fec_pago ,nro_ticket,t.id_receta,t.id ,t.emp,m.ruc
						 from ticket_pago t inner join medico m on t.id_medico=m.id
		               where entregado=0  $where
							 order by fec_genera_ticket desc ";
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarDetallePagos( $ini, $fin, $idmed, $emp ) {
    $ocado = new cado();
    $sql = "select r.nro_orden,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,FORMAT(r.fec_crea,'dd/MM/yyyy hh:mm tt'),monto_medico 
		            from paciente p inner join receta r on p.id=r.id_paciente
                 where r.id_medico=$idmed and  monto_medico>0 and credito=0 and r.anulado=0 and pago_medico=0 and 
				 (cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) ) and emp='$emp' ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarDetalleTicket( $idreceta ) {
    $ocado = new cado();
    $sql = "
		select r.nro_orden,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,FORMAT(r.fec_crea,'dd/MM/yyyy')fecha,
		monto_medico,e.nombre,
		CASE WHEN d.id_examen=616 then 0 else cast((d.subtotal*r.porcentaje_med)/100 as decimal(10,2))  end monto_med, e.id id_examen
		            from paciente p inner join receta r on p.id=r.id_paciente
                    inner join receta_detalle d on r.id=d.id_receta
                    inner join examen e on d.id_examen=e.id
               where r.id in($idreceta) AND r.anulado=0 order by r.fec_crea";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GenerarTickets( $ini, $fin, $id, $user, $sis_ant, $emp ) {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $cn->beginTransaction();
      $sql_ticket = "select correlativo+1 from serie where tipo_doc='TM'";
      $cmd_t = $cn->prepare( $sql_ticket );
      $cmd_t->execute();
      $dato = $cmd_t->fetch();
      $ticket = $dato[ 0 ];
      $sql_con = "select id_medico,sum(monto_medico),'$user',STRING_AGG(id,',') from  receta 
                    where  monto_medico>0 and credito=0 and pago_medico=0 and anulado=0 and 
                   cast(fec_crea as date)>=cast('$ini' as date) and cast(fec_crea as date)<=cast('$fin' as date) and id_medico=$id
                    group by id_medico";
      $cmd = $cn->prepare( $sql_con );
      $cmd->execute();
      $data = $cmd->fetch();
      $idmedico = $data[ 0 ];
      $monto = $data[ 1 ];
      $idreceta = $data[ 3 ];
      $monto_redondeado = round( $monto, 1 );
      $redondeo = $monto - $monto_redondeado;

      $sql = "insert into ticket_pago(id_medico,nro_ticket,monto,monto_redondeado,redondeo,user_crea_ticket,fec_genera_ticket,id_receta,
		   monto_sis_anterior,fec_inicio,fec_fin,emp)
    values('$idmedico',$ticket,'$monto','$monto_redondeado','$redondeo','$user',getdate(),'$idreceta',round('0',1),'$ini',
  '$fin','P')";
      //die($sql);exit;
      $cn->prepare( $sql )->execute();
      $sql_update = "update receta set pago_medico=1 where id in($idreceta)";
      $cn->prepare( $sql_update )->execute();
      $sql_serie = "update serie set correlativo=$ticket where tipo_doc='TM'";
      $cn->prepare( $sql_serie )->execute();
      $cn->commit();
      $cn = null;
      $return = 1;
    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $return = 0;
    }
    return $return;
  }

  function pagarTickets( $idticket, $nro_ticket, $monto, $user, $retencion, $pago, $dni, $des, $per, $tipo, $dref, $emp ) {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $cn->beginTransaction();
      $sql_codigo = "select codigo_ingreso from caja_fondos where activo=1 and nom_user='$user'";
      $cmd = $cn->prepare( $sql_codigo );
      $cmd->execute();
      $dato = $cmd->fetch();
      $cod = $dato[ 0 ];
      if ( strlen( $cod ) > 10 ) {
        $sql_caja_fondos = "insert into caja_fondos_detalle(codigo_ingreso,nro_orden,tipo_doc,nro_documento,tipo_pago,fec_emision,hora_emision,estado,monto_egreso,movimiento,empresa,
		  total_sin_retencion,monto_retencion,dni_egre_ingre,descripcion,persona_egreso,tipodocref_egre,docref_egre)  
values('$cod','$idticket','EG','T-$nro_ticket','$emp',getdate(),getdate(),0,'$pago','PAGO MEDICO','P','$monto','$retencion','$dni','$des','$per','$tipo','$dref');";
        $cn->prepare( $sql_caja_fondos )->execute();
        $sql = "update ticket_pago set entregado=1,user_pago_medico='$user',fec_user_pago=getdate(),retencion='$retencion',tipo_pago='008'
		  where id=$idticket";
        $cn->prepare( $sql )->execute();
        $cn->commit();
        $cn = null;
        $return = 1;
      } else {
        $cn->rollBack();
        $return = 2;
      }
    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $return = 0;
    }
    return $return;
  }

  function pagarTicketsCC( $idticket, $nro_ticket, $monto, $user, $retencion, $pago, $dni, $des, $per, $tipo, $dref, $tipopago, $codbanco, $nrocuenta ) {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      $cn->beginTransaction();
      $sql = "update ticket_pago set entregado=1,user_pago_medico='$user',fec_user_pago=getdate(),retencion='$retencion',tipo_pago='$tipopago',
		  cod_entidad_fin='$codbanco',id_nro_cuenta='$nrocuenta'
		       where id=$idticket";
      $cn->prepare( $sql )->execute();
      $cn->commit();
      $cn = null;
      $return = 1;

    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $return = 0;
    }
    return $return;
  }

  function ListarEmpConv( $emp ) {
    $ocado = new cado();
    $sql = "select c.id,case when sucursal=1 then (c.empresa+' - '+c.nom_ciudad) else c.empresa end empre,ruc,direccion,c.empresa 
		     from convenio c inner join receta r on c.id=r.id_convenio 
		      where c.tipo='C' and r.anulado=0 and r.doc_emitido=0 and r.credito=1 and c.empresa like '%$emp%'
			  group by c.id,c.sucursal,c.empresa,c.nom_ciudad,c.ruc,c.direccion";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarEmpConvDetalle( $idemp ) {
    $ocado = new cado();
    $sql = "select r.id,r.nro_orden,FORMAT(r.fec_crea,'dd/MM/yyyy hh:mm tt')fec,(ape_pat+' '+ape_mat+' '+preNombres)pac,r.total
		       from  receta r inner join paciente p on r.id_paciente=p.id
		      where r.anulado=0 and r.doc_emitido=0 and r.credito=1 and id_convenio=$idemp order by r.fec_crea asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarFact( $idemp, $arreglo ) {
    $ocado = new cado();
    $sql = "select 'POR CONCEPTOS DE ANALISIS CLINICOS',sum(r.total)
		       from  receta r inner join paciente p on r.id_paciente=p.id
		       where r.anulado=0 and r.doc_emitido=0 and r.credito=1 and id_convenio=$idemp and r.id in($arreglo)";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  /*function ListarOrden($id){
  	  $ocado=new cado();
  	  $sql="select id,nro_orden,total,examen_precio from receta where id=$id";
  	  $ejecutar=$ocado->ejecutar($sql);
  	  return $ejecutar;
    }*/
  function ListarOrden( $arreglo ) {
    $ocado = new cado();
    $sql = "select nro_orden,exa.nombre,sum(d.subtotal) 
		        from receta r inner join receta_detalle d on r.id=d.id_receta
				              inner join examen exa on d.id_examen=exa.id
		        where r.id in($arreglo)
				group by nro_orden,exa.nombre";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisExa( $idexamen ) {
    $ocado = new cado();
    $sql = "select nombre from examen where id=$idexamen";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GenerarDocGrupal() {
    $ocado = new cado();
    $sql = "select serie,REPLICATE('0',8-len(correlativo+1))+CAST(correlativo+1 as varchar)cor from serie where tipo_doc='FA' and serie='FA02' ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function RegistrarFacturacionElectronicaGrupal( $cod_operacion, $cod_dom_fiscal, $tipo_documento, $tipodoc_cli, $doc_cliente, $nomcli, $dircliente, $moneda, $op_gravadas, $op_gratuitas, $op_inafecta, $op_exoneradas, $total_descuento, $total_igv, $total_isc, $total_otrtri, $total_otrcar, $total_glosa, $valor_venta, $importe_total, $arreglo_receta, $fec_ven, $dias_credito, $user, $titulo, $idserie, $formapago, $por_det, $mon_det ) {
    try {

      $ocado = new cado();
      $cn = $ocado->conectar();
      //$bdd->setAttribute(PDO::ATTR_AUTOCOMMIT,0);
      //$cn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $cn->beginTransaction();
      //$sql_validar="select count(*) from doc_electronicos where serie";
      // echo $idserie;exit;
      $sql_correlativo = "select serie,REPLICATE('0',8-len(correlativo+1))+CAST(correlativo+1 as varchar) from serie where id='$idserie' ";
      // echo $sql_correlativo;exit;
      $cmd = $cn->prepare( $sql_correlativo );
      $cmd->execute();
      $datos = $cmd->fetch();
      //print_r($datos);die('ok');
      $sucursal = $datos[ 2 ];
      $codigo_ingreso = $datos[ 1 ];
      $serie = $datos[ 0 ];
      $correlativo = $datos[ 1 ];
      //echo $correlativo;exit;
      if ( $correlativo == '' ) {
        $cn->rollBack();
        $return = 0;
      }

      $sql = "insert into doc_electronicos(cod_operacion,fecha_emision,hora_emision,cod_dom_fiscal,tipo_documento,serie,correlativo,
		  tipodoc_cli,doc_cliente,nomcli,dircliente,moneda,op_gravadas,op_gratuitas,op_inafecta,op_exoneradas,total_descuento,total_igv,
	total_isc,total_otrtri,total_otrcar,total_glosa,valor_venta,importe_total,created_at,id_cliente,grupal,titulo_gratuito,emp,forma_pago,fecha_pago,porcentaje_detra,monto_detraido)  	  values('$cod_operacion',getdate(),getdate(),'$cod_dom_fiscal','$tipo_documento','$serie','$correlativo','$tipodoc_cli','$doc_cliente','$nomcli',
	'$dircliente','$moneda','$op_gravadas','$op_gratuitas','$op_inafecta','$op_exoneradas','$total_descuento','$total_igv','$total_isc',
'$total_otrtri','$total_otrcar','$total_glosa','$valor_venta','$importe_total',getdate(),0,1,'$titulo','P',$formapago,'$fec_ven','$por_det','$mon_det');";
      //echo $sql;exit;
      $cn->prepare( $sql )->execute();
      $doc_electronico_id = $cn->lastInsertId();

      //die($doc_electronico_id);
      // 1- AUTOINCREMENTAL, 2- $doc_electronico_id ,3- Orden de Item (De acuerdo al orden en que se reciben),4-tipo_item 1(PRODUCTO ') 2(SERVICIO) NOSOTROS UTILIZAREMOS 2
      // 5- cod_afecta_igv(UTILIZAREMOS 10), 6- unidad_medida(ZZ=SERVICIO), 7- cantidad (POR DEFECTO 1), 8- descripcion_item (NOMBRE DEL SERVICIO), 9- cod_interno (IDEXAMEN)
      // 10- COD_SUNAT (POR EL MOMENTO NULL), 11- valuni(VALOR UNITARIO SIN DESCUENTO ni impuestos), 12- VALDES (VALOR DEL DESCUENTO POR DEFECTO NULL), 13- VALIGV(VALOR DEL IGV)
      // 14- PREUNI(precio unitario (con dscto e impuestos)), 15- valven(Valor de Venta de Item (cantidad * valor uni))
      // 16- tipo_isc (null por defecto) , 17- valisc(null por defecto) , 18- created_at(fecha hora del sistema), 19- updated_at(null por defecto)

      $sql_igv = "select cast(valor/100 as decimal(18,2)) from tasas where nombre='IGV'";
      $cmd_igv = $cn->prepare( $sql_igv );
      $cmd_igv->execute();
      $data = $cmd_igv->fetch();
      $tasa_igv = $data[ 0 ];
      // Falta para detallado
      $preuni = $importe_total;
      $valuni = round( $importe_total / ( 1 + $tasa_igv ), 2 );
      $valigv = $importe_total - $valuni;
      $valven = $valuni;
      if ( $titulo == 0 ) {
        $cod_afec = 10;
      }
      if ( $titulo == 1 ) {
        $cod_afec = 11;
      }
      $sql_detalle = "INSERT INTO doc_electronico_items (doc_electronico_id,nro_orden,tipo_item,cod_afecta_igv,unidad_medida, cantidad, descripcion_item,cod_interno,valuni,valigv,preuni,valven,created_at) 
		       VALUES ('$doc_electronico_id', 1, 2, '$cod_afec', 'ZZ', 1, 'POR CONCEPTOS DE ANALISIS CLINICOS','','$valuni','$valigv', '$preuni', '$valuni', getdate());";
      $cn->prepare( $sql_detalle )->execute();

      $idreceta = implode( ',', $arreglo_receta );
      $sql_cc_corrientes = "insert into cuenta_corriente(doc_electronico_id,id_receta,tipo_doc,nro_serie,nro_documento,total_venta,fec_emision,	hora_emision,fec_vencimiento,
		  dias_credito,saldo_total,estado,user_crea)
		  values($doc_electronico_id,'$idreceta','FA','$serie','$correlativo','$importe_total',getdate(),getdate(),'$fec_ven','$dias_credito','$importe_total',0,'$user')";
      //die($importe_total);
      $cn->prepare( $sql_cc_corrientes )->execute();
      $sql_update = "update serie set correlativo='$correlativo' where serie='$serie'";
      $cn->prepare( $sql_update )->execute();
      for ( $x = 0; $x < count( $arreglo_receta ); $x++ ) {
        $sql_doc = "update receta set doc_emitido=1 where id=" . $arreglo_receta[ $x ];
        $cn->prepare( $sql_doc )->execute();
      }
      if ( $titulo == 0 ) {
        $op_gravadas = $valuni;
        $op_gratuitas = 0.00;
        $igvtotal = $valigv;
        $igvgratuitas = 0.00;
      }
      if ( $titulo == 1 ) {
        $op_gravadas = 0.00;
        $op_gratuitas = $valuni;
        $igvtotal = 0.00;
        $igvgratuitas = $valigv;
      }
      $sql_totales = "update doc_electronicos set total_igv=$igvtotal,op_gravadas=$op_gravadas,valor_venta=$valuni,op_gratuitas=$op_gratuitas,
	 total_igv_gratuitas=$igvgratuitas
		           where id=$doc_electronico_id;";
      $cn->prepare( $sql_totales )->execute();
      $cn->commit();
      $cn = null;
      file_get_contents( "http://localhost/vital/FactElect/firmar/" . $doc_electronico_id );
      $return = 1;

    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $return = 0;
      //return $ex->getMessage();
    }
    return $return;
  }

  function FacturaDetalle( $serie, $correlativo ) {
    $ocado = new cado();
    $sql = "select doc_cliente,nomcli,FORMAT(d.fecha_emision,'dd/MM/yyyy')fecha,FORMAT(cast(d.hora_emision as datetime),N'hh:mm:ss')hora,id_receta,d.serie,d.correlativo 
	       from doc_electronicos d inner join cuenta_corriente c on d.id=c.doc_electronico_id
		    where d.serie='$serie' and d.correlativo='$correlativo'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function DetalleReceta( $id ) {
    $ocado = new cado();
    $sql = "select r.id,r.nro_orden,FORMAT(r.fec_crea,'dd/MM/yyyy hh:mm tt')fec,(ape_pat+ ' '+ape_mat+' '+preNombres)pac,examen_precio,total
		      from receta r inner join paciente p on r.id_paciente=p.id
		       where r.id=$id ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function VerExamenDetalle( $id ) {
    $ocado = new cado();
    $sql = "select id,nombre from examen where id=$id ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  
  function GraficoVentas( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select format(tab.Fecha,'dd/MM/yyyy')Emision, concat('D',tab.dia)dia,isnull(tab2.Efectivo,0)ingresos_efectivo,isnull(tab2.Tarjeta,0)tarjeta,
isnull(tab2.deposito,0)deposito,isnull(tab3.egreso,0)egreso,isnull(Nota_credito,0)nota_credito
,(isnull(tab2.Efectivo,0) +isnull(tab2.Tarjeta,0) + isnull(tab2.deposito,0)+isnull(tab4.Credito_sin_efectivo,0) - isnull(tab3.egreso,0) - isnull(Nota_credito,0)) total,isnull(tab4.Credito_sin_efectivo,0) Credito_sin_efectivo
		   
		   from (
		   select cast(fec_crea as date)Fecha,day(fec_crea)dia
		   from receta where year(fec_crea)=$anio and month(fec_crea)=$mes and anulado=0 
		   group by day(fec_crea),cast(fec_crea as date) ) as tab

		   left join 
		   (
		   select day(cfd.fec_emision)dia,sum(cfd.monto_efectivo)Efectivo,sum(cfd.monto_tarjeta)Tarjeta,sum(cfd.monto_deposito+monto_yape)deposito
		   from receta r inner join caja_fondos_detalle cfd on r.nro_orden=cfd.nro_orden
		   where year(cfd.fec_emision)=$anio and month(cfd.fec_emision)=$mes and anulado=0 and credito=0 and cfd.estado=0 
		   group by day(cfd.fec_emision) ) as tab2 on tab.dia=tab2.dia

		   left join
		   (
		   select day(cfd.fec_emision)dia,sum(cfd.monto_egreso)egreso
		   from caja_fondos_detalle cfd inner join caja_fondos f on cfd.codigo_ingreso=f.codigo_ingreso
		   inner join caja c on f.id_caja=c.id and c.tipo=0
		   where year(cfd.fec_emision)=$anio and month(cfd.fec_emision)=$mes and cfd.estado=0 
           group by day(cfd.fec_emision)
           ) as  tab3 on tab.dia=tab3.dia

		   left join
		   (
		   select day(r.fec_crea)dia,sum(r.total) Credito_sin_efectivo
		   from receta r where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and anulado=0 and credito=1  group by day(r.fec_crea) 
           ) as tab4 on tab.dia=tab4.dia

		   left join
		   (
		   select day(fecha_emision)dia,sum(importe_total) Nota_credito
		   from doc_electronicos  where year(fecha_emision)=$anio and month(fecha_emision)=$mes and estado=0 and tipo_documento='07' 
		   group by day(fecha_emision) ) as tab5 on tab.dia=tab5.dia order by format(tab.Fecha,'dd/MM/yyyy')";
    } else {
      $sql = "select 
	   tab.mes, tab.dia, '' mes,coalesce(tab2.Efectivo,0)ingresos_efectivo,coalesce(tab2.Tarjeta,0)tarjeta,coalesce	(tab3.Credito_sin_efectivo,0)credito_sin_dinero,coalesce(tab4.Credito_con_efectivo,0)credito_con_dinero,coalesce	(Nota_credito,0)nota_credito,(coalesce(tab2.Efectivo,0) +coalesce(tab2.Tarjeta,0) + coalesce	(tab3.Credito_sin_efectivo,0) + coalesce(tab4.Credito_con_efectivo,0) - coalesce(Nota_credito,0)) total
			   
   from (select month(fec_crea)mes,CASE month(fec_crea)
   WHEN '1' THEN 'ENE' WHEN '2' THEN 'FEB' WHEN '3' THEN 'MAR' WHEN '4' THEN 'ABR' WHEN '5' THEN 'MAY' WHEN '6' THEN 'JUN' 
   WHEN '7' THEN 'JUL' WHEN '8' THEN 'AGO' WHEN '9' THEN 'SET' WHEN '10' THEN 'OCT' WHEN '11' THEN 'NOV' ELSE 'DIC'		END AS dia
   from receta where year(fec_crea)=$anio and anulado=0 and emp='P' group by month(fec_crea) ) as tab
   
			   left join 
			   (
			   select month(cfd.fec_emision)mes,sum(cfd.monto_efectivo)Efectivo,sum(cfd.monto_tarjeta)Tarjeta
			   from receta r inner join caja_fondos_detalle cfd on r.nro_orden=cfd.nro_orden
			   where year(cfd.fec_emision)=$anio and anulado=0 and credito=0 and cfd.estado=0 
			   group by month(cfd.fec_emision) ) as tab2 on tab.mes=tab2.mes
   
			   left join
			   (
			   select month(r.fec_crea)mes,sum(r.total) Credito_sin_efectivo
			   from receta r where year(r.fec_crea)=$anio and anulado=0 and credito=1  and ingreso_dinero=0   and id_convenio<>'184' group by month(r.fec_crea) ) as tab3 on tab.mes=tab3.mes
   
			   left join
			   (
			   select month(r.fec_crea)mes,sum(r.total) Credito_con_efectivo
			   from receta r where year(r.fec_crea)=$anio and anulado=0 and credito=1  and ingreso_dinero=1   group by month(r.fec_crea) ) as tab4 on tab.mes=tab4.mes
   
			   left join
			   (
			   select month(fecha_emision)mes,sum(importe_total) Nota_credito
			   from doc_electronicos  where year(fecha_emision)=$anio and estado=0 and tipo_documento='07' 
			   group by month(fecha_emision) ) as tab5 on tab.mes=tab5.mes order by tab.mes asc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoVentasInnova( $emp, $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT FORMAT(cfd.fec_emision,'dd/MM/yyyy')emision,('D'+cast(day(cfd.fec_emision) as varchar)) dia,
		sum(cfd.total_venta-cfd.monto_egreso) total,sum(cfd.total_venta)ingresos,sum(cfd.monto_egreso)egresos
		 FROM caja_fondos_detalle cfd inner join caja_fondos cf on cfd.codigo_ingreso=cf.codigo_ingreso
		                                inner join caja c on cf.id_caja=c.id and c.tipo=0 
          where year(cfd.fec_emision)=$anio and month(cfd.fec_emision)=$mes and cfd.empresa='$emp' and cfd.estado=0
           group by fec_emision";
    } else {
      $sql = "SELECT month(cfd.fec_emision)mes,('M'+cast(month(cfd.fec_emision) as varchar)) dia,sum(cfd.total_venta-cfd.monto_egreso) total,
		sum(cfd.total_venta)ingresos,sum(cfd.monto_egreso)egresos
		 FROM caja_fondos_detalle cfd inner join caja_fondos cf on cfd.codigo_ingreso=cf.codigo_ingreso
		                                inner join caja c on cf.id_caja=c.id and c.tipo=0 
          where year(cfd.fec_emision)=$anio and cfd.empresa='$emp' and cfd.estado=0
           group by month(cfd.fec_emision)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ProduccionTotal( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT case when empresa='P' then 'PRECISA' ELSE 'INNOVA' end empresa,sum(total_venta) total
		          FROM caja_fondos_detalle cfd inner join caja_fondos cf on cfd.codigo_ingreso=cf.codigo_ingreso
		                                inner join caja c on cf.id_caja=c.id and c.tipo=0
                  where year(cfd.fec_emision)=$anio and month(cfd.fec_emision)=$mes and cfd.estado=0
                  group by empresa";
    } else {
      $sql = "SELECT case when empresa='P' then 'PRECISA' ELSE 'INNOVA' end empresa,sum(total_venta) total
		          FROM caja_fondos_detalle cfd inner join caja_fondos cf on cfd.codigo_ingreso=cf.codigo_ingreso
		                                inner join caja c on cf.id_caja=c.id and c.tipo=0
                  where year(cfd.fec_emision)=$anio and cfd.estado=0
                  group by empresa";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoExamenesPre() {
    $ocado = new cado();
    $sql = "SELECT GROUP_CONCAT(examen_precio SEPARATOR ';') FROM `caja_fondos_detalle` c inner join receta r on c.nro_orden=r.nro_orden 
          where year(fec_emision)=year(getdate()) and month(fec_emision)=month(getdate()) and empresa='P'
           group by year(fec_emision)=year(getdate()), month(fec_emision)=month(getdate())";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ReporteProMed( $inicio, $fin, $empresa, $orden ) {
    if ( $orden == 'MED' ) {
      $filtro = '';
    }
    if ( $orden == 'CAN' ) {
      $filtro = 't.total desc,';
    }
    $ocado = new cado();
    $sql = "select me.*,t.total from 
		      (select m.id idmed,m.nombre med,d.id_examen,e.nombre,sum(cantidad)  can  from receta r inner join receta_detalle d on r.id=d.id_receta 
		                                       inner join medico m on r.id_medico=m.id
											   inner join examen e on d.id_examen=e.id
			  where (cast(r.fec_crea as date) >=cast('$inicio' as date) and cast(r.fec_crea as date)<=cast('$fin' as date)) and r.anulado=0 and emp='$empresa'
			  group by m.id,m.nombre,d.id_examen,e.nombre)  me
			    inner join
			  (select re.id_medico,sum(cantidad) as total from receta re inner join receta_detalle d on re.id=d.id_receta 						
			  where (cast(re.fec_crea as date)>=cast('$inicio' as date)  and cast(re.fec_crea as date)<=cast('$fin' as date) ) and re.anulado=0 and emp='$empresa' 
			  group by re.id_medico)  t on me.idmed=t.id_medico
			  order by $filtro me.med asc,me.nombre  asc ";

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ReporteComMed( $inicio, $fin, $empresa, $orden ) {
    if ( $orden == 'MED' ) {
      $filtro = 't.nombre asc';
    }
    if ( $orden == 'CAN' ) {
      $filtro = 't.tot desc';
    }
    $ocado = new cado();
    $sql = "select * from 
		      (select m.id,m.nombre,sum(total) tot,porcentaje_med   from receta r  inner join medico m on r.id_medico=m.id                           
			  where (cast(r.fec_crea as date)>=cast('$inicio' as date) and cast(r.fec_crea as date)<=cast('$fin' as date)) and r.anulado=0 and emp='$empresa'
			  group by m.id,m.nombre,porcentaje_med ) as t
			  order by $filtro ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ReporteProExa( $inicio, $fin, $empresa, $orden ) {
    if ( $orden == 'EXA' ) {
      $filtro = 't.nombre asc';
    }
    if ( $orden == 'CAN' ) {
      $filtro = 't.can desc';
    }
    if ( $orden == 'TOT' ) {
      $filtro = 't.total desc';
    }
    if ( $empresa == 'P' ) {
      $convenio = 'and r.id_convenio<>145';
    }
    if ( $empresa == 'I' ) {
      $convenio = '';
    }
    if ( $empresa == 'L' ) {
      $empresa = 'P';
      $convenio = ' and r.id_convenio=145';
    }
    $ocado = new cado();
    $sql = "select * from 
		      (select e.id,e.nombre,sum(cantidad) can,sum(d.subtotal) total   from receta r  
			     inner join receta_detalle d on r.id=d.id_receta 
			     inner join examen e on d.id_examen=e.id                          
			  where (cast(r.fec_crea as date)>=cast('$inicio' as date) and cast(r.fec_crea as date)<=cast('$fin' as date)) and r.anulado=0 and emp='$empresa'  $convenio
			  group by e.id,e.nombre ) as t
			  order by $filtro ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function encoded( $ses ) {
    $sesencoded = $ses;
    $num = mt_rand( 4, 4 );
    for ( $i = 1; $i <= $num; $i++ ) {
      $sesencoded = base64_encode( $sesencoded );
    }

    $alpha_array = array( 'Y', 'D', 'U', 'R', 'P', 'S', 'B', 'M', 'A', 'T', 'H' );
    $sesencoded =
      $sesencoded . "+" . $alpha_array[ $num ];
    $sesencoded = base64_encode( $sesencoded );
    return $sesencoded;
  } //end of encoded function  

  function decoded( $str ) {
    $alpha_array =
      array( 'Y', 'D', 'U', 'R', 'P',
        'S', 'B', 'M', 'A', 'T', 'H' );
    $decoded = base64_decode( $str );
    //list($decoded,$letter) =  split("\+",$decoded); 
    list( $decoded, $letter ) = explode( "+", $decoded );
    for ( $i = 0; $i < count( $alpha_array ); $i++ ) {
      if ( $alpha_array[ $i ] == $letter )
        break;
    }
    for ( $j = 1; $j <= $i; $j++ ) {
      $decoded =
        base64_decode( $decoded );
    }
    return $decoded;
  }

  function LisDetalleDocWeb( $idreceta ) {
    $ocado = new cado();
    $sql = "select r.id_examen,e.nombre,e.especial,r.estado 
		      from receta_detalle r inner join examen e on r.id_examen=e.id where r.id_receta=$idreceta
			  order by e.especial asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarHoras( $idarea, $idpersonal, $inicio, $fin ) {
    $sql = "exec AdmReporteHoras ?,?,?,? ";
    $ocado = new cado();
    $ejecutar = $ocado->EjecutarPA( $sql, array( $idarea, $idpersonal, $inicio, $fin ) );
    return $ejecutar;
  }

  function ListarHorasLider( $idpersonal, $inicio, $fin ) {
    if ( $idpersonal == 0 ) {
      $where = '';
    } else {
      $where = " and id_user='$idpersonal'";
    }
    $sql = "select persona,turno,format(entrada,'dd/MM/yyyy hh:mm tt') entrada,format(salida,'dd/MM/yyyy hh:mm tt') salida,idper,
TIMESTAMPDIFF(MINUTE, entrada,salida)diferencia 
from (
select nombre persona,'turno' turno,fecha_marcacion entrada,
(select top 1 case when a2.tipo='S' then fecha_marcacion else 'ERROR DE MARCACION' end  
from asistencia a2 where a2.id_user=a.id_user and a2.id>a.id )salida,
id_user idper
from asistencia a
where tipo='E' and cast(fecha_marcacion as date)>=cast('$inicio' as date) and cast(fecha_marcacion as date)<=cast('$fin' as date) $where
order by id_user asc,fecha_marcacion asc
) as t";
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function VerDetalleFactConv( $arreglo ) {
    $sql = "SELECT r.nro_orden,FORMAT(r.fec_crea,'dd/MM/yyyy hh:mm tt') fecha,(ape_pat+' '+ape_mat+' '+preNombres)paciente,
	  r.total,exa.nombre,d.subtotal
               FROM receta r inner join receta_detalle d on r.id=d.id_receta
            inner join paciente p on r.id_paciente=p.id 
			inner join examen exa on d.id_examen=exa.id
			where r.id in($arreglo)
			order by r.fec_crea asc";
    $ocado = new cado();
    $ejecutar = $ocado->Ejecutar( $sql );
    return $ejecutar;
  }

  function Covid() {
    $sql = "SELECT r.nro_orden,d.cantidad,'SARS-COV-2' exam,
		concat(ape_pat,' ',ape_mat,' ',preNombres ) pac,
		case when id_convenio=145 then 'LIDER MEDICA' ELSE 
		CASE WHEN empresa='PARTICULAR' THEN 'PRECISA' ELSE empresa end END EMPRESA,r.id id_re
		FROM `receta` r inner join receta_detalle d on r.id=d.id_receta
		inner join paciente p on r.id_paciente=p.id
		inner join convenio c on r.id_convenio=c.id
		where r.anulado=0 and d.id_examen=616 and date(r.fec_crea)>='2020-06-18' ";
    $ocado = new cado();
    $ejecutar = $ocado->Ejecutar( $sql );
    return $ejecutar;
  }

  function fechaCastellano( $fecha ) {
    $fecha = substr( $fecha, 0, 10 );
    $numeroDia = date( 'd', strtotime( $fecha ) );
    $dia = date( 'l', strtotime( $fecha ) );
    $mes = date( 'F', strtotime( $fecha ) );
    $anio = date( 'Y', strtotime( $fecha ) );
    $dias_ES = array( "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo" );
    $dias_EN = array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" );
    $nombredia = str_replace( $dias_EN, $dias_ES, $dia );
    $meses_ES = array( "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre" );
    $meses_EN = array( "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" );
    $nombreMes = str_replace( $meses_EN, $meses_ES, $mes );
    return $nombredia . " " . $numeroDia . " de " . $nombreMes . " de " . $anio;
  }

  function fechaCastellano1( $fecha ) {
    $fecha = substr( $fecha, 0, 10 );
    $numeroDia = date( 'd', strtotime( $fecha ) );
    $dia = date( 'l', strtotime( $fecha ) );
    $mes = date( 'F', strtotime( $fecha ) );
    $anio = date( 'Y', strtotime( $fecha ) );
    $dias_ES = array( "LUNES", "MARTES", "MIERCOLES", "JUEVES", "VIERNES", "SABADO", "DOMINGO" );
    $dias_EN = array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" );
    $nombredia = str_replace( $dias_EN, $dias_ES, $dia );
    $meses_ES = array( "ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE" );
    $meses_EN = array( "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" );
    $nombreMes = str_replace( $meses_EN, $meses_ES, $mes );
    return $nombredia . " " . $numeroDia . " DE " . $nombreMes . " DE " . $anio;
  }

  function CabeceraDoc( $id ) {
    $sql = "select id,concat(FORMAT(fecha_emision,'dd/MM/yyyy'),' ',FORMAT(hora_emision,'hh:mm tt')) fecha,
	 tipo_documento,serie,correlativo,tipodoc_cli,
 doc_cliente,nomcli,dircliente,moneda,cast(op_gravadas as decimal(10,2))op_gravadas,cast(total_igv as decimal(10,2))total_igv,
 cast(importe_total as decimal(10,2))importe_total,firma_digital,titulo_gratuito,FORMAT(fecha_emision,'yyyy-MM-dd')fec,
 case when left(nrodoc_relacionado,1)='F' then 'FACTURA' ELSE 'BOLETA' END tipo_doc_ref,nrodoc_relacionado,motivo_nota,
 cast(op_gratuitas as decimal(10,2))op_gratuitas,cast(total_igv_gratuitas as decimal(10,2))total_igv_gratuitas,forma_pago,fecha_pago,monto_detraido
	   from doc_electronicos where id=$id";
    $ocado = new cado();
    $ejecutar = $ocado->Ejecutar( $sql );
    return $ejecutar;
  }

  function DetalleDoc( $id ) {
    $sql = "select cast(cantidad as decimal(10,2)) can,unidad_medida,cod_interno,descripcion_item,cast(valuni as decimal(10,2)),
	 cast(valven as decimal(10,2))
	      from doc_electronico_items where doc_electronico_id=$id";
    $ocado = new cado();
    $ejecutar = $ocado->Ejecutar( $sql );
    return $ejecutar;
  }

  function DatosPacXOrden( $id ) {
    $ocado = new cado();
    $sql = "select nro_orden,r.fec_crea,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,estado
		 from receta r inner join paciente p on r.id_paciente=p.id where r.id = $id";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisDetOrdenWebGrupales( $idreceta ) {
    $ocado = new cado();
    $sql = "select r.id_examen,e.nombre,e.especial,r.estado 
		      from receta_detalle r inner join examen e on r.id_examen=e.id where r.id_receta=$idreceta and e.especial=0";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisDetOrdenWebTodos( $idreceta ) {
    $ocado = new cado();
    $sql = "select r.id_examen,e.nombre,e.especial,r.estado 
		      from receta_detalle r inner join examen e on r.id_examen=e.id where r.id_receta=$idreceta ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisDetOrdenWebIndividual( $idreceta ) {
    $ocado = new cado();
    $sql = "select r.id_examen,e.nombre,e.especial,r.estado,archivo_resultado
		      from receta_detalle r inner join examen e on r.id_examen=e.id where r.id_receta=$idreceta and e.especial=1";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisDetOrdenWebIndividualWp( $idreceta, $arreglo ) {
    $ocado = new cado();
    /*$sql = "select r.id_examen,e.nombre,e.especial,r.estado,archivo_resultado
		      from receta_detalle r inner join examen e on r.id_examen=e.id 
			  where r.id_receta=$idreceta and e.especial=1 and e.id in($arreglo)";*/
    $sql=" select r.id_examen,e.nombre,e.especial,r.estado,archivo_resultado
		      from receta_detalle r inner join examen e on r.id_examen=e.id 
		 where r.id_receta=$idreceta and e.especial=1 and e.id in($arreglo)
         union
        select pa.id_exa_detalle,e.nombre,exa.especial,d.estado,exa.archivo_resultado
		 from receta_detalle d inner join examen e on d.id_examen=e.id
		    inner join examen_paquete pa on e.id=pa.id_examen 
			inner join examen exa on pa.id_exa_detalle=exa.id and exa.especial=1
         where id_receta=$idreceta and e.paquete=1 and pa.id_exa_detalle in($arreglo)";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisExamenesWassapt( $idreceta ) {
    $ocado = new cado();
    $sql = "select e.nombre
		      from receta_detalle r inner join examen e on r.id_examen=e.id where r.id_receta=$idreceta 
			  order by e.especial asc ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoFact( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "
     SELECT top 10 left(c.empresa,20) convenio,sum(total)total_credito	
     FROM receta r inner join convenio c on r.id_convenio=c.id 
       where year(fec_crea)=$anio and month(fec_crea)=$mes  and emp='P' and anulado=0 and credito=1 
     group by c.empresa
    order by total_credito desc";
    } else {
      $sql = " SELECT top 10 left(c.empresa,20) convenio,sum(total)total_credito	
     FROM receta r inner join convenio c on r.id_convenio=c.id 
      where year(fec_crea)=$anio  and emp='P' and anulado=0 and credito=1 
     group by c.empresa
    order by total_credito desc";
    }
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }
    //Grafico de torta que funciona en Mysql
    
  /*function GraficoFactNeto( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 concat('{ name : ',concat(\"'\",left(doc.nomcli,30),\"'\"),' , y : ',sum(cu.total_venta),' }') leyenda	
     FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and month(cu.fec_emision)=$mes and cu.estado=0 
     group by doc.nomcli
    order by sum(cu.total_venta) desc";
    } else {
      $sql = "SELECT top 10 concat('{ name : ',concat(\"'\",left(doc.nomcli,30),\"'\"),' , y : ',sum(cu.total_venta),' }') leyenda	
     FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and cu.estado=0 
     group by doc.nomcli
    order by sum(cu.total_venta) desc";
    }
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }*/
    
  function GraficoFactNeto( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 left(doc.nomcli,30) convenio,sum(cu.total_venta) total_facturado
     FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and month(cu.fec_emision)=$mes and cu.estado=0 
     group by doc.nomcli
    order by sum(cu.total_venta) desc";
    } else {
      $sql = "SELECT top 10 left(doc.nomcli,30) convenio,sum(cu.total_venta) total_facturado	
     FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and cu.estado=0 
     group by doc.nomcli
    order by sum(cu.total_venta) desc";
    }
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoFactOrd( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 left(doc.nomcli,20)lab,sum(len(id_receta)-len(replace(id_receta,',',''))+1) can	
                  FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
                  where year(cu.fec_emision)=$anio and month(cu.fec_emision)=$mes and cu.estado=0 
                  group by nomcli order by can DESC";
    } else {
      $sql = "SELECT top 10 left(doc.nomcli,20)lab,sum(len(id_receta)-len(replace(id_receta,',',''))+1) can	
                  FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
                  where year(cu.fec_emision)=$anio and cu.estado=0
                  group by nomcli order by can DESC";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoProExa( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 left(e.nombre,30) exa,sum(cantidad) can
			      from receta r  inner join receta_detalle d on r.id=d.id_receta 
			       inner join examen e on d.id_examen=e.id                          
			       where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P'  
			      group by e.nombre order by can desc";
    } else {
      $sql = "select top 10 left(e.nombre,30) exa,sum(cantidad) can from receta r  
			       inner join receta_detalle d on r.id=d.id_receta 
			       inner join examen e on d.id_examen=e.id                          
			       where year(r.fec_crea)=$anio and r.anulado=0 and emp='P'  
			      group by e.nombre order by can desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoProExaTotal( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 left(e.nombre,30) exa,sum(d.subtotal) tot
			      from receta r  inner join receta_detalle d on r.id=d.id_receta 
			       inner join examen e on d.id_examen=e.id                          
			       where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P'  
			      group by e.nombre order by tot desc";
    } else {
      $sql = "select top 10 left(e.nombre,30) exa,sum(d.subtotal) tot from receta r  
			       inner join receta_detalle d on r.id=d.id_receta 
			       inner join examen e on d.id_examen=e.id                          
			       where year(r.fec_crea)=$anio  and r.anulado=0 and emp='P'  
			      group by e.nombre order by tot desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoProExaParCon( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "
select exa,coalesce((select sum(d.subtotal)  from receta r  inner join receta_detalle d on r.id=d.id_receta                          
           where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P' and  d.id_examen=tbl.id and credito=0),0) part,
          coalesce((select sum(d.subtotal)  from receta r  inner join receta_detalle d on r.id=d.id_receta                          
           where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P' and  d.id_examen=tbl.id and credito=1),0) conv 
from (			
	select top 10 e.id,left(e.nombre,30) exa,sum(d.subtotal) tot
	from receta r  inner join receta_detalle d on r.id=d.id_receta 
		inner join examen e on d.id_examen=e.id                          
	where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P'  
		group by e.id,e.nombre order by tot desc) as tbl";
    } else {
      $sql = "select exa,coalesce((select sum(d.subtotal)  from receta r  inner join receta_detalle d on r.id=d.id_receta                          
           where year(r.fec_crea)=$anio and r.anulado=0 and emp='P' and  d.id_examen=tbl.id and credito=0),0) part,
          coalesce((select sum(d.subtotal)  from receta r  inner join receta_detalle d on r.id=d.id_receta                          
           where year(r.fec_crea)=$anio and r.anulado=0 and emp='P' and  d.id_examen=tbl.id and credito=1),0) conv 
from (			
select top 10 e.id,left(e.nombre,30) exa,sum(d.subtotal) tot
from receta r  inner join receta_detalle d on r.id=d.id_receta 
inner join examen e on d.id_examen=e.id                          
where year(r.fec_crea)=$anio  and r.anulado=0 and emp='P'  
group by e.id,e.nombre order by tot desc) as tbl; ";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoProMed( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 left(m.nombre,30) nombre,sum(cantidad) as total from receta re inner join receta_detalle d on re.id=d.id_receta 	
                     inner join medico m on re.id_medico=m.id
			       where year(re.fec_crea)=$anio  and month(re.fec_crea)=$mes and re.anulado=0 and emp='P' 
			      group by m.nombre order by total desc";
    } else {
      $sql = "select top 10 left(m.nombre,30) nombre,sum(cantidad) as total from receta re inner join receta_detalle d on re.id=d.id_receta 	
                     inner join medico m on re.id_medico=m.id
			       where year(re.fec_crea)=$anio  and re.anulado=0 and emp='P' 
			      group by m.nombre order by total desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoComisionMedPre( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 m.nombre,cast(sum(total*porcentaje_med/100) as decimal(12,2)) tot   
			     from receta r  inner join medico m on r.id_medico=m.id                           
			     where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='P'
			  group by m.nombre order by tot desc";
    } else {
      $sql = "select top 10 m.nombre,cast(sum(total*porcentaje_med/100) as decimal(12,2)) tot   
			     from receta r  inner join medico m on r.id_medico=m.id                           
			     where year(r.fec_crea)=$anio and r.anulado=0 and emp='P'
			  group by m.nombre order by tot desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoComisionMedInno( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 m.nombre,cast(sum(total*porcentaje_med/100) as decimal(12,2)) tot   
			     from receta r  inner join medico m on r.id_medico=m.id                           
			     where (year(r.fec_crea)=$anio and month(r.fec_crea)=$mes) and r.anulado=0 and emp='I'
			  group by m.nombre order by tot desc";
    } else {
      $sql = "select top 10 m.nombre,cast(sum(total*porcentaje_med/100) as decimal(12,2)) tot   
			     from receta r  inner join medico m on r.id_medico=m.id                           
			     where year(r.fec_crea)=$anio and r.anulado=0 and emp='I'
			  group by m.nombre order by tot desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoProUsuario( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 nom_user,sum(total_cierre_caja-fondo_inicial) total from caja_fondos c inner join caja ca on c.id_caja=ca.id
			      where year(fec_ingreso)=$anio and month(fec_ingreso)=$mes and tipo=0
				   group by nom_user order by total desc";
    } else {
      $sql = "select top 10 nom_user,sum(total_cierre_caja-fondo_inicial) total from caja_fondos c inner join caja ca on c.id_caja=ca.id
			      where year(fec_ingreso)=$anio  and tipo=0
				   group by nom_user order by total desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoUsuarioAnulacion1( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 nom_user,count(*) can 
			      from caja_fondos c inner join caja_fondos_detalle f on c.codigo_ingreso=f.codigo_ingreso
				  inner join caja ca on c.id_caja=ca.id
			      where year(fec_ingreso)=$anio and month(fec_ingreso)=$mes and f.estado=1 and tipo=0
				  group by nom_user order by can desc";
    } else {
      $sql = "select top 10 nom_user,count(*) can 
			      from caja_fondos c inner join caja_fondos_detalle f on c.codigo_ingreso=f.codigo_ingreso
				  inner join caja ca on c.id_caja=ca.id
			      where year(fec_ingreso)=$anio and f.estado=1 and tipo=0
				  group by nom_user order by can desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoUsuarioAnulacion( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "select top 10 nom_user,count(*) can 
			      from caja_fondos c  inner join caja ca on c.id_caja=ca.id
				                      inner join receta r on r.codigo_ingreso=c.codigo_ingreso
			      where year(fec_ingreso)=$anio and month(fec_ingreso)=$mes and r.anulado=1 and tipo=0
				  group by nom_user order by can desc";
    } else {
      $sql = "select top 10 nom_user,count(*) can 
			       from caja_fondos c  inner join caja ca on c.id_caja=ca.id
				                      inner join receta r on r.codigo_ingreso=c.codigo_ingreso
			      where year(fec_ingreso)=$anio and r.anulado=1 and tipo=0
				  group by nom_user order by can desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac1( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 concat('{ name : ',concat(\"'\",pac.distDireccion,\"'\"),' , y : ',count(*),' }') leyenda 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 and pac.distDireccion <>''
                  group by pac.distDireccion order by count(*) desc";
    } else {
      $sql = "SELECT top 10 concat('{ name : ',concat(\"'\",pac.distDireccion,\"'\"),' , y : ',count(*),' }') leyenda 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and r.anulado=0 and pac.distDireccion <>''
                  group by pac.distDireccion order by count(*) desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac2( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,count(*) can 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 and r.id_convenio<>8 and pac.distDireccion <>''
                  group by ape_pat,ape_mat,preNombres order by can desc";
    } else {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,count(*) can 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and r.anulado=0 and r.id_convenio<>8 and pac.distDireccion <>''
                  group by ape_pat,ape_mat,preNombres order by can desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac3( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,sum(total) tot 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0
                  group by ape_pat,ape_mat,preNombres order by tot desc";
    } else {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,sum(total) tot 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and r.anulado=0 
                  group by ape_pat,ape_mat,preNombres order by tot desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac4( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,count(*) can 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 and r.id_convenio=8 and pac.distDireccion <>''
                  group by ape_pat,ape_mat,preNombres order by can desc";
    } else {
      $sql = "SELECT top 10 concat(ape_pat,' ',ape_mat,' ',preNombres)paci,count(*) can 
			      FROM receta r inner join paciente pac on r.id_paciente=pac.id
                   where year(r.fec_crea)=$anio and r.anulado=0 and r.id_convenio=8 and pac.distDireccion <>''
                  group by ape_pat,ape_mat,preNombres order by can desc";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac5( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT concat('D',day(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 AND r.id_convenio=8
                  group by day(r.fec_crea) order by day(fec_crea)";
    } else {
      $sql = "SELECT concat('M',month(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and r.anulado=0 AND r.id_convenio=8
                  group by month(r.fec_crea) order by month(fec_crea)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac6( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT concat('D',day(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 AND r.id_convenio=24
                  group by day(r.fec_crea)";
    } else {
      $sql = "SELECT concat('M',month(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and r.anulado=0 AND r.id_convenio=24
                  group by month(r.fec_crea)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac7( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT concat('D',day(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 AND r.id_convenio=107
                  group by day(r.fec_crea) order by day(fec_crea)";
    } else {
      $sql = "SELECT concat('M',month(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and r.anulado=0 AND r.id_convenio=107
                  group by month(r.fec_crea) order by month(fec_crea)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function GraficoPac8( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT concat('D',day(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 AND convenio.tipo='C' AND r.id_convenio<>107
                  group by day(r.fec_crea) order by day(fec_crea)";
    } else {
      $sql = "SELECT concat('M',month(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
                   where year(r.fec_crea)=$anio and r.anulado=0 AND convenio.tipo='C' AND r.id_convenio<>107
                  group by month(r.fec_crea) order by month(fec_crea)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }
	
  function GraficoPac9( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT concat('D',day(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
			  where year(r.fec_crea)=$anio and month(r.fec_crea)=$mes and r.anulado=0 AND r.id_convenio=8 AND domicilio=1
			 group by day(r.fec_crea)";
    } else {
      $sql = "SELECT concat('M',month(fec_crea)) dia,count(*) can FROM receta r inner JOIN convenio on r.id_convenio=convenio.id
			  where year(r.fec_crea)=$anio and r.anulado=0 AND r.id_convenio=8 AND domicilio=1
			 group by month(r.fec_crea)";
    }
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function FacturacionConvenios( $anio, $mes ) {
    if ( $mes > 0 ) {
      $sql = "SELECT doc.nomcli,sum(cu.total_venta)
             FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and month(cu.fec_emision)=$mes and cu.estado=0 and serie='FA02' 
     group by doc.nomcli
    order by sum(cu.total_venta) desc";
    } else {
      $sql = "SELECT doc.nomcli,sum(cu.total_venta)
     FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
      where year(cu.fec_emision)=$anio and cu.estado=0  and serie='FA02'
     group by doc.nomcli
    order by sum(cu.total_venta) desc ";
    }
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ResumenParticulares( $anio ) {
    $sql = "SELECT month(cfd.fec_emision),sum(cfd.total_venta) total
		          FROM caja_fondos_detalle cfd inner join caja_fondos cf on cfd.codigo_ingreso=cf.codigo_ingreso
		                                inner join caja c on cf.id_caja=c.id and c.tipo=0
                 where year(cfd.fec_emision)=$anio and cfd.estado=0
                 group by month(cfd.fec_emision) ";
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ResumenConvenios( $anio ) {
    $sql = "SELECT month(cu.fec_emision),sum(cu.total_venta)
             FROM cuenta_corriente cu inner join doc_electronicos doc on cu.doc_electronico_id=doc.id
             where year(cu.fec_emision)>=$anio and cu.estado=0  
             group by month(cu.fec_emision)";
    $ocado = new cado();
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ComMedJova( $mes ) {
    $sql = "select top 20 m.nombre,cast(sum(total*porcentaje_med/100) as decimal(12,2)) tot   
			     from receta r  inner join medico m on r.id_medico=m.id                           
			     where date(r.fec_crea)>=2020 and  month(r.fec_crea)=$mes and r.anulado=0 and emp='P'
	             group by m.nombre order by tot desc";
    $ocado = new cado();

    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisRepDom( $ini ) {
    $ocado = new cado();
    $sql = "  select t.*,tt.can from   
(     select r.id,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,p.dni,telefono,direccion,nro_orden,total,m.nombre,r.nro_orden orden_1,
r.estado_dom,r.estado,r.obs_dom,r.fecha_entrega_dom,tipo_pago_dom,lugar_dom,tipoentregadom,turno,r.fec_crea,
datediff(YEAR,p.fec_nac,getdate())edad
	   from receta r inner join paciente p on r.id_paciente=p.id 
	                 inner join medico m on r.id_medico=m.id
	   where domicilio=1 and anulado=0 and estado_dom=0 and cast(fec_domicilio as date)=cast('$ini' as date) ) as t
   left JOIN (
        select r.id,CASE WHEN count(*)>1 THEN 1 ELSE COUNT(*) END CAN
from receta r inner join receta_detalle d on r.id=d.id_receta
                                        inner join examen e on d.id_examen=e.id 
    where domicilio=1 and anulado=0 and estado_dom=0 and cast(fec_domicilio as date)=cast('$ini' as date) and 
    e.condiciones='AYUNAS'
    group by r.id,e.condiciones) AS tt on t.id=tt.id
order by t.turno asc, tt.can desc,t.fec_crea asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisRepDom1( $ini, $fin ) {
    $ocado = new cado();
    $sql = " select t.*,tt.can from   
	  (select r.id,concat(ape_pat,' ',ape_mat,' ',preNombres)pac,p.dni,telefono,direccion,nro_orden,total,m.nombre,r.nro_orden orden_1,
	  r.estado_dom,r.estado,r.obs_dom,FORMAT(r.fecha_entrega_dom,'dd/MM/yyyy hh:mm tt') fecha_entrega_dom,
	  tipo_pago_dom,lugar_dom,tipoentregadom,turno,r.fec_crea,
	  datediff(YEAR,p.fec_nac,getdate())edad,r.fec_domicilio
			 from receta r inner join paciente p on r.id_paciente=p.id 
						   inner join medico m on r.id_medico=m.id
			 where domicilio=1 and anulado=0 and (cast(fec_domicilio as date)>=cast('$ini' as date) and 
			 cast(fec_domicilio as date)<=cast('$fin' as date)) ) as t
		 left JOIN (
			  select r.id,CASE WHEN count(*)>1 THEN 1 ELSE COUNT(*) END CAN
	  from receta r inner join receta_detalle d on r.id=d.id_receta
											  inner join examen e on d.id_examen=e.id 
		  where domicilio=1 and anulado=0 and ( cast(fec_domicilio as date)>=cast('$ini' as date) and cast(fec_domicilio as date)<=cast('$fin' as date)) and 
		  e.condiciones='AYUNAS'
		  group by r.id,e.condiciones) AS tt on t.id=tt.id
	  order by t.turno asc, tt.can desc,t.fec_crea asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisPacColas() {
    $ocado = new cado();
    $sql = "SELECT concat(p.ape_pat,' ',p.ape_mat,' ',p.preNombres) pac,c.id,llamado,ventanilla,id_receta
	        FROM cola_ticket c inner join paciente p on c.id_paciente=p.id 
            where date(fecha)=date(getdate()) and finalizado=0 order by c.id asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisPacColasTele() {
    $ocado = new cado();
    $sql = "SELECT top 11 concat(p.ape_pat,' ',p.ape_mat,' ',left(p.preNombres,1)) pac_1,llamado,ventanilla,parpadeo,
	  case when parpadeo=1 then TIMESTAMPDIFF(SECOND, hora_llamado,getdate()) else 0 end resta
	        FROM cola_ticket c inner join paciente p on c.id_paciente=p.id 
            where date(fecha)=date(getdate()) and finalizado=0 order by c.id asc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function Llamar( $id, $ventanilla ) {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->beginTransaction();
      $sql = "update cola_ticket set llamado=0,pantalla=0 where ventanilla=$ventanilla and date(fecha)=date(getdate())";
      $ejecutar = $ocado->ejecutar( $sql );
      $sql1 = "update cola_ticket set llamado=1,pantalla=1,ventanilla=$ventanilla,parpadeo=1,hora_llamado=getdate() where id=$id";
      $ejecutar = $ocado->ejecutar( $sql1 );
      $cn->commit(); //consignar cambios
      $cn = null;
      $return = 1;
    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $cn = null;
      $return = 0;
      //return $ex->getMessage();
    }
    return $return;
  }

  function FinParpadeo() {
    try {
      $ocado = new cado();
      $cn = $ocado->conectar();
      $cn->beginTransaction();
      $sql = "update cola_ticket set parpadeo=0 where date(fecha)=date(getdate()) ";
      $cn->prepare( $sql )->execute();
      $cn->commit();
      $cn = null;
      $return = 1;
    } catch ( PDOException $ex ) {
      $cn->rollBack();
      $cn = null;
      $return = 0;
      //return $ex->getMessage();
    }
    return $return;
  }

  function LisAtenSal( $ini, $fin ) {
    $ocado = new cado();
    $sql = "SELECT format(fec_emision,'dd/MM/yyyy')fecha,nro_carta,nro_solicitud,monto_carta,situacion,nro_ht,nro_documento,nro_orden,
		  (p.ape_pat+' '+p.ape_mat+' '+left(p.preNombres,1)) pac,monto_orden, case when estado=1 then 'ANULADO' else case when 
		  DATEDIFF(DAY,cast(getdate() as date), cast(fec_emision as date) ) > 30 then 'VENCIDO' ELSE 'VIGENTE' end end est,c.id
				FROM atencion_salupol c inner join paciente p on c.id_paciente=p.id 
				where cast(fec_emision as date)>=cast('$ini' as date) and cast(fec_emision as date)<=cast('$fin' as date) ";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisAtenVigentes( $ini, $fin ) {
    $ocado = new cado();
    $sql = "SELECT format(fec_emision,'dd/MM/yyyy')fecha,nro_carta,nro_solicitud,monto_carta,situacion,nro_ht,nro_documento,nro_orden,
		  (p.ape_pat+' '+p.ape_mat+' '+left(p.preNombres,1)) pac,monto_orden,dni
				FROM atencion_salupol c inner join paciente p on c.id_paciente=p.id 
				where cast(fec_emision as date)>=cast('$ini' as date) and cast(fec_emision as date)<=cast('$fin' as date) and DATEDIFF(DAY,cast(getdate() as date), cast(fec_emision as date) ) <= 30";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisAtenVencidas( $ini, $fin ) {
    $ocado = new cado();
    $sql = "SELECT format(fec_emision,'dd/MM/yyyy')fecha,nro_carta,nro_solicitud,monto_carta,situacion,nro_ht,nro_documento,nro_orden,
		  (p.ape_pat+' '+p.ape_mat+' '+left(p.preNombres,1)) pac,monto_orden,dni
				FROM atencion_salupol c inner join paciente p on c.id_paciente=p.id 
				where cast(fec_emision as date)>=cast('$ini' as date) and cast(fec_emision as date)<=cast('$fin' as date) and DATEDIFF(DAY,cast(getdate() as date), cast(fec_emision as date) ) > 30";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function LisConReactivos( $ini, $fin ) {
    $ocado = new cado();
    $sql = "SELECT (p.ape_pat+' '+p.ape_mat+' '+p.preNombres),FORMAT(r.fec_crea,'dd/MM/yyyy hh:mm tt')fecing,
DATEDIFF(minute,cast(r.fec_crea as date),cast(fec_toma_muestra as date))tiempo1,FORMAT(fec_toma_muestra,'dd/MM/yyyy hh:mm ss')fecmuestra,
DATEDIFF(minute,cast(r.fec_toma_muestra as date), cast(fecha_finalizacion as date))tiempo3,FORMAT(fecha_finalizacion,'dd/MM/yyyy hh:mm ss')fecfin
FROM receta r inner join paciente p on r.id_paciente=p.id
			where cast(r.fec_crea as date)>=cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) order by r.id desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function ListarPersonal() {
    $ocado = new cado();
    $sql = "select id,left(persona,10)nombre from trabajador_ca where estado=0 order by nombre asc";
    $ejecutar = $ocado->EjecutarSql( $sql );
    return $ejecutar;
  }

  function LisPacBalones() {
    $ocado = new cado();
    $sql = "SELECT distinct(telefono) FROM paciente WHERE lenght(telefono)=9 and id<60756 order by id desc";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function refrescarBalones() {
    $ocado = new cado();
    $sql = "update consultas_reniec set whatsap=whatsap+1 where ejercicio=2021 and mes=5";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }

  function CalcularDet( $monto ) {
    $ocado = new cado();
    $sql = "select valor,cast(round(($monto-$monto*valor/100),0) as decimal(18,2))mon_det from tasas where nombre='DETRACCION'";
    $ejecutar = $ocado->ejecutar( $sql );
    return $ejecutar;
  }
    
  function ReporteEcografia($ini,$fin){
    $ocado=new cado();
    $sql="select r.id,nro_orden,r.fec_crea,(isnull(ape_pat,'')+' '+isnull(ape_mat,'')+' '+isnull(preNombres,''))paciente,r.estado,r.id_paciente,
		    m.nombre,user_finaliza,r.user_anula,r.anulado,count(d.id) can,d.subtotal,exa.nombre
	        from receta r left join medico m on r.id_medico=m.id
			              inner join paciente p on r.id_paciente=p.id
						  inner join receta_detalle d on r.id=d.id_receta
						  inner join examen exa on d.id_examen=exa.id and exa.cod_tipo='03'
			where r.estado>0  and anulado=0 
            and (cast(r.fec_crea as date) >= cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date))
			group by r.id,nro_orden,r.fec_crea,ape_pat,ape_mat,preNombres,r.estado,r.id_paciente,m.nombre,user_finaliza,r.user_anula,r.anulado,d.subtotal,exa.nombre
			  order by r.fec_crea asc";
    $ejecutar=$ocado->ejecutar($sql);
    return $ejecutar;
  }
    
  function ReporteEcoAnatLab($ini,$fin){
    $ocado=new cado();
    $sql="select r.id,r.nro_orden,r.fec_crea,r.estado,r.id_paciente,(isnull(ape_pat,'')+' '+isnull(ape_mat,'')+' '+isnull(preNombres,''))paciente,r.id_medico,r.medico,r.id_convenio,c.empresa,r.credito,r.ingreso_dinero,r.emp,r.doc_emitido,
    r.codigo_ingreso,cf.nom_caja,cf.nom_user,r.domicilio,r.fec_domicilio,d.id_examen,e.nombre,
    iif(d.id_examen in(620,648),'EPP',g.nombre) grupo,d.precio_part,d.precio_conv,d.precio_part_desc,d.subtotal	
    from receta r
			inner join receta_detalle d on r.id=d.id_receta
			left join caja_fondos cf on r.codigo_ingreso=cf.codigo_ingreso
			inner join paciente p on p.id=r.id_paciente
			inner join convenio c on r.id_convenio=c.id
			inner join examen e on d.id_examen=e.id
			inner join grupo g on e.id_grupo=g.id
where cast(r.fec_crea as date) >= cast('$ini' as date) and cast(r.fec_crea as date)<=cast('$fin' as date) and anulado=0
and r.estado>0";
    $ejecutar=$ocado->ejecutar($sql);
    return $ejecutar;
  }
}

//

?>