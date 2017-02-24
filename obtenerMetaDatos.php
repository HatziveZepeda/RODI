<?php

$db;

function connect()
{
	#$conn = $conn = mysqli_connect("ensenanzainteligente.com","ensenan1_hatzive","H0tm41l4@") or die(mysqli_error());
	#mysqli_select_db($conn, "ensenan1_tata") or die(mysqli_error());
	#$db = new mysqli('ensenanzainteligente.com', 'ensenan1_hatzive', 'H0tm41l4@', 'ensenan1_tata');
	$db = new mysqli('localhost', 'rodiadm1_tata', 'LyA012011', 'rodiadm1_bgc_tata');
	#$db = new mysqli('localhost', 'root', 'Rodi.Data', 'rodiadmi_wp295');
	$db->set_charset("utf8");
	if($db->errno) die("<h1>HOUSTON WE HAVE A PROBLEM...</h1>");
}

function obtenerFamiliares($id_candidato)
{
	global $db;
	connect();
	//LAS CLAVES QUE PODRIAN TENER LOS FAMILIARES DEL CANDIDATO
	$claves_meta = array("nombre","edad","trabaja","compania","ocupacion","ciudad","live","parentezco","vive");
	//ESTOS SON LOS UNICOS FAMILIARES QUE PUEDEN SER VARIABLES
	$consultas = array("Hija" => "'Hija%'", "Hijo" => "'Hijo%'", "Hermana" => "'Hermana%'", "Hermano" => "'Hermano%'");
	//FAMILIARES CONSTANTES: SON LAS FAMILIARES QUE SIEMPRE VAN A SER 1
	$constantes = array("Padre", "Madre", "Espos");
	//AQUI SE ALMACENARAN LOS NUMEROS DE CADA FAMILIAR
	$numeroFamiliares = array();
	//EN ESTA VARIABLE SE GUARDAN TODOS LOS FAMILIARES
	$totalFamiliares = array();
	//AGREGAMOS LOS FAMILIARES CONSTANTES A NUESTRO ARREGLO
	foreach ($constantes as $key => $familiarConstante) {
		foreach ($claves_meta as $key => $valorMeta) {
			$valor = $db->query("SELECT meta_value FROM ad_bgc WHERE (meta_key LIKE '$familiarConstante%$valorMeta') AND id_can = $id_candidato")->fetch_assoc();
			if ($valor['meta_value'] != null) $totalFamiliares[$familiarConstante][$valorMeta] = $valor['meta_value'];
		}
	}
	//PREGUNTAMOS CUANTOS FAMILIARES TIENE EN TOTAL
	foreach ($consultas as $key => $value) {
		$numero = $db->query("SELECT MAX(RIGHT(meta_key,1)) AS 'numero_familiares' FROM `ad_bgc` WHERE (meta_key LIKE $value) AND `id_can` = $id_candidato")->fetch_assoc();
		$numeroFamiliares[$key] = $numero['numero_familiares'];
	}
	//POPULAMOS NUESTRO ARREGLO SEGUN LOS FAMILIARES VARIABLES
	foreach ($numeroFamiliares as $tipoFamiliar => $cantidad) {
		for($contadorDeFamiliares=1; $contadorDeFamiliares<=$cantidad; $contadorDeFamiliares++) {
			foreach ($claves_meta as $key => $value) {
				$resultado = $db->query("SELECT meta_value FROM ad_bgc WHERE (meta_key LIKE '$tipoFamiliar%$value%' AND meta_key LIKE '%$contadorDeFamiliares') AND id_can = $id_candidato")->fetch_assoc();
				if ( $resultado['meta_value'] != null ) $totalFamiliares["$tipoFamiliar $contadorDeFamiliares"]["$value"] = $resultado['meta_value'];
			}
		}
	}
	//IMPRIMIR TABLA FINAL
	$HTMLtable = '<style>
		@font-face {
			font-family: "LatoRegular";
			src: url("Lato-Regular.ttf") format("truetype");
		}
		@font-face {
			font-family: "LatoBold";
			src: url("Lato-Bold.ttf") format("truetype");
		}
		.familiar-name {
			font-family: "LatoBold";
			text-align: center;
			font-size: 12pt;
		}
		.familiar-info {
			font-family: "LatoRegular";
			text-align: center;
			font-size: 8pt;
		}
		th {
			font-size: 12pt;
		}
	</style>
	<table class="main-table" border="1" cellpadding="6" cellspacing="0">
	<!-- HEADERS PARA LA TABLA DEL HAMBIENTE FAMILIAR-->
		<tr align="center">
			<th colspan="2" width="45%">Name</th>
			<th width="8%">Age</th>
			<th width="17%">Ocupation</th>
			<th width="20%">City/Company</th>
			<th width="10%">Live with?</th>
		</tr>';
	foreach ($totalFamiliares as $key => $familiar) 
	{
		$HTMLtable .= '
		<tr>
			<td class="familiar-name" width="17%">'.$familiar['parentezco'].'</td>
			<td class="familiar-info" width="28%">'.$familiar['nombre'].'</td>
			<td class="familiar-info">'.$familiar['edad'].'</td>';
			if($familiar['ocupacion'] != "" || $familiar['ocupacion'] != null)
			{
				$HTMLtable .='<td class="familiar-info">'.$familiar['ocupacion'].'</td>';
			} else {
				$HTMLtable .='<td class="familiar-info">Ninguna</td>';
			}
			if($familiar['vive'] == "Finado")
			{
				$HTMLtable .='
					<td class="familiar-info">Finado</td>
					<td class="familiar-info">Finado</td>
				</tr>';
			} else {
				if($familiar['vive'] == "No")
				{
					$HTMLtable .='
						<td class="familiar-info">Finado</td>
						<td class="familiar-info">Finado</td>
					</tr>';
				} else {
					if($familiar['compania'] != "" || $familiar['compania'] != null)
					{
						$HTMLtable .='
							<td class="familiar-info">'.$familiar['ciudad'].' / '.$familiar['compania'].'</td>
							<td class="familiar-info">'.$familiar['live'].'</td>
						</tr>';
					} else {
						$HTMLtable .='
							<td class="familiar-info">'.$familiar['ciudad'].'</td>
							<td class="familiar-info">'.$familiar['live'].'</td>
						</tr>';
					}
				}
			}
	}
	$HTMLtable .= "</table>";
	return $HTMLtable;
} //FIN DE LA FUNCION 'obtenerFamiliares'

