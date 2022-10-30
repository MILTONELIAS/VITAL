<?php

require_once('conexion.php');
class Ubigeo{
   
     function Listar_departamento(){
	  $ocado=new cado();
	  $sql="select * from ubigeo_peru_departments";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function nombre_departamento($id){
	    $ocado=new cado();
	    $sql="select name from ubigeo_peru_departments where id=$id  ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function Listar_provincia($departamento){
	  $ocado=new cado();
	  $sql="select * from ubigeo_peru_provinces p inner join ubigeo_peru_departments d on p.department_id=d.id where d.id=$departamento  ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function nombre_provincia($id){
	    $ocado=new cado();
	    $sql="select name from ubigeo_peru_provinces where id=$id  ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function Listar_distrito($provincia){
	  $ocado=new cado();
	  $sql="select * from ubigeo_peru_districts d inner join ubigeo_peru_provinces p on d.province_id=p.id  where  p.id=$provincia ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	function nombre_distrito($id){
	    $ocado=new cado();
	    $sql="select name from ubigeo_peru_districts where id=$id  ";
	  $ejecutar=$ocado->ejecutar($sql);
	  return $ejecutar;
	}
	
	
}