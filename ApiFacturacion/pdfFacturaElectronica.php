<?php
    //librerias pdf
define('FPDF_FONTPATH', 'font/');
//require_once('fpdf/fpdf.php');
require_once('fpdf/pdf.php');

//librerias de acceso a datos
require_once('ado/clsCompartido.php');
require_once('ado/clsEmisor.php');
require_once('ado/clsVenta.php');
require_once('ado/clsCliente.php');

//librerias de codigo QR
require_once('phpqrcode/qrlib.php');

//otras librerias
require_once('cantidad_en_letras.php');

//crear los objetos de acceso a datos
$objVenta = new clsVenta();
$objEmisor = new clsEmisor();
$objCompartido = new clsCompartido();
$objCliente = new clsCliente();

//Obtener los datos necesarios para mostrarlos en el PDF - INICIO
$venta = $objVenta->obtenerComprobanteId($_GET['id']);
$venta = $venta->fetch(PDO::FETCH_NAMED); //obtengo y guardo en un array
//echo  $venta['serie'];

$detalle = $objVenta->listarDetalleComprobanteId($_GET['id']);

$emisor = $objEmisor->obtenerEmisor($venta['idemisor']);
$emisor = $emisor->fetch(PDO::FETCH_NAMED);

$tipo_comprobante = $objCompartido->obtenerComprobante($venta['tipocomp']);
$tipo_comprobante = $tipo_comprobante->fetch(PDO::FETCH_NAMED);

$cliente = $objCliente->consultarClientePorCodigo($venta['codcliente']);
$cliente = $cliente->fetch(PDO::FETCH_NAMED);
//Obtener los datos necesarios para mostrarlos en el PDF - FIN

//CREAR EL PDF - INICIO
$pdf=new PDF_MC_Table('L','mm','Legal');
$pdf->SetMargins(10,15);///izquierda,arriba
$pdf->AliasNbPages();
//$pdf->AddPage(); horizontal
$pdf->AddPage('P', 'A4'); //Orientacion y tamaño de la pagina
$pdf->SetFont('Arial','b',12);

/*
// left-Top corner of the img is at (10, 10)
  // Width and height are 35×25
   */
$pdf->Image('logo_empresa.jpg', 10, 10, 35, 25);
$pdf->Ln(18);

$pdf->SetFont('Arial', '', 8);
$pdf->cell(100, 6, $emisor['ruc'] . '-' . $emisor['razon_social']);

$pdf->SetFont('Arial', 'B', 12);
$pdf->cell(80, 6, $emisor['ruc'], 'LRT', 1, 'C', 0);

$pdf->SetFont('Arial', '', 8);
$pdf->cell(100, 6, $emisor['direccion']);

$pdf->SetFont('Arial', 'B', 12);
$pdf->cell(80, 6, $tipo_comprobante['descripcion'] . ' ELECTRONICA', 'LR', 1, 'C', 0);
$pdf->cell(100);

$pdf->cell(80, 6, $venta['serie'] . '-' . $venta['correlativo'], 'BLR', 1, 'C', 0);

$pdf->SetAutoPageBreak('auto', 2);
$pdf->SetDisplayMode(75);



