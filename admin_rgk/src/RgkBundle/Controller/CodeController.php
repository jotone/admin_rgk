<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 10:14
 */

namespace RgkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CodeController extends BaseController
{
    /**
     * @Route("/rival")
     */
    public function indexAction(Request $request)
    {
        $params = $this->getTemplatteParams();
        //var_dump($request->get('_route'));
        //exit();
        return $this->render('RgkBundle:Admin:base.html.twig',$params);
    }
}