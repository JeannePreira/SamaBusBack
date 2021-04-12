<?php
require_once 'header.php';

$pdo= new mysqli('localhost','root', "", "samabus");
/**
 * recuperer les info du bus
 * permet de lister les informations d'un bus par au quartier
 * @params formData
 */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchBus'])){
   
    $data= array();
    $lieu=$_POST["lieu"];
    $quart=$_POST["quartier"];
    $sql = $pdo->query("SELECT quartier.IDENTIFIANT_QUARTIER,quartier.NOM_QUARTIER,ligne.IDENTIFIANT_LIGNE,ligne.DENOMINATION_LIGNE,commune.NOM_COMMUNE,commune.IDENTIFIANT_COMMUNE FROM quartier,quartier_ligne,`commune_ligne` ,ligne,commune 
    WHERE quartier.IDENTIFIANT_QUARTIER=quartier_ligne.IDENTIFIANT_QUARTIER
    AND quartier_ligne.IDENTIFIANT_LIGNE=ligne.IDENTIFIANT_LIGNE 
    AND ligne.IDENTIFIANT_LIGNE= commune_ligne.IDENTIFIANT_LIGNE 
    AND commune_ligne.IDENTIFIANT_COMMUNE=commune.IDENTIFIANT_COMMUNE
    AND quartier.NOM_QUARTIER='$quart' 
    AND commune.NOM_COMMUNE='$lieu'");               
    while($donnee = $sql->fetch_assoc()){
        $data[] = $donnee;
    }
  $res= json_encode($data); 
  echo $res;
}
/**
 * Permet de recuper le contenu d'une table 
 *
 * @param [type] $tableName nom de la table
 * @return array
 */
function findAll($tableName){
    global $pdo;
    $data= array();
    $sql = $pdo->query("SELECT * FROM $tableName");
                            
    while($donnee = $sql->fetch_assoc()){
        $data[] = $donnee;
    }
    return $data;
}
/**
 * Permet de recuperer une ligne dans ue table 
 *
 * @param array $tab exemple: ["client_id"=>10]
 * @param [type] $tableName nom de la table 
 * Exemple: findOneById(["client_id"=>10], 'client');
 * @return object
 */
function findOneById($tab= [],$tableName){
    global $pdo;
    $data= array();
    $req ="SELECT * FROM `$tableName` WHERE";
    if($tab){
        foreach ($tab as $key => $value) {
         
            $req .=' '.$key .' = '. $value;
        }
    }
   // $sql = $pdo->query("SELECT * FROM `$tableName` WHERE ID_OBJET_Perdu = $id");  
    $sql = $pdo->query($req);         
    while($donnee = $sql->fetch_object()){
        $data[] = $donnee;
    }
    return $data[0];
}

//itineraire de tous les bus
if($_SERVER['REQUEST_METHOD'] === 'GET'  && isset($_GET['itineraire'])){
   
    $data = findAll('itineraire');
    exit(json_encode($data)); 
}


//liste des ogjet trouvées
if($_SERVER['REQUEST_METHOD'] === 'GET'  && isset($_GET['object'])){
    $data= array();
    $sql = $pdo->query("SELECT * FROM objet_Retrouve");
                            
    while($donnee = $sql->fetch_assoc()){
        $data[] = $donnee;
    }

    exit(json_encode($data)); 
}

/**
 * 
 */
if($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['objet_perdu'])){
  
    $NUMERO_TICKET=$_POST["NUMERO_TICKET"];$LIBELLE_OBJET=$_POST["LIBELLE_OBJET"];
    $Details=$_POST["Details"];$HEURE=$_POST["HEURE"];
    $t = $_POST["DATE_OBJET_PERDU"];
    $data= array();
   // $t = date('Y-m-d h:i:s');
   // echo $req; exit;
   $sql = $pdo->query("INSERT INTO `objet_perdu` (`NUMERO_TICKET`, `LIBELLE_OBJET`, `Details`, `HEURE`) VALUES
    ('$NUMERO_TICKET', '$LIBELLE_OBJET', '$Details', '$t')");
    $lastid = $pdo->insert_id;
    if ($lastid == 0) {
        http_response_code(500);
        echo json_encode(['message'=>'Error d\'ajout']); 
    }else{
        $data = findOneById(['ID_OBJET_Perdu'=>$lastid], "objet_perdu");
        http_response_code(200);
        $res= json_encode($data); 
        echo $res;
    }
    
}

if($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['AddClient'])){
   
    $NUMERO_TICKET=$_POST["NUMERO_TICKET"];$MATRICULE_BUS=$_POST["MATRICULE_BUS"];$PRENOM=$_POST["PRENOM"];
    $NOM=$_POST["NOM"];$ADRESSE_1=$_POST["ADRESSE_1"];$ADRESSE_2=$_POST["ADRESSE_2"];
    $TEL_CLIENT=$_POST["TEL_CLIENT"];$EMAIL=$_POST["EMAIL"];
   
    $sql = $pdo->query("INSERT INTO `client` (`NUMERO_TICKET`, `MATRICULE_BUS`, `PRENOM`, `NOM`,
                                                 `ADRESSE_1`, `ADRESSE_2`, `TEL_CLIENT`, `EMAIL`) VALUES
    ('$NUMERO_TICKET', '$MATRICULE_BUS', '$PRENOM', '$NOM', '$ADRESSE_1', '$ADRESSE_2', 
        '$TEL_CLIENT', '$EMAIL')");
    $lastid = $pdo;
     $data = findOneById(["NUMERO_TICKET"=>$NUMERO_TICKET],'client');
   if (!empty($data)) {
    $res= json_encode($data); 
    http_response_code(200);
    echo $res; 
   }else{
       http_response_code(500);
       echo json_encode(['message'=> "error d'insertion"]);
   }
    
} 