$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->cell(30, 6, 'RUC/DNI', 0 , 0, 'L', 0);
$pdf->cell(5, 6, ':', 0 , 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->cell(30, 6, $cliente['nrodoc'], 0 , 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->cell(30, 6, 'CLIENTE', 0 , 0, 'L', 0);
$pdf->cell(5, 6, ':', 0 , 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->cell(30, 6, $cliente['razon_social'], 0 , 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->cell(30, 6, 'DIRECCION', 0 , 0, 'L', 0);
$pdf->cell(5, 6, ':', 0 , 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->cell(30, 6, $cliente['direccion'], 0 , 1, 'L', 0);

$pdf->SetFont('Arial', 'B', 8);
$pdf->cell(30, 6, 'FECHA EMISION', 0 , 0, 'L', 0);
$pdf->cell(5, 6, ':', 0 , 0, 'L', 0);
$pdf->SetFont('Arial', '', 8);
$pdf->cell(30, 6, $venta['fecha_emision'], 0 , 1, 'L', 0);

//$pdf->Cell(0,5,'Listado de Reportes del mes de '. $emisor['razon_social'],0,1,'C');
$pdf->Ln(3);
$incr=0;
$pdf->SetFont('Arial', 'B', 8);
$pdf->cell(10, 6, 'ITEM', 1 , 0, 'C', 0);
$pdf->cell(15, 6, 'CANTIDAD', 1 , 0, 'C', 0);
$pdf->cell(115, 6, 'PRODUCTO', 1 , 0, 'C', 0);
$pdf->cell(20, 6, 'Val.Unit', 1 , 0, 'C', 0);
$pdf->cell(20, 6, 'SUBTOTAL', 1 , 1, 'C', 0);

$pdf->SetFont('Arial', '', 8);

/*
$numero=0;*/
$fill=false;

//AQUI DEFINEN LA CANTIDAD DE COLUMNAS QUE VAN A REQUERIR.
$pdf->SetWidths(array(10,15,115,20,20));

while($fila = $detalle->fetch(PDO::FETCH_NAMED))
{ 
    $pdf->Row(array($fila['item'],$fila['cantidad'],$fila['nombre'],$fila['valor_unitario'],$fila['valor_total']));   
}


$pdf->cell(160, 6, 'OP. GRAVADAS', '', 0, 'R', 0);
$pdf->cell(20, 6, $venta['op_gravadas'], 1, 1, 'R', 0);
$pdf->cell(160, 6, 'IGV (18%)', '', 0, 'R', 0);
$pdf->cell(20, 6, $venta['igv'], 1, 1, 'R', 0);
$pdf->cell(160, 6, 'OP. EXONERADAS', '', 0, 'R', 0);
$pdf->cell(20, 6, $venta['op_exoneradas'], 1, 1, 'R', 0);
$pdf->cell(160, 6, 'OP. INAFECTAS', '', 0, 'R', 0);
$pdf->cell(20, 6, $venta['op_inafectas'], 1, 1, 'R', 0);
$pdf->cell(160, 6, 'IMPORTE TOTAL', '', 0, 'R', 0);
$pdf->cell(20, 6, $venta['total'], 1, 1, 'R', 0);

$pdf->ln(10);

$pdf->cell(160, 6, utf8_decode('SON: ' . CantidadEnLetra($venta['total'])), 0 , 0, 'C', 0);

$pdf->ln(20);


//Crear el QR - INICIO

//Esctructura segun SUNAT
//RUC | TIPO DE DOCUMENTO | SERIE | NUMERO | MTO TOTAL IGV | MTO TOTAL DEL COMPROBANTE | FECHA DE EMISION | TIPO DE DOCUMENTO ADQUIRENTE | NUMERO DE DOCUMENTO ADQUIRENTE |
$ruc = $emisor['ruc'];
$tipo_documento = $venta['tipocomp'];
$serie = $venta['serie'];
$correlativo = $venta['correlativo'];
$igv = $venta['igv'];
$total = $venta['total'];
$fecha = $venta['fecha_emision'];
$tipo_doc_cliente = $cliente['tipodoc'];
$nro_doc_cliente = $cliente['nrodoc'];

$nombrepdf = $ruc . '-' . $tipo_documento . '-' . $serie . '-' . $correlativo;
$texto_qr = $ruc . '|' . $tipo_documento . '|' . $serie . '|' . $correlativo . '|' . $igv . '|' . $total . '|' . $fecha . '|' . $tipo_doc_cliente . '| ' . $nro_doc_cliente . '|';


$ruta_qr = "iqr/".$nombrepdf . '.png';

QRcode::png($texto_qr, $ruta_qr, 'Q', 15, 0); //crear la imagen QR

$pdf->Image($ruta_qr, 80, $pdf->GetY(), 25, 25);

//Crear el QR - FIN
$pdf->Ln(30);
$pdf->cell(160, 6, utf8_decode('Representación Impresa de la factura electrónica'), 0, 0, 'C', 0);
$pdf->Ln(10);
$pdf->cell(160, 6, utf8_decode('Este comprobante puede ser consultado en ceti.org.pe'), 0, 0, 'C', 0);

$pdf->Output('I', $nombrepdf . '.pdf');


?>