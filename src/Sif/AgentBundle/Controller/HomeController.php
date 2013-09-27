<?php
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Guzzle\Http\Client;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\FileLocator;

class HomeController extends Controller
{
  // SimpleXML response for our environment
  protected $environment;

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

    // Save to the controller
    $this->environment = $environment;

    // @TODO: Better handling?
    if ($response_environment->isSuccessful()){
      $status = "Connected to " . BASE_URL;
    } else {
      $status = "Unable to connect to " . BASE_URL;
    }

    // Get Session Token
    $sessionToken = $environment->sessionToken;

    // Generate new Authentication Token
    $authorizationKey = "Basic " . base64_encode($sessionToken . ':' . SECRET);
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
      if ($zone['id'] == $environment->defaultZoneId){
        $zone_list[] = array(
          'id' => $zone['id'],
          'name' => $zone['id'] . ' (default)',
        );
      } else {
        $zone_list[] = array(
          'id' => $zone['id'],
          'name' => $zone['id'],
        );
      }
    }

    // Get Students
    $request = $client->get('/api/students', array());
    $response = $request->send();
    $students = $response->json();
    foreach ($students['student'] as $student){
      $student_list[] = array(
        'name' => $student['name']['nameOfRecord']['fullName'],
        'id' => $student['refId'],
      );
    }
    //
    //  if ($zone['id'] == $environment->defaultZoneId){
    //    $zone_list[] = $zone['id'] . ' (default)';
    //  } else {
    //    $zone_list[] = $zone['id'];
    //  }
    //}

    return array(
      'status' => $status,
      'environment' => $this->environmentXML(),
      'description' => 'This app has connected to the ZIS server and granted our application access.',
      'zone_list' => $zone_list,
      'student_list' => $student_list,
      'default_zone' => $environment->defaultZoneId,
      'token' => $authorizationKey,

    );
  }

  public function environmentXML(){
    $dom = dom_import_simplexml($this->environment)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
  }
}
