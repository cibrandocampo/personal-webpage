<?php

//try {include('config.php');} catch (exception $e){

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

//}
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
		// echo ($consulta);
		mysqli_query($conex, $consulta);
		if (mysqli_affected_rows($conex) > 0){
		return true;
		} else {
			return false;
		}
	}
	
	function ListComputers($conex){
		$lista = SelectQuery($conex, 'computers_table', '*', '');
		return $lista;
	}
	
	function PrintLoginForm(){
		echo '<html><head><title>Login</title></head><body><br><br><br><center>'."\r\n";
		echo '<form action="tables.php" method="post">'."\r\n";
		echo 'User: <input type="text" name="user"><br>'."\r\n";
		echo 'Password: <input type="password" name="pwd"><br>'."\r\n";
		echo '<input type="submit">'."\r\n";
		echo '</form>'."\r\n";
		echo '</center></body>';
	}
	
	
	
	if (isset($_GET['password'])){echo base64_encode(md5($_GET['password']));die();}
	
	
	$conexion = StartConnectionDB();
	$acceso = "login";
    session_start();
	
    if (isset($_GET['logoff'])){
		session_unset();
		session_destroy();
		session_start();
		header("Location: https://cibrandocampo.es/trison/tables.php");
		exit();
	}

	
	// CHECK IF THE USER ALREADY STARTED SESSION
    if (!isset ($_SESSION['session_id'])){
		$fecha = new DateTime();
		$val = $fecha->getTimestamp();
        $_SESSION['session_id'] = md5($val);
    }
//	echo $_SESSION['session_id']."<br>";
	
	$querycheck = "SELECT `user_name` FROM `Users` WHERE `session_id`='".$_SESSION['session_id']."'";

	$resultado =mysqli_query($conexion, $querycheck);
 	if ($resultado != false && $resultado != ""){
		$i = 0;
		while ($row = mysqli_fetch_array($resultado, MYSQLI_ASSOC)){
			$devuelvo[$i] = $row;
			$i++;
		}
		if ($i>0){
			$NombreUsuario = $devuelvo[0]['user_name'];
			$acceso = "tabla";
		}
	}
	//ELSE CHECK IF IT'S LOGGIN IN
	if ($acceso == "login"){
		if (isset($_POST['user']) && ($_POST['user'] != "")){
			if (isset($_POST['pwd']) && ($_POST['pwd'] != "")){
				$query_login = "SELECT `user_id`, `user_name`, `user_pass` FROM `Users` WHERE user_name = '".mysqli_real_escape_string($conexion, $_POST['user'])."'";
				$login_result =mysqli_query($conexion, $query_login);
				if ($login_result != false && $login_result != ""){
					$i = 0;
					while ($row = mysqli_fetch_array($login_result, MYSQLI_ASSOC)){
						$devuelvo[$i] = $row;
						$i++;
					}
					if ($i>0){
						if (base64_encode(md5($_POST['pwd'])) == $devuelvo[0]['user_pass']){
							$NombreUsuario = $devuelvo[0]['user_name'];
							$queryLogin="UPDATE `Users` SET `session_id`='".$_SESSION['session_id']."', `user_last_access`=CURRENT_TIMESTAMP WHERE `user_id`=".$devuelvo[0]['user_id'];
							$result = mysqli_query($conexion, $queryLogin);
							// print_r($result);
							$acceso = "tabla";
							// echo "<br>".$queryLogin;
						}
					}else echo "Please check your password <br>"."\r\n";
				}else echo "User not found <br>"."\r\n";
			}else echo "You must enter a password <br>"."\r\n";
		}
	}
	
	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}
	
	
	function ShowTable($conexion, $NombreUsuario){
		$ListaCom = ListComputers($conexion);
		echo '<html><head>'."\r\n";
		?>
		<style>
body {
	background-color: #999999;
	color: #121212;
	font-family: Calibri, "Roboto", helvetica, arial, sans-serif;
	font-size: 16px;
	font-weight: 400;
	text-rendering: optimizeLegibility;
}

table {
    display: table;
    box-sizing: border-box;
    border-spacing: 2px;
    border-color: grey;
    table-layout: fixed;
    border-collapse: collapse;
}

.table-fill {
    background: white;
    border-radius: 3px;
    border-collapse: collapse;
    height: 320px;
    margin: auto;
    padding: 5px;
    width: 100%;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
}

td {
    background: #FFFFFF;
    padding: 20px;
    text-align: left;
    vertical-align: middle;
    font-weight: 300;
    font-size: 12px;
    text-shadow: -1px -1px 1px rgba(0, 0, 0, 0.1);
    border-right: 1px solid #C1C3D1;
}

td.text-left {
    text-align: left;
}

td.error {
    text-align: left;
	color: #ff4e64;
}

tr {
    border-top: 1px solid #C1C3D1;
    border-bottom-: 1px solid #C1C3D1;
    color: #666B85;
    font-size: 12px;
    font-weight: normal;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.1);
}

