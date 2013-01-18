# HXL Validator 

Simple API that validates RDF in Turtle notation for compliance with the **Humanitarian eXchange Language (HXL)** vocabulary (see [hxl.humanitarianresponse.info/ns/](http://hxl.humanitarianresponse.info/ns/)). The [HXL project](https://sites.google.com/site/hxlproject/) is an initiative by the [United Nations Office for the Coordination of Humanitarian Affairs](http://unocha.org/).

For now, the validator is limited to 
- checking data in Turtle notation
- checking whether the posted RDF is syntactically correct
- checking for the presence of 'mandatory' properties, i.e., properties that are defined in the [HXL vocabulary](http://hxl.humanitarianresponse.info/ns/) with an owl:minCardinality > 0.

## Usage 

POST the RDF data to check to the script. Optionally, set the  **format** parameter to either 'html' or 'json'. If omitted, the script will return an html snippet.

## Testing

Easiest via [cURL](http://curl.haxx.se). If the data to check is in a file test.ttl, call

    curl -d @test.ttl http://myserver.com/HXL-Validator/?format=json

## Dependencies

The HXL validator builds on [EasyRDF](https://github.com/njh/easyrdf), which is included in this repository.