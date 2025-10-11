
Matricula
0100180598

Solar 1 D&manzana 369

parcela 57 DC 31

Para consulta utilizando el parámetro “Matricula”: /inmueble/getpropertiesbymatricula?matricula=

Ejemplo: https://sandbox.ri.gob.do/WebApiRiInmueble/inmueble/getpropertiesbymatricula?matricula=0100180598

 

Para consulta utilizando los parámetros “Solar” y “Manzana”: /inmueble/getPropertiesBySolarAndManzana?solar=&manzana=

Ejemplo: https://sandbox.ri.gob.do/WebApiRiInmueble/inmueble/getPropertiesBySolarAndManzana?solar=1-D&manzana=369

 

Para consulta utilizando los parámetros “Parcela” y “DC”: /inmueble/getPropertiesByParcelaAndDC?parcela=&dc=

Ejemplo: https://sandbox.ri.gob.do/WebApiRiInmueble/inmueble/getPropertiesByParcelaAndDC?parcela=57&dc=31

 

Y estos serian los datos que estarían recibiendo a partir de estas consultas:


{

    "data": [

        {

            "matricula": "0100180598",

            "fechaInscripcion": null,

            "superficie": "980.8300",

            "dcPosicional": "400591127885",

            "parcela": null,
        

            "solar": null,

            "manzana": null,

            "porcion": null,

            "distritoCatastral": null,

            "codigoUnidadFuncional": null,

            "nombreCondominio": null,

            "municipio": "SANTO DOMINGO NORTE",

            "provincia": "SANTO DOMINGO",

            "oficinaRegistral": "Registro de Títulos de Santo Domingo"

        }

    ],

    "message": "Propiedad encontrada.",

    "statusCode": 200,

    "isSucces": true

}