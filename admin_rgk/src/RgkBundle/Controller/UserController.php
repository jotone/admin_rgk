<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 18.08.16
 * Time: 09:26
 */

namespace RgkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use RgkBundle\Entity\User;
use FOS\UserBundle\Doctrine\UserManager;

class UserController extends BaseController
{
    /**
     * @Route("/users")
     */
    public function indexAction(Request $request)
    {
        $activeUser = $this->getUser();
        if(!$activeUser)
            return $this->redirectToRoute("rgk_price_index");

        $params = $this->getTemplatteParams();

        $params['registration'] =[
                'email'=>'',
                'username'=>''
            ];

        $params['user_list'] = [];
        $params['form_errors'] = [];
        $params['form_info'] = [];

        if($request->getMethod() == "POST")
        {
            $formdata = $request->request->get('user');
            //stabilization
            $formdata = [
                 'email'=>(isset($formdata['email']) && filter_var($formdata['email'],FILTER_VALIDATE_EMAIL)?$formdata['email']:''),
                 'username'=>(isset($formdata['username'])?trim($formdata['username']):''),
                 'password'=>(isset($formdata['password'])?trim($formdata['password']):''),
                 'repassword'=>(isset($formdata['repassword'])?trim($formdata['repassword']):'')
            ];
            $params['registration'] = $formdata;
            if($formdata['repassword'] != $formdata['password'])
                $params['form_errors'][] = 'Подтверждение пароля не подходит';
            if(strlen($formdata['password'])<8)
                $params['form_errors'][] = 'Пароль долже быть не меньше 8 символов';
            if(strlen($formdata['username'])<5)
                $params['form_errors'][] = 'Имя пользователя должно быть не меньше 5 символов';

            $params['registration']['email'] = $formdata['email'];
            $params['registration']['username'] = $formdata['username'];

            if(empty($params['form_errors'])){
                /**
                 * @var UserManager $userManager
                 */
                $userManager = $this->get('fos_user.user_manager');

                $u = $userManager->findUserByEmail($formdata['email']);
                if($u)
                    $params['form_errors'][] = 'Email уже используется';

                $u = $userManager->findUserByUsername($formdata['username']);
                if($u)
                    $params['form_errors'][] = 'Имя пользователя уже используется';

                if(empty($params['form_errors'])) {
                    $User = $userManager->createUser();

                    $User->setPassword($formdata['password'])
                        ->setEmail($formdata['email'])
                        ->setUsername($formdata['username'])
                        ->setEnabled(true)
                        ->setParentSection($activeUser);

                    $userManager->updateUser($User);

                    $encoder_service = $this->get('security.encoder_factory');
                    $encoder = $encoder_service->getEncoder($User);
                    $encoded_pass = $encoder->encodePassword($formdata['password'], $User->getSalt());
                    $User->setPassword($encoded_pass);
                    $userManager->updateUser($User);
                    $params['form_info'][] = 'Пользователь "'.$User->getUsername().'" создан.';
                }
            }
        }

        $em = $this->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT u1.id, u1.username, u1.last_login, u2.username as parent FROM `user` as u1 LEFT JOIN `user` as u2 on u2.`id`=u1.parentUser WHERE 1 ORDER BY u1.id DESC;");
        $statement->execute();
        $ans = $statement->fetchAll();
        $dateTime = new \DateTime();
        foreach($ans as $line){
            $arr = [
                'id'=>$line['id'],
                'username'=>$line['username'],
                'parent'=>$line['parent'],
                'last_login'=>'---'
            ];
            if($time = strtotime($line['last_login'])){
                $dateTime->setTimestamp($time);
                $arr['last_login'] = $dateTime->format('d.m.Y');
            }

            $params['user_list'][] = $arr;
        }

        return $this->render('RgkBundle:Admin:user.html.twig',$params);
    }
}