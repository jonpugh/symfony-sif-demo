<?php
/**
 * @file
 * Definition of Sif\AgentBundle\SifClient
 */

namespace Sif\AgentBundle;

use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Session\Session;

class SifClient extends Client {
  /**
   * REST server base URL
   */
  public $baseUrl = 'http://rest3api.sifassociation.org';

  /**
   * The SIF "Environment" for this application, and a readable XML
   * string.
   */
  public $environment;
  public $environmentXml;

  /**
   * The SIF REST server Authorization Key
   */
  public $key;

  /**
   * Readable status string.
   */
  public $status = '';

  /**
   * Current/Default Zone.
   */
  public $zone = '';

  /**
   * Constructs a Client object.
   */
  public function __construct() {
    // Check for an existing authorization token for this session.
    $session = new Session();

    $this->key = $session->get('key');
    $this->zone = $session->get('zone');
    $this->environmentXml = $session->get('environment');

    // If we don't have a key yet, getAuth()
    if (empty($this->key)){
      // getAuth() initiates the Guzzle Client...
      $this->getAuth();
    }
    // Otherwise, just initiate the Guzzle client.
    else {

      // Initiate the Guzzle Client
      parent::__construct($this->baseUrl, array(
        'request.options' => array(
          'headers' => array(
            'Authorization' => $this->key,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
          ),
        ),
      ));
    }
  }

  /**
   * Connects and gets a proper authorization key.
   */
  private function getAuth(){
    $session = new Session();

    // @TODO: Implement config for this.
    $token = 'new';
    $secret = 'guest';

    // Generate our pre Authorization Key
    $pre_key = "Basic " . base64_encode($token . ':' . $secret);

    // Initiate the Guzzle Client
    parent::__construct($this->baseUrl, array(
      'request.options' => array(
        'headers' => array(
          'Authorization' => $pre_key,
          'Accept' => 'application/xml',
          'Content-Type' => 'application/xml',
        ),
      ),
    ));

    // POST our environment
    $xml = "<environment>
  <solutionId>testSolution</solutionId>
  <authenticationMethod>Basic</authenticationMethod>
  <instanceId></instanceId>
  <userToken></userToken>
  <consumerName>Guzzle</consumerName>
  <applicationInfo>
    <applicationKey>Basic bmV3Omd1ZXN0Cg==</applicationKey>
    <supportedInfrastructureVersion>3.0</supportedInfrastructureVersion>
    <supportedDataModel>SIF-US</supportedDataModel>
    <supportedDataModelVersion>3.0</supportedDataModelVersion>
    <transport>REST</transport>
    <applicationProduct>
      <vendorName>Guzzle</vendorName>
      <productName>Guzzle</productName>
      <productVersion>Guzzle</productVersion>
    </applicationProduct>
  </applicationInfo>
</environment>
";
    $request = $this->post('/api/environments/environment', array(), $xml);
    $response = $request->send();
    $environment = $this->environment = $response->xml();

    // Save to this SifClient and the session
    $this->environmentXml = $this->cleanXml($environment);
    $this->zone = $this->environment->defaultZoneId;

    // Generate new Authentication Token...
    $this->key = "Basic " . base64_encode($environment->sessionToken . ':' . $secret);

    // Save as guzzle config so future requests use this key.
    $this->setConfig(array('request.options' => array(
      'headers' => array(
        'Authorization' => $this->key,
      ),
    )));

    // Save token to session.
    $session->set('key', $this->key);
    $session->set('environment', $this->environmentXml);
    $session->set('zone', $this->environment->defaultZoneId);
  }

  /**
   * Helper to get human readable XML
   */
  public function cleanXml($simpleXml){
    $dom = dom_import_simplexml($simpleXml)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
  }
}
