# HXL Validator 

Simple API that validates any RDF for compliance with the **Humanitarian eXchange Language (HXL)** vocabulary (see [hxl.humanitarianresponse.info/ns/](http://hxl.humanitarianresponse.info/ns/)). The [HXL project](https://sites.google.com/site/hxlproject/) is an initiative by the [United Nations Office for the Coordination of Humanitarian Affairs](http://unocha.org/).

For now, the validator is limited to 
- checking whether the posted RDF is syntactically correct
- checking for the presence of 'mandatory' properties, i.e., properties that are defined in the HXL vocabulary with an owl:minCardinality > 0.

## Usage 

Make a POST request to the script, with two parameters:
- **rdf**: should contain the RDF code to validate
- **format**: can be set to either 'html' or 'json'. This parameter is optional; if omitted, the script will return an html snippet.

## Dependencies

The HXL validator builds on [EasyRDF](https://github.com/njh/easyrdf), which is included in this repository.