<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 09.08.16
 * Time: 15:05
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    private $menu = [
        0 => [
            'link'=>'rgk_report_index',
            'class'=>'icon_menu3',
            'title'=>'Отчет'
        ],
        1 => [
            'link'=>'rgk_price_index',
            'class'=>'icon_menu6',
            'title'=>'Цены'
        ],
        2 => [
            'link'=>'rgk_code_index',
            'class'=>'icon_menu7',
            'title'=>'Конкуренты'
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
}