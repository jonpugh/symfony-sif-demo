<?php
/**
 * @file
 * Definition of Sif\AgentBundle\Client
 */

namespace Sif\AgentBundle;

use Guzzle\Http\Client;

class SifClient extends Client {

  /**
   * The connection information for this client object.
   *
   * @var array
   */
  protected $connectionOptions = array();

  /**
   * The SIF "Environment" for this application.
   */
  public $environment;

  /**
   * The SIF REST server Authorization Key
   */
  public $key;

  /**
   * Readable status string.
   */
  /**
   * The SIF "Environment" for this application.
   */
  public $status = '';

  /**
   * Constructs a Client object.
   */
  public function __construct() {

    // @TODO: Implement config for this.
    $token = 'new';
    $secret = 'guest';
    $baseUrl = 'http://rest3api.sifassociation.org';

    // Generate our Authorization Key
    $this->key = "Basic " . base64_encode($token . ':' . $secret);

    // Initiate the Guzzle Client
    parent::__construct($baseUrl, array(
      'request.options' => array(
        'headers' => array(
          'Authorization' => $this->key,
          'Accept' => 'application/xml',
          'Content-Type' => 'application/xml',
        ),
      ),
    ));

    // Create our environment
    // @TODO: Only do this if we don't have the final auth token.
    $xml = file_get_contents(__DIR__ . '/Resources/data/environment.xml');
    $request = $this->post('/api/environments/environment', array(), $xml);

    // Send the POST to create the environment
    $response = $request->send();
    $environment = $response->xml();

    // Save to the SifClient
    $this->environment = $environment;
    $this->environmentXml = $this->cleanXml($environment);


    // @TODO: Better handling?
    if ($response->isSuccessful()){
      $this->status = "Connected to " . $baseUrl;
    } else {
      $this->status = "Unable to connect to " . $baseUrl;
    }

     // Generate new Authentication Token
    $authorizationKey = "Basic " . base64_encode($environment->sessionToken . ':' . $secret);
    $this->setConfig(array('request.options' => array(
        'headers' => array(
          'Authorization' => $authorizationKey,
        ),
    )));
  }

  public function cleanXml($simpleXml){
    $dom = dom_import_simplexml($simpleXml)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
  }
}
