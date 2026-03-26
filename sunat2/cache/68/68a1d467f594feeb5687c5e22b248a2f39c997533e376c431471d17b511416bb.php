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

/* invoice2.1.xml.twig */
class __TwigTemplate_fd3140a28bd7d9f9acc7a1a3de61cc342e0c30bd2cf797b448b6bd33d4e38802 extends Template
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
<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\" xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\" xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\" xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" xmlns:ext=\"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2\">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    ";
        // line 9
        $context["emp"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "company", [], "any", false, false, false, 9);
        // line 10
        echo "    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>";
        // line 12
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "serie", [], "any", false, false, false, 12);
        echo "-";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "correlativo", [], "any", false, false, false, 12);
        echo "</cbc:ID>
    <cbc:IssueDate>";
        // line 13
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fechaEmision", [], "any", false, false, false, 13), "Y-m-d");
        echo "</cbc:IssueDate>
    <cbc:IssueTime>";
        // line 14
        echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fechaEmision", [], "any", false, false, false, 14), "H:i:s");
        echo "</cbc:IssueTime>
    ";
        // line 15
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fecVencimiento", [], "any", false, false, false, 15)) {
            // line 16
            echo "    <cbc:DueDate>";
            echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fecVencimiento", [], "any", false, false, false, 16), "Y-m-d");
            echo "</cbc:DueDate>
    ";
        }
        // line 18
        echo "    <cbc:InvoiceTypeCode listID=\"";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoOperacion", [], "any", false, false, false, 18);
        echo "\">";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoDoc", [], "any", false, false, false, 18);
        echo "</cbc:InvoiceTypeCode>
    ";
        // line 19
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "legends", [], "any", false, false, false, 19));
        foreach ($context['_seq'] as $context["_key"] => $context["leg"]) {
            // line 20
            echo "    <cbc:Note languageLocaleID=\"";
            echo twig_get_attribute($this->env, $this->source, $context["leg"], "code", [], "any", false, false, false, 20);
            echo "\"><![CDATA[";
            echo twig_get_attribute($this->env, $this->source, $context["leg"], "value", [], "any", false, false, false, 20);
            echo "]]></cbc:Note>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['leg'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "observacion", [], "any", false, false, false, 22)) {
            // line 23
            echo "    <cbc:Note><![CDATA[";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "observacion", [], "any", false, false, false, 23);
            echo "]]></cbc:Note>
    ";
        }
        // line 25
        echo "    <cbc:DocumentCurrencyCode>";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 25);
        echo "</cbc:DocumentCurrencyCode>
    ";
        // line 26
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "compra", [], "any", false, false, false, 26)) {
            // line 27
            echo "    <cac:OrderReference>
        <cbc:ID>";
            // line 28
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "compra", [], "any", false, false, false, 28);
            echo "</cbc:ID>
    </cac:OrderReference>
    ";
        }
        // line 31
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "guias", [], "any", false, false, false, 31)) {
            // line 32
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "guias", [], "any", false, false, false, 32));
            foreach ($context['_seq'] as $context["_key"] => $context["guia"]) {
                // line 33
                echo "    <cac:DespatchDocumentReference>
        <cbc:ID>";
                // line 34
                echo twig_get_attribute($this->env, $this->source, $context["guia"], "nroDoc", [], "any", false, false, false, 34);
                echo "</cbc:ID>
        <cbc:DocumentTypeCode>";
                // line 35
                echo twig_get_attribute($this->env, $this->source, $context["guia"], "tipoDoc", [], "any", false, false, false, 35);
                echo "</cbc:DocumentTypeCode>
    </cac:DespatchDocumentReference>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['guia'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 38
            echo "    ";
        }
        // line 39
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "relDocs", [], "any", false, false, false, 39)) {
            // line 40
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "relDocs", [], "any", false, false, false, 40));
            foreach ($context['_seq'] as $context["_key"] => $context["rel"]) {
                // line 41
                echo "    <cac:AdditionalDocumentReference>
        <cbc:ID>";
                // line 42
                echo twig_get_attribute($this->env, $this->source, $context["rel"], "nroDoc", [], "any", false, false, false, 42);
                echo "</cbc:ID>
        <cbc:DocumentTypeCode>";
                // line 43
                echo twig_get_attribute($this->env, $this->source, $context["rel"], "tipoDoc", [], "any", false, false, false, 43);
                echo "</cbc:DocumentTypeCode>
    </cac:AdditionalDocumentReference>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['rel'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 46
            echo "    ";
        }
        // line 47
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "anticipos", [], "any", false, false, false, 47)) {
            // line 48
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "anticipos", [], "any", false, false, false, 48));
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
            foreach ($context['_seq'] as $context["_key"] => $context["ant"]) {
                // line 49
                echo "    <cac:AdditionalDocumentReference>
        <cbc:ID>";
                // line 50
                echo twig_get_attribute($this->env, $this->source, $context["ant"], "nroDocRel", [], "any", false, false, false, 50);
                echo "</cbc:ID>
        <cbc:DocumentTypeCode>";
                // line 51
                echo twig_get_attribute($this->env, $this->source, $context["ant"], "tipoDocRel", [], "any", false, false, false, 51);
                echo "</cbc:DocumentTypeCode>
        <cbc:DocumentStatusCode>";
                // line 52
                echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 52);
                echo "</cbc:DocumentStatusCode>
        <cac:IssuerParty>
            <cac:PartyIdentification>
                <cbc:ID schemeID=\"6\">";
                // line 55
                echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 55);
                echo "</cbc:ID>
            </cac:PartyIdentification>
        </cac:IssuerParty>
    </cac:AdditionalDocumentReference>
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ant'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 60
            echo "    ";
        }
        // line 61
        echo "    <cac:Signature>
        <cbc:ID>";
        // line 62
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 62);
        echo "</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>";
        // line 65
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 65);
        echo "</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[";
        // line 68
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "razonSocial", [], "any", false, false, false, 68);
        echo "]]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#GREENTER-SIGN</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID=\"6\">";
        // line 80
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "ruc", [], "any", false, false, false, 80);
        echo "</cbc:ID>
            </cac:PartyIdentification>