function obtenerLaborales($id_candidato)
{
	$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

	$query = "SELECT id_unique, name, skill, project FROM ad_tata WHERE id_unique='$id_candidato'";
	$sqla = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($sqla);
	$projects = $row['project'];

	global $db;
	connect();
	//EN ESTE ARREGLO SE GUARDAN TODOS LOS LABORALES
	$totaLaborales = array();
	//CLAVES META PARA REALIZAR LA CONSULTA
	$clavesMeta = array("nombre_empresa","direccion_empresa","numero_empresa","fechaIn_laboral","fechaOut_laboral","puesto_inicial","puesto_final","salario_inicial","salario_final","jefeInmediato","gerentePos","separacion","comentario_laboral");
	$cantidadLaborales=1;
	while (true) 
	{
		foreach ($clavesMeta as $idMeta => $claveMeta)
		{
			//OBTENER EL VALOR DE LA CLAVE META $KEY
			$meta_value = $db->query("SELECT meta_value AS 'valor' FROM ad_bgc WHERE (meta_key LIKE '$claveMeta%candidato%$cantidadLaborales') AND id_can = $id_candidato")->fetch_assoc();
			//SI ES LA PRIMER CLAVE META Y EL RESULTADO ES NULL, SALIMOS DE LOS 2 CICLOS
			if ( $meta_value['valor'] == null && $idMeta == 0) break 2;
			//SI NO, ASIGNAMOS EL VALOR Y CONTINUAMOS CON LA SIGUIENTE CLAVE META
			if ( $meta_value['valor'] != null) $totaLaborales[$cantidadLaborales][$claveMeta] = $meta_value['valor'];
			else $totaLaborales[$cantidadLaborales][$claveMeta] = "/";
			//ANALISTAS
			$meta_value = $db->query("SELECT meta_value AS 'valor' FROM ad_bgc WHERE (meta_key LIKE '$claveMeta%analista%$cantidadLaborales') AND id_can = $id_candidato")->fetch_assoc();
			//ASIGNAMOS EL VALOR Y CONTINUAMOS CON LA SIGUIENTE
			if ( $meta_value['valor'] != null) 
			{
				$totaLaborales[$cantidadLaborales][$claveMeta."_analista"] = $meta_value['valor'];
			} else {
				$totaLaborales[$cantidadLaborales][$claveMeta."_analista"] = "/";
			}
		}
		$cantidadLaborales++;
	}
	$claves = "SELECT meta_value AS 'valor' FROM ad_bgc WHERE (meta_key LIKE '$claveMeta%analista%$cantidadLaborales') AND id_can = $id_candidato";
	$styles = '<style>
			@font-face {
				font-family: "LatoRegular";
				src: url("Lato-Regular.ttf") format("truetype");
			}
			@font-face {
				font-family: "LatoBold";
				src: url("Lato-Bold.ttf") format("truetype");
			}
			.labor-name {
				font-family: "LatoBold";
				text-align: center;
				font-size: 12pt;
				height: 2;
				line-height: 1;
			}
			.tall {
				height: 5;
				line-height: 2.5;
			}
			.labor-info {
				font-family: "LatoRegular";
				text-align: left;
				font-size: 8pt;
			}
			.classic_table {
				font-famili: "LatoRegular";
				text-align: center;
				font-size: 10pt;
				font-weight: normal;
			}
			.classic_tr {
				font-famili: "LatoBold";
				text-align: left;
				font-size: 10pt;
				font-weight: bold;
			}
			th {
				font-size: 12pt;
			}
			</style>';
	$HTMLtables = array();
		foreach ($totaLaborales as $key => $laboral)
		{
			//DECLARAMOS LA TABLA
			$HTMLtables[$key] .= $styles;
			$HTMLtables[$key] .= '
			<table border="1" cellpadding="3" cellspacing="0">
				<tr align="center">
					<th></th>
					<th>Candidate</th>
					<th>Company</th>
					<th>References</th>
				</tr>
				<tr>
					<td class="labor-name">Company</td>
					<td class="labor-info">'.$laboral['nombre_empresa'].'</td>
					<td class="labor-info">'.$laboral['nombre_empresa_analista'].'</td>
					<td rowspan="12" class="labor-info">'.$laboral['comentario_laboral_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Address</td>
					<td class="labor-info">'.$laboral['direccion_empresa'].'</td>
					<td class="labor-info">'.$laboral['direccion_empresa_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Entry Date</td>
					<td class="labor-info">'.$laboral['fechaIn_laboral'].'</td>
					<td class="labor-info">'.$laboral['fechaIn_laboral_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Exit Date</td>
					<td class="labor-info">'.$laboral['fechaOut_laboral'].'</td>
					<td class="labor-info">'.$laboral['fechaOut_laboral_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Phone</td>
					<td class="labor-info">'.$laboral['numero_empresa'].'</td>
					<td class="labor-info">'.$laboral['numero_empresa_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Initial Job Position</td>
					<td class="labor-info">'.$laboral['puesto_inicial'].'</td>
					<td class="labor-info">'.$laboral['puesto_inicial_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Last Job Position</td>
					<td class="labor-info">'.$laboral['puesto_final'].'</td>
					<td class="labor-info">'.$laboral['puesto_final_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Initial Salary</td>
					<td class="labor-info">'.$laboral['salario_inicial'].'</td>
					<td class="labor-info">'.$laboral['salario_inicial_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Last Salary</td>
					<td class="labor-info">'.$laboral['salario_final'].'</td>
					<td class="labor-info">'.$laboral['salario_final_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Immediate Manager</td>
					<td class="labor-info">'.$laboral['jefeInmediato'].'</td>
					<td class="labor-info">'.$laboral['jefeInmediato_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Manager’s Job Position</td>
					<td class="labor-info">'.$laboral['gerentePos'].'</td>
					<td class="labor-info">'.$laboral['gerentePos_analista'].'</td>
				</tr>
				<tr>
					<td class="labor-name">Cause of Separation</td>
					<td class="labor-info">'.$laboral['separacion'].'</td>
					<td class="labor-info">'.$laboral['separacion_analista'].'</td>
				</tr>
			</table>
			';

			if($projects == "HSBC")
			{
				$HTMLtables[$key] .= '
				<p></p>
				<table border="1" style="text-align: center; font-size: 10pt;">
				<tr>
					<td width="40%" class="classic_table">Did the candidate sue the company:</td>
					<td width="15%" class="classic_table">Yes</td>
					<td width="15%" class="classic_table"></td>
					<td width="15%" class="classic_table">No</td>
					<td width="15%" class="classic_table">X</td>
				</tr>
				</table>
				<p style="text-align: center; font-weight: bold;">Candidate Performance</p>
				';

				$HTMLtables[$key].='
				<table border="1" style="text-align: center; font-size: 10pt;">
				<tr>
				<th width="25%" class="classic_tr" style="text-align: center;">Area</th>
				<th width="15%" class="classic_tr" style="text-align: center;">Excellent</th>
				<th width="15%" class="classic_tr" style="text-align: center;">Good</th>
				<th width="15%" class="classic_tr" style="text-align: center;">Regular</th>
				<th width="15%" class="classic_tr" style="text-align: center;">Bad</th>
				<th width="15%" class="classic_tr" style="text-align: center;">Too Bad</th>
				</tr>';

				$datesa = array("Responsability","Initiative","Quality of work","Discipline","Punctuality and assistance","Cleanliness and order","Stability","Emotional Stability","Honesty","Performance","Attitude with coworkers, bosses and subordinate");

				$HTMLtables[$key].='
						<tr>
						<th width="25%" class="classic_tr">'.$datesa[0].'</th>
						<td colspan="5" rowspan="11" class="classic_table"><br><br><br><br><br><br>Sólo se proporciona información básica por parte de la empresa</td>
						</tr>
						';
				for($i=1; $i<count($datesa); $i++)
				{
						/*<td width="15%" class="classic_table">X</td>
						<td width="15%" class="classic_table">&nbsp;</td>
						<td width="15%" class="classic_table">&nbsp;</td>
						<td width="15%" class="classic_table">&nbsp;</td>*/
					$HTMLtables[$key].='
						<tr>
						<th width="25%" class="classic_tr">'.$datesa[$i].'</th>
						</tr>
						';
				}
				$HTMLtables[$key].='</table>';
				$HTMLtables[$key].='<p></p>';
				$HTMLtables[$key].='
				<table border="1" style="text-align: center; font-size: 10pt;">
				<tr>
				<th width="45%" class="classic_tr">In case of vacancy do you hire her/him again?</th>
				<th width="55%" class="classic_table">Yes (  )   |   No ( x )</th>
				</tr>
				<tr>
				<th width="45%" class="classic_tr">¿Why?</th>
				<th width="55%" class="classic_table">Información no proporcionada por la empresa.</th>
				</tr>
				</table>';
			}
		}
		return $HTMLtables;
}//FIN DE LA FUNCION 'obtenerLaborales'

