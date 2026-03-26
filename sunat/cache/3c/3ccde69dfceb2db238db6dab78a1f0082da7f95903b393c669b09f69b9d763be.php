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
class __TwigTemplate_c1a92c7ee1179cd288042ba5306a123e2837909ff153a5fe5df52d487bb9e07b extends Template
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
        echo "\t<cac:DespatchSupplierParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"6\">";
        // line 32
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 32);
        echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
        // line 35
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "razonSocial", [], "any", false, false, false, 35);
        echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:DespatchSupplierParty>
\t<cac:DeliveryCustomerParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"";
        // line 40
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 40), "tipoDoc", [], "any", false, false, false, 40);
        echo "\">";
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 40), "numDoc", [], "any", false, false, false, 40);
        echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
        // line 43
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "destinatario", [], "any", false, false, false, 43), "rznSocial", [], "any", false, false, false, 43);
        echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:DeliveryCustomerParty>
    ";
        // line 47
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 47)) {
            // line 48
            echo "\t<cac:SellerSupplierParty>
\t\t<cbc:CustomerAssignedAccountID schemeID=\"";
            // line 49
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 49), "tipoDoc", [], "any", false, false, false, 49);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 49), "numDoc", [], "any", false, false, false, 49);
            echo "</cbc:CustomerAssignedAccountID>
\t\t<cac:Party>
\t\t\t<cac:PartyLegalEntity>
\t\t\t\t<cbc:RegistrationName><![CDATA[";
            // line 52
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tercero", [], "any", false, false, false, 52), "rznSocial", [], "any", false, false, false, 52);
            echo "]]></cbc:RegistrationName>
\t\t\t</cac:PartyLegalEntity>
\t\t</cac:Party>
\t</cac:SellerSupplierParty>
    ";
        }
        // line 57
        echo "    ";
        $context["envio"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "envio", [], "any", false, false, false, 57);
        // line 58
        echo "\t<cac:Shipment>
\t\t<cbc:ID>1</cbc:ID>
\t\t<cbc:HandlingCode>";
        // line 60
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codTraslado", [], "any", false, false, false, 60);
        echo "</cbc:HandlingCode>
        ";
        // line 61
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "desTraslado", [], "any", false, false, false, 61)) {
            // line 62
            echo "\t\t<cbc:Information>";
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "desTraslado", [], "any", false, false, false, 62);
            echo "</cbc:Information>
\t\t";
        }
        // line 64
        echo "\t\t<cbc:GrossWeightMeasure unitCode=\"";
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "undPesoTotal", [], "any", false, false, false, 64);
        echo "\">";
        echo call_user_func_array($this->env->getFilter('n_format')->getCallable(), [twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "pesoTotal", [], "any", false, false, false, 64), 3]);
        echo "</cbc:GrossWeightMeasure>
        ";
        // line 65
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numBultos", [], "any", false, false, false, 65)) {
            // line 66
            echo "\t\t<cbc:TotalTransportHandlingUnitQuantity>";
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numBultos", [], "any", false, false, false, 66);
            echo "</cbc:TotalTransportHandlingUnitQuantity>
\t\t";
        }
        // line 68
        echo "\t\t<cbc:SplitConsignmentIndicator>";
        echo ((twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "indTransbordo", [], "any", false, false, false, 68)) ? ("true") : ("false"));
        echo "</cbc:SplitConsignmentIndicator>
\t\t<cac:ShipmentStage>
\t\t\t<cbc:TransportModeCode>";
        // line 70
        echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "modTraslado", [], "any", false, false, false, 70);
        echo "</cbc:TransportModeCode>
\t\t\t<cac:TransitPeriod>
\t\t\t\t<cbc:StartDate>";
        // line 72
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "fecTraslado", [], "any", false, false, false, 72), "Y-m-d");
        echo "</cbc:StartDate>
\t\t\t</cac:TransitPeriod>
            ";
        // line 74
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 74)) {
            // line 75
            echo "\t\t\t<cac:CarrierParty>
\t\t\t\t<cac:PartyIdentification>
\t\t\t\t\t<cbc:ID schemeID=\"";
            // line 77
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 77), "tipoDoc", [], "any", false, false, false, 77);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 77), "numDoc", [], "any", false, false, false, 77);
            echo "</cbc:ID>
\t\t\t\t</cac:PartyIdentification>
\t\t\t\t<cac:PartyName>
\t\t\t\t\t<cbc:Name><![CDATA[";
            // line 80
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 80), "rznSocial", [], "any", false, false, false, 80);
            echo "]]></cbc:Name>
\t\t\t\t</cac:PartyName>
\t\t\t</cac:CarrierParty>
\t\t\t<cac:TransportMeans>
\t\t\t\t<cac:RoadTransport>
\t\t\t\t\t<cbc:LicensePlateID>";
            // line 85
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 85), "placa", [], "any", false, false, false, 85);
            echo "</cbc:LicensePlateID>
\t\t\t\t</cac:RoadTransport>
\t\t\t</cac:TransportMeans>
\t\t\t<cac:DriverPerson>
\t\t\t\t<cbc:ID schemeID=\"";
            // line 89
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 89), "choferTipoDoc", [], "any", false, false, false, 89);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "transportista", [], "any", false, false, false, 89), "choferDoc", [], "any", false, false, false, 89);
            echo "</cbc:ID>
\t\t\t</cac:DriverPerson>
            ";
        }
        // line 92
        echo "\t\t</cac:ShipmentStage>
