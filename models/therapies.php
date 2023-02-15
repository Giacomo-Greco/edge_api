<?php

class Therapies {

    private $conn;

    public $id;
    public $therapy;
    public $due;

    public function __construct($db) {
        $this->conn = $db;
    }


    //nodi del diagramma
    public function getNodes() {
        $query = 'SELECT * FROM hiv_prodotti ORDER BY short_name';  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }


    //parametri url per condizioni query
    public function getParams() {

        $str = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $params_array = explode("/", $str);

        //limite risultati
        if(isset($params_array[3])) {
            $params['limit'] = $params_array[3];
        }else{
            $params['limit'] = 10;
        }
        
        //date calendario
        if (isset($params_array[4]) && $params_array[4] != '') {
            $params['data_inizio'] = $params_array[4];

            if (isset($params_array[5]) && $params_array[5] != '') {
                $params['data_fine'] = strval($params_array[5]);
                $params['data'] = "WHERE data_cambio_terapia between '" . $params['data_inizio'] . "' AND '" . $params['data_fine'] . "'";
            }else{
                $params['data'] = "WHERE data_cambio_terapia > '" . $params['data_inizio'] . "' ";
            }

        }else{
            $params['data'] = '';
        }

        return $params;
    }


    //estrazione dal databse
    public function getData($params) {
        
        //personalmente avrei preferito fare il match dei codici nella terapia tutto in php, però avendo notato una particolare attenzione alle JOIN
        //ho deciso di usare entrambi i metodi a puro scopo dimostrativo

        $query = "SELECT 
        hiv_pazienti.id,
        data_cambio_terapia,
        GROUP_CONCAT(short_name) as terapia_from,
        REPLACE(hiv_pazienti.terapia_corrente, '&', ',') as terapia_to,
        COUNT(*) as switch
        FROM hiv_pazienti
        INNER JOIN hiv_prodotti ON FIND_IN_SET(hiv_prodotti.codice_prod, REPLACE(hiv_pazienti.terapia_precedente, '&', ','))
        " . $params['data'] . "
        GROUP BY hiv_pazienti.id, terapia_precedente, terapia_corrente
        ORDER BY switch DESC
        LIMIT " . $params['limit'] . "
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }

}

