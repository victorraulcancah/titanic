<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>
<body>

<button onclick="consultaNC()">Enviar</button>

<textarea id="msgc"></textarea>
</body>
<script>

    /*function consulta(){
        const data={
            endpoints:"beta",//production
            certGlobal:false,
            peso:5.8,
            ubigeo:'021809',
            direccion:'jilon juh h2 -85',
            serie:"T001",
            numero:"778",
            fecha:"2021-11-05",
            venta:{
                serie:"F001",
                numero:"777",
            },
            cliente:{
                doc_num:"70667181",
                nom_RS:"samuel bruno alva"
            },
            empresa:{
                ruc:"20123456789",
                razon_social:"samuel florentino jose bruno alva",
                direccion:"",
                ubigeo:"021809",
                distrito:"NUEVO CHIMBOTE",
                provincia:"SANTA",
                departamento:"ANCASH",
                clave_sol:"moddatos",
                usuario_sol:"MODDATOS"
            },
            transporte:{
                ruc:"10706671817",
                razon_social:"samuel jose",
                placa:'KIJL-2514',
                doc_chofer:'12548653'
            },
            productos:[
                {
                    cantidad:1,
                    cod_pro:"5",
                    cod_sunat:"002",
                    descripcion:"jabon"
                }
            ]

        }
        $.ajax({
            type: "POST",
            url: "guiaremision.php",
            data: data,
            success: function(resp){
                console.log(JSON.parse(resp))
                $("#msgc").val(resp)
            }
        });
    }*/
    //send factura y boletas
    const data = {
        endpoints: "beta",//production
        certGlobal: false,
        lotes: true, // true, por lote maximo 20 entre facturas y boletas, false, solo una se aceptara
        ruta_xml: '',//http://localhost/files/xml/ <- el "/" al final
        ruc: "20123456789",
        clave_sol: "moddatos",
        usuario_sol: "MODDATOS",
        lote: [
            {
                metas: {},
                nombre_XML: "",
                contenido_XML: ""
            }
        ],//solo si lotes es true
        documento: {
            metas: {},
            nombre_XML: "",
            contenido_XML: ""
        }//solo si lote es false
    }

    function consulta() {
        const data = {
            endpoints: "beta",//production
            certGlobal: false,
            cliente: {
                doc_num: "10706671817",
                nom_RS: "samuel bruno alva",
                direccion: '-'
            },
            empresa: {
                ruc: "20123456789",
                razon_social: "GREENTER S.A.C.",
                direccion: "AV NEW DEÁL 123",
                ubigeo: "150101",
                distrito: "LIMA",
                provincia: "LIMA",
                departamento: "LIMA",
                clave_sol: "moddatos",
                usuario_sol: "MODDATOS"
            },
            total: 336.00,
            serie: "F001",
            numero: "777",
            fecha: "2021-11-05",
            moneda: "PEN",
            productos: [
                {
                    precio: 336.00,
                    cantidad: 1,
                    cod_pro: "5",
                    cod_sunat: "",
                    descripcion: "jabon"
                }
            ]

        }
        $.ajax({
            type: "POST",
            url: "factura.php",
            data: data,
            success: function (resp) {
                $("#msgc").val(resp)
                console.log(JSON.parse(resp))

            }
        });
    }

    function consultaNC() {
        const data = {
            endpoints: "beta",//production
            certGlobal: false,
            cliente: {
                doc_num: "10706671817",
                nom_RS: "samuel bruno alva",
                direccion: '-'
            },
            empresa: {
                ruc: "20123456789",
                razon_social: "GREENTER S.A.C.",
                direccion: "AV NEW DEÁL 123",
                ubigeo: "150101",
                distrito: "LIMA",
                provincia: "LIMA",
                departamento: "LIMA",
                clave_sol: "moddatos",
                usuario_sol: "MODDATOS"
            },
            total: 236,
            serie: "BB01",
            numero: "777",
            fecha: "2021-11-05",
            moneda: "PEN",
            tip_doc_afectado: "03",
            sn_afectado: "B001-2577",
            cod_motivo: "01",// Catalogo. 09
            des_motivo: 'ANULACION DE LA OPERACION',
            productos: [
                {
                    precio: 336.00,
                    cantidad: 2,
                    cod_sunat: "",
                    cod_pro: "",
                    descripcion: "jabon"
                },
                {
                    precio: 336.00,
                    cantidad: 2,
                    cod_sunat: "",
                    cod_pro: "C023",
                    descripcion: "jabon"
                }
            ]

        }

        const data2 = {
            certGlobal: false,
            endpoints: "beta",
            cliente: {
                "doc_num": "70609400",
                "nom_RS": "CISNEROS FLORES CRISTHIAN EDWAR",
                "direccion": ""
            },
            empresa: {
                ruc: "20123456789",
                razon_social: "BLAS PALACIOS MARCIAL ULPILIANO",
                direccion: "JR. CONSTITUCION NRO 201 EL PROGRESO - CHIMBOTE - SANTA - ANCASH",
                ubigeo: "021801",
                distrito: "CHIMBOTE",
                provincia: "SANTA",
                departamento: "ANCASH",
                clave_sol: "moddatos",
                usuario_sol: "MODDATOS"
            },

            total: 16.80,
            serie: "BB01",
            numero: "14",
            tip_doc_afectado: "03",
            sn_afectado: "B001-2577",
            cod_motivo: "01",
            des_motivo: "ANULACION DE LA OPERACION",

            fecha: "2022-03-07",
            moneda: "PEN",
            productos: [{
                precio: 16.80,
                cantidad: "1",
                cod_pro: "",
                cod_sunat: "",
                descripcion: "jjjjjjj kk kjk ljjlkjlj lkjl ji"
            }],
        }


        $.ajax({
            type: "POST",
            url: "nota_credito.php",
            data: data2,
            success: function (resp) {
                $("#msgc").val(resp)
                console.log(JSON.parse(resp))

            }
        });
    }

</script>
</html>