tr:nth-child(odd) td {
    background: #EBEBEB;
}

th {
    color: #D5DDE5;
    background: #1b1e24;
    border-bottom: 4px solid #9ea7af;
    border-right: 1px solid #343a45;
    font-size: 18px;
    font-weight: 100;
    padding: 24px;
    vertical-align: middle;
}

th.text-center {
    text-align: center;
}

th:last-child {
    border-top-right-radius: 3px;
    border-right: none;
}

.table-fill tbody{
  display:block;
  width: 100%;
  overflow: auto;
}

.table-fill thead tr {
   display: block;
}

.table-fill thead {
  background: black;
  color:#fff;
}

.table-fill th, .table-fill td {
  padding: 5px;
  width: 200px;
}

		</style>
		<?php
		
		echo '<title>Computer Table</title></head><body>'."\r\n";
		echo "Welcome ".$NombreUsuario."\r\n <br>";
		echo '<a href="tables.php?logoff=1">Logoff</a><br>'."\r\n";
				?>
		<script>
			function myFunction(boxid, computerid) {
			  var txt;
			  var r = confirm("Do you really want to delete the computer " + boxid);
			  if (r == true) {
				window.location.href = "./tables.php?action=delete&c_id=" + computerid;
			  }
			}
		</script>
		
		<?php
		
		
		echo "<center><table width=100% class=\"table-fill\">"."\r\n";
		
		
		
		echo "<thead><tr>
		<th class=\"text-center\">ID</th>
		<th class=\"text-center\">BOX-ID</th>
		<th class=\"text-center\">DHCP</th>
		<th class=\"text-center\">OS</th>
		<th class=\"text-center\">Upload</th>
		<th class=\"text-center\">Download</th>
		<th class=\"text-center\">Latency</th>
		<th class=\"text-center\">Disk Space</th>
		<th class=\"text-center\">GfConnection</th>
		<th class=\"text-center\">Date</th>
		<th class=\"text-center\">Actions</th>
		</tr></thead><tbody>"."\r\n";
		
		
		foreach($ListaCom as $linea){
			echo "\t"."<tr>"."\r\n";
			foreach ($linea as $campo){
				$clase = "text-left";
				if (endsWith($campo, "_LOW")) $clase.=" error";
				
				echo "\t"."\t"."<td class=\"".$clase."\">$campo</td>"."\r\n";
			}
			echo "\t"."<td class=\"text-left\"><button onclick=\"myFunction('".$linea['Box_id']."','".$linea['gf_computer_id']."')\">Delete Register</button>
			</td>
			</tr>"."\r\n";
		}
		echo "</tbody></table>"."\r\n";

		
	}
	
	if ($acceso=="tabla" && isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['c_id']) && is_numeric($_GET['c_id'])){
		$computer_id = mysqli_real_escape_string($conexion, $_GET['c_id']);
		if(DoesThisExists($conexion, "computers_table", "gf_computer_id", $computer_id)){
			$consulta = "DELETE FROM `computers_table` WHERE `gf_computer_id`=".$computer_id;
			mysqli_query($conexion, $consulta);
			echo "Registro borrado";
		} else {
			
		}
		echo "<html><head><meta http-equiv=\"refresh\" content=\"0; url=./tables.php\">";
		die();
	}

	
	if ($acceso=="login"){ 
		PrintLoginForm();
	}else if ($acceso=="tabla"){
		ShowTable($conexion, $NombreUsuario);
	}
	
	
    CloseConnection($conexion);
?>