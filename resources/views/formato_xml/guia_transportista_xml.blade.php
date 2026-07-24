<DespatchAdvice xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2">

<!-- ext:UBLExtensions>
</ext:UBLExtensions -->
<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
<cbc:CustomizationID>2.0</cbc:CustomizationID>
<cbc:ID>{{$guia->serieguia}}-{{$guia->numeroguia}}</cbc:ID>
<!--  FECHA Y HORA DE EMISION  -->
<cbc:IssueDate>{{$guia->fechaemision}}</cbc:IssueDate>
<cbc:IssueTime>{{$guia->hora}}</cbc:IssueTime>
<cbc:DespatchAdviceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">31</cbc:DespatchAdviceTypeCode>
<!--  DOCUMENTOS ADICIONALES (Catalogo D41) -->
@foreach($relacionados as $rel)
  <cac:AdditionalDocumentReference>
  <cbc:ID>{{$rel->ser_num_doc_rel}}</cbc:ID>
  <cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Documento relacionado al transporte" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo61">{{$rel->tdocod}}</cbc:DocumentTypeCode>
  <cbc:DocumentType>{{$rel->tdodes}}</cbc:DocumentType>
  <cac:IssuerParty>
    <cac:PartyIdentification>
      <cbc:ID schemeID="6" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$rel->ruc_emi_doc_rel}}</cbc:ID>
    </cac:PartyIdentification>
  </cac:IssuerParty>
</cac:AdditionalDocumentReference>
@endforeach
<cac:Signature>
  <cbc:ID>{{$empresa->NomEmpresa}}</cbc:ID>
  <cac:SignatoryParty>
    <cac:PartyIdentification>
      <cbc:ID>{{$empresa->IdEmpresa}}</cbc:ID>
    </cac:PartyIdentification>
    <cac:PartyName>
      <cbc:Name>{{$empresa->NomEmpresa}}</cbc:Name>
    </cac:PartyName>
  </cac:SignatoryParty>
  <cac:DigitalSignatureAttachment>
    <cac:ExternalReference>
      <cbc:URI>#GREENTER-SIGN</cbc:URI>
    </cac:ExternalReference>
  </cac:DigitalSignatureAttachment>
</cac:Signature>
<!--  DATOS DEL EMISOR (TRANSPORTISTA)  -->
<cac:DespatchSupplierParty>
  <cac:Party>
    <cac:PartyIdentification>
      <cbc:ID schemeID="{{$guia->tdicodtransportista}}" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$guia->ructransportista}}</cbc:ID>
    </cac:PartyIdentification>
    <cac:PartyLegalEntity>
      <cbc:RegistrationName>{{$guia->nombretransportista}}</cbc:RegistrationName>
    </cac:PartyLegalEntity>
  </cac:Party>
</cac:DespatchSupplierParty>
<!--  DATOS DEL RECEPTOR (DESTINATARIO)  -->
<cac:DeliveryCustomerParty>
  <cac:Party>
    <cac:PartyIdentification>
      <cbc:ID schemeID="{{$guia->tdicod}}" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$guia->ruccliente}}</cbc:ID>
    </cac:PartyIdentification>
    <cac:PartyLegalEntity>
      <cbc:RegistrationName>{{$guia->nomcliente}}</cbc:RegistrationName>
    </cac:PartyLegalEntity>
  </cac:Party>
</cac:DeliveryCustomerParty>
<!--  DATOS DE QUIEN PAGA EL SERVICIO  -->
<!--<cac:OriginatorCustomerParty>
  <cac:Party>
    <cac:PartyIdentification>
      <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">10474757051</cbc:ID>
    </cac:PartyIdentification>
    <cac:PartyLegalEntity>
      <cbc:RegistrationName>BARRETO LOPEZ JACK ANDERSON</cbc:RegistrationName>
    </cac:PartyLegalEntity>
  </cac:Party>
