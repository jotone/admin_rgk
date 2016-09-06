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
use RgkBundle\Entity\Code;
use RgkBundle\Entity\Rival;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RgkBundle\Entity\Section;
use ParseBundle\Controller\ParseController;

class PriceController extends BaseController
{
    public function getParent(Section $a){
        $return = [$a->getId()];
        if($a->getParentSection()){
            $return = array_merge($return,$this->getParent($a->getParentSection()));
        }
        return $return;
    }
    /**
     * @Route("/", name="rgk_price_index")
     * @Route("/section/{id}", name="rgk_price_section")
     */
    public function indexAction(Request $request,$id=0)
    {
        /**
         * @var Section $a
         */
        $params = $this->getTemplatteParams();
        $params['active_section'] = intval($id);
        $params['active_section_title'] = '';
        $params['sections'] = $this->getDoctrine()
                                     ->getRepository('RgkBundle:Section')
                                     ->findBy(array(), array('title' => 'ASC'));
        $activeObj = false;
        if(!empty($params['sections'])){
            /**
             * @var Section $a
             */
            foreach ($params['sections'] as &$a){
                if($a->getId() == $id) {
                    if($a->getFolder())
                        return $this->redirectToRoute("rgk_price_index");

                    $activeObj = $a;
                    $params['active_section_title'] = $a->getTitle();
                    $params['active_section_parent_id'] = ($a->getParentSection()?$a->getParentSection()->getId():'');
                    break;
                }
            }
        }

        if($request->get("_route") == 'rgk_price_section'){
            //get parent spectre
            $parentSpectre = $this->getParent($activeObj);

            //get all rivals array
            $params['rivals'] = $this->getSectionsRival($parentSpectre,$activeObj);

            //get name+id of all rivals for popup
            $params['popup_rivals'] = $this->getPopupRivals($params['rivals']);

            $params['sections'] = $this->menuStrict($params['sections']);

            //get active section id with all children
            $sectionSpectre = $this->getSectionChildTreeIds($params['active_section'],$params['sections']);
            if(empty($sectionSpectre))
                return $this->redirectToRoute("rgk_price_index");

            //get products of active section
            $params['products'] = $this->getDoctrine()
                                       ->getRepository('RgkBundle:Product')
                                       ->findBy(array('section'=>$sectionSpectre), array('pos' => 'ASC','title' => 'ASC','price'=>'ASC'));
        }
        else {
            $params['sections'] = $this->menuStrict($params['sections']);
        }
        return $this->render('RgkBundle:Admin:price.html.twig',$params);
    }

    /**
     * @Route("/sectionList", name="rgk_section_list")
     */
    public function sectionListAction(Request $request)
    {
        $sections = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array('folder'=>true), array('title' => 'ASC'));
        $sections = $this->menuStrict($sections);

