<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 10.08.16
 * Time: 10:14
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\Rival;
use RgkBundle\Entity\Code;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;

class CodeController extends BaseController
{
    /**
     * @Route("/rival")
     */
    public function indexAction(Request $request)
    {
        $params = $this->getTemplatteParams();

        //get sections array
        $params['sections'] = [];
        foreach ($this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array(), array('title' => 'ASC')) as $section){
            $params['sections'][$section->getId()] = $section;
        };


        //search
        $params['q'] = $request->query->get('q');

        $query = 'SELECT r.id FROM rival as r WHERE 1 ';
        if(!empty($params['q'])){
            $query .= sprintf('AND r.name LIKE \'%s\' OR r.url LIKE \'%s\' ','%'.addslashes($params['q']).'%','%'.addslashes($params['q']).'%');
        }

        //get all rivals array
        $stmt = $this->getDoctrine()->getManager()
            ->getConnection()
            ->prepare(
                $query
            );
        $stmt->execute();
        $ideas=$stmt->fetchAll();
        $params['rivals'] = [];
        if(!empty($ideas)){
            $ideas = array_map(function($a){return $a['id'];},$ideas);
            /**
             * @var Rival $a
             */
            $params['rivals'] = array_map(
                function(Rival $a) use ($params){
                    $sections = [];
                    $active_sect = json_decode($a->getSections(),true);
                    if(is_array($active_sect) && !empty($active_sect)){
                        foreach ($active_sect as $as){
                            if(isset($params['sections'][$as]))
                                $sections[$as] = $params['sections'][$as];
                        }
                    }
                    return [
                        'id'=>$a->getId(),
                        'name'=>$a->getName(),
                        'url'=>$a->getUrl(),
                        'sections'=>$sections,
                        'sectionsArrayId'=>json_decode($a->getSections(),true),
                        'code'=>$a->getCode()
                    ];
                },$this->getDoctrine()
                    ->getRepository('RgkBundle:Rival')
                    ->findBy(['id'=>$ideas], array('name' => 'ASC'))
            );
        }

        $params['sections'] = $this->menuStrict($params['sections']);
        return $this->render('RgkBundle:Admin:rival.html.twig',$params);
    }

    /**
     * @Route("/actionCode/{id}")
     */
    public function codeAction(Request $request,$id=0){
        if($request->getMethod() != 'DELETE')
           return $this->redirectToRoute('rgk_code_index');

        $code = $this->getDoctrine()
            ->getRepository('RgkBundle:Code')
            ->find(intval($id));

        if(!$code)
            $this->renderApiJson(['error'=>'Элемент не найдено']);

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($code);
        $manager->flush();
        $this->renderApiJson(['success'=>true]);
    }

    /**
     * @Route("/actionRival", name="rgk_post_rival")
     * @Route("/actionRival/{id}", name="rgk_action_rival")
     */
    public function rivalAction(Request $request,$id=0)
    {
        if($request->getMethod() != 'POST' && $request->getMethod() != 'DELETE')
           return $this->redirectToRoute('rgk_code_index');

        if($request->get("_route") == 'rgk_action_rival'){
            $rival = $this->getDoctrine()
                          ->getRepository('RgkBundle:Rival')
                          ->find(intval($id));
            if(!$rival)
                $this->renderApiJson(['error'=>'Элемент не найдено']);

            if($request->getMethod() == 'DELETE'){
                $manager = $this->getDoctrine()->getManager();
                $manager->remove($rival);
                $manager->flush();
                $this->renderApiJson(['success'=>true]);
            }
        }
        else
            $rival = new Rival();

        $data = $request->request->get('rival');
        if(isset($data['section']) && is_array($data['section']) && !empty($data['section'])){
            $rival->setSections($this->getSectionJson($data['section']));
        }
        $rival->setName((isset($data['name'])?$data['name']:''))
              ->setUrl((isset($data['url'])?$data['url']:''));

        //set default code
        $codeText = (isset($data['codeText'])?$data['codeText']:'');

        if(empty($codeText))
            $this->renderApiJson(['error'=>'Ошибка передачи кода']);

        if($rival->getId()) { //create code object
            $code = $this->getDoctrine()
                ->getRepository('RgkBundle:Code')
                ->findOneBy(['code'=>$codeText,'rival'=>$rival->getId()]);


        }

        if(!isset($code) || !$code){
            $code = new Code();
            $code->setCode($codeText);
        }

        $errors = $this->get('validator')->validate($rival);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных конкурента']);

        $manager = $this->getDoctrine()->getManager();

        /**
         * @var Code $subCode
         */
        foreach ($rival->getCode() as $subCode){
            if($subCode->getId() != $code->getId()){
                $subCode->setDef(false);
                $rival->addCode($subCode);
            }
        }
        $manager->persist($rival);
        $manager->flush();
        
        $code->setRival($rival)
             ->setDef(true);

        $manager->persist($code);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }
}