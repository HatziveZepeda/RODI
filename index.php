<?php

#error_reporting(E_ALL & ~E_NOTICE);

require_once('pdf/tcpdf.php');
require_once('pdf/include/tcpdf_fonts.php');
require_once('obtenerMetaDatos.php');

$nuevaFuente = new TCPDF_FONTS;
$fecha_documento=date("F d, Y");

$db = new mysqli('localhost', 'rodiadm1_tata', 'LyA012011', 'rodiadm1_bgc_tata');

if ( isset($_GET['id']) )
{
	$texto = $_GET['id'];
	$var = explode("-",$texto);
	if($var[0] == "TAT")
	{
		$lomo = $db->query("SELECT * FROM ad_tata  WHERE id_unique='$var[1]'")->fetch_assoc();
		if($lomo['status'] == "Released and Delivered")
		{
			$id = $var[1];
		} else die("<h1>This document is not yet ready, wait for it to change status.</h1>");
	} else {
		$id = $texto;
	}
	$id_can = $id;
} else die("<H1>NO CANDIDATE DATA DEFINED</H1>");
	

$n = 1;

if($db->errno)
{
	die("<h1>HOUSTON, WE HAVE A PROBLEM...</h1>");
} else {
	$meta = array();
	$db->set_charset("utf8");
	$meta_values = $db->query("SELECT * FROM ad_bgc WHERE id_can='$id_can' AND meta_key IN ('fecha_nacimiento','edad','posicion_trabajo','nacionalidad','genero','religion','direccion','numero_exterior','numero_interior','vecindario','ciudad','estado','codigo_postal','estado_civil','telefono_fijo','telefono_mensajes','telefono_movil','tiempo_actual_vivienda','tiempo_translado_oficina','modo_transporte') ORDER BY id");
	$meta_values = $meta_values->fetch_all(MYSQLI_ASSOC);
	foreach ($meta_values as $key => $value)
	{
		$meta[$value['meta_key']]=$value['meta_value'];
	}

	$comentarios = $db->query("SELECT meta_key, meta_value FROM ad_bgc WHERE (meta_key LIKE '%comentario%') AND id_can = $id_can")->fetch_all(MYSQLI_ASSOC);
	$esenciales = $db->query("SELECT name, project, email FROM ad_tata WHERE id_unique = $id_can")->fetch_assoc();

	$datos = $db->query("SELECT photo,educ FROM files WHERE id_unique = $id_can")->fetch_assoc();
	#$cedula = $db->query("SELECT educ FROM files WHERE id_unique = $id_can")->fetch_assoc(MYSQLI_ASSOC);
	#echo "SELECT meta_key, meta_value FROM ad_bgc WHERE id_can = $id_can AND meta_key='visita_realizada'";
	$visit = $db->query("SELECT meta_key, meta_value FROM ad_bgc WHERE id_can = $id_can AND meta_key='visita_realizada'")->fetch_assoc();
	$laboralesa = $db->query("SELECT COUNT(*) AS Laborales FROM ad_bgc WHERE id_can = $id_can AND meta_key LIKE '%nombre_empresa_candidato_%'")->fetch_assoc();
	$selector = $db->query("SELECT iddicese AS selector FROM ad_tata WHERE id_unique = $id_can")->fetch_assoc();
	#echo "SELECT skill FROM ad_tata WHERE id_unique = $id_can";
	$skill = $db->query("SELECT skill FROM ad_tata WHERE id_unique = $id_can")->fetch_assoc();
	$project = $db->query("SELECT project FROM ad_tata WHERE id_unique = $id_can")->fetch_assoc();
	if($selector['selector'] == 1)
	{
		$dadaroom = array("background-color: #33C93A;","","");
	}
	if($selector['selector'] == 3)
	{
		$dadaroom = array("","background-color: red;","");
	}
	if($selector['selector'] == 2)
	{
		$dadaroom = array("","","background-color: yellow;");
	}

	//ESENCIALES
	$candidate_company="Tata Consultancy Services";
	$candidate_name=$esenciales['name'];
	$candidate_email=$esenciales['email'];
	//PERSONALES
	$candidate_birth=$meta['fecha_nacimiento'];
	$candidate_age=$meta['edad'];
	if($meta['posicion_trabajo'] == "") {$candidate_position_requested= "-";} else {$candidate_position_requested= $meta['posicion_trabajo'];}
	$candidate_nationality=$meta['nacionalidad'];
	$candidate_gender=$meta['genero'];
	$candidate_religion=$meta['religion'];
	$candidate_address=$meta['direccion'];
	$candidate_ext_num=$meta['numero_exterior'];
	if($meta['numero_interior'] == "") {$candidate_int_num= "-";} else {$candidate_int_num= $meta['numero_interior'];}
	$candidate_neighborhood=$meta['vecindario'];
	$candidate_city=$meta['ciudad'];
	$candidate_state=$meta['estado'];
	$candidate_zip=$meta['codigo_postal'];
	$candidate_marital_status=$meta['estado_civil'];
	if($meta['telefono_fijo'] == "") {$candidate_home_num= "-";} else {$candidate_home_num= $meta['telefono_fijo'];}
	if($meta['telefono_mensajes'] == "") {$candidate_messages_num= "-";} else {$candidate_messages_num= $meta['telefono_mensajes'];}
	$candidate_mobile_num=$meta['telefono_movil'];
	$times = obtenerViviendasAnterioresCantidad($id_can);
	if($times == true) {$candidate_time_actual_addres=$candidate_age;} else {$candidate_time_actual_addres=$meta['tiempo_actual_vivienda'];}
	if($meta['tiempo_translado_oficina'] == "") {$candidate_time_transit= "-";} else {$candidate_time_transit= $meta['tiempo_translado_oficina'];}
	if($meta['modo_transporte'] == "") {$candidate_transportation_mode= "-";} else {$candidate_transportation_mode= $meta['modo_transporte'];}

	$comentarios_ultimos = "De acuerdo a los registros de OFAC el candidato no cuenta con antecedentes criminalísticos internacionales.";

	$candidate_gubernamental_comment = "El pretendiente aseguro que no ha trabajado en dependencias gubernamentales, partidos políticos u ONG.";
	$candidate_document_comments = "No cuenta con comentarios adicionales ";
	$candidate_break_career = "No se presentaron periodos inactivos.";
	$candidate_break_labor = "No se presentaron periodos inactivos.";

	foreach ($comentarios as $key => $value)
	{
		if ($value['meta_key'] == "comentario_escolar")
		{
			$candidate_break_career=$value['meta_value'];
		} else if ($value['meta_key'] == "comentario_laboral")
		{
			$candidate_break_labor=$value['meta_value'];
		} else  if ($value['meta_key'] == "comentario_gubernamental")
		{
			$candidate_gubernamental_comment=$value['meta_value'];
		} else  if ($value['meta_key'] == "comentario_documentos")
		{
			$candidate_document_comments=$value['meta_value'];
		}
	}
}
class MYPDF extends TCPDF {
	// Page footer
	public function Footer() {
		$footer_text="Av. Alemania # 1398\nCol. Moderna\nGuadalajara, Jalisco, C.P. 44190\nTel. 3811-8989";
		// Set font and color
		$nuevaFuente = new TCPDF_FONTS;
		$LatoBold = $nuevaFuente->addTTFfont("Lato-Bold.ttf");
		$LatoRegular = $nuevaFuente->addTTFfont("Lato-Regular.ttf");
		$this->SetFont($LatoBold,'', 11);
		$this->setTextColor(119,119,119);
		//El texto a la izquierda en el pie de pagina
		$this->MultiCell(0, 20, $footer_text, 0, 'L',false, 0, 15, 270, true, 0, false, true, 20, "M", false);
		$this->SetFont($LatoBold,'', 9);
		$this->SetY(-15);
		//Texto debajo de la imagen
		$this->Cell(0, 10, 'www.gentex.mx', 0, false, 'R', 0, '', 0, false, 'T', 'M');
		//Insertar la imagen en el pie de pagina
		$this->Image('images/footer_gentex.png', 168, 270, 0, 0, 'PNG', null, '', true, 150, '', false, false, 0, false, false, false);
	}
}
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$LatoBold = $nuevaFuente->addTTFfont("Lato-Bold.ttf");
$LatoRegular = $nuevaFuente->addTTFfont("Lato-Regular.ttf");
#$fontname = $pdf->addTTFfont("Lato-Regular.ttf", "TrueTypeUnicode", "", 32);
// set document information
//$pdf->SetCreator(PDF_CREATOR);
$pdf->SetCreator("Rodi Admin Data");
$pdf->SetAuthor('Rodi Admin Data');
$pdf->SetTitle('BGC PDF of '.$candidate_name);
#$pdf->SetSubject('Name of the candidate');
#$pdf->SetKeywords('RODI, BGC, TATA, PDF, BETA');
// set default header data
$pdf->SetHeaderData("gentex_logo.png", 50, "", "");
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// PORTADA DEL DOCUMENTO


