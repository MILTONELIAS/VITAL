<?php 
 class cado{
	  function conectar(){
	   try {
		//date_default_timezone_set('America/Lima');
	   $db = new PDO('mysql:host=localhost;dbname=bdlab','root','PreDiag%%2019$$');
	   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 return $db;
		 }catch (PDOException $e) {
			 //print "¡Error!: " . $e->getMessage();die('ok');
			 
	       echo $e->getMessage();
          }
	  }
	  function conectar2(){
	   try {
		date_default_timezone_set('America/Lima');
	  $db = new PDO('mysql:host=localhost;dbname=bdlab','root','PreDiag%%2019$$',array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',PDO::MYSQL_ATTR_INIT_COMMAND => "SET lc_time_names=es_PE"));
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 return $db;
		 }catch (PDOException $e) {
	       echo $e->getMessage();
          }
	  }
	   function ejecutar2($isql){
		  $conexion=$this->conectar2();
	      $ejecutar=$conexion->prepare($isql);
		  $ejecutar->execute();
		  $conexion=null;
		  return $ejecutar;
	  }
	  function ejecutar($isql){
		  $conexion=$this->conectar();
	      $ejecutar=$conexion->prepare($isql);
		  $ejecutar->execute();
		  $conexion=null;
		  return $ejecutar;
	  }
	  function ObtenerUltimoId($isql){
		  $conexion=$this->conectar();
	      $ejecutar=$conexion->prepare($isql);
		  $ejecutar->execute();
		  $ultimo_id=$conexion->lastInsertId();
		  $conexion=null;
		  return $ultimo_id;
	  } 
	  function ConectarSql(){
     	 $dns = 'sqlsrv:Database=bd_asistencia;Server=192.168.1.13';
          $user = 'sa';
        // $user = 'cp';
	     $password = 'PreDiag%%2019$$';
		//$password = 'cdp1475963**01+-+$789521';
		 /* $user = 'cp';  
         $password = 'cdp1475963**01+-+$789521'; */
		 
	   try {
		   //ini_set('mssql.charset', 'UTF-8');
	    $this->db = new PDO($dns,$user,$password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
		 return $this->db;
		 }catch (PDOException $e) {
	       echo $e->getMessage();
          }
	  }
	  function EjecutarSql($isql){
		       $conexion=$this->ConectarSql();;
			   $ejecutar=$conexion->prepare($isql,array (PDO :: ATTR_CURSOR => PDO :: CURSOR_SCROLL));
		       $ejecutar->execute();
			   $conexion=null;
		       return  $ejecutar;

	  }
	  function EjecutarPA($isql,$array_param){
	  try{     
	           $conexion=$this->ConectarSql();
			   $ejecutar=$conexion->prepare($isql,array ());
			   //$ejecutar=$this->ConectarSql()->prepare($isql,array ());
		       $ejecutar->execute($array_param);
			   $conexion=null;

	  }catch(PDOException $e){
	  echo ' - '.$e->getMessage();
	  }
		return  $ejecutar;
	}
   }
?>