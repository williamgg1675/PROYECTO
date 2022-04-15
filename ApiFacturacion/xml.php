<?php

class GeneradorXML
{
    function CrearXMLFactura($nombrexml, $emisor, $cliente, $comprobante, $detalle) //crear xml para Boleta y Factura
    {
        $doc = new DOMDocument();//clase que permite crear documentos o archivos: XML
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $doc->encoding = 'utf-8';

        //crear el texto XML de la factura electronica
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
           <ext:UBLExtensions>
              <ext:UBLExtension>
                 <ext:ExtensionContent />
              </ext:UBLExtension>
           </ext:UBLExtensions>
           <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
           <cbc:CustomizationID>2.0</cbc:CustomizationID>
           <cbc:ID>'.$comprobante['serie'].'-'.$comprobante['correlativo'].'</cbc:ID>
           <cbc:IssueDate>'.$comprobante['fecha_emision'].'</cbc:IssueDate>
           <cbc:IssueTime>00:00:00</cbc:IssueTime>
           <cbc:DueDate>'.$comprobante['fecha_emision'].'</cbc:DueDate>
           <cbc:InvoiceTypeCode listID="0101">'.$comprobante['tipodoc'].'</cbc:InvoiceTypeCode>
           <cbc:Note languageLocaleID="1000"><![CDATA['.$comprobante['total_texto'].']]></cbc:Note>
           <cbc:DocumentCurrencyCode>'.$comprobante['moneda'].'</cbc:DocumentCurrencyCode>
           <cac:Signature>
              <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
              <cbc:Note><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Note>
              <cac:SignatoryParty>
                 <cac:PartyIdentification>
                    <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyName>
                    <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                 </cac:PartyName>
              </cac:SignatoryParty>
              <cac:DigitalSignatureAttachment>
                 <cac:ExternalReference>
                    <cbc:URI>#SIGN-EMPRESA</cbc:URI>
                 </cac:ExternalReference>
              </cac:DigitalSignatureAttachment>
           </cac:Signature>
           <cac:AccountingSupplierParty>
              <cac:Party>
                 <cac:PartyIdentification>
                    <cbc:ID schemeID="'.$emisor['tipodoc'].'">'.$emisor['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyName>
                    <cbc:Name><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Name>
                 </cac:PartyName>
                 <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                    <cac:RegistrationAddress>
                       <cbc:ID>'.$emisor['ubigeo'].'</cbc:ID>
                       <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                       <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
                       <cbc:CityName>'.$emisor['provincia'].'</cbc:CityName>
                       <cbc:CountrySubentity>'.$emisor['departamento'].'</cbc:CountrySubentity>
                       <cbc:District>'.$emisor['distrito'].'</cbc:District>
                       <cac:AddressLine>
                          <cbc:Line><![CDATA['.$emisor['direccion'].']]></cbc:Line>
                       </cac:AddressLine>
                       <cac:Country>
                          <cbc:IdentificationCode>'.$emisor['pais'].'</cbc:IdentificationCode>
                       </cac:Country>
                    </cac:RegistrationAddress>
                 </cac:PartyLegalEntity>
              </cac:Party>
           </cac:AccountingSupplierParty>
           <cac:AccountingCustomerParty>
              <cac:Party>
                 <cac:PartyIdentification>
                    <cbc:ID schemeID="'.$cliente['tipodoc'].'">'.$cliente['ruc'].'</cbc:ID>
                 </cac:PartyIdentification>
                 <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA['.$cliente['razon_social'].']]></cbc:RegistrationName>
                    <cac:RegistrationAddress>
                       <cac:AddressLine>
                          <cbc:Line><![CDATA['.$cliente['direccion'].']]></cbc:Line>
                       </cac:AddressLine>
                       <cac:Country>
                          <cbc:IdentificationCode>'.$cliente['pais'].'</cbc:IdentificationCode>
                       </cac:Country>
                    </cac:RegistrationAddress>
                 </cac:PartyLegalEntity>
              </cac:Party>
           </cac:AccountingCustomerParty>

