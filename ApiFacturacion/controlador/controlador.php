<?php
require_once("../ado/clsCompartido.php");
require_once("../ado/clsEmisor.php");
require_once("../ado/clsVenta.php");
require_once("../ado/clsNotaCredito.php");
require_once("../ado/clsNotaDebito.php");
require_once("../ado/clsCliente.php");

require_once("../xml.php");
require_once("../cantidad_en_letras.php");
require_once("../ApiFacturacion.php");

$accion = $_POST['accion'];

controlador($accion);

function controlador($accion){

	$objCompartido = new clsCompartido();
	$objEmisor = new clsEmisor();
	//ARCHIVO PHP xml.php
	$generadoXML = new GeneradorXML();
	//ARCHIVO PHP ApiFacturacion.php
	$api = new ApiFacturacion();
	$objVenta = new clsVenta();
	$objNC = new clsNotaCredito();
	$objND = new clsNotaDebito();
	$objCliente = new clsCliente();

	switch ($accion) {

		case 'LISTAR_SERIES':
			
			$series = $objCompartido->listarSerie($_POST['tipocomp']);
			$series = $series->fetchAll(PDO::FETCH_NAMED);
			$series = array("series"=>$series);
			echo json_encode($series);			
			break;
		
		case 'OBTENER_CORRELATIVO':
			$serie = $objCompartido->obtenerSerie($_POST['idserie']);
			$serie = $serie->fetch(PDO::FETCH_NAMED);
			$correlativo = $serie['correlativo']+1;
			echo $correlativo;
			break;

		case 'CONSULTA_RUC':

			$ruc = $_POST['ruc'];
			$ws = "https://dni.optimizeperu.com/api/company/$ruc?format=json";

			$header = array();

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);
			curl_setopt($ch,CURLOPT_URL,$ws);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
			curl_setopt($ch,CURLOPT_TIMEOUT,30);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
			//para ejecutar los procesos de forma local en windows
			//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/../cacert.pem");

			$datos = curl_exec($ch);
			curl_close($ch);

			echo $datos;
			break;

		case 'CONSULTA_DNI':

			$dni = $_POST['dni'];
			$ws = "https://dni.optimizeperu.com/api/persons/$dni?format=json";

			$header = array();

			$ch = curl_init();
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);
			curl_setopt($ch,CURLOPT_URL,$ws);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
			curl_setopt($ch,CURLOPT_TIMEOUT,30);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
			//para ejecutar los procesos de forma local en windows
			//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
			curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/../cacert.pem");

			$datos = curl_exec($ch);
			curl_close($ch);

			echo $datos;
			break;


		case 'BUSCAR_PRODUCTO':
			$productos = $objCompartido->listarProducto($_POST['filtro']);
			$productos = $productos->fetchAll(PDO::FETCH_NAMED);
			$productos = array("productos"=>$productos);
			echo json_encode($productos);			
			break;			

		case 'ADD_PRODUCTO':

			// ----- INICIO LOGICA DE CARRITO ----- //

			$producto = $objCompartido->obtenerProducto($_POST['codigo']);
			$producto = $producto->fetch(PDO::FETCH_NAMED);

			$cantidad_agregar = 1;

			if(isset($_POST['precio'])){
				$producto['precio'] = $_POST['precio'];
			}

			if(isset($_POST['cantidad'])){
				$cantidad_agregar = $_POST['cantidad'];
			}

			session_start();

			if(!isset($_SESSION['carrito'])){
				$_SESSION['carrito'] = array();
			}

			$carrito = $_SESSION['carrito'];

			$item = count($carrito)+1;
			$cantidad = $cantidad_agregar;
			$existe = false;
			foreach ($carrito as $k => $v) {
				if($v['codigo']==$_POST['codigo']){
					$item = $k;
					$existe = true;
					break;
				}
			}

			if(!$existe){
				$carrito[$item] = array(
						'codigo'=>$producto['codigo'],
						'nombre'=>$producto['nombre'],
						'precio'=>$producto['precio'],
						'unidad'=>$producto['unidad'],
						'codigoafectacion'=>$producto['codigoafectacion'],
						'cantidad'=>$cantidad
						);

			}else{
				//$carrito[$item]['cantidad']++;
				$carrito[$item]['cantidad'] = $carrito[$item]['cantidad'] + $cantidad_agregar;
			}

			$_SESSION['carrito'] = $carrito;

			//------------------ FIN LOGICA DE CARRITO ---------- //

			//-------------- INICIO DE CALCULO DE TOTALES -------//
			$op_gravadas=0.00;
			$op_exoneradas=0.00;
			$op_inafectas=0.00;
			$igv=0.0;
			$igv_porcentaje=0.18;

			foreach ($carrito as $K => $v) {
				if($v['codigoafectacion']=='10'){
					$op_gravadas = $op_gravadas+$v['precio']*$v['cantidad'];
				}

				if($v['codigoafectacion']=='20'){
					$op_exoneradas = $op_exoneradas+$v['precio']*$v['cantidad'];
				}

				if($v['codigoafectacion']=='30'){
					$op_inafectas = $op_inafectas+$v['precio']*$v['cantidad'];
				}												
			}

			$igv = $op_gravadas * $igv_porcentaje;

			$total = $op_gravadas + $op_exoneradas + $op_inafectas + $igv;

			//----- FIN DEL CALCULO DE TOTALES --------//

			//------ INICIO DE LA TABLITA DEL CARRITO ---- //

			echo "<table class='table table-bordered table-hover'>";
			echo "<tr>";
			echo "<th>ITEM</th><th>CANT</th><th>UND</th><th>PRODUCTO</th><th>VU</th><th>SUBT</th>";
			echo "</tr>";
			foreach($carrito as $k=>$v){
				echo "<tr>";
				echo "<td>".$k."</td><td>".$v['cantidad']."</td><td>".$v['unidad']."</td><td>".$v['nombre']."</td><td>".$v['precio']."</td><td>".($v['precio']*$v['cantidad'])."</td>";
				echo "</tr>";
			}

			echo "<tr><td colspan='5' align='right'>OP. GRAVADAS</td><td>".$op_gravadas."</td></tr>";
			echo "<tr><td colspan='5' align='right'>IGV(18%)</td><td>".$igv."</td></tr>";			
			echo "<tr><td colspan='5' align='right'>OP. EXONERADAS</td><td>".$op_exoneradas."</td></tr>";
			echo "<tr><td colspan='5' align='right'>OP. INAFECTAS</td><td>".$op_inafectas."</td></tr>";						
			echo "<tr><td colspan='5' align='right'><b>TOTAL</b></td><td><b>".$total."</b></td></tr>";		
			echo "</table>";
			//------------ FIN DE LA TABLITA DEL CARRITO ------//
			break;	


		case 'CANCELAR_CARRITO':
			session_start();
			session_destroy();
			break;


		case 'GUARDAR_VENTA':
			session_start();

			//logica de ventas
			//--------------------------
			//fin logica de ventas

			//INICIO PROCESO FACTURACION

			//$generadoXML = new Funciones();

			//obtenemos los datos del emisor de la BD
			$idemisor = $_POST['idemisor'];
			$emisor = $objEmisor->obtenerEmisor($idemisor);
			$emisor = $emisor->fetch(PDO::FETCH_NAMED);


			$cliente = array(
				'tipodoc'		=> $_POST['tipodoc'],//6->ruc, 1-> dni 
				'ruc'			=> $_POST['nrodoc'], 
				'razon_social'  => $_POST['razon_social'], 
				'direccion'		=> $_POST['direccion'],
				'pais'			=> 'PE'
				);	

			$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);

			if($cliente_existe->rowCount()>0){
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}else{
				$objCliente->insertarCliente($cliente);
				$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}
			$idcliente = $cliente_existe['id'];

			$carrito = $_SESSION['carrito'];
			$detalle = array();
			$igv_porcentaje = 0.18;

			$op_gravadas=0.00;
			$op_exoneradas=0.00;
			$op_inafectas=0.00;
			$igv = 0;

			foreach ($carrito as $k => $v){

				$producto = $objCompartido->obtenerProducto($v['codigo']);
				$producto = $producto->fetch(PDO::FETCH_NAMED);

				$afectacion = $objCompartido->obtenerRegistroAfectacion($producto['codigoafectacion']);
				$afectacion = $afectacion->fetch(PDO::FETCH_NAMED);

				$igv_detalle =0;
				$factor_porcentaje = 1;
				if($producto['codigoafectacion']==10){
					$igv_detalle = $v['precio']*$v['cantidad']*$igv_porcentaje;
					$factor_porcentaje = 1+ $igv_porcentaje;
				}

				$itemx = array(
					'item' 				=> $k,
					'codigo'			=> $v['codigo'],
					'descripcion'		=> $v['nombre'],
					'cantidad'			=> $v['cantidad'],
					'valor_unitario'	=> $v['precio'],
					'precio_unitario'	=> $v['precio']*$factor_porcentaje,
					'tipo_precio'		=> $producto['tipo_precio'], //ya incluye igv
					'igv'				=> $igv_detalle,
					'porcentaje_igv'	=> $igv_porcentaje*100,
					'valor_total'		=> $v['precio']*$v['cantidad'],
					'importe_total'		=> $v['precio']*$v['cantidad']*$factor_porcentaje,
					'unidad'			=> $v['unidad'],//unidad,
					'codigo_afectacion_alt'	=> $producto['codigoafectacion'],
					'codigo_afectacion'	=> $afectacion['codigo_afectacion'],
					'nombre_afectacion'	=> $afectacion['nombre_afectacion'],
					'tipo_afectacion'	=> $afectacion['tipo_afectacion']			 
				);

				$itemx;

				$detalle[] = $itemx;
				// codigo de afectacion 10 = operaciones grabadas 
				// 20 operaciones Exoneradas
				// 30 = operaciones INAFECTAS	
				if($itemx['codigo_afectacion_alt']==10){
					$op_gravadas = $op_gravadas + $itemx['valor_total'];
				}

				if($itemx['codigo_afectacion_alt']==20){
					$op_exoneradas = $op_exoneradas + $itemx['valor_total'];
				}				

				if($itemx['codigo_afectacion_alt']==30){
					$op_inafectas = $op_inafectas + $itemx['valor_total'];
				}

				$igv = $igv + $igv_detalle;				
			}


			$total = $op_gravadas + $op_exoneradas + $op_inafectas + $igv;

			$idserie = $_POST['idserie'];

			$seriex = $objCompartido->obtenerSerie($idserie);
			$seriex = $seriex->fetch(PDO::FETCH_NAMED);

			$comprobante =	array(
					'tipodoc'		=> $_POST['tipocomp'],
					'idserie'		=> $idserie,
					'serie'			=> $seriex['serie'],
					'correlativo'	=> $seriex['correlativo']+1, //INCREMENTO EL CORRELATIVO DE LA SERIE
					'fecha_emision' => $_POST['fecha_emision'],
					'moneda'		=> $_POST['moneda'], //PEN->SOLES; USD->DOLARES
					'total_opgravadas'	=> $op_gravadas,
					'igv'			=> $igv,
					'total_opexoneradas' => $op_exoneradas,
					'total_opinafectas'	=> $op_inafectas,
					'total'			=> $total,
					'total_texto'	=> CantidadEnLetra($total),
					'codcliente'	=> $idcliente
				);			

			$objCompartido->actualizarSerie($idserie, $comprobante['correlativo']);
			///$nombre=Nombre que servira para el guardado del archivo  xml, en la carpeta del mismo nombre
			$nombre = $emisor['ruc'].'-'.$comprobante['tipodoc'].'-'.$comprobante['serie'].'-'.$comprobante['correlativo'];

			$ruta = "../xml/";
			if($comprobante['tipodoc']=='01' || $comprobante['tipodoc']=='03'){
				// funcion de la clase GeneradorXML del archivo xml.php
				$generadoXML->CrearXMLFactura($ruta.$nombre, $emisor, $cliente, $comprobante, $detalle);
			}

			///funcion de la clase ApiFacturacion archivo  ApiFacturacion.php
			//$nombre = nombr del archivo xml
			// "../" = ruta del certificado
			// "../xml/"= ruta donde se guardara e archivo xml
			// "../cdr/" = ruta donde se guardara el cdr
			$api->EnviarComprobanteElectronico($emisor,$nombre,"../","../xml/","../cdr/");
			//FIN FACTURACION ELECTRONICA


			//REGISTRO EN BASE DE DATOS

			$objVenta->insertarVenta($idemisor, $comprobante);
			$venta = $objVenta->obtenerUltimoComprobanteId();
			$venta = $venta->fetch(PDO::FETCH_NAMED);

			$objVenta->insertarDetalle($venta['id'],$detalle);

			//FIN DE REGISTRO EN BASE DE DATOS
			echo "<br/>VENTA CORRECTA";
			session_destroy(); // elimina sesion blanquea el carrito

			//MODO DE IMPRESION INICIO
			echo "<script>window.open('./apifacturacion/pdfFacturaElectronica.php?id=".$venta['id']."','_blank')</script>";	
			//MODO DE IMPRESION FIN

			break;

		case 'GUARDAR_NC':
			session_start();

			//logica de nota de credito
			//--------------------------
			//fin logica de nota de credito


			//INICIO PROCESO FACTURACION

			//$generadoXML = new Funciones();

			//obtenemos los datos del emisor de la BD
			$idemisor = $_POST['idemisor'];
			$emisor = $objEmisor->obtenerEmisor($idemisor);
			$emisor = $emisor->fetch(PDO::FETCH_NAMED);


			$cliente = array(
				'tipodoc'		=> $_POST['tipodoc'],//6->ruc, 1-> dni 
				'ruc'			=> $_POST['nrodoc'], 
				'razon_social'  => $_POST['razon_social'], 
				'direccion'		=> $_POST['direccion'],
				'pais'			=> 'PE'
				);	

			$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);

			if($cliente_existe->rowCount()>0){
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}else{
				$objCliente->insertarCliente($cliente);
				$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}
			$idcliente = $cliente_existe['id'];

			$carrito = $_SESSION['carrito'];
			$detalle = array();
			$igv_porcentaje = 0.18;



			$op_gravadas=0.00;
			$op_exoneradas=0.00;
			$op_inafectas=0.00;
			$igv = 0;

			foreach ($carrito as $k => $v) {

				$producto = $objCompartido->obtenerProducto($v['codigo']);
				$producto = $producto->fetch(PDO::FETCH_NAMED);

				$afectacion = $objCompartido->obtenerRegistroAfectacion($producto['codigoafectacion']);
				$afectacion = $afectacion->fetch(PDO::FETCH_NAMED);

				$igv_detalle =0;
				$factor_porcentaje = 1;
				if($producto['codigoafectacion']==10){
					$igv_detalle = $v['precio']*$v['cantidad']*$igv_porcentaje;
					$factor_porcentaje = 1+ $igv_porcentaje;
				}

				$itemx = array(
					'item' 				=> $k,
					'codigo'			=> $v['codigo'],
					'descripcion'		=> $v['nombre'],
					'cantidad'			=> $v['cantidad'],
					'valor_unitario'	=> $v['precio'],
					'precio_unitario'	=> $v['precio']*$factor_porcentaje,
					'tipo_precio'		=> $producto['tipo_precio'], //ya incluye igv
					'igv'				=> $igv_detalle,
					'porcentaje_igv'	=> $igv_porcentaje*100,
					'valor_total'		=> $v['precio']*$v['cantidad'],
					'importe_total'		=> $v['precio']*$v['cantidad']*$factor_porcentaje,
					'unidad'			=> $v['unidad'],//unidad,
					'codigo_afectacion_alt'	=> $producto['codigoafectacion'],
					'codigo_afectacion'	=> $afectacion['codigo_afectacion'],
					'nombre_afectacion'	=> $afectacion['nombre_afectacion'],
					'tipo_afectacion'	=> $afectacion['tipo_afectacion']

				);

				$itemx;

				$detalle[] = $itemx;

				if($itemx['codigo_afectacion_alt']==10){
					$op_gravadas = $op_gravadas + $itemx['valor_total'];
				}

				if($itemx['codigo_afectacion_alt']==20){
					$op_exoneradas = $op_exoneradas + $itemx['valor_total'];
				}				

				if($itemx['codigo_afectacion_alt']==30){
					$op_inafectas = $op_inafectas + $itemx['valor_total'];
				}

				$igv = $igv + $igv_detalle;				
			}


			$total = $op_gravadas + $op_exoneradas + $op_inafectas + $igv;

			$idserie = $_POST['idserie'];

			$seriex = $objCompartido->obtenerSerie($idserie);
			$seriex = $seriex->fetch(PDO::FETCH_NAMED);

			$motivo = $objCompartido->getRegistroTablaParametrica('C',$_POST['motivo']);
			$motivo = $motivo->fetch(PDO::FETCH_NAMED);

			$comprobante =	array(
					'tipodoc'		=> $_POST['tipocomp'],
					'idserie'		=> $idserie,
					'serie'			=> $seriex['serie'],
					'correlativo'	=> $seriex['correlativo']+1,
					'fecha_emision' => $_POST['fecha_emision'],
					'moneda'		=> $_POST['moneda'], //PEN->SOLES; USD->DOLARES
					'total_opgravadas'	=> $op_gravadas,
					'igv'			=> $igv,
					'total_opexoneradas'	=> $op_exoneradas,
					'total_opinafectas'	=> $op_inafectas,
					'total'			=> $total,
					'total_texto'	=> CantidadEnLetra($total),
					'codcliente'	=> $idcliente,
					'tipodoc_ref'	=> $_POST['tipocomp_ref'],
					'serie_ref'		=> $_POST['serie_ref'],
					'correlativo_ref'=> $_POST['correlativo_ref'],
					'codmotivo'		=> $_POST['motivo'],
					'descripcion'	=> $motivo['descripcion']					
				);			

			$objCompartido->actualizarSerie($idserie, $comprobante['correlativo']);


			$ruta = "../xml/";
			$nombre = $emisor['ruc'].'-'.$comprobante['tipodoc'].'-'.$comprobante['serie'].'-'.$comprobante['correlativo'];

			$generadoXML->CrearXMLNotaCredito($ruta.$nombre, $emisor, $cliente, $comprobante, $detalle);
			
			$api->EnviarComprobanteElectronico($emisor,$nombre,"../","../xml/","../cdr/");
			//FIN FACTURACION ELECTRONICA


			//REGISTRO EN BASE DE DATOS

			$objNC->insertarNotaCredito($idemisor, $comprobante);
			$nc = $objNC->obtenerUltimoComprobanteId();
			$nc = $nc->fetch(PDO::FETCH_NAMED);

			$objNC->insertarDetalleNotaCredito($nc['id'],$detalle);

			//FIN DE REGISTRO EN BASE DE DATOS
			echo " Nota Credito Registrada Correcta<BR/>";
			//echo "<script>window.open('./apifacturacion/pdfFacturaElectronica.php?id=".$venta['id']."','_blank')</script>";
			session_destroy();
			break;


		case 'GUARDAR_ND':
			session_start();

			//logica de nota de credito
			//--------------------------
			//fin logica de nota de credito


			//INICIO PROCESO FACTURACION

			//$generadoXML = new Funciones();

			//obtenemos los datos del emisor de la BD
			$idemisor = $_POST['idemisor'];
			$emisor = $objEmisor->obtenerEmisor($idemisor);
			$emisor = $emisor->fetch(PDO::FETCH_NAMED);


			$cliente = array(
				'tipodoc'		=> $_POST['tipodoc'],//6->ruc, 1-> dni 
				'ruc'			=> $_POST['nrodoc'], 
				'razon_social'  => $_POST['razon_social'], 
				'direccion'		=> $_POST['direccion'],
				'pais'			=> 'PE'
				);	

			$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);

			if($cliente_existe->rowCount()>0){
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}else{
				$objCliente->insertarCliente($cliente);
				$cliente_existe = $objCliente->consultarCliente($_POST['nrodoc']);
				$cliente_existe = $cliente_existe->fetch(PDO::FETCH_NAMED);
			}
			$idcliente = $cliente_existe['id'];

			$carrito = $_SESSION['carrito'];
			$detalle = array();
			$igv_porcentaje = 0.18;



			$op_gravadas=0.00;
			$op_exoneradas=0.00;
			$op_inafectas=0.00;
			$igv = 0;

			foreach ($carrito as $k => $v) {

				$producto = $objCompartido->obtenerProducto($v['codigo']);
				$producto = $producto->fetch(PDO::FETCH_NAMED);

				$afectacion = $objCompartido->obtenerRegistroAfectacion($producto['codigoafectacion']);
				$afectacion = $afectacion->fetch(PDO::FETCH_NAMED);

				$igv_detalle =0;
				$factor_porcentaje = 1;
				if($producto['codigoafectacion']==10){
					$igv_detalle = $v['precio']*$v['cantidad']*$igv_porcentaje;
					$factor_porcentaje = 1+ $igv_porcentaje;
				}

				$itemx = array(
					'item' 				=> $k,
					'codigo'			=> $v['codigo'],
					'descripcion'		=> $v['nombre'],
					'cantidad'			=> $v['cantidad'],
					'valor_unitario'	=> $v['precio'],
					'precio_unitario'	=> $v['precio']*$factor_porcentaje,
					'tipo_precio'		=> $producto['tipo_precio'], //ya incluye igv
					'igv'				=> $igv_detalle,
					'porcentaje_igv'	=> $igv_porcentaje*100,
					'valor_total'		=> $v['precio']*$v['cantidad'],
					'importe_total'		=> $v['precio']*$v['cantidad']*$factor_porcentaje,
					'unidad'			=> $v['unidad'],//unidad,
					'codigo_afectacion_alt'	=> $producto['codigoafectacion'],
					'codigo_afectacion'	=> $afectacion['codigo_afectacion'],
					'nombre_afectacion'	=> $afectacion['nombre_afectacion'],
					'tipo_afectacion'	=> $afectacion['tipo_afectacion']

				);

				$itemx;

				$detalle[] = $itemx;

				if($itemx['codigo_afectacion_alt']==10){
					$op_gravadas = $op_gravadas + $itemx['valor_total'];
				}

				if($itemx['codigo_afectacion_alt']==20){
					$op_exoneradas = $op_exoneradas + $itemx['valor_total'];
				}				

				if($itemx['codigo_afectacion_alt']==30){
					$op_inafectas = $op_inafectas + $itemx['valor_total'];
				}

				$igv = $igv + $igv_detalle;				
			}


			$total = $op_gravadas + $op_exoneradas + $op_inafectas + $igv;

			$idserie = $_POST['idserie'];

			$seriex = $objCompartido->obtenerSerie($idserie);
			$seriex = $seriex->fetch(PDO::FETCH_NAMED);

			$motivo = $objCompartido->getRegistroTablaParametrica('D',$_POST['motivo']);
			$motivo = $motivo->fetch(PDO::FETCH_NAMED);

			$comprobante =	array(
					'tipodoc'		=> $_POST['tipocomp'],
					'idserie'		=> $idserie,
					'serie'			=> $seriex['serie'],
					'correlativo'	=> $seriex['correlativo']+1,
					'fecha_emision' => $_POST['fecha_emision'],
					'moneda'		=> $_POST['moneda'], //PEN->SOLES; USD->DOLARES
					'total_opgravadas'	=> $op_gravadas,
					'igv'			=> $igv,
					'total_opexoneradas'	=> $op_exoneradas,
					'total_opinafectas'	=> $op_inafectas,
					'total'			=> $total,
					'total_texto'	=> CantidadEnLetra($total),
					'codcliente'	=> $idcliente,
					'tipodoc_ref'	=> $_POST['tipocomp_ref'],
					'serie_ref'		=> $_POST['serie_ref'],
					'correlativo_ref'=> $_POST['correlativo_ref'],
					'codmotivo'		=> $_POST['motivo'],
					'descripcion'	=> $motivo['descripcion']					
				);			

			$objCompartido->actualizarSerie($idserie, $comprobante['correlativo']);

			$ruta = "../xml/";
			$nombre = $emisor['ruc'].'-'.$comprobante['tipodoc'].'-'.$comprobante['serie'].'-'.$comprobante['correlativo'];

			$generadoXML->CrearXMLNotaDebito($ruta.$nombre, $emisor, $cliente, $comprobante, $detalle);
			
			$api->EnviarComprobanteElectronico($emisor,$nombre,"../","../xml/","../cdr/");
			//FIN FACTURACION ELECTRONICA


			//REGISTRO EN BASE DE DATOS

			$objND->insertarNotaDebito($idemisor, $comprobante);
			$nd = $objND->obtenerUltimoComprobanteId();
			$nd = $nd->fetch(PDO::FETCH_NAMED);

			$objND->insertarDetalleNotaDebito($nd['id'],$detalle);

			//FIN DE REGISTRO EN BASE DE DATOS
			echo " Nota Debito Registrada Correcta<BR/>";
			//echo "<script>window.open('./apifacturacion/pdfFacturaElectronica.php?id=".$venta['id']."','_blank')</script>";
			session_destroy();
			break;

		case "ENVIO_RESUMEN":

			$idemisor = $_POST['idemisor'];
			$emisor = $objEmisor->obtenerEmisor($idemisor);
			$emisor = $emisor->fetch(PDO::FETCH_NAMED);

			//CONTROLAR VARIOS ENVIOS EN EL MISMO DÍA
			$serie = date('Ymd');
			$fila_serie = $objCompartido->obtenerSerieResumen('RC');
			$fila_serie = $fila_serie->fetch(PDO::FETCH_NAMED);

			$correlativo = 1;
			if($fila_serie['serie']!=$serie){
				$objCompartido->actualizarSerieResumen('RC', $serie);
			}else{
				$correlativo = $fila_serie['correlativo']+1;
			}

			$objCompartido->actualizarSerie($fila_serie['id'], $correlativo);

			$cabecera = array(
						"tipodoc"		=>"RC",
						"serie"			=>$serie,
						"correlativo"	=>$correlativo,
						"fecha_emision" =>date('Y-m-d'),			
						"fecha_envio"	=>date('Y-m-d')	
				);


			$items = array();

			$ids = $_POST['documento'];
			$i=1;
			foreach($ids as $v){
				$boleta = $objVenta->obtenerComprobanteId($v);
				$boleta = $boleta->fetch(PDO::FETCH_NAMED);

				$items[] = array(
						"item"				=> $i,
						"tipodoc"			=> $boleta['tipocomp'],
						"serie"				=> $boleta['serie'],
						"correlativo"		=> $boleta['correlativo'],
						"condicion"			=> 1, //1->Registro, 2->Actuali, 3->Bajas
						"moneda"			=> $boleta['codmoneda'],			
						"importe_total"		=> $boleta['total'],
						"valor_total"		=> $boleta['op_gravadas'],
						"igv_total"			=> $boleta['igv'],
						"tipo_total"		=> "01", //GRA->01, EXO->02, INA->03
						"codigo_afectacion"	=> "1000",
						"nombre_afectacion"	=> "IGV",
						"tipo_afectacion"	=> "VAT"
					);
				$i++;
			}


			$ruta = "../xml/";
			$nombrexml = $emisor['ruc'].'-'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'];

			$generadoXML->CrearXMLResumenDocumentos($emisor, $cabecera, $items, $ruta.$nombrexml);

			$ticket = $api->EnviarResumenComprobantes($emisor,$nombrexml,"../","../xml/");

			$api->ConsultarTicket($emisor, $cabecera, $ticket,"../cdr/");

			echo 'envio realizado';
			break;

		case "ENVIO_BAJAS":

			$idemisor = $_POST['idemisor'];
			$emisor = $objEmisor->obtenerEmisor($idemisor);
			$emisor = $emisor->fetch(PDO::FETCH_NAMED);

			$serie = date('Ymd');
			$fila_serie = $objCompartido->obtenerSerieResumen('RA');
			$fila_serie = $fila_serie->fetch(PDO::FETCH_NAMED);

			$correlativo = 1;
			if($fila_serie['serie']!=$serie){
				$objCompartido->actualizarSerieResumen('RA', $serie);
			}else{
				$correlativo = $fila_serie['correlativo']+1;
			}

			$objCompartido->actualizarSerie($fila_serie['id'], $correlativo);

			$cabecera = array(
						"tipodoc"		=>"RA",
						"serie"			=>$serie,
						"correlativo"	=>$correlativo,
						"fecha_emision" =>date('Y-m-d'),			
						"fecha_envio"	=>date('Y-m-d')	
				);


			$items = array();

			$ids = $_POST['documento'];
			$i=1;
			foreach($ids as $v){
				$factura = $objVenta->obtenerComprobanteId($v);
				$factura = $factura->fetch(PDO::FETCH_NAMED);

				$items[] = array(
						"item"				=> $i,
						"tipodoc"			=> $factura["tipocomp"],
						"serie"				=> $factura["serie"],
						"correlativo"		=> $factura["correlativo"],
						"motivo"			=> "ERROR EN DOCUMENTO"
					);
				$i++;
			}
			
			$ruta = "../xml/";
			$nombrexml = $emisor['ruc'].'-'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'];

			$generadoXML->CrearXmlBajaDocumentos($emisor, $cabecera, $items, $ruta.$nombrexml);

			$ticket = $api->EnviarResumenComprobantes($emisor,$nombrexml,"../","../xml/");

			$api->ConsultarTicket($emisor, $cabecera, $ticket, "../cdr/");

			echo 'envío realizado';
			break;

		case "CONSULTAR_DOCUMENTO":				
			try {
				//===================ENVIO FACTURACION=====================
				$soapUrl = 'https://e-factura.sunat.gob.pe/ol-it-wsconscpegem/billConsultService?wsdl';
				$ruc = "10167594781";
				$tipodoc = "01";
				$serie = "F001";
				$correlativo = "234";
				$usuariosol = "MODDATOS";
				$clavesol = "MODDATOS";

				// xml post structure
				$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
				xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" 
				xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					<soapenv:Header>
						<wsse:Security>
							<wsse:UsernameToken>
								<wsse:Username>'.$ruc.$usuariosol.'</wsse:Username>
								<wsse:Password>'.$clavesol.'</wsse:Password>
							</wsse:UsernameToken>
						</wsse:Security>
					</soapenv:Header>
					<soapenv:Body>
						<ser:getStatus>
							<rucComprobante>'.$ruc.'</rucComprobante>
							<tipoComprobante>'.$tipodoc.'</tipoComprobante>
							<serieComprobante>'.$serie.'</serieComprobante>
							<numeroComprobante>'.$correlativo.'</numeroComprobante>
						</ser:getStatus>
					</soapenv:Body>
				</soapenv:Envelope>';
			
				$headers = array(
					"Content-type: text/xml;charset=\"utf-8\"",
					"Accept: text/xml",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					"SOAPAction: ",
					"Content-length: " . strlen($xml_post_string),
				); //SOAPAction: your op URL
			
				$url = $soapUrl;
			
				// PHP cURL  for https connection with auth
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				//curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
				// converting
				$response = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				//echo $xml_post_string;
				echo var_dump($response);				

				//============= ENVIO DE EMAIL ================
			} catch (Exception $e) {
				echo "SUNAT ESTA FUERA SERVICIO: ".$e->getMessage();
			}
			break;

		default:
			# code...
			break;
	}

}

?>