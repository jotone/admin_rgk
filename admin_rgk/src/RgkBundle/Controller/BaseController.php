<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 09.08.16
 * Time: 15:05
 */

namespace RgkBundle\Controller;

use RgkBundle\Entity\User;

class BaseController
{
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
}