<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* despatch.xml.twig */
class __TwigTemplate_7a9861328e853ad01d5a6c362e4c4328457783ed9b6cf613c66bbf518a1d2a1b extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        ob_start(function () { return ''; });
        // line 2
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<DespatchAdvice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\">
\t<ext:UBLExtensions>
\t\t<ext:UBLExtension>
\t\t\t<ext:ExtensionContent/>
\t\t</ext:UBLExtension>
\t</ext:UBLExtensions>
\t<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
\t<cbc:CustomizationID>1.0</cbc:CustomizationID>
\t<cbc:ID>";
        // line 11
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "serie", [], "any", false, false, false, 11);
        echo "-";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "correlativo", [], "any", false, false, false, 11);
        echo "</cbc:ID>
\t<cbc:IssueDate>";
        // line 12
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fechaEmision", [], "any", false, false, false, 12), "Y-m-d");
        echo "</cbc:IssueDate>
\t<cbc:IssueTime>";
        // line 13
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fechaEmision", [], "any", false, false, false, 13), "H:i:s");
        echo "</cbc:IssueTime>
\t<cbc:DespatchAdviceTypeCode>";
        // line 14
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoDoc", [], "any", false, false, false, 14);
        echo "</cbc:DespatchAdviceTypeCode>
    ";
        // line 15
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "observacion", [], "any", false, false, false, 15)) {
            // line 16
            echo "\t<cbc:Note><![CDATA[";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "observacion", [], "any", false, false, false, 16);
            echo "]]></cbc:Note>
    ";
        }
        // line 18
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "docBaja", [], "any", false, false, false, 18)) {
            // line 19
            echo "\t<cac:OrderReference>
\t\t<cbc:ID>";
            // line 20
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "docBaja", [], "any", false, false, false, 20), "nroDoc", [], "any", false, false, false, 20);
            echo "</cbc:ID>
\t\t<cbc:OrderTypeCode listAgencyName=\"PE:SUNAT\" listName=\"SUNAT:Identificador de Tipo de Documento\" listURI=\"urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01\">";
            // line 21
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "docBaja", [], "any", false, false, false, 21), "tipoDoc", [], "any", false, false, false, 21);
            echo "</cbc:OrderTypeCode>
\t</cac:OrderReference>
    ";
        }
        // line 24
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "relDoc", [], "any", false, false, false, 24)) {
            // line 25
            echo "\t<cac:AdditionalDocumentReference>
\t\t<cbc:ID>";
            // line 26
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "relDoc", [], "any", false, false, false, 26), "nroDoc", [], "any", false, false, false, 26);
            echo "</cbc:ID>
\t\t<cbc:DocumentTypeCode listAgencyName=\"PE:SUNAT\" listName=\"SUNAT:Identificador de documento relacionado\" listURI=\"urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo21\">";
            // line 27
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "relDoc", [], "any", false, false, false, 27), "tipoDoc", [], "any", false, false, false, 27);
            echo "</cbc:DocumentTypeCode>
\t</cac:AdditionalDocumentReference>
    ";
        }
        // line 30
        echo "    ";
        $context["emp"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "company", [], "any", false, false, false, 30);
        // line 31
        echo "    \t<cac:Signature>
\t\t<cbc:ID>";
        // line 32
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 32);
        echo "</cbc:ID>
\t\t<cac:SignatoryParty>
\t\t\t<cac:PartyIdentification>
\t\t\t\t<cbc:ID>";
        // line 35
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 35);
        echo "</cbc:ID>
\t\t\t</cac:PartyIdentification>
\t\t\t<cac:PartyName>
\t\t\t\t<cbc:Name><![CDATA[";
        // line 38
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "razonSocial", [], "any", false, false, false, 38);
        echo "]]></cbc:Name>
\t\t\t</cac:PartyName>
\t\t</cac:SignatoryParty>
\t\t<cac:DigitalSignatureAttachment>
\t\t\t<cac:ExternalReference>
\t\t\t\t<cbc:URI>#GREENTER-SIGN</cbc:URI>
\t\t\t</cac:ExternalReference>
\t\t</cac:DigitalSignatureAttachment>
\t</cac:Signature>
\t<cac:DespatchSupplierParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"6\">";
        // line 48
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 48);
        echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
        // line 51
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "razonSocial", [], "any", false, false, false, 51);
        echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:DespatchSupplierParty>
