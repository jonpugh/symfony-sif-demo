<?php
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Guzzle\Http\Client;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\FileLocator;

class HomeController extends Controller
{
  /**
   * @Template()
   */
  public function indexAction()
  {
    // Connect to the SIF REST Server
    // @TODO: Use config yml for this.
    define('TOKEN', 'new');
    define('SECRET', 'guest');
    define('BASE_URL', 'http://rest3api.sifassociation.org');

    // @TODO: Use config for this and make an XML template.
    //define("solutionId", "MyApp");
    //define("consumerName", "Guzzle");
    //define("vendorName", "Guzzle");
    //define("productName", "Guzzle");
    //define("productVersion", "1.x");

    // Generate our Authorization Key
    $authorizationKey = "Basic " . base64_encode(TOKEN . ':' . SECRET);

    // Our SIF client
    $client = new Client(BASE_URL, array(
      'request.options' => array(
        'headers' => array(
          'Authorization' => $authorizationKey,
          'Accept' => 'application/xml',
          'Content-Type' => 'application/xml',
        ),
      ),
    ));

    // Create our environment
    $xml = file_get_contents(__DIR__ . '/environment.xml');
    $request = $client->post('/api/environments/environment', array(), $xml);

    // Send the request and get the response
    $response_environment = $request->send();
    $environment = $response_environment->xml();

    // @TODO: Better handling?
    if ($response_environment->isSuccessful()){
      $status = "Connected to " . BASE_URL;
    } else {
      $status = "Unable to connect to " . BASE_URL;
    }

    // Get Session Token
    $sessionToken = $environment->sessionToken;
    print "OK! Got sessionToken! $sessionToken \n";

    // Generate new Authentication Token
    $authorizationKey = "Basic " . base64_encode($sessionToken . ':' . SECRET);

    print "New Auth Token: $authorizationKey \n";

    $client->setConfig(array('request.options' => array(
        'headers' => array(
          'Authorization' => $authorizationKey,
        ),
    )));

    // Get Zones
    $request = $client->get('/api/zones', array());

    // Send the request and get the response
    $response = $request->send();
    $zones = $response->json();

    foreach ($zones['zone'] as $zone){
      $name = $zone['id'];
      print "Zone Found! $name\n";
    }

    return array(
      'status' => $status,
      'environment' => 'test'.$response_environment->getBody(),
    );
  }
}