        $id = $request->query->get('id');
        if($id>0){
            $sections = $this->unsetChild($sections,intval($id));
        }
        return $this->renderApiJson($sections);
    }

    /**
     * @Route("/sectionRival/{id}")
     */
    public function sectionRival(Request $request, $id=0){
        if($request->getMethod() != 'DELETE')
            return $this->redirectToRoute('rgk_price_index');

        /**
         * @var Section $section
         */
        $params['sections'] = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array(), array('title' => 'ASC'));

        $activeObj = false;
        if(!empty($params['sections'])){
            /**
             * @var Section $a
             */
            foreach ($params['sections'] as &$a){
                if($a->getId() == $id) {
                    $activeObj = $a;
                    break;
                }
            }
        }

        if($activeObj == false)
            $this->renderApiJson(['error'=>'Ошибка передачи данных раздела']);

        //get parent spectre
        $parentSpectre = $this->getParent($activeObj);

        $rivalId = $request->request->get('rival');
        if(!$rivalId)
            $rivalId = $request->query->get('rival');

        /**
         * @var Rival $rival
         */
        $rival = $this->getDoctrine()
            ->getRepository('RgkBundle:Rival')
            ->find(intval($rivalId));

        if(!$rival)
            $this->renderApiJson(['error'=>'Ошибка передачи данных конкурента']);

        $a = json_decode($rival->getSections(),true);
        if(!$a || !is_array($a))
            return $this->renderApiJson([]);

        $resp = array_uintersect($parentSpectre, $a, "strcasecmp");
        if(!empty($resp)){
            foreach ($resp as $r){
                $key = array_search($r,$a);
                if($key !== false)
                    unset($a[$key]);
            }
        }
        $rival->setSections(json_encode($a));

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($rival);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionSectionPos/{id}")
     */
    public function sectionPosAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST'){
            //if ajax
            if ( $request->isXmlHttpRequest() )
                $this->renderApiJson(['error'=>'invalidMethod']);
            return $this->redirectToRoute('rgk_price_index');
        }
        /**
         * @var Section $section
         */
        $section = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->find(intval($id));
        if(!$section)
            $this->renderApiJson(['error'=>'Элемент не найдено']);

        $param = [];
        if($a = $request->request->get('rivals')){
            if(is_array($a) && !empty($a)){
                $a = array_map(function($a){return intval($a);},$a);
                $a = array_unique($a);
                $a = array_filter($a,function($a){return ($a>0);});
            }
        }
        $section->setSortInfo(json_encode($a));
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($section);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }
    /**
     * @Route("/actionSection", name="rgk_post_section")
     * @Route("/actionSection/{id}", name="rgk_action_section")
     */
    public function sectionAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' && $request->getMethod() != 'DELETE') {
            //if ajax
            if ( $request->isXmlHttpRequest() )
                $this->renderApiJson(['error'=>'invalidMethod']);
            return $this->redirectToRoute('rgk_price_index');
        }
        $data = $request->request->get('section');

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
        else {
            $section = new Section();
            $section->setFolder((isset($data['folder'])&&$data['folder']>0?true:false));
        }

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
     * @Route("/actionProductPos/{id}")
     */
    public function ProductPosAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST')
            return $this->redirectToRoute('rgk_price_index');

        /**
         * @var Product $product
         */
        $product = $this->getDoctrine()
            ->getRepository('RgkBundle:Product')
            ->find(intval($id));

        if(!$product || !$product->getSection())
            $this->renderApiJson(['error'=>'Элемент не найдено']);

        $up = ($request->request->get('up')?true:false);

        //get closest
        $q = sprintf("SELECT id FROM `product` WHERE `section` = %d and pos %s %d ORDER BY pos %s LIMIT 0, 1",$product->getSection()->getId(),($up?'<':'>'),$product->getPos(),($up?'DESC':'ASC'));
        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $q
            );
        $stmt->execute();
        $ideas=$stmt->fetchAll();

        if($ideas && isset($ideas[0]['id'])){
            $product2 = $this->getDoctrine()
                ->getRepository('RgkBundle:Product')
                ->find(intval($ideas[0]['id']));
            if(!$product2)
                $this->renderApiJson(['error'=>'Элемент не найдено']);

            //swap position value
            $a = $product->getPos();
            $product->setPos($product2->getPos());
            $product2->setPos($a);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->persist($product2);
            $manager->flush();
        }
        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionProduct", name="rgk_post_product")
     * @Route("/actionProduct/{id}", name="rgk_action_product")
     */
    public function productAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' && $request->getMethod() != 'DELETE')
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
        /**
         * @var Section $section
         */
        $section = (isset($data['section']) && $data['section']>0?$this->getDoctrine()->getRepository('RgkBundle:Section')->find(intval($data['section'])):null);
        if($section->getFolder())
            $this->renderApiJson(['error' => 'Ошибка передачи данных раздела']);

        $product->setTitle((isset($data['title'])?$data['title']:''))
                ->setPrice((isset($data['price']) && $data['price']>0?floatval($data['price']):0))
                ->setUrl((isset($data['url'])?$data['url']:''))
                ->setSection($section);
        
        $errors = $this->get('validator')->validate($product);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных продукта']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($product);
        $manager->flush();
        if($request->get("_route") == 'rgk_post_product'){
            $product->setPos($product->getId());
            $manager->persist($product);
            $manager->flush();
        }
        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionPrice", name="rgk_post_price")
     * @Route("/actionPrice/{id}", name="rgk_action_price")
     */
    public function priceAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' && $request->getMethod() != 'DELETE')
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
        if(!isset($data['code']) || empty($data['code']))
            $this->renderApiJson(['error'=>'Ошибка передачи кода']);

        $rival = (isset($data['rival'])?$this->getDoctrine()->getRepository('RgkBundle:Rival')->find(intval($data['rival'])):null);
        if(!$rival)
            $this->renderApiJson(['error' => 'Ошибка передачи конкурента']);

        $manager = $this->getDoctrine()->getManager();
        $code = $this->getDoctrine()->getRepository('RgkBundle:Code')->findOneBy(['code'=>$data['code'],'rival'=>$rival->getId()]);
        if(!$code){
            $code = new Code();
            $code->setCode($data['code'])
                 ->setRival($rival);
            $manager->persist($code);
        }

        $price->setCode($code)
             ->setUrl((isset($data['url'])?$data['url']:''))
             ->setTitle((isset($data['title'])?$data['title']:''))
        ;

        $errors = $this->get('validator')->validate($price);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных цены']);

        $manager->persist($price);
        $manager->flush();

        $this->productCheckPrices($price->getProduct());

        $this->renderApiJson(['success'=>true, 'id'=>$price->getId()]);
    }

    /**
     * @Route("/actionSectionParse/{id}")
     */
    public function sectionParseAction(Request $request,$id=0)
    {
        if ($request->getMethod() != 'POST')
            return $this->redirectToRoute('rgk_price_index');
        /**
         * @var Rival $rival
         * @var Section $section
         * @var Price $price
         */
        $section = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->find(intval($id));
        if(!$section)
            $this->renderApiJson(['error'=>'Элемент не найдено']);

        $rival = $request->request->get('rival');
        if(!$rival || $rival<=0)
            $this->renderApiJson(['error'=>'Конкурент не найден']);

        $rival = $this->getDoctrine()
            ->getRepository('RgkBundle:Rival')
            ->find(intval($rival));
        if(!$rival)
            $this->renderApiJson(['error'=>'Элемент не найдено']);

        //get section-rival products
        $prods = $this->getDoctrine()
            ->getRepository('RgkBundle:Product')
            ->findBy(array('section'=>$section->getId()));

        if($prods){
            $prodIds = array_map(function($a){return (strpos($a->getUrl(),'http')===0?$a->getId():0);},$prods);
            //get all prices
            $prices = $this->getDoctrine()
                ->getRepository('RgkBundle:Price')
                ->findBy(['product'=>$prodIds,'code'=>array(function($a){return $a->getId();},$rival->getCode()->toArray())]);

            if($prices){
                $manager = $this->getDoctrine()->getManager();
                $parse = new ParseController();
                foreach ($prices as $price) {
                    $priceValue = $parse->get_price($price->getUrl(), $price->getCode()->getCode());
                    if($priceValue) {
                        $price->setPrice($priceValue)
                              ->setDate(new \DateTime());
                        $manager->persist($price);
                    }
                }
                $manager->flush();
            }
        }

        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionPriceParse/{id}", name="rgk_action_price_parse")
     */
    public function priceParseAction(Request $request,$id=0)
    {
        if ($request->getMethod() != 'POST')
            return $this->redirectToRoute('rgk_price_index');

        /**
         * @var Price $price
         */
        $price = $this->getDoctrine()
            ->getRepository('RgkBundle:Price')
            ->find(intval($id));
        if(!$price)
            $this->renderApiJson(['error'=>'Элемент не найдено']);
        
        if(strpos($price->getUrl(),'http')!==0)
            $this->renderApiJson(['error'=>'URL товара в недопустимом формате']);
        //check parce
        $parse = new ParseController();
        $priceValue = $parse->get_price($price->getUrl(), $price->getCode()->getCode());

        if (!$priceValue)
            $this->renderApiJson(['error' => 'Ошибка парсинга кода']);

        $price->setPrice($priceValue)
              ->setDate(new \DateTime());

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($price);
        $manager->flush();

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

    private function unsetChild($objects,$id)
    {
        foreach ($objects as $key=>$object){
            if($object['id'] == $id){
                unset($objects[$key]);
                break;
            } elseif (!empty($object['children'])){
                $objects[$key]['children'] = $this->unsetChild($objects[$key]['children'],$id);
            }
        }
        return array_values($objects);
    }

}