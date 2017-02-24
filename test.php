<?php
$conn = mysqli_connect("localhost","rodiadm1_tata","LyA012011") or die(mysqli_error());
mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

$id = $_REQUEST['f_f'];

function obtenerPrimerLinea($id)
{
	global $conn;
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	$query = "SELECT name AS nombre FROM ad_tata WHERE id_unique='$id'";
	#echo $query.";<br><br>";
	$sql = mysqli_query($conn, $query);
	$row = mysqli_fetch_assoc($sql);

	$query2 = "SELECT meta_key, meta_value FROM ad_bgc WHERE id_can='$id' AND meta_key IN('edad','estado','religion')";
	#echo $query2.";<br><br>";
	$sql2 = mysqli_query($conn, $query2);
	$data = array();
	while($row2 = mysqli_fetch_assoc($sql2))
	{
		$data[$row2['meta_key']] = $row2['meta_value'];
	}

	// Variables del texto fijas
	$am = 0;
	$nombre = $row['nombre'];
	$edad = $data['edad'];
	$originaria = "";

	$mystring1 = $data['estado'];
	$findme = 'Estado';
	$pos = strpos($mystring1, $findme);
	#echo $pos."<br><br>";
	if($pos !== false)
	{
		$originaria .= " del ".$mystring1;
	} else {
		$am++;
	}

	$mystring2 = $data['estado'];
	$findme = 'Ciudad';
	$pos = strpos($mystring2, $findme);
	#echo $pos."<br><br>";
	if($pos !== false)
	{
		$originaria .= " de la ".$mystring2;
	} else {
		$am++;
	}

	if($am != 0)
	{
		$originaria .= " de ".$data['estado'];
	}

	$mystring3 = $data['religion'];
	$findme = 'ateo';
	$pos = strpos($mystring3, $findme);
	#echo $pos."<br><br>";
	if($pos !== false)
	{
		$religion = "no profesar ninguna religión";
	} else {
		$religion = "profesar la religión ".strtolower($data['religion']);
	}
	$texto = "C. ".$nombre." de ".$edad." de edad, originaria ".$originaria.", refiere ".$religion.".<br>";
	return $texto;
}

