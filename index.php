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
$missing = false;

if($_GET["format"] == "json"){
	$return = '"properties": [{';
}else{
	$return = '<div class="alert alert-error">';
}

foreach ($test->resourcesMatching('rdf:type') as $resource) {
	$thisMissing = false;

	if($_GET["format"] == "json"){
		$thisReturn = '"'.$resource.'": [';
	}else{
		$thisReturn = '<p>Missing mandatory properties for '.$resource.':</p>';
	}

	$mandatoryProps = sparqlQuery('
SELECT ?property WHERE {
  <'.$resource->get('rdf:type').'> rdfs:subClassOf* ?super.
  ?property rdfs:domain ?super ;
           owl:minCardinality "1" .
}');

	foreach ($mandatoryProps as $prop) {
		if(!$test->hasProperty($resource, $prop->property)){
			$thisMissing = true;
			$missing = true;
			if($_GET["format"] == "json"){
				$thisReturn .= '"'.$prop->property.'", ';
			}else{
				$thisReturn .= "<li>".$prop->property."</li>\n";
			}			
		}        
    }

    if($_GET["format"] == "json"){
    	// remove trailing comma, close array:
    	$thisReturn = substr($thisReturn, 0, -2);
    	$thisReturn .= '], ';
    }

    if($thisMissing){
    	$return .= $thisReturn;
    }

}
if($_GET["format"] == "json"){
	// remove trailing comma, close array:
    $return = substr($return, 0, -2);
	$return .= '}]}';
}else{
	$return .= '</div>';
}

if($missing){
	if($_GET["format"] == "json"){
		echo '{"message": "missing properties", 
  '.$return.'
';
	}else{
		echo $return;
	}
}else{
	if($_GET["format"] == "json"){
		echo '{"message": "okay"}
';	}else{
		echo '<div>HXL code is valid and complete.</div>';
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