           <cac:PaymentTerms>
               <cbc:ID>FormaPago</cbc:ID>
               <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
           </cac:PaymentTerms>


           <cac:TaxTotal>
              <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>';

              if($comprobante['total_opgravadas']>0)
              {
                  $xml.='<cac:TaxSubtotal>
                     <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opgravadas'].'</cbc:TaxableAmount>
                     <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
                     <cac:TaxCategory>
                        <cac:TaxScheme>
                           <cbc:ID>1000</cbc:ID>
                           <cbc:Name>IGV</cbc:Name>
                           <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                     </cac:TaxCategory>
                  </cac:TaxSubtotal>';
              }             
                
              if($comprobante['total_opexoneradas']>0){
                 $xml.='<cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opexoneradas'].'</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                       <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                       <cac:TaxScheme>
                          <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID>
                          <cbc:Name>EXO</cbc:Name>
                          <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';
              }
        
              if($comprobante['total_opinafectas']>0){
                 $xml.='<cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opinafectas'].'</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                       <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                       <cac:TaxScheme>
                          <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID>
                          <cbc:Name>INA</cbc:Name>
                          <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';
              }
        
               $total_antes_de_impuestos = $comprobante['total_opgravadas']+$comprobante['total_opexoneradas']+$comprobante['total_opinafectas'];
        
           $xml.='</cac:TaxTotal>
           <cac:LegalMonetaryTotal>
              <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$total_antes_de_impuestos.'</cbc:LineExtensionAmount>
              <cbc:TaxInclusiveAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:TaxInclusiveAmount>
              <cbc:PayableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:PayableAmount>
           </cac:LegalMonetaryTotal>';
           
           foreach($detalle as $k=>$v){
        
               $xml.='<cac:InvoiceLine>
                  <cbc:ID>'.$v['item'].'</cbc:ID>
                  <cbc:InvoicedQuantity unitCode="'.$v['unidad'].'">'.$v['cantidad'].'</cbc:InvoicedQuantity>
                  <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:LineExtensionAmount>
                  <cac:PricingReference>
                     <cac:AlternativeConditionPrice>
                        <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['precio_unitario'].'</cbc:PriceAmount>
                        <cbc:PriceTypeCode>'.$v['tipo_precio'].'</cbc:PriceTypeCode>
                     </cac:AlternativeConditionPrice>
                  </cac:PricingReference>
                  <cac:TaxTotal>
                     <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                     <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                        <cac:TaxCategory>
                           <cbc:Percent>'.$v['porcentaje_igv'].'</cbc:Percent>
                           <cbc:TaxExemptionReasonCode>'.$v['codigo_afectacion_alt'].'</cbc:TaxExemptionReasonCode>
                           <cac:TaxScheme>
                              <cbc:ID>'.$v['codigo_afectacion'].'</cbc:ID>
                              <cbc:Name>'.$v['nombre_afectacion'].'</cbc:Name>
                              <cbc:TaxTypeCode>'.$v['tipo_afectacion'].'</cbc:TaxTypeCode>
                           </cac:TaxScheme>
                        </cac:TaxCategory>
                     </cac:TaxSubtotal>
                  </cac:TaxTotal>
                  <cac:Item>
                     <cbc:Description><![CDATA['.$v['descripcion'].']]></cbc:Description>
                     <cac:SellersItemIdentification>
                        <cbc:ID>'.$v['codigo'].'</cbc:ID>
                     </cac:SellersItemIdentification>
                  </cac:Item>
                  <cac:Price>
                     <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_unitario'].'</cbc:PriceAmount>
                  </cac:Price>
               </cac:InvoiceLine>';
                   
                   }
        
        $xml.="</Invoice>";
        
        $doc->loadXML($xml); //crear el xml en base a un texto
        $doc->save($nombrexml . '.XML'); //guarda el xml generado en la Ruta indicada
    }
    
    function CrearXMLNotaCredito($nombrexml, $emisor, $cliente, $comprobante, $detalle)
    {
       $doc = new DOMDocument();
       $doc->formatOutput = FALSE;
       $doc->preserveWhiteSpace = TRUE;
       $doc->encoding = 'utf-8'; 
 
       $xml = '<?xml version="1.0" encoding="UTF-8"?>
       <CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
          <ext:UBLExtensions>
             <ext:UBLExtension>
                <ext:ExtensionContent />
             </ext:UBLExtension>
          </ext:UBLExtensions>
          <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
          <cbc:CustomizationID>2.0</cbc:CustomizationID>
          <cbc:ID>'.$comprobante['serie'].'-'.$comprobante['correlativo'].'</cbc:ID>
          <cbc:IssueDate>'.$comprobante['fecha_emision'].'</cbc:IssueDate>
          <cbc:IssueTime>00:00:01</cbc:IssueTime>
          <cbc:Note languageLocaleID="1000"><![CDATA['.$comprobante['total_texto'].']]></cbc:Note>
          <cbc:DocumentCurrencyCode>'.$comprobante['moneda'].'</cbc:DocumentCurrencyCode>
          <cac:DiscrepancyResponse>
             <cbc:ReferenceID>'.$comprobante['serie_ref'].'-'.$comprobante['correlativo_ref'].'</cbc:ReferenceID>
             <cbc:ResponseCode>'.$comprobante['codmotivo'].'</cbc:ResponseCode>
             <cbc:Description>'.$comprobante['descripcion'].'</cbc:Description>
          </cac:DiscrepancyResponse>
          <cac:BillingReference>
             <cac:InvoiceDocumentReference>
                <cbc:ID>'.$comprobante['serie_ref'].'-'.$comprobante['correlativo_ref'].'</cbc:ID>
                <cbc:DocumentTypeCode>'.$comprobante['tipodoc_ref'].'</cbc:DocumentTypeCode>
             </cac:InvoiceDocumentReference>
          </cac:BillingReference>
          <cac:Signature>
             <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
             <cbc:Note><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Note>
             <cac:SignatoryParty>
                <cac:PartyIdentification>
                   <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                   <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                </cac:PartyName>
             </cac:SignatoryParty>
             <cac:DigitalSignatureAttachment>
                <cac:ExternalReference>
                   <cbc:URI>#SIGN-EMPRESA</cbc:URI>
                </cac:ExternalReference>
             </cac:DigitalSignatureAttachment>
          </cac:Signature>
          <cac:AccountingSupplierParty>
             <cac:Party>
                <cac:PartyIdentification>
                   <cbc:ID schemeID="'.$emisor['tipodoc'].'">'.$emisor['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                   <cbc:Name><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Name>
                </cac:PartyName>
                <cac:PartyLegalEntity>
                   <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                   <cac:RegistrationAddress>
                      <cbc:ID>'.$emisor['ubigeo'].'</cbc:ID>
                      <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                      <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
                      <cbc:CityName>'.$emisor['provincia'].'</cbc:CityName>
                      <cbc:CountrySubentity>'.$emisor['departamento'].'</cbc:CountrySubentity>
                      <cbc:District>'.$emisor['distrito'].'</cbc:District>
                      <cac:AddressLine>
                         <cbc:Line><![CDATA['.$emisor['direccion'].']]></cbc:Line>
                      </cac:AddressLine>
                      <cac:Country>
                         <cbc:IdentificationCode>'.$emisor['pais'].'</cbc:IdentificationCode>
                      </cac:Country>
                   </cac:RegistrationAddress>
                </cac:PartyLegalEntity>
             </cac:Party>
          </cac:AccountingSupplierParty>
          <cac:AccountingCustomerParty>
             <cac:Party>
                <cac:PartyIdentification>
                   <cbc:ID schemeID="'.$cliente['tipodoc'].'">'.$cliente['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyLegalEntity>
                   <cbc:RegistrationName><![CDATA['.$cliente['razon_social'].']]></cbc:RegistrationName>
                   <cac:RegistrationAddress>
                      <cac:AddressLine>
                         <cbc:Line><![CDATA['.$cliente['direccion'].']]></cbc:Line>
                      </cac:AddressLine>
                      <cac:Country>
                         <cbc:IdentificationCode>'.$cliente['pais'].'</cbc:IdentificationCode>
                      </cac:Country>
                   </cac:RegistrationAddress>
                </cac:PartyLegalEntity>
             </cac:Party>
          </cac:AccountingCustomerParty>
          <cac:TaxTotal>
             <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
             <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opgravadas'].'</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
                <cac:TaxCategory>
                   <cac:TaxScheme>
                      <cbc:ID>1000</cbc:ID>
                      <cbc:Name>IGV</cbc:Name>
                      <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                   </cac:TaxScheme>
                </cac:TaxCategory>
             </cac:TaxSubtotal>';
 
             if($comprobante['total_opexoneradas']>0){
                $xml.='<cac:TaxSubtotal>
                   <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opexoneradas'].'</cbc:TaxableAmount>
                   <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                   <cac:TaxCategory>
                      <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                      <cac:TaxScheme>
                         <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID>
                         <cbc:Name>EXO</cbc:Name>
                         <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                      </cac:TaxScheme>
                   </cac:TaxCategory>
                </cac:TaxSubtotal>';
             }
 
             if($comprobante['total_opinafectas']>0){
                $xml.='<cac:TaxSubtotal>
                   <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opinafectas'].'</cbc:TaxableAmount>
                   <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">0.00</cbc:TaxAmount>
                   <cac:TaxCategory>
                      <cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">E</cbc:ID>
                      <cac:TaxScheme>
                         <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID>
                         <cbc:Name>INA</cbc:Name>
                         <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                      </cac:TaxScheme>
                   </cac:TaxCategory>
                </cac:TaxSubtotal>';
             }
 
          $xml.='</cac:TaxTotal>
          <cac:LegalMonetaryTotal>
             <cbc:PayableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:PayableAmount>
          </cac:LegalMonetaryTotal>';
          
          foreach($detalle as $k=>$v){
 
             $xml.='<cac:CreditNoteLine>
                <cbc:ID>'.$v['item'].'</cbc:ID>
                <cbc:CreditedQuantity unitCode="'.$v['unidad'].'">'.$v['cantidad'].'</cbc:CreditedQuantity>
                <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:LineExtensionAmount>
                <cac:PricingReference>
                   <cac:AlternativeConditionPrice>
                      <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['precio_unitario'].'</cbc:PriceAmount>
                      <cbc:PriceTypeCode>'.$v['tipo_precio'].'</cbc:PriceTypeCode>
                   </cac:AlternativeConditionPrice>
                </cac:PricingReference>
                <cac:TaxTotal>
                   <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                   <cac:TaxSubtotal>
                      <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:TaxableAmount>
                      <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                      <cac:TaxCategory>
                         <cbc:Percent>'.$v['porcentaje_igv'].'</cbc:Percent>
                         <cbc:TaxExemptionReasonCode>'.$v['codigo_afectacion_alt'].'</cbc:TaxExemptionReasonCode>
                         <cac:TaxScheme>
                            <cbc:ID>'.$v['codigo_afectacion'].'</cbc:ID>
                            <cbc:Name>'.$v['nombre_afectacion'].'</cbc:Name>
                            <cbc:TaxTypeCode>'.$v['tipo_afectacion'].'</cbc:TaxTypeCode>
                         </cac:TaxScheme>
                      </cac:TaxCategory>
                   </cac:TaxSubtotal>
                </cac:TaxTotal>
                <cac:Item>
                   <cbc:Description><![CDATA['.$v['descripcion'].']]></cbc:Description>
                   <cac:SellersItemIdentification>
                      <cbc:ID>'.$v['codigo'].'</cbc:ID>
                   </cac:SellersItemIdentification>
                </cac:Item>
                <cac:Price>
                   <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_unitario'].'</cbc:PriceAmount>
                </cac:Price>
             </cac:CreditNoteLine>';
          }
          $xml.='</CreditNote>';
 
       $doc->loadXML($xml);
       $doc->save($nombrexml.'.XML'); 
    }

    function CrearXMLNotaDebito($nombrexml, $emisor, $cliente, $comprobante, $detalle)
    {
 
       $doc = new DOMDocument();
       $doc->formatOutput = FALSE;
       $doc->preserveWhiteSpace = TRUE;
       $doc->encoding = 'utf-8';    
 
       $xml = '<?xml version="1.0" encoding="UTF-8"?>
       <DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
          <ext:UBLExtensions>
             <ext:UBLExtension>
                <ext:ExtensionContent />
             </ext:UBLExtension>
          </ext:UBLExtensions>
          <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
          <cbc:CustomizationID>2.0</cbc:CustomizationID>
          <cbc:ID>'.$comprobante['serie'].'-'.$comprobante['correlativo'].'</cbc:ID>
          <cbc:IssueDate>'.$comprobante['fecha_emision'].'</cbc:IssueDate>
          <cbc:IssueTime>00:00:03</cbc:IssueTime>
          <cbc:Note languageLocaleID="1000"><![CDATA['.$comprobante['total_texto'].']]></cbc:Note>
          <cbc:DocumentCurrencyCode>'.$comprobante['moneda'].'</cbc:DocumentCurrencyCode>
          <cac:DiscrepancyResponse>
             <cbc:ReferenceID>'.$comprobante['serie_ref'].'-'.$comprobante['correlativo_ref'].'</cbc:ReferenceID>
             <cbc:ResponseCode>'.$comprobante['codmotivo'].'</cbc:ResponseCode>
             <cbc:Description>'.$comprobante['descripcion'].'</cbc:Description>
          </cac:DiscrepancyResponse>
          <cac:BillingReference>
             <cac:InvoiceDocumentReference>
                <cbc:ID>'.$comprobante['serie_ref'].'-'.$comprobante['correlativo_ref'].'</cbc:ID>
                <cbc:DocumentTypeCode>'.$comprobante['tipodoc_ref'].'</cbc:DocumentTypeCode>
             </cac:InvoiceDocumentReference>
          </cac:BillingReference>
          <cac:Signature>
             <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
             <cbc:Note><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Note>
             <cac:SignatoryParty>
                <cac:PartyIdentification>
                   <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                   <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                </cac:PartyName>
             </cac:SignatoryParty>
             <cac:DigitalSignatureAttachment>
                <cac:ExternalReference>
                   <cbc:URI>#SIGN-EMPRESA</cbc:URI>
                </cac:ExternalReference>
             </cac:DigitalSignatureAttachment>
          </cac:Signature>
          <cac:AccountingSupplierParty>
             <cac:Party>
                <cac:PartyIdentification>
                   <cbc:ID schemeID="'.$emisor['tipodoc'].'">'.$emisor['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                   <cbc:Name><![CDATA['.$emisor['nombre_comercial'].']]></cbc:Name>
                </cac:PartyName>
                <cac:PartyLegalEntity>
                   <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                   <cac:RegistrationAddress>
                      <cbc:ID>'.$emisor['ubigeo'].'</cbc:ID>
                      <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                      <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
                      <cbc:CityName>'.$emisor['provincia'].'</cbc:CityName>
                      <cbc:CountrySubentity>'.$emisor['departamento'].'</cbc:CountrySubentity>
                      <cbc:District>'.$emisor['distrito'].'</cbc:District>
                      <cac:AddressLine>
                         <cbc:Line><![CDATA['.$emisor['direccion'].']]></cbc:Line>
                      </cac:AddressLine>
                      <cac:Country>
                         <cbc:IdentificationCode>'.$emisor['pais'].'</cbc:IdentificationCode>
                      </cac:Country>
                   </cac:RegistrationAddress>
                </cac:PartyLegalEntity>
             </cac:Party>
          </cac:AccountingSupplierParty>
             <cac:AccountingCustomerParty>
             <cac:Party>
                <cac:PartyIdentification>
                   <cbc:ID schemeID="'.$cliente['tipodoc'].'">'.$cliente['ruc'].'</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyLegalEntity>
                   <cbc:RegistrationName><![CDATA['.$cliente['razon_social'].']]></cbc:RegistrationName>
                   <cac:RegistrationAddress>
                      <cac:AddressLine>
                         <cbc:Line><![CDATA['.$cliente['direccion'].']]></cbc:Line>
                      </cac:AddressLine>
                      <cac:Country>
                         <cbc:IdentificationCode>'.$cliente['pais'].'</cbc:IdentificationCode>
                      </cac:Country>
                   </cac:RegistrationAddress>
                </cac:PartyLegalEntity>
             </cac:Party>
          </cac:AccountingCustomerParty>
          <cac:TaxTotal>
             <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
             <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total_opgravadas'].'</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['igv'].'</cbc:TaxAmount>
                <cac:TaxCategory>
                   <cac:TaxScheme>
                      <cbc:ID>1000</cbc:ID>
                      <cbc:Name>IGV</cbc:Name>
                      <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                   </cac:TaxScheme>
                </cac:TaxCategory>
             </cac:TaxSubtotal>
          </cac:TaxTotal>
          <cac:RequestedMonetaryTotal>
             <cbc:PayableAmount currencyID="'.$comprobante['moneda'].'">'.$comprobante['total'].'</cbc:PayableAmount>
          </cac:RequestedMonetaryTotal>';
          
          foreach($detalle as $k=>$v){
 
             $xml.='<cac:DebitNoteLine>
                <cbc:ID>'.$v['item'].'</cbc:ID>
                <cbc:DebitedQuantity unitCode="'.$v['unidad'].'">'.$v['cantidad'].'</cbc:DebitedQuantity>
                <cbc:LineExtensionAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:LineExtensionAmount>
                <cac:PricingReference>
                   <cac:AlternativeConditionPrice>
                      <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['precio_unitario'].'</cbc:PriceAmount>
                      <cbc:PriceTypeCode>'.$v['tipo_precio'].'</cbc:PriceTypeCode>
                   </cac:AlternativeConditionPrice>
                </cac:PricingReference>
                <cac:TaxTotal>
                   <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                   <cac:TaxSubtotal>
                      <cbc:TaxableAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_total'].'</cbc:TaxableAmount>
                      <cbc:TaxAmount currencyID="'.$comprobante['moneda'].'">'.$v['igv'].'</cbc:TaxAmount>
                      <cac:TaxCategory>
                         <cbc:Percent>'.$v['porcentaje_igv'].'</cbc:Percent>
                         <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
                         <cac:TaxScheme>
                            <cbc:ID>'.$v['codigo_afectacion'].'</cbc:ID>
                            <cbc:Name>'.$v['nombre_afectacion'].'</cbc:Name>
                            <cbc:TaxTypeCode>'.$v['tipo_afectacion'].'</cbc:TaxTypeCode>
                         </cac:TaxScheme>
                      </cac:TaxCategory>
                   </cac:TaxSubtotal>
                </cac:TaxTotal>
                <cac:Item>
                   <cbc:Description><![CDATA['.$v['descripcion'].']]></cbc:Description>
                   <cac:SellersItemIdentification>
                      <cbc:ID>'.$v['codigo'].'</cbc:ID>
                   </cac:SellersItemIdentification>
                </cac:Item>
                <cac:Price>
                   <cbc:PriceAmount currencyID="'.$comprobante['moneda'].'">'.$v['valor_unitario'].'</cbc:PriceAmount>
                </cac:Price>
             </cac:DebitNoteLine>';
          
          }
 
             $xml.='</DebitNote>';
 
             $doc->loadXML($xml);
             $doc->save($nombrexml.'.XML'); 
    }

    function CrearXMLResumenDocumentos($emisor, $cabecera, $detalle, $nombrexml)
    {
         $doc = new DOMDocument();
         $doc->formatOutput = FALSE;
         $doc->preserveWhiteSpace = TRUE;
         $doc->encoding = 'utf-8';   
   
         $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <SummaryDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2">
           <ext:UBLExtensions>
               <ext:UBLExtension>
                   <ext:ExtensionContent />
               </ext:UBLExtension>
           </ext:UBLExtensions>
           <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
           <cbc:CustomizationID>1.1</cbc:CustomizationID>
           <cbc:ID>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:ID>
           <cbc:ReferenceDate>'.$cabecera['fecha_emision'].'</cbc:ReferenceDate>
           <cbc:IssueDate>'.$cabecera['fecha_envio'].'</cbc:IssueDate>
           <cac:Signature>
               <cbc:ID>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:ID>
               <cac:SignatoryParty>
                   <cac:PartyIdentification>
                       <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                   </cac:PartyIdentification>
                   <cac:PartyName>
                       <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                   </cac:PartyName>
               </cac:SignatoryParty>
               <cac:DigitalSignatureAttachment>
                   <cac:ExternalReference>
                       <cbc:URI>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:URI>
                   </cac:ExternalReference>
               </cac:DigitalSignatureAttachment>
           </cac:Signature>
           <cac:AccountingSupplierParty>
               <cbc:CustomerAssignedAccountID>'.$emisor['ruc'].'</cbc:CustomerAssignedAccountID>
               <cbc:AdditionalAccountID>'.$emisor['tipodoc'].'</cbc:AdditionalAccountID>
               <cac:Party>
                   <cac:PartyLegalEntity>
                       <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                   </cac:PartyLegalEntity>
               </cac:Party>
           </cac:AccountingSupplierParty>';
   
           foreach ($detalle as $k => $v) {
              $xml.='<sac:SummaryDocumentsLine>
                  <cbc:LineID>'.$v['item'].'</cbc:LineID>
                  <cbc:DocumentTypeCode>'.$v['tipodoc'].'</cbc:DocumentTypeCode>
                  <cbc:ID>'.$v['serie'].'-'.$v['correlativo'].'</cbc:ID>
                  <cac:Status>
                     <cbc:ConditionCode>'.$v['condicion'].'</cbc:ConditionCode>
                  </cac:Status>                
                  <sac:TotalAmount currencyID="'.$v['moneda'].'">'.$v['importe_total'].'</sac:TotalAmount><sac:BillingPayment>
                            <cbc:PaidAmount currencyID="'.$v['moneda'].'">'.$v['valor_total'].'</cbc:PaidAmount>
                            <cbc:InstructionID>'.$v['tipo_total'].'</cbc:InstructionID>
                        </sac:BillingPayment><cac:TaxTotal>
                      <cbc:TaxAmount currencyID="'.$v['moneda'].'">'.$v['igv_total'].'</cbc:TaxAmount>';
                      
                      if($v['codigo_afectacion']!='1000'){
                      $xml.='<cac:TaxSubtotal>
                          <cbc:TaxAmount currencyID="'.$v['moneda'].'">'.$v['igv_total'].'</cbc:TaxAmount>
                          <cac:TaxCategory>
                              <cac:TaxScheme>
                                  <cbc:ID>'.$v['codigo_afectacion'].'</cbc:ID>
                                  <cbc:Name>'.$v['nombre_afectacion'].'</cbc:Name>
                                  <cbc:TaxTypeCode>'.$v['tipo_afectacion'].'</cbc:TaxTypeCode>
                              </cac:TaxScheme>
                          </cac:TaxCategory>
                      </cac:TaxSubtotal>';
                     }
   
                      $xml.='<cac:TaxSubtotal>
                          <cbc:TaxAmount currencyID="'.$v['moneda'].'">'.$v['igv_total'].'</cbc:TaxAmount>
                          <cac:TaxCategory>
                              <cac:TaxScheme>
                                  <cbc:ID>1000</cbc:ID>
                                  <cbc:Name>IGV</cbc:Name>
                                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                              </cac:TaxScheme>
                          </cac:TaxCategory>
                      </cac:TaxSubtotal>';
   
                  $xml.='</cac:TaxTotal>
              </sac:SummaryDocumentsLine>';
           }
           
         $xml.='</SummaryDocuments>';
   
         $doc->loadXML($xml);
         $doc->save($nombrexml.'.XML'); 
    }
 
    function CrearXmlBajaDocumentos($emisor, $cabecera, $detalle, $nombrexml)
    {
         $doc = new DOMDocument();
         $doc->formatOutput = FALSE;
         $doc->preserveWhiteSpace = TRUE;
         $doc->encoding = 'utf-8';   
   
         $xml = '<?xml version="1.0" encoding="UTF-8"?>
         <VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
           <ext:UBLExtensions>
               <ext:UBLExtension>
                   <ext:ExtensionContent />
               </ext:UBLExtension>
           </ext:UBLExtensions>
           <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
           <cbc:CustomizationID>1.0</cbc:CustomizationID>
           <cbc:ID>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:ID>
           <cbc:ReferenceDate>'.$cabecera['fecha_emision'].'</cbc:ReferenceDate>
           <cbc:IssueDate>'.$cabecera['fecha_envio'].'</cbc:IssueDate>
           <cac:Signature>
               <cbc:ID>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:ID>
               <cac:SignatoryParty>
                   <cac:PartyIdentification>
                       <cbc:ID>'.$emisor['ruc'].'</cbc:ID>
                   </cac:PartyIdentification>
                   <cac:PartyName>
                       <cbc:Name><![CDATA['.$emisor['razon_social'].']]></cbc:Name>
                   </cac:PartyName>
               </cac:SignatoryParty>
               <cac:DigitalSignatureAttachment>
                   <cac:ExternalReference>
                       <cbc:URI>'.$cabecera['tipodoc'].'-'.$cabecera['serie'].'-'.$cabecera['correlativo'].'</cbc:URI>
                   </cac:ExternalReference>
               </cac:DigitalSignatureAttachment>
           </cac:Signature>
           <cac:AccountingSupplierParty>
               <cbc:CustomerAssignedAccountID>'.$emisor['ruc'].'</cbc:CustomerAssignedAccountID>
               <cbc:AdditionalAccountID>'.$emisor['tipodoc'].'</cbc:AdditionalAccountID>
               <cac:Party>
                   <cac:PartyLegalEntity>
                       <cbc:RegistrationName><![CDATA['.$emisor['razon_social'].']]></cbc:RegistrationName>
                   </cac:PartyLegalEntity>
               </cac:Party>
           </cac:AccountingSupplierParty>';
   
           foreach ($detalle as $k => $v) {
              $xml.='<sac:VoidedDocumentsLine>
                  <cbc:LineID>'.$v['item'].'</cbc:LineID>
                  <cbc:DocumentTypeCode>'.$v['tipodoc'].'</cbc:DocumentTypeCode>
                  <sac:DocumentSerialID>'.$v['serie'].'</sac:DocumentSerialID>
                  <sac:DocumentNumberID>'.$v['correlativo'].'</sac:DocumentNumberID>
                  <sac:VoidReasonDescription><![CDATA['.$v['motivo'].']]></sac:VoidReasonDescription>
              </sac:VoidedDocumentsLine>';
           }
           
         $xml.='</VoidedDocuments>';
   
         $doc->loadXML($xml);
         $doc->save($nombrexml.'.XML'); 
    } 

}

?>