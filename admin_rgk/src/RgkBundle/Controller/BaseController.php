<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 09.08.16
 * Time: 15:05
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\User;
use RgkBundle\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    private $menu = [
        0 => [
            'link'=>'rgk_price_index',
            'sub_links'=>['rgk_price_section'],
            'class'=>'icon_menu6',
            'title'=>'Цены'
        ],
        1 => [
            'link'=>'rgk_code_index',
            'sub_links'=>[],
            'class'=>'icon_menu7',
            'title'=>'Конкуренты'
        ],
        2 => [
            'link'=>'rgk_report_xls',
            'sub_links'=>[],
            'class'=>'icon_menu4',
            'title'=>'Отчет'
        ],
        3 => [
            'link'=>'rgk_user_index',
            'sub_links'=>[],
            'class'=>'icon_menu3',
            'title'=>'Пользователи'
        ]
    ];

    public static function sendEmail($attributes, $container)
    {
        if(!isset($attributes['emailTo']) || !isset($attributes['emailFrom']))
            return false;

        $message = \Swift_Message::newInstance()
            ->setSubject((isset($attributes['subject'])?$attributes['subject']:''))
            ->setFrom($attributes['emailFrom'])
            ->setTo($attributes['emailTo'])
            ->setBody(

                $container->get('templating')->render(
                    'RgkBundle:EmailTemplate:default.html.twig',
                    $attributes
                ),
                'text/html'
            );

        if ($container->get('mailer')->send($message) == 1)
            return true;
        return false;
    }

    public function getTemplatteParams()
    {
        return [
            'leftMenu'=>$this->getLeftMenu()
        ];
    }

    public function addLeftMenu($link,$title,$class='')
    {
        $this->menu[] = [
            'link'=>strval($link),
            'class'=>strval($title),
            'title'=>strval($class)
        ];
    }

    public function getLeftMenu()
    {
        return $this->menu;
    }


    public function renderApiJson($value, $isJson=false, $httpMessage = 'Message', $contentType = 'application/json'){
        @ob_clean(); // clear output buffer to avoid rendering anything else
        @header("Content-type: $contentType");
        // @header('HTTP/1.1 '.$httpStatus.' '.$httpMessage);
        @header("Access-Control-Allow-Headers: origin, content-type, accept");
        @header("Access-Control-Allow-Origin: *");
        @header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, PATCH, OPTIONS");

        echo ($isJson?$value:json_encode($value));
        exit();
    }

    public function menuStrict(&$objects,$parent=0)
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
                    'parent_id'=>($object->getParentSection()?$object->getParentSection()->getId():''),
                    'children'=>$this->menuStrict($objects,$object->getId())
                ];
            }
        }
        return $resp;
    }

    public function getSectionChildTreeIds($id,$sectArray)
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

    /**
     * @param $array
     * @return string
     */
    public function getSectionJson($array){
        $sections = $this->getDoctrine()
            ->getRepository('RgkBundle:Section')
            ->findBy(array(), array('title' => 'ASC'));

        $sections = $this->menuStrict($sections);

        $resp = $this->checkStrict($sections,$array);
        return json_encode($resp);
    }

    private function checkStrict($sections,$array){
        $r = [];
        foreach ($sections as $section){
            if(in_array($section['id'],$array)){//active
                //check if have not
                $r[] = $section['id'];
            } elseif(!empty($section['children'])) {
                $a = $this->checkStrict($section['children'],$array);
                foreach ($a as $section_id){
                    $r[] = $section_id;
                }
            }
        }
        return $r;
    }
}