function obtenerSegundaLinea($id)
{
	global $conn;
	mysqli_select_db($conn, "rodiadm1_bgc_tata") or die(mysqli_error());

	mysqli_query($conn, "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
	$query = "SELECT meta_key, meta_value FROM ad_bgc WHERE id_can='$id' AND meta_key REGEXP 'Esposa_live|Esposo_live|Esposa_nombre|Esposo_nombre|documento_analista_13|fechaInst_analista_13|genero' ORDER BY id DESC";

	#echo $query.";<br><br>";
	$sql = mysqli_query($conn, $query);
	$data = array();
	while($row = mysqli_fetch_assoc($sql))
	{
		$data[$row['meta_key']] = $row['meta_value'];
	}

	#print_r($data);
	#echo "<br><br>";

	// Variables del texto fijas
	if($data['Esposo_nombre'] != "" || $data['Esposa_nombre'] != "")
	{
		if($data['Esposa_nombre'] != "")
		{
			if($data['Esposa_live'] != "No")
			{
				$pareja = "Actualmente vive con su esposo ".$data['Esposa_nombre'].". ";
			}
		}
		if($data['Esposo_nombre'] != "")
		{
			if($data['Esposo_live'] != "No")
			{
				$pareja = "Actualmente vive con su esposo ".$data['Esposo_nombre'].". ";
			}
		}
	}

	$estudios = " un ".$data['documento_analista_13'];
	
	$ms = explode(" ",$data['fechaInst_analista_13']);
	#print_r($ms);
	$cont = count($ms)-1;

	#echo "<br>$cont<br>";
	$school = "";
	for($i=0; $i<$cont; $i++)
	{
		$school .= $ms[$i]." ";
	}
	$vam = strlen($school)-1;
	$school = substr($school,0,$vam);

	if($data['genero'] == "Masculino")
	{
		$genero = "egresado";
	} else {
		$genero = "egresada";
	}
	

	$texto = $pareja."Cuenta con ".$estudios.", ".$genero." de ".$school.".<br>";
	return $texto;
}

/*
	C. Giovanni Antonio Ramírez López de 27 años de edad, originario de Oaxaca, refiere no profesar la religión católica. Actualmente vive con su esposa Ana Enriqueta Ríos Contreras. Cuenta con una Licenciatura en Ingeniería en Sistemas Computacionales, egresado de la Universidad IEU.
	    
	Actualmente vive en un departamento, la vivienda en su interior se observa en buenas condiciones, se observa  orden y limpieza.

	La fachada del departamento se encuentra en buen estado, cuenta con cocina, comedor, 2 recamaras, 1 baño, sala, garaje. Los espacios son suficientes. El mobiliario es de buena calidad. El mantenimiento del inmueble es bueno.

	El investigado genera un ingreso mensual de $12,500 y de esa cantidad aporta $11,500 para solventar los gastos del hogar. En alimentos gasta $2,500 mensuales, en servicios $2,00 mensual, en transporte $1,600 mensual, y en renta $5,400 mensuales.

	Refiere ingerir bebidas alcohólicas socialmente y no fumar.

	En la investigación realizada el candidato mostró buena disponibilidad y una buena actitud. No ha demandado ni ha estado sindicalizado. Por lo que consideramos que es una persona recomendable.
	/////////////////////////////////////////////////
	C. Carlos Alberto Rosas Torres de 31 (Años), originaria de Guadalajara, Jalisco, su familia está conformada por esposa (en casa), hija (en casa), papa (medico), mama (en casa), hermana (empleada), hermana (empleada), hermana (empleada), hermano (empleado).


	Con Titulo y Cédula en la Licenciatura Ingeniería en Sistemas Computacionales, egresado de la Universidad Cuauhtémoc. Durante la visita el candidato mostró disposición y apertura, la colonia cuenta con todos los servicios básicos municipales

	Actualmente vive con su esposa, su hija y una hermana en una casa habitación, cuenta con varias vías de acceso y rutas de transporte público. La casa en su interior se observa en buenas condiciones, con orden y limpieza.

	Actualmente se encontraba laborando en Tratamiento De Agua y Embotelladoras S.a. De C.V. de abril 2015 a diciembre 2016, y en Colegio Pedregal De Guadalajara de agosto 2008 a enero 2015


	En la investigación realizada se encontró que el candidato es una persona con hábitos y costumbres buenas. En lo que a su persona se refiere desde el inicio de la investigación el candidato mostro la mejor disposición, por lo que le consideramos recomendable.
	/////////////////////////////////////////////////
	C. Luis Bernardo España Román de 26 años de edad, originario de Zapopan, Jalisco, refiere profesar la religión católica. Actualmente vive con su hermana quien es freelance.

	Cuenta con una Licenciatura en Ingeniería en Tecnologías de Información y Comunicaciones, egresado del Instituto Tecnológico y de Estudios Superiores de Monterrey.

	 Actualmente vive en una casa habitación la cual es propiedad de sus padres, la colonia cuenta con todos los servicios básicos municipales, cuenta con varias vías de acceso y rutas de transporte público. La vivienda en su interior se observa en buenas condiciones, se observa orden y limpieza.

	La fachada de la casa se encuentra en buen estado, cuenta con patio, cocina, comedor, sala, patio de servicio, 3 recamaras, y 3 baños. Los espacios son suficientes. El mobiliario es de buena calidad, completo y en buen estado. El mantenimiento del inmueble es bueno.

	El investigado genera un ingreso de $19,000 mensual y de esa cantidad aporta $6,000 para los gastos del hogar. En alimentos gastan $3,800 mensuales, en servicios $1,000 mensuales, en transporte $1,400 mensual. Los gastos generados en el hogar son solventados por el candidato y por sus padres.

	En su tiempo libre le gusta salir, con amigos y estudiar.

	Su salud es buena. Refiere ingerir bebidas alcohólicas socialmente y también acostumbra a fumar. Enfermedades en su familia directa; abuelo paterno (hipertensión).

	En la investigación realizada el candidato mostró buena disponibilidad y una buena actitud. Se pudo percatar que la dinámica familiar es buena. No ha demandado ni ha estado sindicalizado. Por lo que consideramos que es una persona recomendable.
	/////////////////////////////////////////////////
	C. Manuel Alejandro Díaz Santoyo de 33 años de edad, originario del Distrito Federal, refiere profesar la religión católica. Actualmente vive con su hermana Blanca Alicia Díaz Santoyo. Cuenta con una Licenciatura en Ingeniería en Telecomunicaciones, egresado de la Universidad Cristóbal Colón.
	Actualmente vive en una casa habitación rentada, la vivienda en su interior se observa en buenas condiciones, se observa  orden y limpieza.
	La fachada de la vivienda se encuentra en buen estado, cuenta con cocina, comedor, 2 recamaras, 1 baño, sala, jardín, garaje. Los espacios son suficientes. El mobiliario es de buena calidad. El mantenimiento del inmueble es bueno.
	Refiere no ingerir bebidas alcohólicas y no fumar.
	En la investigación realizada el candidato mostró buena disponibilidad y una buena actitud. No ha demandado ni ha estado sindicalizado. Por lo que consideramos que es una persona recomendable.
	/////////////////////////////////////////////////
	C. Saúl Cajero Puga, de 29 años, es soltero, profesa la religión católica, es originario de Jalisco. Su familia nuclear: padre (albañil), madre (Limpieza), hermana (empleada), hermana (ayudante de cocina), hermano (chofer).

	Con estudios en Licenciatura en Sistemas de Información egresado del Centro Universitario de Ciencias Económico Administrativas Campus Los Belenes. Durante la visita se mostró disponible.

	Actualmente vive en una zona media, la colonia cuenta con todos los servicios básicos municipales, cuenta con varias vías de acceso y rutas de transporte público. La casa en su interior se observa orden y limpieza. El mobiliario es de calidad media.

	En la investigación realizada se encontró que el candidato es una persona con hábitos y costumbres buenas. En cuanto a su persona muestra una excelente disposición y actitud. Por lo que le consideramos Recomendable. 
*/

$texto .= obtenerPrimerLinea($id);

$texto .= obtenerSegundaLinea($id);

$texto .= "Actualmente vive en un departamento rentado, la vivienda en su interior se observa en buenas condiciones, se observa orden y limpieza.<br>";

$texto .= "La fachada de la vivienda se encuentra en buen estado, cuenta con cocina, comedor, 1 recamara, 1 baño, sala.<br>";

$texto .= "Los espacios son suficientes. El mobiliario es de calidad media e incompleto. El mantenimiento del inmueble es bueno.<br>";

$texto .= "El investigado genera un ingreso mensual de $38,000 y de esa cantidad aporta $21,900 para solventar los gastos del hogar.<br>";

$texto .= "En alimentos gasta $6,000 mensuales, en renta $4,000 mensual, en servicios $3,000 mensual, en transporte $2,400 y otros $6,000 mensuales.<br>";

$texto .= "Refiere no ingerir bebidas alcohólicas y no fumar. En la investigación realizada el candidato mostró buena disponibilidad y una buena actitud.<br>";

$texto .= "No ha demandado ni ha estado sindicalizado. Por lo que consideramos que es una persona recomendable.<br>";

echo $texto;
?>