</cac:OriginatorCustomerParty>-->
<!--  DATOS DEL TRASLADO  -->
<cac:Shipment>
  <!--  ID OBLIGATORIO POR UBL  -->
  <cbc:ID>SUNAT_Envio</cbc:ID>
  <!--  PESO BRUTO TOTAL DE LA CARGA  -->
  <cbc:GrossWeightMeasure unitCode="KGM">{{$guia->pesobruto}}</cbc:GrossWeightMeasure>
  <!--  INDICADORES  -->
  <!--  Indicador de pagador del flete  -->
  <cbc:SpecialInstructions>SUNAT_Envio_IndicadorPagadorFlete_Remitente</cbc:SpecialInstructions>
  <cac:Consignment>
    <!--  ID OBLIGATORIO POR UBL  -->
    <cbc:ID>SUNAT_Envio</cbc:ID>
  </cac:Consignment>
  <cac:ShipmentStage>
    <!--  FECHA DE INICIO DEL TRASLADO  -->
    <cac:TransitPeriod>
      <cbc:StartDate>{{$guia->fechatraslado}}</cbc:StartDate>
    </cac:TransitPeriod>
    <!--  CONDUCTOR PRINCIPAL  -->
    <cac:DriverPerson>
      <!--  TIPO Y NUMERO DE DOCUMENTO DE IDENTIDAD  -->
      <cbc:ID schemeID="1" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$guia->rucconductor}}</cbc:ID>
      <!--  NOMBRES  -->
      <cbc:FirstName>{{$guia->nomconductor}}</cbc:FirstName>
      <!--  APELLIDOS  -->
      <cbc:FamilyName>{{$guia->apeconductor}}</cbc:FamilyName>
      <!--  TIPO DE CONDUCTOR: PRINCIPAL  -->
      <cbc:JobTitle>Principal</cbc:JobTitle>
      <cac:IdentityDocumentReference>
        <!--  LICENCIA DE CONDUCIR  -->
        <cbc:ID>{{$guia->licencia}}</cbc:ID>
      </cac:IdentityDocumentReference>
    </cac:DriverPerson>
  </cac:ShipmentStage>
  <cac:Delivery>
    <!--  DIRECCION DEL PUNTO DE LLEGADA  -->
    <cac:DeliveryAddress>
      <!--  UBIGEO DE LLEGADA  -->
      <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">{{$guia->ubigeollegada}}</cbc:ID>
      <!--  DIRECCION COMPLETA Y DETALLADA DE LLEGADA  -->
      <cac:AddressLine>
        <cbc:Line>{{$guia->direccionllegada}}</cbc:Line>
      </cac:AddressLine>
    </cac:DeliveryAddress>
    <cac:Despatch>
      <!--  DIRECCION DEL PUNTO DE PARTIDA  -->
      <cac:DespatchAddress>
        <!--  UBIGEO DE PARTIDA  -->
        <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">{{$guia->ubigeopartida}}</cbc:ID>
        <!--  DIRECCION COMPLETA Y DETALLADA DE PARTIDA  -->
        <cac:AddressLine>
          <cbc:Line>{{$guia->direccionpartida}}</cbc:Line>
        </cac:AddressLine>
      </cac:DespatchAddress>
      <!--  DATOS DEL REMITENTE  -->
      <cac:DespatchParty>
        <cac:PartyIdentification>
          <cbc:ID schemeID="{{$guia->tdicodrem}}" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$guia->rucclienterem}}</cbc:ID>
        </cac:PartyIdentification>
        <cac:PartyLegalEntity>
          <cbc:RegistrationName>{{$guia->nomclienterem}}</cbc:RegistrationName>
        </cac:PartyLegalEntity>
      </cac:DespatchParty>
    </cac:Despatch>
  </cac:Delivery>
  <cac:TransportHandlingUnit>
    <!--  NUMERO DE CONTENEDOR  -->
    <cbc:ID>-</cbc:ID>
    <cac:TransportEquipment>
      <!--  VEHICULO PRINCIPAL  -->
      <!--  PLACA - VEHICULO PRINCIPAL  -->
      <cbc:ID>{{$guia->placa}}</cbc:ID>
    </cac:TransportEquipment>
  </cac:TransportHandlingUnit>
</cac:Shipment>
@php
  $i=0;
@endphp
@foreach($detalle as $det)
@php
  $i=$i+1;
@endphp
<cac:DespatchLine>
  <!--  NUMERO DE ORDEN DEL ITEM  -->
  <cbc:ID>{{$i}}</cbc:ID>
  <!--  CANTIDAD  -->
  <cbc:DeliveredQuantity unitCode="{{$det->umecod}}" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">{{$det->cantidad}}</cbc:DeliveredQuantity>
  <cac:OrderLineReference>
    <cbc:LineID>{{$i}}</cbc:LineID>
  </cac:OrderLineReference>
  <cac:Item>
    <!--  DESCRIPCION  -->
    <cbc:Description>{{$det->pronom}}</cbc:Description>
    <!--  CODIGO DEL ITEM  -->
    <cac:SellersItemIdentification>
      <cbc:ID>-</cbc:ID>
    </cac:SellersItemIdentification>
    <!-- INDICADOR DE BIEN REGULADO POR SUNAT  -->
  </cac:Item>
</cac:DespatchLine>
@endforeach
</DespatchAdvice>