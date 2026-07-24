<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Excel;
use PDF;
use DB;
use XMLWriter;
use DomDocument;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;

class xmlController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    


   /* $xml = new DomDocument('1.0', 'ISO-8859-1');
    $xml->standalone         = false;
    $xml->preserveWhiteSpace = false;
    $Invoice = $xml->createElement('Invoice');
    $Invoice = $xml->appendChild($Invoice);
    // Set the attributes.
    $Invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
    $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
    $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $Invoice->setAttribute('xmlns:ccts', "urn:un:unece:uncefact:documentation:2");
    $Invoice->setAttribute('xmlns:ds', "http://www.w3.org/2000/09/xmldsig#");
    $Invoice->setAttribute('xmlns:ext', "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
    $Invoice->setAttribute('xmlns:qdt', "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
    $Invoice->setAttribute('xmlns:sac', "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
    //$Invoice->setAttribute('xmlns:schemaLocation', "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 ../xsd/maindoc/UBLPE-Invoice-1.0.xsd");
    $Invoice->setAttribute('xmlns:udt', "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
    $UBLExtension = $xml->createElement('ext:UBLExtensions'); $UBLExtension = $Invoice->appendChild($UBLExtension);
        // 18.- Total valor de venta - operaciones gravadas
        // 19.- Total valor de venta - operaciones inafectas
        // 20.- Total valor de venta - operaciones exoneradas
        // 49.- Total valor de venta - operaciones gratuitass
    $ext = $xml->createElement('ext:UBLExtension'); 
    $ext = $UBLExtension->appendChild($ext);
    $contents = $xml->createElement('ext:ExtensionContent'); 
    $contents = $ext->appendChild($contents);
    $sac = $xml->createElement('sac:AdditionalInformation'); 
    $sac = $contents->appendChild($sac);
    
    // el 2005 es Total descuentos
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '2005'); 
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', '18'); 
    $cbc = $monetary->appendChild($cbc); 
    $cbc->setAttribute('currencyID', "PEN");
    
    // el 1001 total velor venta - operaciones gravadas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1001'); 
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', '50'); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    
    // el 1002 total valor venta - operaciones inafectas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1002'); 
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', '0.00'); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    
    // el 1003 total valor venta - operaciones exoneradas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1003');
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', '0.00'); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    
    //31.- leyendas - esta en el catalogo 15 sunat
    $aditional = $xml->createElement('sac:AdditionalProperty'); $aditional = $sac->appendChild($aditional);
    $cbc = $xml->createElement('cbc:ID', '1000'); $cbc = $aditional->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Value', 'SON CIENTO TREINTACINCO PUNTO CINCUENTA Y CUATRO SOLES'); $cbc = $aditional->appendChild($cbc);
                    // falta encontrar y especificar
    $sunat = $xml->createElement('sac:SUNATTransaction'); $sunat = $sac->appendChild($sunat);
    $cbc = $xml->createElement('cbc:ID', '1'); $cbc = $sunat->appendChild($cbc);
    
    // 2.- Firma electronica
    $ext = $xml->createElement('ext:UBLExtension'); $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent', ' '); $contents = $ext->appendChild($contents);

    // 36. Version del UBL
    $cbc = $xml->createElement('cbc:UBLVersionID', '2.0'); 
    $cbc = $Invoice->appendChild($cbc);
    // 37.- Version de la estructura del documento
    $cbc = $xml->createElement('cbc:CustomizationID', '1.0'); 
    $cbc = $Invoice->appendChild($cbc);
    // 8.- Numeracion , conformada por serie y numero correlativo B001-00012926
    $cbc = $xml->createElement('cbc:ID','f001-00000001'); 
    $cbc = $Invoice->appendChild($cbc);
    // 1.- Fecha de emision 2017-04-13
    $cbc = $xml->createElement('cbc:IssueDate', '2018-05-01'); 
    $cbc = $Invoice->appendChild($cbc);
    // 7.- Tipo de Documento 01 Factura 03 Boleta 07 Nota credito - catalogo numero 06
    $cbc = $xml->createElement('cbc:InvoiceTypeCode', '01'); 
    $cbc = $Invoice->appendChild($cbc);
    // 28.- Tipo de moneda en la cual se emite la factura electronica
    $cbc = $xml->createElement('cbc:DocumentCurrencyCode', 'PEN'); 
    $cbc = $Invoice->appendChild($cbc);
    // 2.- Parte de la firma electronica. esto es de quien creo la firma electronica
  
    $cbc = $xml->createElement('cbc:ID', '20532710066'); 
    $cbc = $cac_signature->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Note', 'Elaborado por Sistema de Emision Electronica Facturador SUNAT (SEE-SFS) 1.0.0'); 
    $cbc = $cac_signature->appendChild($cbc);
    
    $cbc = $xml->createElement('cbc:ValidatorID', '780086'); 
    $cbc = $cac_signature->appendChild($cbc);
    $cac_signatory = $xml->createElement('cac:SignatoryParty');
    $cac_signatory = $cac_signature->appendChild($cac_signatory);
    $cac = $xml->createElement('cac:PartyIdentification'); $cac = $cac_signatory->appendChild($cac);
    $cbc = $xml->createElement('cbc:ID', '20532710066'); $cbc = $cac->appendChild($cbc);
    $cac = $xml->createElement('cac:PartyName'); $cac = $cac_signatory->appendChild($cac);
    $cbc = $xml->createElement('cbc:Name', 'DESARROLLO DE SISTEMAS INTEGRADOS DE GESTIÓN'); $cbc = $cac->appendChild($cbc);
    $agent = $xml->createElement('cac:AgentParty'); 
    $agent = $cac_signatory->appendChild($agent);
    $cac = $xml->createElement('cac:PartyIdentification'); 
    $cac = $agent->appendChild($cac);
    $cbc = $xml->createElement('cbc:ID', '20532710066'); 
    $cbc = $cac->appendChild($cbc);
    $cac = $xml->createElement('cac:PartyName'); 
    $cac = $agent->appendChild($cac);
    $cbc = $xml->createElement('cbc:Name', 'prueba'); 
    $cbc = $cac->appendChild($cbc);
    $cac = $xml->createElement('cac:PartyLegalEntity'); 
    $cac = $agent->appendChild($cac);
    $cbc = $xml->createElement('cbc:RegistrationName','preubare'); 
    $cbc = $cac->appendChild($cbc);
    $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
    $cac_digital = $cac_signature->appendChild($cac_digital);
    $cac = $xml->createElement('cac:ExternalReference'); 
    $cac = $cac_digital->appendChild($cac);
    $cbc = $xml->createElement('cbc:URI', 'SIGN'); 
    $cbc = $cac->appendChild($cbc);
    // 3.- Apellidos y nombres, denominacion o razon social (DATOS DEL PROVEEDOR)
    // 4.- Nombre Comercial
    // 5.- Domicilio fiscal
    // 6.- Numero RUC
    $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
    $cac_accounting = $Invoice->appendChild($cac_accounting);
    $cbc = $xml->createElement('cbc:CustomerAssignedAccountID', '20532710066'); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cbc = $xml->createElement('cbc:AdditionalAccountID', '6'); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $cac_accounting->appendChild($cac_party);
    $cac = $xml->createElement('cac:PartyName'); 
    $cac = $cac_party->appendChild($cac);
    $cbc = $xml->createElement('cbc:Name', 'TOYOTA SURMOTRIZ'); 
    $cbc = $cac->appendChild($cbc);
    $address = $xml->createElement('cac:PostalAddress'); 
    $address = $cac_party->appendChild($address);
                // este numerito no se de donde es me parece que es direccion postal
    $cbc = $xml->createElement('cbc:ID', '040101'); 
    $cbc = $address->appendChild($cbc);
    $cbc = $xml->createElement('cbc:StreetName', 'AV. LEGUIA NRO. 1870 (FRENTE A I.E. JOSE ROSA ARA)'); 
    $cbc = $address->appendChild($cbc);
    $cbc = $xml->createElement('cbc:CityName', 'TACNA'); 
    $cbc = $address->appendChild($cbc);
    $cbc = $xml->createElement('cbc:District', 'TACNA'); 
    $cbc = $address->appendChild($cbc);
    $country = $xml->createElement('cac:Country'); 
    $country = $address->appendChild($country);
    $cbc = $xml->createElement('cbc:IdentificationCode', 'PER'); 
    $cbc = $country->appendChild($cbc);
    $legal = $xml->createElement('cac:PartyLegalEntity'); 
    $legal = $cac_party->appendChild($legal);
    $cbc = $xml->createElement('cbc:RegistrationName', 'TOYOTA SURMOTRIZ'); 
    $cbc = $legal->appendChild($cbc);
    // 9.- Tipo y numero de documento de identidad del adquiriente o usuario
    // 10.- Apellidos y nombres, denominacion o razon social del adquiriente o usuario
    $cac_accounting = $xml->createElement('cac:AccountingCustomerParty'); 
    $cac_accounting = $Invoice->appendChild($cac_accounting);
    $cbc = $xml->createElement('cbc:CustomerAssignedAccountID', "150"); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cbc = $xml->createElement('cbc:AdditionalAccountID', "150"); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $cac_accounting->appendChild($cac_party);
    $legal = $xml->createElement('cac:PartyLegalEntity'); 
    $legal = $cac_party->appendChild($legal);
    $cbc = $xml->createElement('cbc:RegistrationName', 'jack'); 
    $cbc = $legal->appendChild($cbc);
    // no tiene numero o no esta registrado
    $seller = $xml->createElement('cac:SellerSupplierParty'); 
    $seller = $Invoice->appendChild($seller);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $seller->appendChild($cac_party);
    $address = $xml->createElement('cac:PostalAddress');
    $address = $cac_party->appendChild($address);
    $cbc = $xml->createElement('cbc:AddressTypeCode', '0'); 
    $cbc = $address->appendChild($cbc);
    // 22.- Sumatoria IGV
    // 23.- Sumatoria ISC
    // 24.- Sumatoria otros tributos
    $taxtotal = $xml->createElement('cac:TaxTotal'); 
    $taxtotal = $Invoice->appendChild($taxtotal);
    $cbc = $xml->createElement('cbc:TaxAmount', '54'); 
    $cbc = $taxtotal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
    $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
    $cbc = $xml->createElement('cbc:TaxAmount', '144'); 
    $cbc = $taxtsubtotal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $taxtcategory = $xml->createElement('cac:TaxCategory'); 
    $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
    $taxscheme = $xml->createElement('cac:TaxScheme'); 
    $taxscheme = $taxtcategory->appendChild($taxscheme);
    $cbc = $xml->createElement('cbc:ID', '1000'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Name', 'IGV'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
    $cbc = $taxscheme->appendChild($cbc);
    // 25.- Sumatoria otros cargos
    $legal = $xml->createElement('cac:LegalMonetaryTotal'); 
    $legal = $Invoice->appendChild($legal);
    $cbc = $xml->createElement('cbc:AllowanceTotalAmount', '0.00'); 
    $cbc = $legal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $cbc = $xml->createElement('cbc:ChargeTotalAmount', '0.00'); 
    $cbc = $legal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $cbc = $xml->createElement('cbc:PayableAmount', '150'); 
    $cbc = $legal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    // detalle de la factura
    $InvoiceLine = $xml->createElement('cac:InvoiceLine'); 
    $InvoiceLine = $Invoice->appendChild($InvoiceLine);
    $cbc = $xml->createElement('cbc:ID', '1'); 
    $cbc = $InvoiceLine->appendChild($cbc);
    $cbc = $xml->createElement('cbc:InvoicedQuantity', '100.00'); 
    $cbc = $InvoiceLine->appendChild($cbc); $cbc->setAttribute('unitCode', "ZZ");
    $cbc = $xml->createElement('cbc:LineExtensionAmount', '100.00'); 
    $cbc = $InvoiceLine->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $pricing = $xml->createElement('cac:PricingReference'); 
    $pricing = $InvoiceLine->appendChild($pricing);
    $cac = $xml->createElement('cac:AlternativeConditionPrice'); 
    $cac = $pricing->appendChild($cac);
    $cbc = $xml->createElement('cbc:PriceAmount', '118.00'); 
    $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $cbc = $xml->createElement('cbc:PriceTypeCode', '01'); 
    $cbc = $cac->appendChild($cbc);
    $allowance = $xml->createElement('cac:AllowanceCharge'); 
    $allowance = $InvoiceLine->appendChild($allowance);
    $cbc = $xml->createElement('cbc:ChargeIndicator', 'false'); 
    $cbc = $allowance->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Amount', '0.00'); 
    $cbc = $allowance->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $taxtotal = $xml->createElement('cac:TaxTotal'); 
    $taxtotal = $InvoiceLine->appendChild($taxtotal);
    $cbc = $xml->createElement('cbc:TaxAmount', '18.00'); 
    $cbc = $taxtotal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
    $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
    $cbc = $xml->createElement('cbc:TaxableAmount', '18.00'); 
    $cbc = $taxtsubtotal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $cbc = $xml->createElement('cbc:TaxAmount', '18.00'); 
    $cbc = $taxtsubtotal->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $taxtcategory = $xml->createElement('cac:TaxCategory'); 
    $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
    $cbc = $xml->createElement('cbc:TaxExemptionReasonCode', '10'); 
    $cbc = $taxtcategory->appendChild($cbc);
    $taxscheme = $xml->createElement('cac:TaxScheme'); 
    $taxscheme = $taxtcategory->appendChild($taxscheme);
    $cbc = $xml->createElement('cbc:ID', '1000'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Name', 'IGV'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
    $cbc = $taxscheme->appendChild($cbc);

    $item = $xml->createElement('cac:Item'); 
    $item = $InvoiceLine->appendChild($item);
    $cbc = $xml->createElement('cbc:Description', 'CLAVO PARA CONCRETO DE  2"'); 
    $cbc = $item->appendChild($cbc);
    $sellers = $xml->createElement('cac:SellersItemIdentification'); 
    $sellers = $item->appendChild($sellers);
    $cbc = $xml->createElement('cbc:ID', 'ALM'); 
    $cbc = $sellers->appendChild($cbc);
    $additional = $xml->createElement('cac:AdditionalItemIdentification'); 
    $additional = $item->appendChild($additional);
    $cbc = $xml->createElement('cbc:ID', 'A'); 
    $cbc = $additional->appendChild($cbc);
    $price = $xml->createElement('cac:Price'); 
    $price = $InvoiceLine->appendChild($price);
    $cbc = $xml->createElement('cbc:PriceAmount', '1.00'); 
    $cbc = $price->appendChild($cbc); $cbc->setAttribute('currencyID', "PEN");
    $xml->formatOutput = true;
    $strings_xml       = $xml->saveXML();
        // Directorio

    $xml->save('archivo.xml');*/

   
    $xmlPath = 'archivo.xml';
$certPath = 'certificate.pem'; // Convertir pfx to pem 

$xmlDocument = new DOMDocument();
$xmlDocument->load($xmlPath);
$xmlTool = new SignedXml();
$xmlTool->setCertificateFromFile($certPath);
$xmlTool->sign($xmlDocument);
$content = $xmlDocument->saveXML();
$xmlTool->verify($xmlDocument);
header('Content-Type: text/xml');
echo $content;

      //return Redirect::to('/SisFact/create/03');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
