<?php
require_once('signature.php');

class ApiFacturacion
{
    //Envío de BV, FA, NC, ND - SendBill
    public function EnviarComprobanteElectronico($emisor, $nombre, $rutacertificado = "", $ruta_archivo_xml = 'xml/', $ruta_archivo_cdr = 'cdr/')
    {
        //Firma digitalmente el XML - INICIO
        $objFirma = new Signature(); //se crea el objeto para la clase signature del archivo signature.php 
        $flg_firma = 0; //posicion 0 en el xml donde se mostrará la firma digital

        
        //$nombre = $emisor['ruc'].'-'.$comprobante['tipodoc'].'-'.$comprobante['serie'].'-'.$comprobante['correlativo'];
        $ruta = $ruta_archivo_xml . $nombre . '.XML';

        $ruta_firma = $rutacertificado . 'CERTIFICADO_16759478.pfx'; //ruta del certificado digital + nombre del mismo
        $pass_firma = 'WYLYgg1975'; //contraseña del certificado digital
        ///signature_xml es funcion de la clase Signature, archivo signature.php
        $objFirma->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);
        
        echo '</br> XML FIRMADO DIGITALMENTE CON EXITO .PASO 02';
        //Firma digitalmente el XML - FIN


        //COMPRIMIR EN .ZIP EL ARCHIVO XML - INICIO

        $zip = new ZipArchive();
        $nombrezip = $nombre . '.ZIP';
        $ruta_zip = $ruta_archivo_xml . $nombre . '.ZIP';

        if($zip->open($ruta_zip, ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($ruta, $nombre . '.XML');
            $zip->close();
        }

        echo '</br> XML COMPRIMIDO EN FORMATO .ZIP CON EXITO .PASO 03';
        //COMPRIMIR EN .ZIP EL ARCHIVO XML - FIN

        //CODIFICAR EN BASE64 EL ARCHIVO .ZIP comprimido en el paso anterior- INICIO
        $datosasunat;
        $ruta_achivo = $ruta_zip;
        $nombre_archivo = $nombrezip;
        $contenido_del_zip = base64_encode(file_get_contents($ruta_achivo)) ; 
        //codifica

        echo '</br> ARCHIVO .ZIP CODIFICADO CON EXITO .PASO 04';
        //echo '</br> CONTENIDO: ' . $contenido_del_zip;

        //CODIFICAR EN BASE64 EL ARCHIVO .ZIP - FIN

        //ENVIAR XML A WEB SERVICES DE SUNAT - INICIO

       // $ws = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'; //url ws beta de sunat
       //url ws Produccion 
       $datosasunat=$emisor['ruc'] . $emisor['usuario_secundario'];
        $ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'; 
        
        $xml_envio ='
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <soapenv:Header>
                    <wsse:Security>
                        <wsse:UsernameToken>
                            <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                            <wsse:Password>' . $emisor['usuario_secundario_pass'] . '</wsse:Password>
                        </wsse:UsernameToken>
                    </wsse:Security>
                    </soapenv:Header>
                    <soapenv:Body>
                    <ser:sendBill> 
                        <fileName>' . $nombre_archivo . '</fileName>
                        <contentFile>' . $contenido_del_zip . '</contentFile>
                    </ser:sendBill>
                    </soapenv:Body>
                </soapenv:Envelope>';

        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-lenght: " . strlen($xml_envio)
            );

