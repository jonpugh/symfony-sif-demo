<?php
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Config\FileLocator;

use Sif\AgentBundle\SifClient;

class StudentController extends Controller
{

  /**
   * @Template()
   */
  public function getAction($studentId)
  {
    $client = new SifClient();

    // Get the Student
    $request = $client->get('/api/students/' . $studentId, array());
    $response = $request->send();
    $student = $response->json();

    $output = print_r($student, 1);
    $name = $student['value']['name']['nameOfRecord']['fullName'];

    return array(
      'name' => $name,
      'id' => $studentId,
      'description' => $output,
    );
  }
}
