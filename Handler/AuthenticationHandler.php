<?php

namespace Skimia\AngularBundle\Handler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException; 

class AuthenticationHandler
implements AuthenticationSuccessHandlerInterface,
           AuthenticationFailureHandlerInterface,
                    AccessDeniedHandlerInterface,
               AuthenticationEntryPointInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
        } else {
            return new Response(json_encode($token->getUser()->getJson()));
            // If the user tried to access a protected resource and was forces to login
            // redirect him back to that resource
            if ($targetPath = $request->getSession()->get('_security.target_path')) {
                $url = $targetPath;
            } else {
                // Otherwise, redirect him to wherever you want
                $url = $this->router->generate('fos_user_profile_show');
            }

            return new RedirectResponse($url);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
        } else {
            //return new Response(json_encode($exception));
            // Create a flash message with the authentication error message
            $request->getSession()->set(SecurityContext::AUTHENTICATION_ERROR, $exception);
            /*$url = $this->router->generate('user_login');
            
            return new RedirectResponse($url);*/
            return new Response(json_encode(array('error'=>$exception->getMessage())),409);
        }
    }
    function handle(Request $request, AccessDeniedException $accessDeniedException){
        //die('401');
        return new Response(json_encode(array('error'=>$accessDeniedException->getMessage())),401);
        // do something with your exception and return Response object (plain message of rendered template)
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        return new Response(json_encode(array('error'=>$authException->getMessage())),401);
    }

}