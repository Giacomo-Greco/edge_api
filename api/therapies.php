<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');


include_once './../config/database.php';
include_once './../models/therapies.php';


$database = new Database();
$db = $database->connect();

$therapies = new Therapies($db);


$params = $therapies->getParams();
$nodes = $therapies->getNodes();
$data = $therapies->getData($params);

$num = $data->rowCount();


if ($num > 0) {

    $output = array();
    $output['records'] = $num; //totale dei records
    $output['data'] = array(); //records
    $output['nodes'] = array(); //nodi per diagramma
    $nodes_array = array();

    //nodi diagramma
    while($row = $nodes->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $nodes_array[$short_name] = $codice_prod;

        $newNode = array(
            'codice_prod' => $codice_prod,
            'short_name' => $short_name,
            'name' => $name
        );

        array_push($output['nodes'], $newNode);
       
    }

    //records
    while($row = $data->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $obj_array = array(
            'from' => $terapia_from,
            'to' => $terapia_to,
            'value' => $switch
        );

        array_push($output['data'], $obj_array);
    }

    //match in php codiceprodotto/nomeprodotto
    foreach($output['data'] as $key => $value) {
        
            $array = $output['data'][$key]['from'];

            $output['data'][$key]['from'] = array_reduce(explode(",", $array), function ($previous, $next)use($nodes_array) {
                $medicine_name = array_search($next, $nodes_array);
                return $previous . $medicine_name . ",";
            });

            $array = $output['data'][$key]['to'];

            $output['data'][$key]['to'] = array_reduce(explode(",", $array), function ($previous, $next)use($nodes_array) {
                $medicine_name = array_search($next, $nodes_array);
                return $previous . $medicine_name . ",";
            });
            
    };

    echo json_encode($output);

} else {
    echo json_encode(
        array('message' => 'No results found')
    );
}
