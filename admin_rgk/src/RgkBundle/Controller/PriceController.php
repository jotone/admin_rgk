<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 10:10
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\Price;
use RgkBundle\Entity\Product;
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
        //$parse = new ParseController();
        //$result=$parse->get_price('http://carpan.com.ua/tovar/katushka-jaxon-tabias-fdx-kj-tab100','.item-price-current');
        //var_dump($result); exit();
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

    /**
     * @Route("/actionSection", name="rgk_post_section")
     * @Route("/actionSection/{id}", name="rgk_action_section")
     */
    public function sectionAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' || $request->getMethod() != 'DELETE')
            return $this->redirectToRoute('rgk_price_index');


        if($request->get("_route") == 'rgk_action_section'){
            $section = $this->getDoctrine()
                ->getRepository('RgkBundle:Section')
                ->find(intval($id));
            if(!$section)
                $this->renderApiJson(['error'=>'Элемент не найдено']);

            if($request->getMethod() == 'DELETE'){
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($section);
                $manager->flush();
                $this->renderApiJson(['success'=>true]);
            }
        }
        else
            $section = new Section();

        $data = $request->request->get('product');
        $parentSection = (isset($data['section']) && $data['section']>0?
            $this->getDoctrine()->getRepository('RgkBundle:Section')->find(intval($data['section'])):
            null
        );
        $section->setTitle((isset($data['title'])?$data['title']:''));

        if(!$section->getId() || !$parentSection){
            $section->setParentSection((is_object($parentSection)?$parentSection:null));
        } else { //check if $parentSection is not $sectionChaild
            $all = $this->getDoctrine()
                ->getRepository('RgkBundle:Section')
                ->findBy(array(), array('title' => 'ASC'));
            $all = $this->menuStrict($all);
            $childArray = $this->getSectionChildTreeIds($section->getId(),$all);
            if(in_array($parentSection->getId(),$childArray))
                $this->renderApiJson(['error' => 'Ошибка передачи родительского раздела']);
            $section->setParentSection($parentSection);
        }

        $errors = $this->get('validator')->validate($section);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных раздела']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($section);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionProduct", name="rgk_post_product")
     * @Route("/actionProduct/{id}", name="rgk_action_product")
     */
    public function productAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' || $request->getMethod() != 'DELETE')
            return $this->redirectToRoute('rgk_price_index');

        if($request->get("_route") == 'rgk_action_product'){
            $product = $this->getDoctrine()
                ->getRepository('RgkBundle:Product')
                ->find(intval($id));
            if(!$product)
                $this->renderApiJson(['error'=>'Элемент не найдено']);

            if($request->getMethod() == 'DELETE'){
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($product);
                $manager->flush();
                $this->renderApiJson(['success'=>true]);
            }
        }
        else
            $product = new Product();

        $data = $request->request->get('product');
        $section = (isset($data['section']) && $data['section']>0?$this->getDoctrine()->getRepository('RgkBundle:Section')->find(intval($data['section'])):null);
        $product->setTitle((isset($data['title'])?$data['title']:''))
                ->setPrice((isset($data['price']) && $data['price']>0?floatval($data['price']):0))
                ->setSection($section);

        $errors = $this->get('validator')->validate($product);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных продукта']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($product);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }


    /**
     * @Route("/actionPrice", name="rgk_post_price")
     * @Route("/actionPrice/{id}", name="rgk_action_price")
     */
    public function priceAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' || $request->getMethod() != 'DELETE')
            return $this->redirectToRoute('rgk_price_index');

        $data = $request->request->get('price');
        if($request->get("_route") == 'rgk_action_price'){
            $price = $this->getDoctrine()
                ->getRepository('RgkBundle:Price')
                ->find(intval($id));
            if(!$price)
                $this->renderApiJson(['error'=>'Элемент не найдено']);

            if($request->getMethod() == 'DELETE'){
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($price);
                $manager->flush();
                $this->renderApiJson(['success'=>true]);
            }
        }
        else {
            $price = new Price();
            $product = (
                isset($data['product']) && $data['product']>0?
                    $this->getDoctrine()->getRepository('RgkBundle:Product')->find(intval($data['product'])):false
            );
            if($product)
                $price->setProduct($product);
            else
                $this->renderApiJson(['error' => 'Ошибка передачи продукта']);
        }

        //check code
        $code = (isset($data['code']) && $data['code']>0?$this->getDoctrine()->getRepository('RgkBundle:Code')->find(intval($data['code'])):null);
        if(!$code)
            $this->renderApiJson(['error' => 'Ошибка передачи кода']);

        $price->setCode($code)
             ->setUrl((isset($data['url'])?$data['url']:''))
             ->setDate(new \DateTime());
        //check parce
        $parse = new ParseController();
        $priceValue = $parse->get_price($price->getUrl(),$price->getCode()->getCode());

        if(!$priceValue)
            $this->renderApiJson(['error' => 'Ошибка передачи кода']);

        $price->setPrice($priceValue);

        $errors = $this->get('validator')->validate($price);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных цены']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($price);
        $manager->flush();

        $this->productCheckPrices($price->getProduct());

        $this->renderApiJson(['success'=>true]);
    }

    private function productCheckPrices(Product $product){
        if($product->getPrices()){
            /**
             * @var Price $price
             */
            $rivalSpectre = [];
            foreach ($product->getPrices()->toArray() as $price){
                if(!$price->getCode())
                    continue;
                if(!in_array($price->getCode()->getRival()->getId(),$rivalSpectre))
                    $rivalSpectre[] =  $price->getCode()->getRival()->getId();
                else { //remove
                    $manager = $this->getDoctrine()->getManager();
                    $manager->remove($price);
                    $manager->flush();
                }
            }
        }
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