function obtenerEscolares($id_candidato)
{
	global $db;
	connect();
	$atributosEscolares = array("nivel","inicio","final","nombre","ciudad","certificado","presento");
	//TOTAL ESCOLARES
	$totalEscolares = array();
	//RECORRER LOS NIVELES ESCOLARES
	$escolar=1;
	while (true)
	{
		foreach ($atributosEscolares as $key => $atributoEscolar)
		{
			$resultSet = $db->query("SELECT meta_value FROM ad_bgc WHERE (meta_key LIKE '$atributoEscolar%escolar%$escolar') AND id_can = $id_candidato")->fetch_assoc();
			//Si el primer atributo no esta definidio, terminatos los 2 bucles
			if($atributoEscolar == "nivel" && $resultSet['meta_value'] == null) break 2;
			//Si el resultado es NULL se asigna "/"
			if ($resultSet['meta_value'] != null)
			{
				$totalEscolares[$escolar][$atributoEscolar] = $resultSet['meta_value'];
			} else 
			{
				$totalEscolares[$escolar][$atributoEscolar] = "No Proporciona";
			}
			//CUANDO SEA EL FINAL DE LOS ATRIBUTOS SALIMOS DEL PRIMER BUCLE
		}
		$escolar++;
	}
	$HTMLtable = '<style>
		@font-face {
			font-family: "LatoRegular";
			src: url("Lato-Regular.ttf") format("truetype");
		}
		@font-face {
			font-family: "LatoBold";
			src: url("Lato-Bold.ttf") format("truetype");
		}
		.studies-name {
			font-family: "LatoBold";
			text-align: center;
			font-size: 10pt;
		}
		.studies-info {
			font-family: "LatoRegular";
			text-align: center;
			font-size: 8pt;
		}
		th {
			font-family: "LatoBold";
			font-size: 10pt;
		}
	</style>
	<table class="main-table" border="1" cellpadding="6" cellspacing="0">
		<tr align="center">
			<th width="18%">Level</th>
			<th width="17%">Period</th>
			<th width="20%">Institute</th>
			<th width="17%">City</th>
			<th width="15%">Certificate obtained</th>
			<th width="13%">Documents</th>
		</tr>';
		$casco = 2;
		foreach ($totalEscolares as $key => $escuela) {
			if($casco == $escolar)
			{
				if($escuela['certificado'] == "Si")
				{
					$talon = "Si";
				} else {
					$talon = "No";
				}
			} else {
				$talon = "No";
			}
			$HTMLtable .= '
			<tr>
				<td class="studies-name">'.$escuela['nivel'].'</td>
				<td class="studies-info">'.$escuela['inicio'].' - '.$escuela['final'].'</td>
				<td class="studies-info">'.$escuela['nombre'].'</td>
				<td class="studies-info">'.$escuela['ciudad'].'</td>
				<td class="studies-info">'.$escuela['certificado'].'</td>
				<td class="studies-info">'.$talon.'</td>
			</tr>';
			$casco++;
		}
		$HTMLtable .= '</table>';
	return $HTMLtable;
} //FIN DE LA FUNCION 'obtenerEscolares'

