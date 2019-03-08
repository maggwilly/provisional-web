<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Commende;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use FOS\RestBundle\View\View;
use AppBundle\Entity\PointVente; 
use AppBundle\Entity\User; 
/**
 * Commende controller.
 *
 */
class CommendeController extends Controller
{
    /**
     * Lists all commende entities.
     *
     */
    public function indexAction(PointVente $pointVente=null,User $user=null)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();
        $region=$session->get('region');
        $startDate=$session->get('startDate',date('Y').'-01-01');
        $endDate=$session->get('endDate', date('Y').'-12-31');
        $commendes =is_null($pointVente)? $em->getRepository('AppBundle:Commende')->findAll($user,$startDate,$endDate):$em->getRepository('AppBundle:Commende')->findByPointVente($pointVente);
        return $this->render('commende/index.html.twig', array(
            'commendes' => $commendes,
        ));
    }

    /**
     * @Rest\View(serializerGroups={"commende"})
     * 
     */
    public function indexJsonAction(Request $request)
    {
         $em = $this->getDoctrine()->getManager();
          $order=$request->query->get('order');
          $start=$request->query->get('start');
          $user=$this->getMobileUser($request);
          $commendes = $em->getRepository('AppBundle:Commende')->findByUser($user,$start);
        return $commendes;
    }

    /**
     * Creates a new commende entity.
     *
     */
    public function newAction(Request $request)
    {
         $user=$this->getUser();
        $commende = new Commende($user);
        $form = $this->createForm('AppBundle\Form\CommendeType', $commende);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($commende);
            $em->flush();
            return $this->redirectToRoute('commende_show', array('id' => $commende->getId()));
        }

        return $this->render('commende/new.html.twig', array(
            'commende' => $commende,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Rest\View(serializerGroups={"commende"})
     * 
     */
    public function newAJsonction(Request $request)
    {
        $user=$this->getMobileUser($request);
        $commende = new Commende($user);
        $form = $this->createForm('AppBundle\Form\CommendeType', $commende);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($commende);
            $em->flush();
             $commende->setNumFacture(str_pad((string)$this->id, 5, "0", STR_PAD_LEFT));
              $em->flush();
            return $commende;
        }

        return  array(
            'status' => 'error');
    }

    /**
     * Finds and displays a commende entity.
     *
     */
    public function showAction(Commende $commende)
    {
        $deleteForm = $this->createDeleteForm($commende);

        return $this->render('commende/show.html.twig', array(
            'commende' => $commende,
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * @Rest\View(serializerGroups={"full"})
     * 
     */
    public function showJsonAction(Commende $commende)
    {
        return $commende;
    }

    /**
     * Displays a form to edit an existing commende entity.
     *
     */
    public function editAction(Request $request, Commende $commende)
    {
        $deleteForm = $this->createDeleteForm($commende);
        $editForm = $this->createForm('AppBundle\Form\CommendeType', $commende);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('commende_edit', array('id' => $commende->getId()));
        }

        return $this->render('commende/edit.html.twig', array(
            'commende' => $commende,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Rest\View(serializerGroups={"commende"})
     * 
     */
    public function editJsonAction(Request $request, Commende $commende)
    {
        $editForm = $this->createForm('AppBundle\Form\CommendeType', $commende);
        $form->submit($request->request->all());

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $commende;
        }

        return $form;
    }
    /**
     * Deletes a commende entity.
     *
     */
    public function deleteAction(Request $request, Commende $commende)
    {
        $form = $this->createDeleteForm($commende);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($commende);
            $em->flush();
        }

        return $this->redirectToRoute('commende_index');
    }


    /**
     * Deletes a commende entity.
     *
     */
    public function deleteJsonAction(Request $request, Commende $commende)
    {
         $id=$commende->getId();
            $em = $this->getDoctrine()->getManager();
       try {
          $em->remove($commende);
          $em->flush();
        } catch (\Exception $e) {
       return array('status' => "error" );
     }
        return array('ok' => true,'deletedId' => $id );
    }


    /**
     * Creates a form to delete a commende entity.
     *
     * @param Commende $commende The commende entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Commende $commende)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('commende_delete', array('id' => $commende->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

        public function getMobileUser(Request $request)
    {
         $em = $this->getDoctrine()->getManager();
          $user = $em->getRepository('AppBundle:User')->findOneById($request->headers->get('X-User-Id'));
        return $user;
    }   
}