$n = 1;
// add a page
$dh = explode(".",$_SERVER['HTTP_HOST']);
$direccion_host = $dh[1].".".$dh[2];

$pdf->AddPage();

$pdf->SetFont($LatoBold,'',12);
$pdf->Cell(120,0,"");
if($datos['photo'] == "")
{
	$ruta = "images/default_image.jpg";
} else {
	$ruta = "http://$direccion_host/bgccandidate/cargaArchivos/".$datos['photo'];
}
 $pdf->Image($ruta, 0, 40, 64, 64, '', null, '', null, null, 'C', false, false, 0, false, false, false);
#$pdf->Image($file, 0, 40, 64, 64, '', null, $align=“, null, null, 'C', false, false, 0, false, false, false)
$pdf->Cell(0,0,"Date: $fecha_documento");


//POSICION DE LA TABLA

//$pdf->SetX(160);
$pdf->SetY(120);
$pdf->SetFont($LatoBold,'', 11);
$pdf->setTextColor(0,0,0);
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
.dataCandidato {
	font-family: "LatoRegular";
	text-align: center;
}
.tituloCandidato {
	font-family: "LatoBold";
}
</style>
<table border="1" cellpadding="2" cellspacing="0" nobr="true">
<tr>
<th colspan="2" align="center">Candidate Data</th>
</tr>
<tr>
<td class="tituloCandidato">Company</td>
<td class="dataCandidato">'.$candidate_company.'</td>
</tr>
<tr>
<td class="tituloCandidato">Name</td>
<td class="dataCandidato">'.$candidate_name.'</td>
</tr>
</table>';

