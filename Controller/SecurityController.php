<?php

namespace Skimia\AngularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    public function getAuthAction()
    {
        $user = $this->getUser();
        if(isset($user)){
            return new Response(json_encode($user->getJson()));
        }else
        {
            return new Response(json_encode(array('error'=>'Unauthaurised')),409);
        }
    }
    
}
