<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 09.08.16
 * Time: 11:41
 */

namespace RgkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sonata\UserBundle\Entity\UserManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use RgkBundle\Entity\User;
use Symfony\Component\Yaml\Parser;

class AuthController extends Controller
{
    /**
     * @Route("/login")
     */
    public function loginAction(){
        $user = $this->getUser();
        if($user)
            return $this->redirect('/');

        /////
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            //$error = $error->getMessage();
            $error = 'Неверный логин или пароль';
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');


        $params['last_username'] = $lastUsername;
        $params['error'] = $error;
        $params['csrf_token'] = $csrfToken;

        return $this->render('RgkBundle:Auth:login.html.twig',$params);
    }

    /**
     * @Route("/remindPassword")
     */
    public function remindPassword(Request $request)
    {
        $user = $this->getUser();
        if($user)
            return $this->redirect('/');

        $yaml = new Parser();
        $params = $yaml->parse(file_get_contents(__DIR__ . '/../../../app/config/params.yml'));

        $params['form_user'] = [
            'key'=>'form_user',
            'ms'=>'',
            'fields'=>[
                'user'=>[
                    'value'=>'',
                    'error'=>''
                ]
            ]
        ];

        $form_pass = [
            'key'=>'form_pass',
            'error'=>'',
            'ms'=>'',
            'token'=>''
        ];
        /**
         * @var User $user
         */
        $userManager = $this->get('fos_user.user_manager');
        if($request->getMethod() == "POST")
        {
            $userPass = $request->request->get($form_pass['key']);
            if(!empty($userPass)){
                $params['form_pass'] = $form_pass;
                $params['form_pass']['error'] = 'Ошибка передачи данных';
                if(isset($userPass['token']) && !empty($userPass['token']) && isset($userPass['password']) && isset($userPass['repassword'])){
                    $params['form_pass']['token'] = $userPass['token'];
                    $pass = $userPass['password'];
                    if($userPass['password'] != $userPass['repassword']) {
                        $params['form_pass']['error'] = 'Пароли не совкадают';
                        $pass = false;
                    }

                    if($pass && strlen($pass)<8){
                        $pass = false;
                        $params['form_pass']['error'] = 'Пароль должен быть не меньше 8 сымволов';
                    }

                    if($pass) {
                        $user = $userManager->findUserByConfirmationToken($userPass['token']);
                        if ($user && $user->getCreateTokenAt() && time() - $user->getCreateTokenAt()->getTimestamp() < 86400) {
                            $encoder_service = $this->get('security.encoder_factory');
                            $encoder = $encoder_service->getEncoder($user);
                            $encoded_pass = $encoder->encodePassword($pass, $user->getSalt());
                            $user->setPassword($encoded_pass)
                                 ->setConfirmationToken('');

                            $userManager->updateUser($user);

                            $params['form_pass']['error'] = '';
                            $params['form_pass']['ms'] = 'Пароль успешно изменен.';
                        }
                    }
                }
            } else {
                $user = $request->request->get($params['form_user']['key']);
                if(isset($user['user']) && !empty($user['user'])){
                    //check user
                    $thisIsEmail = filter_var($user['user'],FILTER_VALIDATE_EMAIL);
                    if($thisIsEmail){
                        $user = $userManager->findUserByEmail($user['user']);
                    } else {
                        $user = $userManager->findUserByUsername($user['user']);
                    }
                    if(!$user || !$user->isEnabled())
                        $params['form_user']['fields']['user']['error'] = 'На сайте нет пользователя с такими данными';
                    else {
                        //check cache

                        // generate token and sent email
                        $token = md5(time().'randomStringTextChangePw'.rand(1,100).$user->getId());
                        $user->setConfirmationToken($token)
                            ->setCreateTokenAt(new \DateTime());
                        $userManager->updateUser($user);

                        //send email
                        BaseController::sendEmail([
                            'emailFrom'=>$params['email_from'],
                            'emailTo'=>$user->getEmail(),
                            'subject'=>'Смена пароля',
                            'content'=>'Для смены пароля перейдите по ссылке <a href="'.
                                $params['site'].'/remindPassword?token='.$token.'" >'.
                                $params['site'].'/remindPassword?token='.$token.'</a>'
                        ], $this);


                        $params['form_user']['ms'] = 'На адрес электронной почты выслано письмо с ссылкой на смену регистрации';
                    }
                }
            }
        } else {
            $token = $request->query->get('token');
            if (!empty($token)) {
                $params['form_user']['fields']['user']['error'] = 'Неверный или неактивный код активации';
                $user = $userManager->findUserByConfirmationToken($token);
                if($user && $user->getCreateTokenAt() && time()-$user->getCreateTokenAt()->getTimestamp()<86400){
                    //show change form
                    $params['form_pass'] = $form_pass;
                    $params['form_pass']['token'] = $token;
                }
            }
        }

        return $this->render('RgkBundle:Auth:remind_pass.html.twig',$params);
    }
}