$tbl2 = '
<style>
@font-face {
	font-family: "LatoRegular";
	src: url("Lato-Regular.ttf") format("truetype");
}
@font-face {
	font-family: "LatoBold";
	src: url("Lato-Bold.ttf") format("truetype");
}
.dataCandidato {
	font-family: "LatoRegular";
	text-align: center;
}
.tituloCandidato {
	font-family: "LatoBold";
}
</style>
<table border="0" nobr="true" style="text-align: center;">
<tr>
<th colspan="5" class="tituloCandidato" align="center">ELIGIBLE CANDIDATE FOR RECRUITMENT</th>
</tr>
<tr>
<th colspan="5" align="center"></th>
</tr>
<tr>
<td class="dataCandidato" style="border: 1px solid black; '.$dadaroom[0].'">YES</td>
<td class=""></td>
<td class="dataCandidato" style="border: 1px solid black; '.$dadaroom[1].'">NO</td>
<td class=""></td>
<td class="dataCandidato" style="border: 1px solid black; '.$dadaroom[2].'">TO CONSIDER</td>
</tr>
</table>';

$asdasd = '
<table border="0" cellpading="3" nobr="true" style="text-align: center;">
<tr>
<th colspan="5" align="center"></th>
</tr>
<tr>
<th colspan="5" align="center"></th>
</tr>
<tr>
<th colspan="5" align="center">FINAL CONCLUSIONS</th>
</tr>
<tr>
<th colspan="5" align="center"></th>
</tr>
<tr>
<td style="border: 1px solid black; font-family: LatoRegular;" colspan="5"><br><br>'.$comentarios_ultimos.'<br></td>
</tr>
</table>';

$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->Ln();
$pdf->writeHTML($tbl2, true, false, false, false, '');
$pdf->Ln(5);
$pdf->writeHTML($asdasd, true, false, false, false, '');
// reset pointer to the last page
$pdf->lastPage();

$n = 1;
// ---------------------------------------------------------
$pdf->AddPage();
//DEFINIMOS LA FUENTE Y ESTILOS QUE USAREMOS EN LA SEGUNDA PAGINA
$pdf->SetFont($LatoBold,'',12);
$pdf->setTextColor(0,0,0);
//TITULO DE LA PAGINA
$pdf->Cell(0, 0, "$n. Personal Data", 0, 1);
$pdf->Ln();
$tbl = <<<EOD
<style>
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
table.table-top {
	border-left: 1px solid black;
	border-right: 1px solid black;
	border-top: 1px solid black;
	border-bottom: 1px none black;

}
table.table-middle {
	border-left: 1px solid black;
	border-right: 1px solid black;
	border-top: 1px none black;
	border-bottom: 1px none black;

}
table.table-bottom {
	border-left: 1px solid black;
	border-right: 1px solid black;
	border-top: 1px none black;
	border-bottom: 1px solid black;

}
</style>
<table class="table-top" cellpadding="2" cellspacing="0">
<tr>
<td width="20%">Name:</td>
<td width="80%" class="information-holder">$candidate_name</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
<td>Date of Birth:</td>
<td class="information-holder">$candidate_birth</td>
<td>Age:</td>
<td class="information-holder">$candidate_age</td>
<td>Job Position Requested:</td>
<td class="information-holder">$candidate_position_requested</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
	 <td>Nationality:</td>
	 <td class="information-holder">$candidate_nationality</td>
	 <td>Gender:</td>
	 <td class="information-holder">$candidate_gender</td>
	 <td>Religion:</td>
	 <td class="information-holder">$candidate_religion</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