\t\t<cac:Delivery>
\t\t\t<cac:DeliveryAddress>
\t\t\t\t<cbc:ID>";
        // line 95
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "llegada", [], "any", false, false, false, 95), "ubigueo", [], "any", false, false, false, 95);
        echo "</cbc:ID>
\t\t\t\t<cbc:StreetName>";
        // line 96
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "llegada", [], "any", false, false, false, 96), "direccion", [], "any", false, false, false, 96);
        echo "</cbc:StreetName>
\t\t\t</cac:DeliveryAddress>
\t\t</cac:Delivery>
        ";
        // line 99
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numContenedor", [], "any", false, false, false, 99)) {
            // line 100
            echo "\t\t<cac:TransportHandlingUnit>
\t\t\t<cbc:ID>";
            // line 101
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "numContenedor", [], "any", false, false, false, 101);
            echo "</cbc:ID>
\t\t</cac:TransportHandlingUnit>
        ";
        }
        // line 104
        echo "\t\t<cac:OriginAddress>
\t\t\t<cbc:ID>";
        // line 105
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "partida", [], "any", false, false, false, 105), "ubigueo", [], "any", false, false, false, 105);
        echo "</cbc:ID>
\t\t\t<cbc:StreetName>";
        // line 106
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "partida", [], "any", false, false, false, 106), "direccion", [], "any", false, false, false, 106);
        echo "</cbc:StreetName>
\t\t</cac:OriginAddress>
        ";
        // line 108
        if (twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codPuerto", [], "any", false, false, false, 108)) {
            // line 109
            echo "\t\t<cac:FirstArrivalPortLocation>
\t\t\t<cbc:ID>";
            // line 110
            echo twig_get_attribute($this->env, $this->source, ($context["envio"] ?? null), "codPuerto", [], "any", false, false, false, 110);
            echo "</cbc:ID>
\t\t</cac:FirstArrivalPortLocation>
        ";
        }
        // line 113
        echo "\t</cac:Shipment>
    ";
        // line 114
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "details", [], "any", false, false, false, 114));
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
            // line 115
            echo "\t<cac:DespatchLine>
\t\t<cbc:ID>";
            // line 116
            echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 116);
            echo "</cbc:ID>
\t\t<cbc:DeliveredQuantity unitCode=\"";
            // line 117
            echo twig_get_attribute($this->env, $this->source, $context["det"], "unidad", [], "any", false, false, false, 117);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["det"], "cantidad", [], "any", false, false, false, 117);
            echo "</cbc:DeliveredQuantity>
\t\t<cac:OrderLineReference>
\t\t\t<cbc:LineID>";
            // line 119
            echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 119);
            echo "</cbc:LineID>
\t\t</cac:OrderLineReference>
\t\t<cac:Item>
\t\t\t<cbc:Name><![CDATA[";
            // line 122
            echo twig_get_attribute($this->env, $this->source, $context["det"], "descripcion", [], "any", false, false, false, 122);
            echo "]]></cbc:Name>
\t\t\t<cac:SellersItemIdentification>
\t\t\t\t<cbc:ID>";
            // line 124
            echo twig_get_attribute($this->env, $this->source, $context["det"], "codigo", [], "any", false, false, false, 124);
            echo "</cbc:ID>
\t\t\t</cac:SellersItemIdentification>
\t\t\t";
            // line 126
            if (twig_get_attribute($this->env, $this->source, $context["det"], "codProdSunat", [], "any", false, false, false, 126)) {
                // line 127
                echo "\t\t\t\t<cac:CommodityClassification>
\t\t\t\t\t<cbc:ItemClassificationCode listID=\"UNSPSC\" listAgencyName=\"GS1 US\" listName=\"Item Classification\">";
                // line 128
                echo twig_get_attribute($this->env, $this->source, $context["det"], "codProdSunat", [], "any", false, false, false, 128);
                echo "</cbc:ItemClassificationCode>
\t\t\t\t</cac:CommodityClassification>
\t\t\t";
            }
            // line 131
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
        // line 134
        echo "</DespatchAdvice>
";
        $___internal_fb1cef821e178bf7d8b2993ad35932349e70efe1520ea3aa2b28eb5af2eae1c7_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 1
        echo twig_spaceless($___internal_fb1cef821e178bf7d8b2993ad35932349e70efe1520ea3aa2b28eb5af2eae1c7_);
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
        return array (  381 => 1,  377 => 134,  361 => 131,  355 => 128,  352 => 127,  350 => 126,  345 => 124,  340 => 122,  334 => 119,  327 => 117,  323 => 116,  320 => 115,  303 => 114,  300 => 113,  294 => 110,  291 => 109,  289 => 108,  284 => 106,  280 => 105,  277 => 104,  271 => 101,  268 => 100,  266 => 99,  260 => 96,  256 => 95,  251 => 92,  243 => 89,  236 => 85,  228 => 80,  220 => 77,  216 => 75,  214 => 74,  209 => 72,  204 => 70,  198 => 68,  192 => 66,  190 => 65,  183 => 64,  177 => 62,  175 => 61,  171 => 60,  167 => 58,  164 => 57,  156 => 52,  148 => 49,  145 => 48,  143 => 47,  136 => 43,  128 => 40,  120 => 35,  114 => 32,  111 => 31,  108 => 30,  102 => 27,  98 => 26,  95 => 25,  92 => 24,  86 => 21,  82 => 20,  79 => 19,  76 => 18,  70 => 16,  68 => 15,  64 => 14,  60 => 13,  56 => 12,  50 => 11,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "despatch.xml.twig", "/var/www/html/factura_biker/sunat/vendor/greenter/xml/src/Xml/Templates/despatch.xml.twig");
    }
}
