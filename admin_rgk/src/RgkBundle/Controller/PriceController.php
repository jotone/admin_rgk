<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 10:10
 */

namespace RgkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PriceController extends BaseController
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $params = $this->getTemplatteParams();
        //var_dump($request->get('_route'));
        //exit();
        return $this->render('RgkBundle:Admin:base.html.twig',$params);
    }

}