<td width="14%">Address:</td>
<td width="36%" class="information-holder">$candidate_address</td>
<td width="14%">Ext. Num:</td>
<td width="11%" class="information-holder">$candidate_ext_num</td>
<td width="13%">Int. Num:</td>
<td width="12%" class="information-holder">$candidate_int_num</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
	 <td width="19%">Neighborhood:</td>
	 <td width="21%" class="information-holder">$candidate_neighborhood</td>
	 <td width="7%">City:</td>
	 <td width="13%" class="information-holder">$candidate_city</td>
	 <td width="8%">State:</td>
	 <td width="12%" class="information-holder">$candidate_state</td>
	 <td width="13%">ZIP Code:</td>
	 <td width="7%" class="information-holder">$candidate_zip</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
	 <td>Marital Status:</td>
	 <td class="information-holder">$candidate_marital_status</td>
	 <td>Home Num:</td>
	 <td class="information-holder">$candidate_home_num</td>
	 <td>Number for messages:</td>
	 <td class="information-holder">$candidate_messages_num</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
	 <td width="16%">Mobile Num:</td>
	 <td width="25%" class="information-holder">$candidate_mobile_num</td>
	 <td width="10%">Email:</td>
	 <td width="49%" class="information-holder">$candidate_email</td>
</tr>
</table>
<table class="table-middle" cellpadding="2" cellspacing="0">
<tr>
	 <td>Time in actual address:</td>
	 <td class="information-holder">$candidate_time_actual_addres</td>
	 <td>Transit time to office:</td>
	 <td class="information-holder">$candidate_time_transit</td>
	 <td>Transportation mode:</td>
	 <td class="information-holder">$candidate_transportation_mode</td>
</tr>
</table>

EOD;
$tbl .= obtenerViviendasAnteriores($id_can);
$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->lastPage();
$n++;

// ---------------------------------------------------------

$pdf->AddPage();
$pdf->SetFont($LatoBold,'', 12);
$pdf->setTextColor(0,0,0);
$pdf->Cell(0, 0, "$n. Documents", 0, 1);
$tbl=obtenerDocumentos($id_can,$candidate_document_comments);
$pdf->Ln();
$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->lastPage();
$n++;
// ---------------------------------------------------------

$pdf->AddPage();
$pdf->SetFont($LatoBold,'', 12);
$pdf->setTextColor(0,0,0);
$pdf->Cell(0, 0, "$n. Family Environment", 0, 1);
$n++;
$tbl=obtenerFamiliares($id_can);
$pdf->Ln();
$pdf->writeHTML($tbl, true, false, false, false, '');
#$pdf->lastPage();


// ---------------------------------------------------------

#$pdf->AddPage();
$pdf->SetFont($LatoBold,'', 12);
$pdf->setTextColor(0,0,0);
$pdf->Cell(0, 0, "$n. Studies Record", 0, 1);
$n++;
$pdf->Ln();
$tbl=obtenerEscolares($id_can);
$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->Cell(0, 0, "$n. Break in Career", 0, 1);
$n++;
$pdf->SetFont($LatoBold,'', 12);
$pdf->Ln();
$table1 = '<style>
@font-face {
	font-family: "LatoRegular";
	src: url("Lato-Regular.ttf") format("truetype");
}
@font-face {
	font-family: "LatoBold";
	src: url("Lato-Bold.ttf") format("truetype");
}
</style>
<table border="1" cellpadding="2">
<tr>
<td style="font-family: LatoRegular;">'.$candidate_break_career.'</td>
</tr>
</table>';
$pdf->writeHTML($table1);
$pdf->lastPage();

#print_r($skill);
// ---------------------------------------------------------

