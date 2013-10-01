<?php
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sif\AgentBundle\SifClient;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\FileLocator;

class ZoneController extends Controller
{
  // SimpleXML response for our environment
  protected $xml;

  /**
   * @Template()
   */
  public function getAction($zoneId)
  {
    $client = new SifClient();

    // Get Zone
    $request = $client->get('/api/zones/' . $zoneId, array());

    // Send the request and get the response
    $response = $request->send();
    $zone = $response->json();

    return array(
      'status' => 'Zone: ' . $zone['id'],
      'description' => $zone['description'],
    );
  }

  public function environmentXML(){
    $dom = dom_import_simplexml($this->environment)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
  }
}
