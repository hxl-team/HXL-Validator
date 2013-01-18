<?php 

set_include_path('Classes/');

require_once 'EasyRdf.php';
require_once 'html_tag_helpers.php';

$msg = "Nothing to validate. Please make a POST request to this script and submit some RDF code in a POST parameter called 'rdf'.";

if($_GET["format"] == "json"){
	header('Content-type: application/json');
}else{
	header('Content-type: text/html');
}

$rdf = file_get_contents('php://input');

if($rdf == ""){
	if($_GET["format"] == "json"){
		die('{"message": "'.$msg.'"}
');
	}else{
		die('<div class="alert alert-error">'.$msg.'</div>
');	
	}
	
}

// load the data to check into a graph:
$test = new EasyRdf_Graph();
$ttlparser = new EasyRdf_Parser_Turtle();

// in case the parsing fails, return an error:
try{
	$ttlparser->parse($test, $rdf, 'turtle', 'http://example.graph.org/hxl');
}catch(Exception $e){
	if($_GET["format"] == "json"){
		die('{"message": "'.$e->getMessage().'"}
');
	}else{
		die('<div class="alert alert-error">'.$e->getMessage().'</div>
');	
	}
}


// first find all types used in the data, so that we know what we have to take care of:
$types = array();
foreach ($test->resourcesMatching('rdf:type') as $resource) {
	$thisMissing = false;

	$return = '<p>Missing mandatory properties for '.$resource.':</p>';

	$mandatoryProps = sparqlQuery('
SELECT ?property WHERE {
  <'.$resource->get('rdf:type').'> rdfs:subClassOf* ?super.
  ?property rdfs:domain ?super ;
           owl:minCardinality "1" .
}');

	foreach ($mandatoryProps as $prop) {
		if(!$test->hasProperty($resource, $prop->property)){
			$thisMissing = true;
			$return .= "<li>".$prop->property."</li>\n";
		}        
    }

    if($thisMissing){
    	echo $return;
    }

}


function sparqlQuery($query){

	$prefixes = 'prefix xsd: <http://www.w3.org/2001/XMLSchema#>  
	prefix skos: <http://www.w3.org/2004/02/skos/core#> 
	prefix hxl:   <http://hxl.humanitarianresponse.info/ns/#> 
	prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> 
	prefix label: <http://www.wasab.dk/morten/2004/03/label#> 
	prefix owl: <http://www.w3.org/2002/07/owl#>
	
	';

	$sparql = new EasyRdf_Sparql_Client('http://hxl.humanitarianresponse.info/sparql');
	$query = $prefixes.$query;
	
	try {
  	$results = $sparql->query($query);      
    	return $results;
	} catch (Exception $e) {
    	return "<div class='error'>".$e->getMessage()."</div>\n";
	}
}



?>