<?php
/**
 * Our helpful Homepage.
 */
namespace Sif\AgentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HelloController extends Controller
{
  /**
   * @Route("/", name="_hello")
   * @Template()
   */
  public function indexAction()
  {
      /*
       * The action's view can be rendered using render() method
       * or @Template annotation as demonstrated in DemoController.
       *
       */
      return $this->render('SifAgentBundle:Hello:index.html.twig');
      //return new Response('<h1>Hello</h1>', 200, array(
      //  'Content-Type' => 'text/html',
      //));

  }
}
