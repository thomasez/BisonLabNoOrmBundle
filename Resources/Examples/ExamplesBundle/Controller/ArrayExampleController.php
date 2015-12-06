<?php

namespace BisonLab\ExamplesBundle\Controller;

use BisonLab\ExamplesBundle\Model as Model;
use BisonLab\ExamplesBundle\Form as Form;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ArrayExampleController extends BaseController
{

    private static $_manager = 'arrayexample_manager';

    /**
     * @Route("/", name="arrayexample", requirements = { "_method"="get"})
     * @Template()
     */
    public function indexAction()
    {

        $arrayexample_manager = $this->get('arrayexample_manager');

        $arrayexamples = $arrayexample_manager->findAll();
// error_log("Cname:" . $arrayexamples[0]->getName());

        return $this->render('BisonLabExamplesBundle:ArrayExample:list.html.twig', 
              array( 'arrayexamples' => $arrayexamples,
              ));

    }

    /**
     * @Route("/new", name="arrayexample_new", requirements = { "_method"="get"})
     * @Template()
     */
    public function newAction()
    {

      $arrayexample = new Model\ArrayExample;
      $form = $this->get('form.factory')->create( new Form\ArrayExampleForm(), $arrayexample);

      return $this->render('BisonLabExamplesBundle:ArrayExample:arrayexample.html.twig', 
            array( 'form' => $form->createView(), 
                   'id' => null,
                   'fields' => array_keys($arrayexample->getFormSetup()
            )));

    }

    /**
     * @Route("/{id}", name="arrayexample_update", requirements = { "_method"="post"})
     * @Template()
     */
    public function updateAction($id)
    {
      $arrayexample = new Model\ArrayExample;
      $form = $this->get('form.factory')->create( new Form\ArrayExampleForm(), $arrayexample);

      $request = $this->get('request');
      $form->handleRequest($request);

      if (!$form->isValid() )
      {
        error_log("Not valid");
      }
      if (!$form->isBound() )
      {
        error_log("Not bound");
      }

      $arrayexample->setId($id);

      $arrayexample_manager = $this->get('arrayexample_manager');

      if ($arrayexample_manager->save($arrayexample))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

      return $this->getAction($arrayexample->getId());

    }


    /**
     * @Route("/delete/{id}", name="arrayexample_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
      $arrayexample_manager = $this->get('arrayexample_manager');

      if ($arrayexample_manager->delete($id))
      {
        $this->get('session')->setFlash('notice', 'Sletta!');
      }

      return $this->indexAction();

    }

    /**
     * @Route("/", name="arrayexample_insert", requirements = { "_method"="post"})
     * @Template()
     */
    public function insertAction()
    {

      $arrayexample = new Model\ArrayExample;

      $form = $this->get('form.factory')->create( new Form\ArrayExampleForm(), $arrayexample);

      $request = $this->get('request');
      $form->handleRequest($request);

      if (!$form->isValid() )
      {
        error_log("Not valid");
      }
      if (!$form->isBound() )
      {
        error_log("Not bound");
      }

      $arrayexample_manager = $this->get('arrayexample_manager');

      if ($saved_arrayexample = $arrayexample_manager->save($arrayexample))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

error_log("id:" . $saved_arrayexample->getId());
      return $this->getAction($saved_arrayexample->getId());

    }


    public function putAction()
    {
        return $this->render('BisonLabExamplesBundle:ArrayExample:index.html.twig');
    }

    /**
     * @Route("/{key}/{value}", name="arrayexample_arrayexample")
     * @Template()
     */
    public function searchAction($key, $value)
    {
        $arrayexample_manager = $this->get('arrayexample_manager');

        $arrayexamples = $arrayexample_manager->findByKeyVal($key, $value);

        if (count($arrayexamples) == 1)
        {
          $arrayexample = $arrayexamples[0];
          $form = $this->get('form.factory')->create( new Form\ArrayExampleForm(), $arrayexample);

          return 
              $this->render('BisonLabExamplesBundle:ArrayExample:arrayexample.html.twig', 
              array( 'form' => $form->createView(), 'id' => $arrayexample->getId()
              ));
        }

        if (count($arrayexamples) > 1)
        {
          return 
              $this->render('BisonLabExamplesBundle:ArrayExample:list.html.twig', 
              array( 'arrayexamples' => $arrayexamples ));
        }

        if (count($arrayexamples) < 1)
        {
          throw new NotFoundHttpException("No ArrayExample with that value (".$value.")");
        }

    }

    /**
     * @Route("/{id}", name="arrayexample_get")
     * @Template()
     */
    public function getAction($id)
    {
        $arrayexample_manager = $this->get('arrayexample_manager');

error_log("id:" . $id);
        $arrayexample = $arrayexample_manager->findOneById($id);

        if (!$arrayexample)
        {
          throw new NotFoundHttpException("No ArrayExample with that id (".$id.")");
        }

        $form = $this->get('form.factory')->create( new Form\ArrayExampleForm(), $arrayexample);

        return $this->render('BisonLabExamplesBundle:ArrayExample:arrayexample.html.twig', 
              array( 'form' => $form->createView(), 
                    'id' => $arrayexample->getId(),
                    'fields' => array_keys($arrayexample->getFormSetup())
        ));
    }


}
