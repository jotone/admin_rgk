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
use RgkBundle\Entity\Section;

class PriceController extends BaseController
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        /**
         * @var Section $a
         */
        $params = $this->getTemplatteParams();
        $params['sections'] = $this->getDoctrine()
                                     ->getRepository('RgkBundle:Section')
                                     ->findBy(array(), array('title' => 'ASC'));
        $params['sections'] = $this->menuStrict($params['sections']);
        return $this->render('RgkBundle:Admin:price.html.twig',$params);
    }

    private function menuStrict(&$objects,$parent=0)
    {

        $resp = [];
        /**
         * @var Section $object
         */
        if(!empty($objects)){
            foreach ($objects as &$object){
                $cPar = ($object->getParentSection()?$object->getParentSection()->getId():0);
                if($cPar != $parent)
                    continue;

                $resp[] = [
                    'id'=>$object->getId(),
                    'title'=>$object->getTitle(),
                    'children'=>$this->menuStrict($objects,$object->getId())
                ];
            }
        }
        return $resp;
    }
}