\t\t\t";
        // line 82
        if (twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "nombreComercial", [], "any", false, false, false, 82)) {
            // line 83
            echo "            <cac:PartyName>
                <cbc:Name><![CDATA[";
            // line 84
            echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "nombreComercial", [], "any", false, false, false, 84);
            echo "]]></cbc:Name>
            </cac:PartyName>
\t\t\t";
        }
        // line 87
        echo "            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[";
        // line 88
        echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "razonSocial", [], "any", false, false, false, 88);
        echo "]]></cbc:RegistrationName>
                ";
        // line 89
        $context["addr"] = twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "address", [], "any", false, false, false, 89);
        // line 90
        echo "                <cac:RegistrationAddress>
                    <cbc:ID>";
        // line 91
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 91);
        echo "</cbc:ID>
                    <cbc:AddressTypeCode>";
        // line 92
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "codLocal", [], "any", false, false, false, 92);
        echo "</cbc:AddressTypeCode>
                    ";
        // line 93
        if (twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "urbanizacion", [], "any", false, false, false, 93)) {
            // line 94
            echo "                    <cbc:CitySubdivisionName>";
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "urbanizacion", [], "any", false, false, false, 94);
            echo "</cbc:CitySubdivisionName>
                    ";
        }
        // line 96
        echo "                    <cbc:CityName>";
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "provincia", [], "any", false, false, false, 96);
        echo "</cbc:CityName>
                    <cbc:CountrySubentity>";
        // line 97
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "departamento", [], "any", false, false, false, 97);
        echo "</cbc:CountrySubentity>
                    <cbc:District>";
        // line 98
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "distrito", [], "any", false, false, false, 98);
        echo "</cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[";
        // line 100
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "direccion", [], "any", false, false, false, 100);
        echo "]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>";
        // line 103
        echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "codigoPais", [], "any", false, false, false, 103);
        echo "</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
            ";
        // line 107
        if ((twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "email", [], "any", false, false, false, 107) || twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "telephone", [], "any", false, false, false, 107))) {
            // line 108
            echo "            <cac:Contact>
                ";
            // line 109
            if (twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "telephone", [], "any", false, false, false, 109)) {
                // line 110
                echo "                <cbc:Telephone>";
                echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "telephone", [], "any", false, false, false, 110);
                echo "</cbc:Telephone>
                ";
            }
            // line 112
            echo "                ";
            if (twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "email", [], "any", false, false, false, 112)) {
                // line 113
                echo "                <cbc:ElectronicMail>";
                echo twig_get_attribute($this->env, $this->source, ($context["emp"] ?? null), "email", [], "any", false, false, false, 113);
                echo "</cbc:ElectronicMail>
                ";
            }
            // line 115
            echo "            </cac:Contact>
            ";
        }
        // line 117
        echo "        </cac:Party>
    </cac:AccountingSupplierParty>
    ";
        // line 119
        $context["client"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "client", [], "any", false, false, false, 119);
        // line 120
        echo "    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID=\"";
        // line 123
        echo twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "tipoDoc", [], "any", false, false, false, 123);
        echo "\">";
        echo twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "numDoc", [], "any", false, false, false, 123);
        echo "</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[";
        // line 126
        echo twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "rznSocial", [], "any", false, false, false, 126);
        echo "]]></cbc:RegistrationName>
                ";
        // line 127
        if (twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "address", [], "any", false, false, false, 127)) {
            // line 128
            echo "                ";
            $context["addr"] = twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "address", [], "any", false, false, false, 128);
            // line 129
            echo "                <cac:RegistrationAddress>
                    ";
            // line 130
            if (twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 130)) {
                // line 131
                echo "                    <cbc:ID>";
                echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 131);
                echo "</cbc:ID>
                    ";
            }
            // line 133
            echo "                    <cac:AddressLine>
                        <cbc:Line><![CDATA[";
            // line 134
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "direccion", [], "any", false, false, false, 134);
            echo "]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>";
            // line 137
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "codigoPais", [], "any", false, false, false, 137);
            echo "</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
                ";
        }
        // line 141
        echo "            </cac:PartyLegalEntity>
            ";
        // line 142
        if ((twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "email", [], "any", false, false, false, 142) || twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "telephone", [], "any", false, false, false, 142))) {
            // line 143
            echo "            <cac:Contact>
                ";
            // line 144
            if (twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "telephone", [], "any", false, false, false, 144)) {
                // line 145
                echo "                <cbc:Telephone>";
                echo twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "telephone", [], "any", false, false, false, 145);
                echo "</cbc:Telephone>
                ";
            }
            // line 147
            echo "                ";
            if (twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "email", [], "any", false, false, false, 147)) {
                // line 148
                echo "                <cbc:ElectronicMail>";
                echo twig_get_attribute($this->env, $this->source, ($context["client"] ?? null), "email", [], "any", false, false, false, 148);
                echo "</cbc:ElectronicMail>
                ";
            }
            // line 150
            echo "            </cac:Contact>
            ";
        }
        // line 152
        echo "        </cac:Party>
    </cac:AccountingCustomerParty>
    ";
        // line 154
        $context["seller"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "seller", [], "any", false, false, false, 154);
        // line 155
        echo "    ";
        if (($context["seller"] ?? null)) {
            // line 156
            echo "    <cac:SellerSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID=\"";
            // line 159
            echo twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "tipoDoc", [], "any", false, false, false, 159);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "numDoc", [], "any", false, false, false, 159);
            echo "</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[";
            // line 162
            echo twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "rznSocial", [], "any", false, false, false, 162);
            echo "]]></cbc:RegistrationName>
                ";
            // line 163
            if (twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "address", [], "any", false, false, false, 163)) {
                // line 164
                echo "                ";
                $context["addr"] = twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "address", [], "any", false, false, false, 164);
                // line 165
                echo "                <cac:RegistrationAddress>
                    ";
                // line 166
                if (twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 166)) {
                    // line 167
                    echo "                    <cbc:ID>";
                    echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 167);
                    echo "</cbc:ID>
                    ";
                }
                // line 169
                echo "                    <cac:AddressLine>
                        <cbc:Line><![CDATA[";
                // line 170
                echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "direccion", [], "any", false, false, false, 170);
                echo "]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode>";
                // line 173
                echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "codigoPais", [], "any", false, false, false, 173);
                echo "</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
                ";
            }
            // line 177
            echo "            </cac:PartyLegalEntity>
            ";
            // line 178
            if ((twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "email", [], "any", false, false, false, 178) || twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "telephone", [], "any", false, false, false, 178))) {
                // line 179
                echo "            <cac:Contact>
                ";
                // line 180
                if (twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "telephone", [], "any", false, false, false, 180)) {
                    // line 181
                    echo "                <cbc:Telephone>";
                    echo twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "telephone", [], "any", false, false, false, 181);
                    echo "</cbc:Telephone>
                ";
                }
                // line 183
                echo "                ";
                if (twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "email", [], "any", false, false, false, 183)) {
                    // line 184
                    echo "                <cbc:ElectronicMail>";
                    echo twig_get_attribute($this->env, $this->source, ($context["seller"] ?? null), "email", [], "any", false, false, false, 184);
                    echo "</cbc:ElectronicMail>
                ";
                }
                // line 186
                echo "            </cac:Contact>
            ";
            }
            // line 188
            echo "        </cac:Party>
    </cac:SellerSupplierParty>
    ";
        }
        // line 191
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "direccionEntrega", [], "any", false, false, false, 191)) {
            // line 192
            echo "        ";
            $context["addr"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "direccionEntrega", [], "any", false, false, false, 192);
            // line 193
            echo "        <cac:Delivery>
            <cac:DeliveryLocation>
                <cac:Address>
                    <cbc:ID>";
            // line 196
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "ubigueo", [], "any", false, false, false, 196);
            echo "</cbc:ID>
                    ";
            // line 197
            if (twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "urbanizacion", [], "any", false, false, false, 197)) {
                // line 198
                echo "                    <cbc:CitySubdivisionName>";
                echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "urbanizacion", [], "any", false, false, false, 198);
                echo "</cbc:CitySubdivisionName>
                    ";
            }
            // line 200
            echo "                    <cbc:CityName>";
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "provincia", [], "any", false, false, false, 200);
            echo "</cbc:CityName>
                    <cbc:CountrySubentity>";
            // line 201
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "departamento", [], "any", false, false, false, 201);
            echo "</cbc:CountrySubentity>
                    <cbc:District>";
            // line 202
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "distrito", [], "any", false, false, false, 202);
            echo "</cbc:District>
                    <cac:AddressLine>
                        <cbc:Line><![CDATA[";
            // line 204
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "direccion", [], "any", false, false, false, 204);
            echo "]]></cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode listID=\"ISO 3166-1\" listAgencyName=\"United Nations Economic Commission for Europe\" listName=\"Country\">";
            // line 207
            echo twig_get_attribute($this->env, $this->source, ($context["addr"] ?? null), "codigoPais", [], "any", false, false, false, 207);
            echo "</cbc:IdentificationCode>
                    </cac:Country>
                </cac:Address>
            </cac:DeliveryLocation>
        </cac:Delivery>
    ";
        }
        // line 213
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "detraccion", [], "any", false, false, false, 213)) {
            // line 214
            echo "    ";
            $context["detr"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "detraccion", [], "any", false, false, false, 214);
            // line 215
            echo "    <cac:PaymentMeans>
        <cbc:ID>Detraccion</cbc:ID>
        <cbc:PaymentMeansCode>";
            // line 217
            echo twig_get_attribute($this->env, $this->source, ($context["detr"] ?? null), "codMedioPago", [], "any", false, false, false, 217);
            echo "</cbc:PaymentMeansCode>
        <cac:PayeeFinancialAccount>
            <cbc:ID>";
            // line 219
            echo twig_get_attribute($this->env, $this->source, ($context["detr"] ?? null), "ctaBanco", [], "any", false, false, false, 219);
            echo "</cbc:ID>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>
    <cac:PaymentTerms>
        <cbc:ID>Detraccion</cbc:ID>
        <cbc:PaymentMeansID>";
            // line 224
            echo twig_get_attribute($this->env, $this->source, ($context["detr"] ?? null), "codBienDetraccion", [], "any", false, false, false, 224);
            echo "</cbc:PaymentMeansID>
        <cbc:PaymentPercent>";
            // line 225
            echo twig_get_attribute($this->env, $this->source, ($context["detr"] ?? null), "percent", [], "any", false, false, false, 225);
            echo "</cbc:PaymentPercent>
        <cbc:Amount currencyID=\"PEN\">";
            // line 226
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["detr"] ?? null), "mount", [], "any", false, false, false, 226));
            echo "</cbc:Amount>
    </cac:PaymentTerms>
    ";
        }
        // line 229
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "perception", [], "any", false, false, false, 229)) {
            // line 230
            echo "    <cac:PaymentTerms>
        <cbc:ID>Percepcion</cbc:ID>
        <cbc:Amount currencyID=\"PEN\">";
            // line 232
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "perception", [], "any", false, false, false, 232), "mtoTotal", [], "any", false, false, false, 232));
            echo "</cbc:Amount>
    </cac:PaymentTerms>
    ";
        }
        // line 235
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, false, false, 235)) {
            // line 236
            echo "    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>";
            // line 238
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, false, false, 238), "tipo", [], "any", false, false, false, 238);
            echo "</cbc:PaymentMeansID>
        ";
            // line 239
            if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, false, false, 239), "monto", [], "any", false, false, false, 239)) {
                // line 240
                echo "        <cbc:Amount currencyID=\"";
                echo ((twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, true, false, 240), "moneda", [], "any", true, true, false, 240)) ? (_twig_default_filter(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, true, false, 240), "moneda", [], "any", false, false, false, 240), twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 240))) : (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 240)));
                echo "\">";
                echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "formaPago", [], "any", false, false, false, 240), "monto", [], "any", false, false, false, 240);
                echo "</cbc:Amount>
        ";
            }
            // line 242
            echo "    </cac:PaymentTerms>
    ";
        }
        // line 244
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "cuotas", [], "any", false, false, false, 244)) {
            // line 245
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "cuotas", [], "any", false, false, false, 245));
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
            foreach ($context['_seq'] as $context["_key"] => $context["cuota"]) {
                // line 246
                echo "    <cac:PaymentTerms>
        <cbc:ID>FormaPago</cbc:ID>
        <cbc:PaymentMeansID>Cuota";
                // line 248
                echo twig_sprintf("%03d", twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 248));
                echo "</cbc:PaymentMeansID>
        <cbc:Amount currencyID=\"";
                // line 249
                echo ((twig_get_attribute($this->env, $this->source, $context["cuota"], "moneda", [], "any", true, true, false, 249)) ? (_twig_default_filter(twig_get_attribute($this->env, $this->source, $context["cuota"], "moneda", [], "any", false, false, false, 249), twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 249))) : (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 249)));
                echo "\">";
                echo twig_get_attribute($this->env, $this->source, $context["cuota"], "monto", [], "any", false, false, false, 249);
                echo "</cbc:Amount>
        <cbc:PaymentDueDate>";
                // line 250
                echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["cuota"], "fechaPago", [], "any", false, false, false, 250), "Y-m-d");
                echo "</cbc:PaymentDueDate>
    </cac:PaymentTerms>
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['cuota'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 253
            echo "    ";
        }
        // line 254
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "anticipos", [], "any", false, false, false, 254)) {
            // line 255
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "anticipos", [], "any", false, false, false, 255));
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
            foreach ($context['_seq'] as $context["_key"] => $context["ant"]) {
                // line 256
                echo "    <cac:PrepaidPayment>
        <cbc:ID>";
                // line 257
                echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 257);
                echo "</cbc:ID>
        <cbc:PaidAmount currencyID=\"";
                // line 258
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 258);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["ant"], "total", [], "any", false, false, false, 258));
                echo "</cbc:PaidAmount>
    </cac:PrepaidPayment>
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['ant'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 261
            echo "    ";
        }
        // line 262
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "cargos", [], "any", false, false, false, 262)) {
            // line 263
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "cargos", [], "any", false, false, false, 263));
            foreach ($context['_seq'] as $context["_key"] => $context["cargo"]) {
                // line 264
                echo "    <cac:AllowanceCharge>
        <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>";
                // line 266
                echo twig_get_attribute($this->env, $this->source, $context["cargo"], "codTipo", [], "any", false, false, false, 266);
                echo "</cbc:AllowanceChargeReasonCode>
        <cbc:MultiplierFactorNumeric>";
                // line 267
                echo twig_get_attribute($this->env, $this->source, $context["cargo"], "factor", [], "any", false, false, false, 267);
                echo "</cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID=\"";
                // line 268
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 268);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["cargo"], "monto", [], "any", false, false, false, 268));
                echo "</cbc:Amount>
        <cbc:BaseAmount currencyID=\"";
                // line 269
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 269);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["cargo"], "montoBase", [], "any", false, false, false, 269));
                echo "</cbc:BaseAmount>
    </cac:AllowanceCharge>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['cargo'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 272
            echo "    ";
        }
        // line 273
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "descuentos", [], "any", false, false, false, 273)) {
            // line 274
            echo "    ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "descuentos", [], "any", false, false, false, 274));
            foreach ($context['_seq'] as $context["_key"] => $context["desc"]) {
                // line 275
                echo "    <cac:AllowanceCharge>
        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>";
                // line 277
                echo twig_get_attribute($this->env, $this->source, $context["desc"], "codTipo", [], "any", false, false, false, 277);
                echo "</cbc:AllowanceChargeReasonCode>
        ";
                // line 278
                if ( !(null === twig_get_attribute($this->env, $this->source, $context["desc"], "factor", [], "any", false, false, false, 278))) {
                    // line 279
                    echo "        <cbc:MultiplierFactorNumeric>";
                    echo twig_get_attribute($this->env, $this->source, $context["desc"], "factor", [], "any", false, false, false, 279);
                    echo "</cbc:MultiplierFactorNumeric>
        ";
                }
                // line 281
                echo "        <cbc:Amount currencyID=\"";
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 281);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["desc"], "monto", [], "any", false, false, false, 281));
                echo "</cbc:Amount>
        <cbc:BaseAmount currencyID=\"";
                // line 282
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 282);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["desc"], "montoBase", [], "any", false, false, false, 282));
                echo "</cbc:BaseAmount>
    </cac:AllowanceCharge>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['desc'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 285
            echo "    ";
        }
        // line 286
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "perception", [], "any", false, false, false, 286)) {
            // line 287
            echo "    ";
            $context["perc"] = twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "perception", [], "any", false, false, false, 287);
            // line 288
            echo "    <cac:AllowanceCharge>
        <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>";
            // line 290
            echo twig_get_attribute($this->env, $this->source, ($context["perc"] ?? null), "codReg", [], "any", false, false, false, 290);
            echo "</cbc:AllowanceChargeReasonCode>
        <cbc:MultiplierFactorNumeric>";
            // line 291
            echo twig_get_attribute($this->env, $this->source, ($context["perc"] ?? null), "porcentaje", [], "any", false, false, false, 291);
            echo "</cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID=\"PEN\">";
            // line 292
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["perc"] ?? null), "mto", [], "any", false, false, false, 292));
            echo "</cbc:Amount>
        <cbc:BaseAmount currencyID=\"PEN\">";
            // line 293
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["perc"] ?? null), "mtoBase", [], "any", false, false, false, 293));
            echo "</cbc:BaseAmount>
    </cac:AllowanceCharge>
    ";
        }
        // line 296
        echo "    <cac:TaxTotal>
        <cbc:TaxAmount currencyID=\"";
        // line 297
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 297);
        echo "\">";
        echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "totalImpuestos", [], "any", false, false, false, 297));
        echo "</cbc:TaxAmount>
        ";
        // line 298
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoISC", [], "any", false, false, false, 298)) {
            // line 299
            echo "        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID=\"";
            // line 300
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 300);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoBaseIsc", [], "any", false, false, false, 300));
            echo "</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID=\"";
            // line 301
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 301);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoISC", [], "any", false, false, false, 301));
            echo "</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>2000</cbc:ID>
                    <cbc:Name>ISC</cbc:Name>
                    <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        ";
        }
        // line 311
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperGravadas", [], "any", false, false, false, 311)) {
            // line 312
            echo "        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID=\"";
            // line 313
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 313);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperGravadas", [], "any", false, false, false, 313));
            echo "</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID=\"";
            // line 314
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 314);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoIGV", [], "any", false, false, false, 314));
            echo "</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        ";
        }
        // line 324
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperInafectas", [], "any", false, false, false, 324)) {
            // line 325
            echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
            // line 326
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 326);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperInafectas", [], "any", false, false, false, 326));
            echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
            // line 327
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 327);
            echo "\">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9998</cbc:ID>
                        <cbc:Name>INA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        ";
        }
        // line 337
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperExoneradas", [], "any", false, false, false, 337)) {
            // line 338
            echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
            // line 339
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 339);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperExoneradas", [], "any", false, false, false, 339));
            echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
            // line 340
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 340);
            echo "\">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9997</cbc:ID>
                        <cbc:Name>EXO</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        ";
        }
        // line 350
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperGratuitas", [], "any", false, false, false, 350)) {
            // line 351
            echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
            // line 352
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 352);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperGratuitas", [], "any", false, false, false, 352));
            echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
            // line 353
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 353);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoIGVGratuitas", [], "any", false, false, false, 353));
            echo "</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9996</cbc:ID>
                        <cbc:Name>GRA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        ";
        }
        // line 363
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperExportacion", [], "any", false, false, false, 363)) {
            // line 364
            echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
            // line 365
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 365);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOperExportacion", [], "any", false, false, false, 365));
            echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
            // line 366
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 366);
            echo "\">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9995</cbc:ID>
                        <cbc:Name>EXP</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        ";
        }
        // line 376
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoIvap", [], "any", false, false, false, 376)) {
            // line 377
            echo "        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID=\"";
            // line 378
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 378);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoBaseIvap", [], "any", false, false, false, 378));
            echo "</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID=\"";
            // line 379
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 379);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoIvap", [], "any", false, false, false, 379));
            echo "</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>1016</cbc:ID>
                    <cbc:Name>IVAP</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        ";
        }
        // line 389
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOtrosTributos", [], "any", false, false, false, 389)) {
            // line 390
            echo "        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID=\"";
            // line 391
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 391);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoBaseOth", [], "any", false, false, false, 391));
            echo "</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID=\"";
            // line 392
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 392);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoOtrosTributos", [], "any", false, false, false, 392));
            echo "</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID>9999</cbc:ID>
                    <cbc:Name>OTROS</cbc:Name>
                    <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        ";
        }
        // line 402
        echo "        ";
        if (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "icbper", [], "any", false, false, false, 402)) {
            // line 403
            echo "            <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID=\"";
            // line 404
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 404);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "icbper", [], "any", false, false, false, 404));
            echo "</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>7152</cbc:ID>
                        <cbc:Name>ICBPER</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        ";
        }
        // line 414
        echo "    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID=\"";
        // line 416
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 416);
        echo "\">";
        echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "valorVenta", [], "any", false, false, false, 416));
        echo "</cbc:LineExtensionAmount>
        ";
        // line 417
        if ( !(null === twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "subTotal", [], "any", false, false, false, 417))) {
            // line 418
            echo "        <cbc:TaxInclusiveAmount currencyID=\"";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 418);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "subTotal", [], "any", false, false, false, 418));
            echo "</cbc:TaxInclusiveAmount>
        ";
        }
        // line 420
        echo "        ";
        if ( !(null === twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "sumOtrosDescuentos", [], "any", false, false, false, 420))) {
            // line 421
            echo "        <cbc:AllowanceTotalAmount currencyID=\"";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 421);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "sumOtrosDescuentos", [], "any", false, false, false, 421));
            echo "</cbc:AllowanceTotalAmount>
        ";
        }
        // line 423
        echo "        ";
        if ( !(null === twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "sumOtrosCargos", [], "any", false, false, false, 423))) {
            // line 424
            echo "        <cbc:ChargeTotalAmount currencyID=\"";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 424);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "sumOtrosCargos", [], "any", false, false, false, 424));
            echo "</cbc:ChargeTotalAmount>
        ";
        }
        // line 426
        echo "        ";
        if ( !(null === twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "totalAnticipos", [], "any", false, false, false, 426))) {
            // line 427
            echo "        <cbc:PrepaidAmount currencyID=\"";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 427);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "totalAnticipos", [], "any", false, false, false, 427));
            echo "</cbc:PrepaidAmount>
        ";
        }
        // line 429
        echo "        ";
        if ( !(null === twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "redondeo", [], "any", false, false, false, 429))) {
            // line 430
            echo "        <cbc:PayableRoundingAmount currencyID=\"";
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 430);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "redondeo", [], "any", false, false, false, 430));
            echo "</cbc:PayableRoundingAmount>
        ";
        }
        // line 432
        echo "        <cbc:PayableAmount currencyID=\"";
        echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 432);
        echo "\">";
        echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "mtoImpVenta", [], "any", false, false, false, 432));
        echo "</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    ";
        // line 434
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "details", [], "any", false, false, false, 434));
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
        foreach ($context['_seq'] as $context["_key"] => $context["detail"]) {
            // line 435
            echo "    <cac:InvoiceLine>
        <cbc:ID>";
            // line 436
            echo twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 436);
            echo "</cbc:ID>
        <cbc:InvoicedQuantity unitCode=\"";
            // line 437
            echo twig_get_attribute($this->env, $this->source, $context["detail"], "unidad", [], "any", false, false, false, 437);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["detail"], "cantidad", [], "any", false, false, false, 437);
            echo "</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID=\"";
            // line 438
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 438);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoValorVenta", [], "any", false, false, false, 438));
            echo "</cbc:LineExtensionAmount>
        <cac:PricingReference>
            ";
            // line 440
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "mtoValorGratuito", [], "any", false, false, false, 440)) {
                // line 441
                echo "            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID=\"";
                // line 442
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 442);
                echo "\">";
                echo $this->env->getFilter('n_format_limit')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoValorGratuito", [], "any", false, false, false, 442), 10);
                echo "</cbc:PriceAmount>
                <cbc:PriceTypeCode>02</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
            ";
            } else {
                // line 446
                echo "            <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID=\"";
                // line 447
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 447);
                echo "\">";
                echo $this->env->getFilter('n_format_limit')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoPrecioUnitario", [], "any", false, false, false, 447), 10);
                echo "</cbc:PriceAmount>
                <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
            ";
            }
            // line 451
            echo "        </cac:PricingReference>
        ";
            // line 452
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "cargos", [], "any", false, false, false, 452)) {
                // line 453
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["detail"], "cargos", [], "any", false, false, false, 453));
                foreach ($context['_seq'] as $context["_key"] => $context["cargo"]) {
                    // line 454
                    echo "        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>";
                    // line 456
                    echo twig_get_attribute($this->env, $this->source, $context["cargo"], "codTipo", [], "any", false, false, false, 456);
                    echo "</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>";
                    // line 457
                    echo twig_get_attribute($this->env, $this->source, $context["cargo"], "factor", [], "any", false, false, false, 457);
                    echo "</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID=\"";
                    // line 458
                    echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 458);
                    echo "\">";
                    echo twig_get_attribute($this->env, $this->source, $context["cargo"], "monto", [], "any", false, false, false, 458);
                    echo "</cbc:Amount>
            <cbc:BaseAmount currencyID=\"";
                    // line 459
                    echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 459);
                    echo "\">";
                    echo twig_get_attribute($this->env, $this->source, $context["cargo"], "montoBase", [], "any", false, false, false, 459);
                    echo "</cbc:BaseAmount>
        </cac:AllowanceCharge>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['cargo'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 462
                echo "        ";
            }
            // line 463
            echo "        ";
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "descuentos", [], "any", false, false, false, 463)) {
                // line 464
                echo "        ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["detail"], "descuentos", [], "any", false, false, false, 464));
                foreach ($context['_seq'] as $context["_key"] => $context["desc"]) {
                    // line 465
                    echo "        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>";
                    // line 467
                    echo twig_get_attribute($this->env, $this->source, $context["desc"], "codTipo", [], "any", false, false, false, 467);
                    echo "</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>";
                    // line 468
                    echo twig_get_attribute($this->env, $this->source, $context["desc"], "factor", [], "any", false, false, false, 468);
                    echo "</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID=\"";
                    // line 469
                    echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 469);
                    echo "\">";
                    echo twig_get_attribute($this->env, $this->source, $context["desc"], "monto", [], "any", false, false, false, 469);
                    echo "</cbc:Amount>
            <cbc:BaseAmount currencyID=\"";
                    // line 470
                    echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 470);
                    echo "\">";
                    echo twig_get_attribute($this->env, $this->source, $context["desc"], "montoBase", [], "any", false, false, false, 470);
                    echo "</cbc:BaseAmount>
        </cac:AllowanceCharge>
        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['desc'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 473
                echo "        ";
            }
            // line 474
            echo "        <cac:TaxTotal>
            <cbc:TaxAmount currencyID=\"";
            // line 475
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 475);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "totalImpuestos", [], "any", false, false, false, 475));
            echo "</cbc:TaxAmount>
            ";
            // line 476
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "isc", [], "any", false, false, false, 476)) {
                // line 477
                echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
                // line 478
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 478);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoBaseIsc", [], "any", false, false, false, 478));
                echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
                // line 479
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 479);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "isc", [], "any", false, false, false, 479));
                echo "</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>";
                // line 481
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "porcentajeIsc", [], "any", false, false, false, 481);
                echo "</cbc:Percent>
                    <cbc:TierRange>";
                // line 482
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "tipSisIsc", [], "any", false, false, false, 482);
                echo "</cbc:TierRange>
                    <cac:TaxScheme>
                        <cbc:ID>2000</cbc:ID>
                        <cbc:Name>ISC</cbc:Name>
                        <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            ";
            }
            // line 491
            echo "            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID=\"";
            // line 492
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 492);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoBaseIgv", [], "any", false, false, false, 492));
            echo "</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID=\"";
            // line 493
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 493);
            echo "\">";
            echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "igv", [], "any", false, false, false, 493));
            echo "</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>";
            // line 495
            echo twig_get_attribute($this->env, $this->source, $context["detail"], "porcentajeIgv", [], "any", false, false, false, 495);
            echo "</cbc:Percent>
                    <cbc:TaxExemptionReasonCode>";
            // line 496
            echo twig_get_attribute($this->env, $this->source, $context["detail"], "tipAfeIgv", [], "any", false, false, false, 496);
            echo "</cbc:TaxExemptionReasonCode>
                    ";
            // line 497
            $context["afect"] = Greenter\Xml\Filter\TributoFunction::getByAfectacion(twig_get_attribute($this->env, $this->source, $context["detail"], "tipAfeIgv", [], "any", false, false, false, 497));
            // line 498
            echo "                    <cac:TaxScheme>
                        <cbc:ID>";
            // line 499
            echo twig_get_attribute($this->env, $this->source, ($context["afect"] ?? null), "id", [], "any", false, false, false, 499);
            echo "</cbc:ID>
                        <cbc:Name>";
            // line 500
            echo twig_get_attribute($this->env, $this->source, ($context["afect"] ?? null), "name", [], "any", false, false, false, 500);
            echo "</cbc:Name>
                        <cbc:TaxTypeCode>";
            // line 501
            echo twig_get_attribute($this->env, $this->source, ($context["afect"] ?? null), "code", [], "any", false, false, false, 501);
            echo "</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            ";
            // line 505
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "otroTributo", [], "any", false, false, false, 505)) {
                // line 506
                echo "                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID=\"";
                // line 507
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 507);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoBaseOth", [], "any", false, false, false, 507));
                echo "</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID=\"";
                // line 508
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 508);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "otroTributo", [], "any", false, false, false, 508));
                echo "</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>";
                // line 510
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "porcentajeOth", [], "any", false, false, false, 510);
                echo "</cbc:Percent>
                        <cac:TaxScheme>
                            <cbc:ID>9999</cbc:ID>
                            <cbc:Name>OTROS</cbc:Name>
                            <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            ";
            }
            // line 519
            echo "            ";
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "icbper", [], "any", false, false, false, 519)) {
                // line 520
                echo "            <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID=\"";
                // line 521
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 521);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "icbper", [], "any", false, false, false, 521));
                echo "</cbc:TaxAmount>
                <cbc:BaseUnitMeasure unitCode=\"NIU\">";
                // line 522
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "cantidad", [], "any", false, false, false, 522);
                echo "</cbc:BaseUnitMeasure>
                <cac:TaxCategory>
                    <cbc:PerUnitAmount currencyID=\"";
                // line 524
                echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 524);
                echo "\">";
                echo $this->env->getFilter('n_format')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "factorIcbper", [], "any", false, false, false, 524));
                echo "</cbc:PerUnitAmount>
                    <cac:TaxScheme>
                        <cbc:ID>7152</cbc:ID>
                        <cbc:Name>ICBPER</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
            ";
            }
            // line 533
            echo "        </cac:TaxTotal>
        <cac:Item>
            <cbc:Description><![CDATA[";
            // line 535
            echo twig_get_attribute($this->env, $this->source, $context["detail"], "descripcion", [], "any", false, false, false, 535);
            echo "]]></cbc:Description>
            ";
            // line 536
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "codProducto", [], "any", false, false, false, 536)) {
                // line 537
                echo "            <cac:SellersItemIdentification>
                <cbc:ID>";
                // line 538
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "codProducto", [], "any", false, false, false, 538);
                echo "</cbc:ID>
            </cac:SellersItemIdentification>
            ";
            }
            // line 541
            echo "            ";
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "codProdGS1", [], "any", false, false, false, 541)) {
                // line 542
                echo "            <cac:StandardItemIdentification>
                <cbc:ID>";
                // line 543
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "codProdGS1", [], "any", false, false, false, 543);
                echo "</cbc:ID>
            </cac:StandardItemIdentification>
            ";
            }
            // line 546
            echo "            ";
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "codProdSunat", [], "any", false, false, false, 546)) {
                // line 547
                echo "            <cac:CommodityClassification>
                <cbc:ItemClassificationCode>";
                // line 548
                echo twig_get_attribute($this->env, $this->source, $context["detail"], "codProdSunat", [], "any", false, false, false, 548);
                echo "</cbc:ItemClassificationCode>
            </cac:CommodityClassification>
            ";
            }
            // line 551
            echo "            ";
            if (twig_get_attribute($this->env, $this->source, $context["detail"], "atributos", [], "any", false, false, false, 551)) {
                // line 552
                echo "                ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["detail"], "atributos", [], "any", false, false, false, 552));
                foreach ($context['_seq'] as $context["_key"] => $context["atr"]) {
                    // line 553
                    echo "                    <cac:AdditionalItemProperty >
                        <cbc:Name>";
                    // line 554
                    echo twig_get_attribute($this->env, $this->source, $context["atr"], "name", [], "any", false, false, false, 554);
                    echo "</cbc:Name>
                        <cbc:NameCode>";
                    // line 555
                    echo twig_get_attribute($this->env, $this->source, $context["atr"], "code", [], "any", false, false, false, 555);
                    echo "</cbc:NameCode>
                        ";
                    // line 556
                    if (twig_get_attribute($this->env, $this->source, $context["atr"], "value", [], "any", false, false, false, 556)) {
                        // line 557
                        echo "                        <cbc:Value>";
                        echo twig_get_attribute($this->env, $this->source, $context["atr"], "value", [], "any", false, false, false, 557);
                        echo "</cbc:Value>
                        ";
                    }
                    // line 559
                    echo "                        ";
                    if (((twig_get_attribute($this->env, $this->source, $context["atr"], "fecInicio", [], "any", false, false, false, 559) || twig_get_attribute($this->env, $this->source, $context["atr"], "fecFin", [], "any", false, false, false, 559)) || twig_get_attribute($this->env, $this->source, $context["atr"], "duracion", [], "any", false, false, false, 559))) {
                        // line 560
                        echo "                            <cac:UsabilityPeriod>
                                ";
                        // line 561
                        if (twig_get_attribute($this->env, $this->source, $context["atr"], "fecInicio", [], "any", false, false, false, 561)) {
                            // line 562
                            echo "                                <cbc:StartDate>";
                            echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["atr"], "fecInicio", [], "any", false, false, false, 562), "Y-m-d");
                            echo "</cbc:StartDate>
                                ";
                        }
                        // line 564
                        echo "                                ";
                        if (twig_get_attribute($this->env, $this->source, $context["atr"], "fecFin", [], "any", false, false, false, 564)) {
                            // line 565
                            echo "                                <cbc:EndDate>";
                            echo twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, $context["atr"], "fecFin", [], "any", false, false, false, 565), "Y-m-d");
                            echo "</cbc:EndDate>
                                ";
                        }
                        // line 567
                        echo "                                ";
                        if (twig_get_attribute($this->env, $this->source, $context["atr"], "duracion", [], "any", false, false, false, 567)) {
                            // line 568
                            echo "                                <cbc:DurationMeasure unitCode=\"DAY\">";
                            echo twig_get_attribute($this->env, $this->source, $context["atr"], "duracion", [], "any", false, false, false, 568);
                            echo "</cbc:DurationMeasure>
                                ";
                        }
                        // line 570
                        echo "                            </cac:UsabilityPeriod>
                        ";
                    }
                    // line 572
                    echo "                    </cac:AdditionalItemProperty>
                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['atr'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 574
                echo "            ";
            }
            // line 575
            echo "        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID=\"";
            // line 577
            echo twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "tipoMoneda", [], "any", false, false, false, 577);
            echo "\">";
            echo $this->env->getFilter('n_format_limit')->getCallable()(twig_get_attribute($this->env, $this->source, $context["detail"], "mtoValorUnitario", [], "any", false, false, false, 577), 10);
            echo "</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
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
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['detail'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 581
        echo "</Invoice>
";
        $___internal_parse_0_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 1
        echo twig_spaceless($___internal_parse_0_);
    }

    public function getTemplateName()
    {
        return "invoice2.1.xml.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  1633 => 1,  1629 => 581,  1609 => 577,  1605 => 575,  1602 => 574,  1595 => 572,  1591 => 570,  1585 => 568,  1582 => 567,  1576 => 565,  1573 => 564,  1567 => 562,  1565 => 561,  1562 => 560,  1559 => 559,  1553 => 557,  1551 => 556,  1547 => 555,  1543 => 554,  1540 => 553,  1535 => 552,  1532 => 551,  1526 => 548,  1523 => 547,  1520 => 546,  1514 => 543,  1511 => 542,  1508 => 541,  1502 => 538,  1499 => 537,  1497 => 536,  1493 => 535,  1489 => 533,  1475 => 524,  1470 => 522,  1464 => 521,  1461 => 520,  1458 => 519,  1446 => 510,  1439 => 508,  1433 => 507,  1430 => 506,  1428 => 505,  1421 => 501,  1417 => 500,  1413 => 499,  1410 => 498,  1408 => 497,  1404 => 496,  1400 => 495,  1393 => 493,  1387 => 492,  1384 => 491,  1372 => 482,  1368 => 481,  1361 => 479,  1355 => 478,  1352 => 477,  1350 => 476,  1344 => 475,  1341 => 474,  1338 => 473,  1327 => 470,  1321 => 469,  1317 => 468,  1313 => 467,  1309 => 465,  1304 => 464,  1301 => 463,  1298 => 462,  1287 => 459,  1281 => 458,  1277 => 457,  1273 => 456,  1269 => 454,  1264 => 453,  1262 => 452,  1259 => 451,  1250 => 447,  1247 => 446,  1238 => 442,  1235 => 441,  1233 => 440,  1226 => 438,  1220 => 437,  1216 => 436,  1213 => 435,  1196 => 434,  1188 => 432,  1180 => 430,  1177 => 429,  1169 => 427,  1166 => 426,  1158 => 424,  1155 => 423,  1147 => 421,  1144 => 420,  1136 => 418,  1134 => 417,  1128 => 416,  1124 => 414,  1109 => 404,  1106 => 403,  1103 => 402,  1088 => 392,  1082 => 391,  1079 => 390,  1076 => 389,  1061 => 379,  1055 => 378,  1052 => 377,  1049 => 376,  1036 => 366,  1030 => 365,  1027 => 364,  1024 => 363,  1009 => 353,  1003 => 352,  1000 => 351,  997 => 350,  984 => 340,  978 => 339,  975 => 338,  972 => 337,  959 => 327,  953 => 326,  950 => 325,  947 => 324,  932 => 314,  926 => 313,  923 => 312,  920 => 311,  905 => 301,  899 => 300,  896 => 299,  894 => 298,  888 => 297,  885 => 296,  879 => 293,  875 => 292,  871 => 291,  867 => 290,  863 => 288,  860 => 287,  857 => 286,  854 => 285,  843 => 282,  836 => 281,  830 => 279,  828 => 278,  824 => 277,  820 => 275,  815 => 274,  812 => 273,  809 => 272,  798 => 269,  792 => 268,  788 => 267,  784 => 266,  780 => 264,  775 => 263,  772 => 262,  769 => 261,  750 => 258,  746 => 257,  743 => 256,  725 => 255,  722 => 254,  719 => 253,  702 => 250,  696 => 249,  692 => 248,  688 => 246,  670 => 245,  667 => 244,  663 => 242,  655 => 240,  653 => 239,  649 => 238,  645 => 236,  642 => 235,  636 => 232,  632 => 230,  629 => 229,  623 => 226,  619 => 225,  615 => 224,  607 => 219,  602 => 217,  598 => 215,  595 => 214,  592 => 213,  583 => 207,  577 => 204,  572 => 202,  568 => 201,  563 => 200,  557 => 198,  555 => 197,  551 => 196,  546 => 193,  543 => 192,  540 => 191,  535 => 188,  531 => 186,  525 => 184,  522 => 183,  516 => 181,  514 => 180,  511 => 179,  509 => 178,  506 => 177,  499 => 173,  493 => 170,  490 => 169,  484 => 167,  482 => 166,  479 => 165,  476 => 164,  474 => 163,  470 => 162,  462 => 159,  457 => 156,  454 => 155,  452 => 154,  448 => 152,  444 => 150,  438 => 148,  435 => 147,  429 => 145,  427 => 144,  424 => 143,  422 => 142,  419 => 141,  412 => 137,  406 => 134,  403 => 133,  397 => 131,  395 => 130,  392 => 129,  389 => 128,  387 => 127,  383 => 126,  375 => 123,  370 => 120,  368 => 119,  364 => 117,  360 => 115,  354 => 113,  351 => 112,  345 => 110,  343 => 109,  340 => 108,  338 => 107,  331 => 103,  325 => 100,  320 => 98,  316 => 97,  311 => 96,  305 => 94,  303 => 93,  299 => 92,  295 => 91,  292 => 90,  290 => 89,  286 => 88,  283 => 87,  277 => 84,  274 => 83,  272 => 82,  267 => 80,  252 => 68,  246 => 65,  240 => 62,  237 => 61,  234 => 60,  215 => 55,  209 => 52,  205 => 51,  201 => 50,  198 => 49,  180 => 48,  177 => 47,  174 => 46,  165 => 43,  161 => 42,  158 => 41,  153 => 40,  150 => 39,  147 => 38,  138 => 35,  134 => 34,  131 => 33,  126 => 32,  123 => 31,  117 => 28,  114 => 27,  112 => 26,  107 => 25,  101 => 23,  98 => 22,  87 => 20,  83 => 19,  76 => 18,  70 => 16,  68 => 15,  64 => 14,  60 => 13,  54 => 12,  50 => 10,  48 => 9,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "invoice2.1.xml.twig", "/var/www/html/factura_biker/sunat2/vendor/greenter/xml/src/Xml/Templates/invoice2.1.xml.twig");
    }
}
