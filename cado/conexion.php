<?php 
 class cado{
	 
	function ObtenerUltimoId($isql){
		  $conexion=$this->conectar();
	      $ejecutar=$conexion->prepare($isql);
		  $ejecutar->execute();
		  $ultimo_id=$conexion->lastInsertId();
		  $conexion=null;
		  return $ultimo_id;
	  } 
	function conectar(){
		//variables
		$dns ="sqlsrv:Database=bdlab_vital;Server=(local)";
		$user = 'sa';
		$password ='123456';
		try {   
			$con = new PDO($dns,$user,$password,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION) );
			return $con;
		} catch (PDOException $ex) {
			echo $ex->getMessage();
		}
	}
	//Funcion para ejecutar sentencias
	function ejecutar($isql){
		//variables
		$conexion = $this->conectar();
		$ejecutar = $conexion->prepare($isql, array(PDO :: ATTR_CURSOR => PDO :: CURSOR_SCROLL));
		$ejecutar->execute();
		$conexion = null;
		return $ejecutar;
	}

	function ConectarSql(){
		$dns = 'sqlsrv:Database=bdlab_vital;Server=(local)';
		$user = 'sa';
	  // $user = 'cp';
	   $password = '123456';
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
			 $conexion=$this->ConectarSql();
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