function obtenerReferencias($id_candidato)
{
	global $db;
	connect();
	//CLAVES META
	$atributos = array("nombre_referencia","telefono_referencia","tiempo_referencia","conoce_referencia","trabajo_referencia","vive_referencia","comentario_referencia");
	//EN $totalReferencias SE GUARDAN TODAS LAS REFERENCIAS PERSONALES
	$totalReferencias = array();
	//PARA CONTAR LAS REFERENCIAS QUE TIENE ESTE CANDIDATO
	$contadorReferencias=1;
	while (true)
	{
		foreach ($atributos as $key => $atributo) 
		{
			$meta_value = $db->query("SELECT meta_value AS 'value' FROM ad_bgc WHERE (meta_key LIKE '$atributo%$contadorReferencias') AND id_can = $id_candidato LIMIT 3")->fetch_assoc();
			$meta_value = $meta_value['value'];
			//COMPROBAR SI EXISTE UNA REFERENCIA PERSONAL
			if ($meta_value == null && $key == 0) break 2;
			//GUARDAMOS EL VALOR EN NUESTRO ARREGLO
			if ($meta_value != null) $totalReferencias[$contadorReferencias][$atributo] = $meta_value;
			else $totalReferencias[$contadorReferencias][$atributo] = "No Proporciona";
		}
		$contadorReferencias++;
	}
	$HTMLtables = array();
	$styles = '<style>
	@font-face {
		font-family: "LatoRegular";
		src: url("Lato-Regular.ttf") format("truetype");
	}
	@font-face {
		font-family: "LatoBold";
		src: url("Lato-Bold.ttf") format("truetype");
	}
	td {
		font-size: 10px;
		font-family: "LatoRegular";
		border-left: 1px solid black;
		border-right: 1px solid black;
		border-top: 1px solid black;
		border-bottom: 1px solid black;
	}
	td.information-holder {
		text-align: center;
		color: black;
		font-size: 12px;
		font-family: "LatoRegular";
	}
	td.information-name {
		text-align: center;
		color: black;
		font-size: 12px;
		font-family: "LatoBold";
	}
	table.table-top {
		border-left: 1px solid black;
		border-right: 1px solid black;
		border-top: 1px solid black;
		border-bottom: 1px none black;
	}
	table.table-bottom {
		border-left: 1px solid black;
		border-right: 1px solid black;
		border-top: 1px none black;
		border-bottom: 1px solid black;
	}
	</style>';
	foreach ($totalReferencias as $key => $referencia)
	{
		$HTMLtables[$key] = $styles . '
		<table class="table-top" cellpadding="2" cellspacing="0">
			<tr>
				<td width="10%" class="information-name">Name:</td>
				<td width="60%" class="information-holder">'.$referencia['nombre_referencia'].'</td>
				<td width="10%" class="information-name">Phone:</td>
				<td width="20%" class="information-holder">'.$referencia['telefono_referencia'].'</td>
			</tr>
			<tr>
				<td width="20%" class="information-name">Time to know her/him:</td>
				<td width="30%" class="information-holder">'.$referencia['tiempo_referencia'].'</td>
				<td width="20%" class="information-name">Why do you know her/him:</td>
				<td width="30%" class="information-holder">'.$referencia['conoce_referencia'].'</td>
			</tr>
			<tr>
				<td width="35%" class="information-name">Does she/he know where the candidate work or where have the candidate worked?</td>
				<td width="15%" class="information-holder">'.$referencia['trabajo_referencia'].'</td>
				<td width="35%" class="information-name">Does she/he know where the candidate lives?</td>
				<td width="15%" class="information-holder">'.$referencia['vive_referencia'].'</td>
			</tr>
		</table>
		<table class="table-bottom" cellpadding="2" cellspacing="0">
			<tr>
				<td width="15%" class="information-name">Comments:</td>
				<td width="85%" class="information-holder">'.$referencia['comentario_referencia'].'</td>
			</tr>
		</table>
		';
	}
	return $HTMLtables;
} //FIN DE LA FUNCION 'obtenerReferencias'