\t<cac:DeliveryCustomerParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"";
        // line 56
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 56), "tipoDoc", [], "any", false, false, false, 56);
        echo "\">";
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 56), "numDoc", [], "any", false, false, false, 56);
        echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
        // line 59
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 59), "rznSocial", [], "any", false, false, false, 59);
        echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:DeliveryCustomerParty>
    ";
        // line 63
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 63)) {
            // line 64
            echo "\t<cac:SellerSupplierParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"";
            // line 65
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 65), "tipoDoc", [], "any", false, false, false, 65);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 65), "numDoc", [], "any", false, false, false, 65);
            echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
            // line 68
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 68), "rznSocial", [], "any", false, false, false, 68);
            echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:SellerSupplierParty>
    ";
        }
        // line 73
        echo "    ";
        $context["envio"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "envio", [], "any", false, false, false, 73);
        // line 74
        echo "\t<cac:Shipment>
\t\t<cbc:ID>1</cbc:ID>
\t\t<cbc:HandlingCode>";
        // line 76
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codTraslado", [], "any", false, false, false, 76);
        echo "</cbc:HandlingCode>
        ";
        // line 77
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "desTraslado", [], "any", false, false, false, 77)) {
            // line 78
            echo "\t\t<cbc:Information>";
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "desTraslado", [], "any", false, false, false, 78);
            echo "</cbc:Information>
\t\t";
        }
        // line 80
        echo "\t\t<cbc:GrossWeightMeasure unitCode=\"";
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "undPesoTotal", [], "any", false, false, false, 80);
        echo "\">";
        echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "pesoTotal", [], "any", false, false, false, 80), 3);
        echo "</cbc:GrossWeightMeasure>
        ";
        // line 81
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numBultos", [], "any", false, false, false, 81)) {
            // line 82
            echo "\t\t<cbc:TotalTransportHandlingUnitQuantity>";
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numBultos", [], "any", false, false, false, 82);
            echo "</cbc:TotalTransportHandlingUnitQuantity>
\t\t";
        }
        // line 84
        echo "\t\t<cbc:SplitConsignmentIndicator>";
        echo ((twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "indTransbordo", [], "any", false, false, false, 84)) ? ("true") : ("false"));
        echo "</cbc:SplitConsignmentIndicator>
\t\t<cac:ShipmentStage>
\t\t\t<cbc:TransportModeCode>";
        // line 86
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "modTraslado", [], "any", false, false, false, 86);
        echo "</cbc:TransportModeCode>
\t\t\t<cac:TransitPeriod>
\t\t\t\t<cbc:StartDate>";
        // line 88
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "fecTraslado", [], "any", false, false, false, 88), "Y-m-d");
        echo "</cbc:StartDate>
\t\t\t</cac:TransitPeriod>
            ";
        // line 90
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 90)) {
            // line 91
            echo "\t\t\t<cac:CarrierParty>
\t\t\t\t<cac:PartyIdentification>
\t\t\t\t\t<cbc:ID schemeID=\"";
            // line 93
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 93), "tipoDoc", [], "any", false, false, false, 93);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 93), "numDoc", [], "any", false, false, false, 93);
            echo "</cbc:ID>
\t\t\t\t</cac:PartyIdentification>
\t\t\t\t<cac:PartyName>
\t\t\t\t\t<cbc:Name><![CDATA[";
            // line 96
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 96), "rznSocial", [], "any", false, false, false, 96);
            echo "]]></cbc:Name>
\t\t\t\t</cac:PartyName>
\t\t\t</cac:CarrierParty>
\t\t\t<cac:TransportMeans>
\t\t\t\t<cac:RoadTransport>
\t\t\t\t\t<cbc:LicensePlateID>";
            // line 101
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 101), "placa", [], "any", false, false, false, 101);
            echo "</cbc:LicensePlateID>
\t\t\t\t</cac:RoadTransport>
\t\t\t</cac:TransportMeans>
\t\t\t<cac:DriverPerson>
\t\t\t\t<cbc:ID schemeID=\"";
            // line 105
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 105), "choferTipoDoc", [], "any", false, false, false, 105);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 105), "choferDoc", [], "any", false, false, false, 105);
            echo "</cbc:ID>
\t\t\t</cac:DriverPerson>
            ";
        }
        // line 108
        echo "\t\t</cac:ShipmentStage>
\t\t<cac:Delivery>
\t\t\t<cac:DeliveryAddress>
\t\t\t\t<cbc:ID>";
        // line 111
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "llegada", [], "any", false, false, false, 111), "ubigueo", [], "any", false, false, false, 111);
        echo "</cbc:ID>
\t\t\t\t<cbc:StreetName>";
        // line 112
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "llegada", [], "any", false, false, false, 112), "direccion", [], "any", false, false, false, 112);
        echo "</cbc:StreetName>
\t\t\t</cac:DeliveryAddress>
\t\t</cac:Delivery>
        ";
        // line 115
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numContenedor", [], "any", false, false, false, 115)) {
            // line 116
            echo "\t\t<cac:TransportHandlingUnit>
\t\t\t<cbc:ID>";
            // line 117
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numContenedor", [], "any", false, false, false, 117);
            echo "</cbc:ID>
