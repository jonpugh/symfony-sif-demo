<?php
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\FileLocator;

use Sif\AgentBundle\SifClient;

class HomeController extends Controller
{

  /**
   * @Template()
   */
  public function indexAction()
  {
    $client = new SifClient();

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

    // Get Zones
    $request = $client->get('/api/zones', array());
    $response = $request->send();
    $zones = $response->json();
    foreach ($zones['zone'] as $zone){
      $zone['default'] = '';
      if ($zone['id'] == $client->zone){
        $zone['default'] = 'Default';
      }
      $zone_list[] = $zone;
    }

    return array(
      'status' => $client->status,
      'environment' => $client->environmentXml,
      'description' => 'This app has connected to the ZIS server and granted our application access.',
      'zone_list' => $zone_list,
      'student_list' => $student_list,
      'student_count' => count($student_list),
      'zone' => $client->zone,
      'token' => $client->key,
    );
  }
}