function obtenerViviendasAnteriores($id_candidato)
{
	$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	$tbl = '<style>
	@font-face {
		font-family: "LatoRegular";
		src: url("Lato-Regular.ttf") format("truetype");
	}
	@font-face {
		font-family: "LatoBold";
		src: url("Lato-Bold.ttf") format("truetype");
	}
	td {
		border-left: 1px solid black;
		border-right: 1px solid black;
		border-top: 1px solid black;
		border-bottom: 1px solid black;
	}
	td.information-holder {
		text-align: center;
		color: black;
		font-size: 12px;
		font-family: "LatoRegular";
	}
	table.table-bottom {
		border-left: 1px solid black;
		border-right: 1px solid black;
		border-top: 1px none black;
		border-bottom: 1px solid black;
	}
	th {
		font-family: "LatoBold";
		font-size: 12pt;
	}
	</style>';
	$dataes = "ultima_vivienda_calle_";
	$sql = mysqli_query($conn, "SELECT * FROM ad_bgc WHERE meta_key LIKE '%$dataes%' AND id_can='$id_candidato' ORDER BY id DESC LIMIT 1");
	if(mysqli_num_rows($sql) != 0)
	{
		$zas = mysqli_fetch_assoc($sql);
		$lomb = explode("_",$zas["meta_key"]);

		for($i=1; $i<=$lomb[3]; $i++)
		{
			$sa = mysqli_query($conn, "SELECT * FROM ad_bgc WHERE id_can='$id_candidato' AND meta_key IN('ultima_vivienda_calle_$i','ultima_vivienda_next_$i','ultima_vivienda_nint_$i','ultima_vivienda_colonia_$i','ultima_vivienda_estado_$i','ultima_vivienda_tiempo_$i')");
			while($row = mysqli_fetch_assoc($sa))
			{
				// Genera la variable bucle y adjunta los datos
				$dataKey2[] = $row['meta_key'];
				$dataPerson2[] = $row['meta_value'];
			}

			$texto = $dataPerson2[5].". ".$dataPerson2[4].", ".$dataPerson2[0]." #".$dataPerson2[1].", Col. ".$dataPerson2[2].", ".$dataPerson2[3];
			// Imprime la variable
			$tbl .= '<table class="table-bottom" cellpadding="2" cellspacing="0">
				<tr>
					<td width="25%">Previous addres and time lived</td>
					<td width="75%" class="information-holder">'.$texto.'</td>
				</tr>
			</table>';
			unset($dataPerson2);
		}
			$tbl .= '<table class="table-bottom" cellpadding="2" cellspacing="0">
				<tr>
					<td width="25%">Previous addres and time lived</td>
					<td width="75%" class="information-holder">No proporciona más domicilios</td>
				</tr>
			</table>';
	} else {
		$tbl .= '<table class="table-bottom" cellpadding="2" cellspacing="0">
				<tr>
					<td width="25%">Previous addres and time lived</td>
					<td width="75%" class="information-holder">No proporciona más domicilios</td>
				</tr>
			</table>';
	}
	return $tbl;
}

