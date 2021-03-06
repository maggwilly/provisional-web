<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use AppBundle\Form\CredentialsType;
use AppBundle\Form\CodeType;
use AppBundle\Entity\AuthToken;
use AppBundle\Entity\Credentials;
use AppBundle\Entity\User;

/**
 * CommandeClient controller.
 */
class AuthTokenController extends Controller
{


    
        /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"auth-token"})
     * 
     */
    public function postAuthTokensAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm( CredentialsType::class, $credentials);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
         $em = $this->getDoctrine()->getManager();
         $user = $em->getRepository('AppBundle:User')->findOneByUsername($credentials->getLogin());
        if (!$user) { 
             return  array('create' =>$credentials );
        }
        $authToken=$this->postAuthToken($user);
        return  $authToken;
    }



   public function postAuthToken(User $user)
    {   $em = $this->getDoctrine()->getManager();
        $authToken=$user->getAuthToken();
        if($authToken)
            $authToken->generateValue();
         else{
           $authToken=AuthToken::create($user);
           $em->persist($authToken); 
         }
          $em->flush();
        $numero='+237'.$user->getUsername();
        $contacts=urlencode($numero);
        $message=urlencode(`Utilisez le code `.$authToken->getValue().' Pour vous connecter');
       $url='https://api-public.mtarget.fr/api-sms.json?username=omegatelecombuilding&password=79sawbfF&msisdn='.$contacts.'&sender=Previsional&msg='.$message;  
           $res = $this->get('http_request_maker')->sendOrGetData($url,null,'GET',false); 
        return  $authToken;
    } 


        /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * 
     */
    public function checkAuthTokensAction(Request $request)
    {
        $code = new AuthToken();
        $form = $this->createForm( CodeType::class, $code);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

         $em = $this->getDoctrine()->getManager();
         $token = $em->getRepository('AppBundle:AuthToken')->findOneByValue($code->getValue());
        if (!$token) { 
             return  array(
                'error_code' =>'invalid_verify_code',
                'code' =>$code->getValue(),
                'message' =>"Le code n'est pas correct " 
                          );
        }
        
        if ($code->getUser()->getId()!=$token->getUser()->getId()) { 
             return  array(
                'error_code' =>'inconsistant_verify_code',
                'code' =>$code->getValue(),
                'message' =>"Le code n'est pas correct " 
            );
        }
      /*  $requests=$request->request->all()['receiveRequests'];

        foreach ( $requests as $key => $request) {
              $request->setStatus('accepted');
               $em->flush();
        }*/
        return $token->getUser();
    }


      /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * 
     */
  /*  public function postCredentialAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm( CredentialsType::class, $credentials);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }
         $em = $this->getDoctrine()->getManager();
         $user = $em->getRepository('AppBundle:User')->findOneByEmail($credentials->getLogin());
        if (!$user) { // L'utilisateur n'existe pas encore
 
          return array('error_code' =>'user_not_exist');
        }

         $encoder = $this->get('security.password_encoder');
         $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());
         if (!$isPasswordValid) { // Le mot de passe n'est pas correct
            return array('error_code' =>'invalid_credentials');
        }

        $authToken=$user->getAuthToken();
        if($authToken)
            $authToken->setValue();
         else{
        $authToken=AuthToken::create($user);
        $em->persist($authToken); 
         }
          $em->flush();
        return $authToken->getUser();
    }*/

      /**
     * @Rest\View(serializerGroups={"user"})
     * 
     */
    public function showJsonAction(User $user=null)
    {

        return $user;
    }


    /**
     * @Rest\View()
      
     */
    public function removeAuthTokensAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
         $authToken = $em->getRepository('AppBundle:AuthToken')->findOneByValue($request->get('value'));
        if (!$authToken) {
            throw $this->createNotFoundException('Unable to find  $authToken entity.');
        }
            $em->remove($authToken);
            $em->flush();
       
        return ['success'=>true];
    }

    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }


   private function getToken(){
    
    return $this->get('security.token_storage')->getToken();
} 
}