<?php
//	include ('config.php');
	function StartConnectionDB(){
		$servername = "rdbms.strato.de";
		$username = "U4172970";
		$password = "cibranmolamogollon";
		$database = "DB4172970";

		// Create connection
		$conn = new mysqli($servername, $username, $password);
		mysqli_select_db($conn, $database);
		mysqli_set_charset($conn, "utf8");
		
		
	
		// Check connection
		if ($conn->connect_error) {
			die("Error de conexión: " . $conn->connect_error);
		}
		return $conn;
	}

	function CloseConnection($conn){
		mysqli_close($conn);
	}


	//include ('config.php');

	
	$conexion = StartConnectionDB();
	$WebInfo = "";
	$WebTitle = "Grassfish Computers";
	$get=false;

	$boxid="boxid";
	$dhcp="dhcp";
	$os="os";
	$net_up="netup";
	$net_dw="netdown";
	$net_la="netlatency";
	$disk_space="diskspace";
	$gfConnection="gfConnection";

	if (isset($_GET['Debug'])) $boxid=$_GET['Debug'];
	if (isset($_POST['ID'])) $boxid=$_POST['ID'];
	if (isset($_POST['DHCP'])) $dhcp=$_POST['DHCP'];
	if (isset($_POST['Disk'])) $disk_space=$_POST['Disk'];
	if (isset($_POST['NetworkDonwnload'])) $net_dw=$_POST['NetworkDonwnload'];
	if (isset($_POST['NetworkUp'])) $net_up=$_POST['NetworkUp'];
	if (isset($_POST['Latency'])) $net_la=$_POST['Latency'];
	if (isset($_POST['WinVer'])) $os=$_POST['WinVer'];
	if (isset($_POST['GfCon'])) $gfConnection=$_POST['GfCon'];

	if (isset($_GET['ID'])){ $boxid=$_GET['ID']; $get=true;}
	if (isset($_GET['DHCP'])) $dhcp=$_GET['DHCP'];
	if (isset($_GET['Disk'])) $disk_space=$_GET['Disk'];
	if (isset($_GET['NetworkDonwnload'])) $net_dw=$_GET['NetworkDonwnload'];
	if (isset($_GET['NetworkUp'])) $net_up=$_GET['NetworkUp'];
	if (isset($_GET['Latency'])) $net_la=$_GET['Latency'];
	if (isset($_GET['WinVer'])) $os=$_GET['WinVer'];
	if (isset($_GET['GfCon'])) $gfConnection=$_GET['GfCon'];

	if ($boxid == "boxid") {
		header("Location: https://cibrandocampo.es");
		exit();
	}

	function SelectQuery($conn, $tabla, $columnas, $where){
		//First we escape strings
		$tabla = mysqli_real_escape_string($conn, $tabla);
		$column  = explode(',', $columnas);
		$i = 0;

		foreach($column as $una){
			$column[$i] = mysqli_real_escape_string($conn, $una);
			$i++;
		}

		$columnas_escp = "";
		for ($i=0;$i<sizeof($column); $i++){
			if ($i > 0){
				$columnas_escp .= ", ";
			}
			$columnas_escp .= $column[$i];
		}
		if ($where != ""){
			$where = ' WHERE '. $where;
		}
		$consulta = 'SELECT ' . $columnas_escp . ' FROM ' . $tabla . $where;


		//echo "<br>".$consulta."<br>";

		$resultado = mysqli_query($conn, $consulta);
		//		print_r($resultado);
		if ($resultado != false && $resultado != ""){
			$i = 0;
			while ($row = mysqli_fetch_array($resultado, MYSQLI_ASSOC)){
				$devuelvo[$i] = $row;
				$i++;
			}
			
			return $devuelvo;
		} else {
			return $resultado;
		}
	}

	function DoesThisExists($conex, $tabla, $columna, $valor) : bool{
		$consulta = "SELECT * FROM " . $tabla . " WHERE " . $columna . " = '" . $valor ."'";;
		//		echo ($consulta);
		mysqli_query($conex, $consulta);
		if (mysqli_affected_rows($conex) > 0){
		return true;
		} else {
			return false;
		}
	}

	if (DoesThisExists($conexion, "computers_table", "Box_id", mysqli_real_escape_string($conexion,$boxid))){

		$sql_query = "UPDATE `computers_table` SET `Dhcp`='". mysqli_real_escape_string($conexion,$dhcp)."', `Op_Sis`='". mysqli_real_escape_string($conexion,$os)."', `Netw_Upl`='". mysqli_real_escape_string($conexion,$net_up)."', `Netw_Down`='". mysqli_real_escape_string($conexion,$net_dw)."', `Netw_Latency`='". mysqli_real_escape_string($conexion,$net_la)."', `Disk_Space`='". mysqli_real_escape_string($conexion,$disk_space)."', `GfConnection`='". mysqli_real_escape_string($conexion,$gfConnection)."',`Date`=CURRENT_TIMESTAMP() WHERE `Box_id` = '".mysqli_real_escape_string($conexion,$boxid)."'";
	} else {
		$sql_query = "INSERT INTO `computers_table` (`Box_id`, `Dhcp`, `Op_Sis`, `Netw_Upl`, `Netw_Down`, `Netw_Latency`, `Disk_Space`, `GfConnection`) VALUES ('". mysqli_real_escape_string($conexion,$boxid)."','". mysqli_real_escape_string($conexion,$dhcp)."','". mysqli_real_escape_string($conexion,$os)."','". mysqli_real_escape_string($conexion,$net_up)."','". mysqli_real_escape_string($conexion,$net_dw)."','". mysqli_real_escape_string($conexion,$net_la)."','". mysqli_real_escape_string($conexion,$disk_space)."','". mysqli_real_escape_string($conexion,$gfConnection)."')";
	}

	echo "Following information will be added to the database: \r\n <br>";
	echo "BOX-ID: $boxid \r\n <br>";
	echo "DHCP: $dhcp \r\n <br>";
	echo "Operative system: $os \r\n <br>";
	echo "Network Download: $net_dw \r\n <br>";
	echo "Network Upload: $net_up \r\n <br>";
	echo "Network Latency: $net_la \r\n <br>";
	echo "Disk Space (Mb): $disk_space \r\n <br>";
	echo "Grassfish connection: $gfConnection \r\n <br>";
	echo $sql_query;
	
	mysqli_query($conexion, $sql_query);
	CloseConnection($conexion);
	
?>