if($laboralesa['Laborales'] != 0)
{
	if($project['project'] == "HSBC" && $skill['skill'] != "Trainee")
	{
		$pdf->AddPage();
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Cell(0, 0, "$n. Labor References", 0, 1);
		$n++;
		$pdf->Ln();
		$tda = 0;
		foreach (obtenerLaborales($id_can) as $numeroDeLaboral => $laboral) {
			if ( $numeroDeLaboral == 1)
			{
				$pdf->writeHTML($laboral);
				$pdf->lastPage();
			} else {
				$pdf->AddPage();
				$pdf->writeHTML($laboral);
				$pdf->lastPage();
			}
		}
		$pdf->AddPage();
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Cell(0, 0, "$n. Break in Employment ", 0, 1);
		$n++;
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Ln();
		$table1 = '<style>
		@font-face {
			font-family: "LatoRegular";
			src: url("Lato-Regular.ttf") format("truetype");
		}
		@font-face {
			font-family: "LatoBold";
			src: url("Lato-Bold.ttf") format("truetype");
		}
		</style>
		<table border="1" cellpadding="2">
		<tr>
		<td style="font-family: LatoRegular;">'.$candidate_break_labor.'</td>
		</tr>
		</table>';
		$pdf->writeHTML($table1);
		#$pdf->lastPage();
	}
	if($skill['skill'] != "Trainee" && $project['project'] != "HSBC")
	{
		$pdf->AddPage();
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Cell(0, 0, "$n. Labor References", 0, 1);
		$n++;
		$pdf->Ln();
		$tda = 0;
		foreach (obtenerLaborales($id_can) as $numeroDeLaboral => $laboral) {
			if ( $numeroDeLaboral == 1) $pdf->writeHTML($laboral);
			else {
				$calculo = $numeroDeLaboral%2;
				if($calculo != 0)
				{
					$pdf->AddPage();
					$pdf->writeHTML($laboral);
					$tda++;
				} else {
					$tda--;
					$pdf->writeHTML($laboral);
				}
			}
		}
		if($tda == 1)
		{
			$pdf->lastPage();
			$pdf->AddPage();
		}
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Cell(0, 0, "$n. Break in Employment ", 0, 1);
		$n++;
		$pdf->SetFont($LatoBold,'', 12);
		$pdf->Ln();
		$table1 = '<style>
		@font-face {
			font-family: "LatoRegular";
			src: url("Lato-Regular.ttf") format("truetype");
		}
		@font-face {
			font-family: "LatoBold";
			src: url("Lato-Bold.ttf") format("truetype");
		}
		</style>
		<table border="1" cellpadding="2">
		<tr>
		<td style="font-family: LatoRegular;">'.$candidate_break_labor.'</td>
		</tr>
		</table>';
		$pdf->writeHTML($table1);
		$pdf->lastPage();
		$pdf->AddPage();
	}
	if($skill['skill'] == "Trainee")
	{
		$pdf->AddPage();
	}
} else {
	$pdf->AddPage();
}
// ---------------------------------------------------------
$pdf->SetFont($LatoBold,'', 12);
$pdf->Cell(0, 0, "$n. Personal References", 0, 1);
$n++;
$contadorReferencias=1;

foreach (obtenerReferencias($id_can) as $numeroReferencia => $referencia)
{
	if ($contadorReferencias > 4)
	{
		$pdf->AddPage();
		$contadorReferencias=1;
	}
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->setTextColor(100,100,100);
	#$pdf->Ln();
	#$pdf->Cell(0, 0, "$numeroReferencia) Personal References", 0, 1);
	$pdf->Ln();
	$pdf->setTextColor(0,0,0);
	$pdf->SetFont($LatoRegular,'', 10);
	$pdf->writeHTML($referencia);
	$contadorReferencias++;
}
#$pdf->AddPage();
$pdf->SetFont($LatoBold,'', 12);
$pdf->Cell(0, 0, "$n. Have you worked in any government entity, political party or NGO?", 0, 1);
$n++;
$pdf->SetFont($LatoBold,'', 12);
$pdf->Ln();
$table1 = '<style>
@font-face {
	font-family: "LatoRegular";
	src: url("Lato-Regular.ttf") format("truetype");
}
@font-face {
	font-family: "LatoBold";
	src: url("Lato-Bold.ttf") format("truetype");
}
</style>
<table border="1" cellpadding="2">
<tr>
<td style="font-family: LatoRegular;">'.$candidate_gubernamental_comment.'</td>
</tr>
</table>';
$pdf->writeHTML($table1);