\t\t</cac:TransportHandlingUnit>
        ";
        }
        // line 120
        echo "\t\t<cac:OriginAddress>
\t\t\t<cbc:ID>";
        // line 121
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "partida", [], "any", false, false, false, 121), "ubigueo", [], "any", false, false, false, 121);
        echo "</cbc:ID>
\t\t\t<cbc:StreetName>";
        // line 122
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "partida", [], "any", false, false, false, 122), "direccion", [], "any", false, false, false, 122);
        echo "</cbc:StreetName>
\t\t</cac:OriginAddress>
        ";
        // line 124
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codPuerto", [], "any", false, false, false, 124)) {
            // line 125
            echo "\t\t<cac:FirstArrivalPortLocation>
\t\t\t<cbc:ID>";
            // line 126
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codPuerto", [], "any", false, false, false, 126);
            echo "</cbc:ID>
\t\t</cac:FirstArrivalPortLocation>
        ";
        }
        // line 129
        echo "\t</cac:Shipment>
    ";
        // line 130
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "details", [], "any", false, false, false, 130));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["det"]) {
            // line 131
            echo "\t<cac:DespatchLine>
\t\t<cbc:ID>";
            // line 132
            echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 132);
            echo "</cbc:ID>
\t\t<cbc:DeliveredQuantity unitCode=\"";
            // line 133
            echo twig_get_attribute($this->env, $this->source, $context["det"], "unidad", [], "any", false, false, false, 133);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["det"], "cantidad", [], "any", false, false, false, 133);
            echo "</cbc:DeliveredQuantity>
\t\t<cac:OrderLineReference>
\t\t\t<cbc:LineID>";
            // line 135
            echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 135);
            echo "</cbc:LineID>
\t\t</cac:OrderLineReference>
\t\t<cac:Item>
\t\t\t<cbc:Name><![CDATA[";
            // line 138
            echo twig_get_attribute($this->env, $this->source, $context["det"], "descripcion", [], "any", false, false, false, 138);
            echo "]]></cbc:Name>
\t\t\t<cac:SellersItemIdentification>
\t\t\t\t<cbc:ID>";
            // line 140
            echo twig_get_attribute($this->env, $this->source, $context["det"], "codigo", [], "any", false, false, false, 140);
            echo "</cbc:ID>
\t\t\t</cac:SellersItemIdentification>
\t\t\t";
            // line 142
            if (twig_get_attribute($this->env, $this->source, $context["det"], "codProdSunat", [], "any", false, false, false, 142)) {
                // line 143
                echo "\t\t\t\t<cac:CommodityClassification>
\t\t\t\t\t<cbc:ItemClassificationCode listID=\"UNSPSC\" listAgencyName=\"GS1 US\" listName=\"Item Classification\">";
                // line 144
                echo twig_get_attribute($this->env, $this->source, $context["det"], "codProdSunat", [], "any", false, false, false, 144);
                echo "</cbc:ItemClassificationCode>
\t\t\t\t</cac:CommodityClassification>
\t\t\t";
            }
            // line 147
            echo "\t\t</cac:Item>
\t</cac:DespatchLine>
    ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['det'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 150
        echo "</DespatchAdvice>
";
        $___internal_parse_0_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 1
        echo twig_spaceless($___internal_parse_0_);
    }

    public function getTemplateName()
    {
        return "despatch.xml.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  406 => 1,  402 => 150,  386 => 147,  380 => 144,  377 => 143,  375 => 142,  370 => 140,  365 => 138,  359 => 135,  352 => 133,  348 => 132,  345 => 131,  328 => 130,  325 => 129,  319 => 126,  316 => 125,  314 => 124,  309 => 122,  305 => 121,  302 => 120,  296 => 117,  293 => 116,  291 => 115,  285 => 112,  281 => 111,  276 => 108,  268 => 105,  261 => 101,  253 => 96,  245 => 93,  241 => 91,  239 => 90,  234 => 88,  229 => 86,  223 => 84,  217 => 82,  215 => 81,  208 => 80,  202 => 78,  200 => 77,  196 => 76,  192 => 74,  189 => 73,  181 => 68,  173 => 65,  170 => 64,  168 => 63,  161 => 59,  153 => 56,  145 => 51,  139 => 48,  126 => 38,  120 => 35,  114 => 32,  111 => 31,  108 => 30,  102 => 27,  98 => 26,  95 => 25,  92 => 24,  86 => 21,  82 => 20,  79 => 19,  76 => 18,  70 => 16,  68 => 15,  64 => 14,  60 => 13,  56 => 12,  50 => 11,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "despatch.xml.twig", "/var/www/html/factura_biker/sunat2/vendor/greenter/xml/src/Xml/Templates/despatch.xml.twig");
    }
}