        $ch = curl_init(); //iniciar la llamada
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, 1); // 
        curl_setopt($ch,CURLOPT_URL, $ws);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch,CURLOPT_TIMEOUT, 30);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml_envio);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $header);    

        //para ejecutar los procesos de forma local en windows
        //enlace de descarga del cacert.pem: https://curl.haxx.se/docs/caextract.html
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem"); //solo funciona en local, si en cambio estas en el servidor web con ssl comentar esta línea

        $response = curl_exec($ch); //ejecuto y objetine resultado
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo '</br> Valor de la varaible  $httpcode '.$httpcode ;
                
        echo '</br> CONSUMO DE WS DE SUNAT .PASO 05';

        $estadofe = '0';

        if($httpcode == 200) //tuve respuesta
        {
            $doc = new DOMDocument();
            $doc->loadXML($response);

            if(isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue))
            {
                echo '</br> RESPUESTA XML DE SUNAT .PASO 06';
                $cdr = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;

                $cdr = base64_decode($cdr);
                echo '</br> DECODIFCO XML ENVELOPE DE SUNAT .PASO 07';

                file_put_contents($ruta_archivo_cdr . 'R-' . $nombrezip , $cdr); //guardando en disco el CDR

                $zip = new ZipArchive();
                if($zip->open($ruta_archivo_cdr . 'R-' . $nombrezip ) === TRUE)
                {
                    $zip->extractTo( $ruta_archivo_cdr, 'R-' . $nombre . '.XML');
                    $zip->close();

                    echo '</br> EXTRACCION DEL ZIP .PASO 08';
                }

                $estadofe = '1';
                echo '</br> TODO OK, OBTENEMOS EL XML CDR FIRMADO POR SUNAT  .PASO 09';
            }
            else //rechazo, error de SUNAT
            {
                $estadofe = '2';
                $codigo = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

                echo 'ERROR CODIGO: ' . $codigo . ' MENSAJE: ' . $mensaje;
            }
        }
        else // PROBLEMAS DE CONEXION, RED
        {
            $estadofe = '3';
            echo curl_error($ch);
            echo '</br> Problema de conexion';
        }

        curl_close($ch);

        //ENVIAR XML A WEB SERVICES DE SUNAT - FIN
    }

    //Envío de RC/RA - SendSummary
    public function EnviarResumenComprobantes($emisor, $nombre, $rutacertificado = "", $ruta_archivo_xml = "xml/")
    {
        //Firmar el documento - INICIO
        $objSignature = new Signature();
        $flg_firma = '0';
        $ruta = $ruta_archivo_xml . $nombre . '.XML';
        
        $ruta_firma = $rutacertificado . 'CERTIFICADO_16759478.pfx'; //ruta del certificado digital + nombre del mismo
        //$ruta_firma = $rutacertificado . 'CDT-10167594781-DEMO.pfx';
        $pass_firma = "WYLYgg75";

        $resp = $objSignature->signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma);

        echo '</br> XML firmado digitalmente con exito. PASO 02';
        //print_r($resp); //hash

        //Firmar el documento - FIN

        //generar el ZIP - INICIO
        $zip = new ZipArchive();
        $nombrezip = $nombre . '.ZIP';
        $rutazip = $ruta_archivo_xml . $nombre . '.ZIP';

        if($zip->open($rutazip, ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($ruta, $nombre . '.XML');
            $zip->close();
        }

        echo '</br> XML comprimido en formato ZIP. PASO 03';

        //generar el ZIP - FIN


        //envio a SUNAT - INICIO
        

        $ruta_archivo = $ruta_archivo_xml . $nombrezip;
        $nombre_archivo = $nombrezip;
        $ruta_archivo_cdr = "cdr/";

        $contenido_del_zip = base64_encode(file_get_contents($ruta_archivo));
        echo '</br> XML codificado. PASO 04';

        $ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'; //url ws Produccion 
        //$ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //ws sunat beta

        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                        <wsse:Password>' . $emisor['usuario_secundario_pass'] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
                </soapenv:Header>
                <soapenv:Body>
                <ser:sendSummary>
                    <fileName>' . $nombre_archivo . '</fileName>
                    <contentFile>' . $contenido_del_zip . '</contentFile>
                </ser:sendSummary>
                </soapenv:Body>
            </soapenv:Envelope>';

        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_envio)
        );

        $ch = curl_init(); //inicio la llamada al ws
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 1);
        curl_setopt($ch, CURLOPT_URL , $ws);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $xml_envio);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //para descargarl el cacert.pem y solo de forma local en windows: https://curl.haxx.se/docs/caextract.html
        //cuando hagan pase a produccion o no trabajen windows quitar la linea
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");

        echo '</br> Envio del XML ENVELOPE A SUNAT. PASO 05';
        $response = curl_exec($ch); //Ejectuo y obtengo el resultado XML
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $estadofe = '0';
        $ticket = "0";

        if($httpcode == 200)
        {
            $doc = new DOMDocument();
            $doc->loadXML($response);

            if(isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue))
            {
                $estadofe = '1';
               $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;
               echo '</br> OBTENGO EL NRO DE TICKET:' . $ticket . ' -  PASO 06';
            }
            else
            {
                $codigo = $doc->getElementsByTagName("faultcode")->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName("faultstring")->item(0)->nodeValue;
                $estadofe = '2';
                echo '</br> Error:' . $codigo . ' Mensaje: ' . $mensaje;
            }
        }
        else
        {
            echo curl_error($ch);
            echo "Problema de conexion";
        }

        curl_close($ch);
        return $ticket;

        //envio a SUNAT - FIN
    }

    //Consulta TK RC/RA - GetStatus
    public function ConsultarTicket($emisor, $cabecera, $ticket, $ruta_archivo_cdr = 'cdr/')
    {
        //$ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService"; //ws sunat beta
        $ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'; //url ws Produccion 
        $nombre = $emisor['ruc'] . '-' . $cabecera['tipodoc'] . '-' . $cabecera['serie'] . '-' . $cabecera['correlativo'];
        $nombre_xml = $nombre . '.XML';
        $nombre_archivo = $nombre;

        $xml_envio ='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasisopen.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $emisor['ruc'] . $emisor['usuario_secundario'] . '</wsse:Username>
                        <wsse:Password>' . $emisor['usuario_secundario_pass'] . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
                </soapenv:Header>
                <soapenv:Body>
                <ser:getStatus>
                    <ticket>' . $ticket . '</ticket>
                </ser:getStatus>
                </soapenv:Body>
            </soapenv:Envelope>';
        
        $header = array(
            "Content-type: text/xml; charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_envio)
        );

        $ch = curl_init(); //inicio la llamada al ws
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , 1);
        curl_setopt($ch, CURLOPT_URL , $ws);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $xml_envio);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //para descargarl el cacert.pem y solo de forma local en windows: https://curl.haxx.se/docs/caextract.html
        //cuando hagan pase a produccion o no trabajen windows quitar la linea
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/cacert.pem");

        $response = curl_exec($ch); //Ejectuo y obtengo el resultado XML
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $estadofe = '0';

        if ($httpcode == 200) { //200: Obtuve respuesta
            $doc = new DOMDocument();
            $doc->loadXML($response);

            if (isset($doc->getElementsByTagName("content")->item(0)->nodeValue)) {                
                $cdr = $doc->getElementsByTagName("content")->item(0)->nodeValue;
                echo '</br> SUNAT RESPONDE CON XML ENVELOPE. PASO 07';

                $cdr = base64_decode($cdr); //decodifico el envelope que envia SUNAT
                echo '</br> DECODIFICO EL XML ENVELOPE Y OBTENGO . ZIP. PASO 08';

                //pasar de memoria a disco
                file_put_contents( $ruta_archivo_cdr . 'R-' . $nombre_archivo . '.ZIP', $cdr );

                //extraer el contenido del ZIP                
                $zip = new ZipArchive();
                if($zip->open( $ruta_archivo_cdr . 'R-' . $nombre_archivo . '.ZIP') === TRUE)
                {
                    $zip->extractTo( $ruta_archivo_cdr, 'R-' . $nombre_archivo . '.XML' );
                    $zip->close();
                }
                echo '</br> EXTRAIGO EL CONTENIDO DEL ZIP. PASO 09';

                $estadofe = '1';
                echo '</br> TODO OK, TENGO EL XML - CDR. PASO 10';

            }else{
                $codigo = $doc->getElementsByTagName("faultcode")->item(0)->nodeValue;
                $mensaje = $doc->getElementsByTagName("faultstring")->item(0)->nodeValue;
                $estadofe = '2';
                echo '</br> Error:' . $codigo . ' Mensaje: ' . $mensaje;
            }
        }else{ //problemas de conexion
            $estadofe = '3';
            echo curl_error($ch);
            echo '</br> PROBLEMA DE CONEXION';
        }

        curl_close($ch);

    }

    //Consulta Comprobantes - GetStatus
    function consultarComprobante($emisor, $comprobante)
    {
		try{
                $ws = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'; //url ws Produccion 
				//$ws = "https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService";
				$soapUser = "";  
				$soapPassword = "";

				$xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
				xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" 
				xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					<soapenv:Header>
						<wsse:Security>
							<wsse:UsernameToken>
								<wsse:Username>'.$emisor['ruc'].$emisor['usuariosol'].'</wsse:Username>
								<wsse:Password>'.$emisor['clavesol'].'</wsse:Password>
							</wsse:UsernameToken>
						</wsse:Security>
					</soapenv:Header>
					<soapenv:Body>
						<ser:getStatus>
							<rucComprobante>'.$emisor['ruc'].'</rucComprobante>
							<tipoComprobante>'.$comprobante['tipodoc'].'</tipoComprobante>
							<serieComprobante>'.$comprobante['serie'].'</serieComprobante>
							<numeroComprobante>'.$comprobante['correlativo'].'</numeroComprobante>
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
				); 			
			
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
				curl_setopt($ch, CURLOPT_URL, $ws);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
				//para ejecutar los procesos de forma local en windows
				//enlace de descarga del cacert.pem https://curl.haxx.se/docs/caextract.html
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");

				$response = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				echo var_dump($response);
				
			} catch (Exception $e) {
				echo "SUNAT ESTA FUERA SERVICIO: ".$e->getMessage();
			}
    }


}

?>