function obtenerDocumentos($id_candidato,$comentarios)
{
	$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

	$data = array("","birth","govid","CURP","RFC","IMSS","educ","address","employment","police","","","credit","study_proof");
	$textoSpain = array("","Acta de Nacimiento","Identificación Oficial","CURP","RFC","Numero de Seguridad Social","Cédula Profesional","Comprobante de Domicilio","Carta de Recomendación","Carta Policia","Buró de Crédito","Comprobante de Estudios");

	$tbl = '
	<style>
		@font-face {
			font-family: "LatoRegular";
			src: url("Lato-Regular.ttf") format("truetype");
		}
		@font-face {
			font-family: "LatoBold";
			src: url("Lato-Bold.ttf") format("truetype");
		}
		.document-name {
			font-family: "LatoBold";
			text-align: center;
			font-size: 12pt;
		}
		.document-info {
			font-family: "LatoRegular";
			text-align: center;
			font-size: 8pt;
		}
		th {
			font-family: "LatoBold";
			font-size: 12pt;
		}
	</style>
	<!-- TABLE OF THE DOCUMENTS -->
	<table border="1" cellpadding="6" cellspacing="0">
		<tr align="center">
			<th>Documents</th>
			<th>Number of Document</th>
			<th>Institution / Date</th>
		</tr>';
	for($i=1; $i<count($textoSpain); $i++)
	{
		if($i >= 10)
		{
			$z = $i+2;
		} else {
			$z = $i;
		}
		#echo "SELECT * FROM ad_bgc WHERE meta_key IN('documento_analista_$z','fechaInst_analista_$z') AND id_can='$id_candidato';<br>";
		$sql = mysqli_query($conn, "SELECT * FROM ad_bgc WHERE meta_key IN('documento_analista_$z','fechaInst_analista_$z') AND id_can='$id_candidato'");

		if(mysqli_num_rows($sql) > 0)
		{
			$tbl .='<tr><td class="document-name">'.$textoSpain[$i].'</td>';
			while($row = mysqli_fetch_assoc($sql))
			{
				$valos = $row["meta_value"];
				$tbl .= '<td class="document-info">'.$valos.'</td>';
			}
			$tbl .= "</tr>";
		} else {
			$tbl .= '<tr>
			<td class="document-name">'.$textoSpain[$i].'</td>
			<td class="document-info">No Proporciono</td>
			<td class="document-info">No Proporciono</td>
			</tr>';
		}
	}
	$tbl .='<tr>
			<td class="document-name">Comentarios</td>
			<td class="document-info" colspan="2">'.$comentarios.'</td>
			</tr>';
	$tbl .= "</table>";
	return $tbl;
}

