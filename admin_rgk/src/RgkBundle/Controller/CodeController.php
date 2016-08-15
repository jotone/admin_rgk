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
            $params['rivals'] = $this->getDoctrine()
                ->getRepository('RgkBundle:Rival')
                ->findBy(['id'=>$ideas], array('name' => 'ASC'));
        }

        return $this->render('RgkBundle:Admin:rival.html.twig',$params);
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
        $rival->setName((isset($data['name'])?$data['name']:''))
              ->setUrl((isset($data['url'])?$data['url']:''));

        //set default code
        $codeId = (isset($data['code'])?intval($data['code']):0);
        $codeText = (isset($date['codeText'])?$date['codeText']:'');

        if(!$rival->getId()) { //create code object
            if(empty($codeText))
                $this->renderApiJson(['error'=>'Ошибка передачи кода']);
            $code = new Code();
            $code->setCode($codeText);
        } else {
            /**
             * @var Code $code
             */
            $code = $this->getDoctrine()
                ->getRepository('RgkBundle:Code')
                ->find($codeId);
            if(!$code || $code->getRival()->getId() != $rival->getId())
                $this->renderApiJson(['error'=>'Ошибка передачи кода']);
        }

        $errors = $this->get('validator')->validate($rival);
        if (count($errors) > 0)
            $this->renderApiJson(['error' => 'Ошибка передачи данных конкурента']);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($rival);

        $code->setRival($rival)
             ->setDefault(true);

        $manager->persist($code);
        $manager->flush();

        $this->renderApiJson(['success'=>true]);
    }
}