$pdf->lastPage();
if($skill['skill'] != "Trainee")
{
	$pdf->AddPage();
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->Cell(0, 0, "$n. INFONAVIT Report", 0, 1);
	$n++;
	$pdf->Ln(0, FALSE);
	$pdf->Image('images/infonavit_report.png', null, null, 180, 82);
	$pdf->Ln(80, FALSE);
	$pdf->Ln();
	$pdf->Cell(0, 0, "$n. Hiden Jobs", 0, 1);
	$n++;
	$hiddenJobsText="\"La Gerencia de Administración del Patrimonio Social y de Servicios al Trabajador, privatizo la información sobre los movimientos patronales, motivo por el cual ya no se pueden verificar desde algún sito oficial. Es necesario que el candidato realice su alta en sistema y agregue información personal para generar una contraseña y poder revisar sus movimientos\"\nTCS está avisado y enterado de esta situación.";
	$pdf->SetFontSize(10);
	$pdf->MultiCell(null, null, $hiddenJobsText, 0, 'C');
	$pdf->lastPage();
}
// 
if($datos['educ'] != "")
{
	$pdf->AddPage();
	$latos = $db->query("SELECT * FROM ad_bgc WHERE id_can = '$id_can' AND meta_key='professional_license'")->fetch_assoc();
	$loomp = "http://$direccion_host/bgccandidate/cargaArchivos/".$latos['meta_value'];
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->Cell(0, 0, "$n. Professional License", 0, 1);
	$pdf->Image($loomp, null, null, 180, 100);
	$pdf->lastPage();
	$n++;
}
if($visit['meta_key'] == "visita_realizada")
{
	$conc = $db->query("SELECT * FROM ad_bgc WHERE id_can = '$id_can' AND meta_key='conclusion_visita'")->fetch_assoc();
	$pdf->AddPage();
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->Cell(0, 0, "$n. Visit Conclusions", 0, 1);
	$pdf->Ln();
	$pdf->SetFontSize(10);
	$pdf->MultiCell(null, null, $conc['meta_value'], 0);
	$pdf->lastPage();
	$n++;
	$pdf->AddPage();
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->Cell(0, 0, "$n. Location", 0, 1);
	$pdf->Image(obtenerCoordenadas($id_can),null,null,180,120);
	$pdf->lastPage();
	$n++;
	#echo "SELECT * FROM ad_bgc WHERE id_can = '$id_can' AND meta_key IN('foto_visita_1','foto_visita_2','foto_visita_3','foto_visita_4')";
	$mosa = $db->query("SELECT * FROM ad_bgc WHERE id_can = '$id_can' AND meta_key IN('foto_visita_1','foto_visita_2','foto_visita_3','foto_visita_4')")->fetch_all(MYSQLI_ASSOC);
	foreach ($mosa as $key => $value)
	{
		if ($value['meta_key'] == "foto_visita_1")
		{
			$foto1= "http://$direccion_host/bgccandidate/cargaArchivos/".$value['meta_value'];
		}
		if ($value['meta_key'] == "foto_visita_2")
		{		
			$foto2= "http://$direccion_host/bgccandidate/cargaArchivos/".$value['meta_value'];
		}
		if ($value['meta_key'] == "foto_visita_3")
		{
			$foto3= "http://$direccion_host/bgccandidate/cargaArchivos/".$value['meta_value'];
		}
		if ($value['meta_key'] == "foto_visita_4")
		{
			$foto4= "http://$direccion_host/bgccandidate/cargaArchivos/".$value['meta_value'];
		}
		#echo $value['meta_value'];
	}
	$pdf->AddPage();
	$pdf->SetFont($LatoBold,'', 12);
	$pdf->Cell(0, 0, "$n. Photographs", 0, 1);
	$pdf->Image($foto1, null, null, 85, 100, '', null, '', true, 600, 'L', false, false, 0, false, false, false);
	$pdf->Image($foto2, null, null, 85, 100, '', null, '', true, 600, 'R', false, false, 0, false, false, false);
	$pdf->Ln(110, FALSE);
	$pdf->Image($foto3, null, null, 85, 100, '', null, '', true, 600, 'L', false, false, 0, false, false, false);
	$pdf->Image($foto4, null, null, 85, 100, '', null, '', true, 600, 'R', false, false, 0, false, false, false);
	$pdf->lastPage();
}

// ---------------------------------------------------------

//Close and output PDF document

$pdf->Output('BGC PDF of '.$candidate_name.' TAT-'.$id_can.'.pdf', 'I');



//============================================================+

// END OF FILE

//============================================================+

