<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 09:59
 */

namespace RgkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ReportController extends BaseController
{
    /**
     * @Route("/report")
     */
    public function indexAction(Request $request)
    {
        $params = $this->getTemplatteParams();
        //var_dump($request->get('_route'));
       // exit();
        return $this->render('RgkBundle:Admin:base.html.twig',$params);
    }
}