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
use ParseBundle\Controller\ParseController;

class PriceController extends BaseController
{
    /**
     * @Route("/", name="rgk_price_index")
     * @Route("/section/{id}", name="rgk_price_section")
     */
    public function indexAction(Request $request,$id=0)
    {
  //      $parse = new ParseController();
 //       $result=$parse->get_price('http://stylus.ua/sokovyzhimalki/philips-hr-183202.html','#product-block .price');
//        var_dump($result); exit();
        /**
         * @var Section $a
         */
        $params = $this->getTemplatteParams();
        $params['active_section'] = intval($id);
        $params['active_section_title'] = '';
        $params['sections'] = $this->getDoctrine()
                                     ->getRepository('RgkBundle:Section')
                                     ->findBy(array(), array('title' => 'ASC'));
        if(!empty($params['sections'])){
            /**
             * @var Section $a
             */
            foreach ($params['sections'] as &$a){
                if($a->getId() == $id) {
                    $params['active_section_title'] = $a->getTitle();
                    break;
                }
            }
        }
        $params['sections'] = $this->menuStrict($params['sections']);
        if($request->get("_route") == 'rgk_price_section'){
            //get active section id with all children
            $sectionSpectre = $this->getSectionChildTreeIds($params['active_section'],$params['sections']);
            if(empty($sectionSpectre))
                return $this->redirectToRoute("rgk_price_index");

            //get all rivals array
            $params['rivals'] = $this->getDoctrine()
                                     ->getRepository('RgkBundle:Rival')
                                     ->findBy(array(), array('name' => 'ASC'));

            //get products of active section
            $params['products'] = $this->getDoctrine()
                                       ->getRepository('RgkBundle:Product')
                                       ->findBy(array('section'=>$sectionSpectre), array('title' => 'ASC','price'=>'ASC'));
        }
        return $this->render('RgkBundle:Admin:price.html.twig',$params);
    }

    /**
     * @Route("/sectionList", name="rgk_section_list")
     */
    public function sectionListAction()
    {
        $sections = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array(), array('title' => 'ASC'));
        $sections = $this->menuStrict($sections);

        return $this->renderApiJson($sections);
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

    private function getSectionChildTreeIds($id,$sectArray)
    {
        $resp = [];
        foreach ($sectArray as $item){
            if($id != $item['id'])
            {
                if(!empty($item['children'])){
                    $resp = array_merge($resp,$this->getSectionChildTreeIds($id,$item['children']));
                }
                continue;
            }

            $resp[] = $item['id'];

            if(!empty($item['children'])){
                foreach ($item['children'] as $child){
                    $resp = array_merge($resp,$this->getSectionChildTreeIds($child['id'],$item['children']));
                }
            }
        }
        return $resp;
    }
}