function obtenerCoordenadas($id_candidato)
{
	$keyApi = "AIzaSyCUS1mhaP8RcPwK0ufx95joAtz9rLuaqfo";
	$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	#if()
	$sql = mysqli_query($conn, "SELECT * FROM ad_bgc WHERE meta_key IN('direccion','numero_exterior','ciudad','estado') AND id_can='$id_candidato'");
	$resultado = "";
	$saa = 1;
	while($row = mysqli_fetch_assoc($sql))
	{
		if($saa == 4)
		{
			break;
		}
		switch($row['meta_key'])
		{
			case "direccion":
			$resultado .= $row['meta_value'];
			break;
			case "numero_exterior":
			$resultado .= " ".$row['meta_value'];
			break;
			case "vecindario":
			$resultado .= " ".$row['meta_value'];
			break;
			#case "codigo_postal":
			#$resultado .= " ".$row['meta_value'];
			#break;
			case "ciudad":
			$resultado .= " ".$row['meta_value'];
			break;
		} // Calle 3a. 2 Ciudad De México CDMX
		// Calle 3a. 2, San Pedro Apostol, 14070 Ciudad de México
		$saa++;
	}

	#echo $resultado;

	$addressCandidate = str_replace(" ","+",$resultado);
	$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".$addressCandidate."&key=".$keyApi;
	#echo $url."<br>";
	// Get cURL resource
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url
	));

	$result = curl_exec($curl);
	$data = json_decode($result,false);
	if($data->status == "OK")
	{
		$sql2 = mysqli_query($conn, "SELECT id_unique,name FROM ad_tata WHERE id_unique='$id_candidato'");
		$row = mysqli_fetch_assoc($sql2);
		// Se ha realizado la consulta de coordenadas
		$latitud = $data->results[0]->geometry->location->lat;
		$longitud = $data->results[0]->geometry->location->lng;

		$coordenates = $latitud.",".$longitud;
		$marker = "&markers=";
		$marker .= "color:red";
		$nombres = explode(" ",$row['name']);
		$marker .= "|".$coordenates;

		$newURL = "https://maps.googleapis.com/maps/api/staticmap?center=".$coordenates."&zoom=17&size=640x640&maptype=roadmap&format=jpg".$marker."&key=".$keyApi;
	}
	curl_close($curl);
	#echo $newURL."<br>";
	return $newURL;
}

function obtenerViviendasAnterioresCantidad($id_candidato)
{
	$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	
	$dataes = "ultima_vivienda_calle_";
	$sql = mysqli_query($conn, "SELECT * FROM ad_bgc WHERE meta_key LIKE '%$dataes%' AND id_can='$id_candidato' ORDER BY id DESC LIMIT 1");
	if(mysqli_num_rows($sql) == 0)
	{
		$tbl = true;
	}
	return $tbl;
}