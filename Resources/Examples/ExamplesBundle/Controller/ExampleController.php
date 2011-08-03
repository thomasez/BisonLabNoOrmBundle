<?php

namespace RedpillLinpro\ExamplesBundle\Controller;

use RedpillLinpro\ExamplesBundle\Model as Model;
use RedpillLinpro\ExamplesBundle\Form as Form;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ExampleController extends BaseController
{

    private static $_manager = 'example_manager';

    /**
     * @Route("/", name="example", requirements = { "_method"="get"})
     * @Template()
     */
    public function indexAction()
    {

        $example_manager = $this->get('example_manager');

        $examples = $example_manager->findAll();

        return $this->render('RedpillLinproExamplesBundle:Example:list.html.twig', 
              array( 'examples' => $examples,
              ));

    }

    /**
     * @Route("/new", name="example_new", requirements = { "_method"="get"})
     * @Template()
     */
    public function newAction()
    {

      $example = new Model\Example;
      $form = $this->get('form.factory')->create( new Form\ExampleForm(), $example);

      return $this->render('RedpillLinproExamplesBundle:Example:example.html.twig', 
            array( 'form' => $form->createView(), 
                   'id' => null,
                   'fields' => array_keys($example->getFormSetup()
            )));

    }

    /**
     * @Route("/{id}", name="example_update", requirements = { "_method"="post"})
     * @Template()
     */
    public function updateAction($id)
    {
      $example = new Model\Example;
      $form = $this->get('form.factory')->create( new Form\ExampleForm(), $example);

      $request = $this->get('request');
      $form->bindRequest($request);

      if ($form->isValid() )
      {
        error_log("Valid");
      }
      if ($form->isBound() )
      {
        error_log("Bound");
      }

      $example->setId($id);

      $example_manager = $this->get('example_manager');

      if ($example_manager->save($example))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

      return $this->getAction($example->getId());

    }


    /**
     * @Route("/delete/{id}", name="example_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
      $example_manager = $this->get('example_manager');

      if ($example_manager->delete($id))
      {
        $this->get('session')->setFlash('notice', 'Sletta!');
      }

      return $this->indexAction();

    }

    /**
     * @Route("/", name="example_insert", requirements = { "_method"="post"})
     * @Template()
     */
    public function insertAction()
    {

      $example = new Model\Example;

      $form = $this->get('form.factory')->create( new Form\ExampleForm(), $example);

      $request = $this->get('request');
      $form->bindRequest($request);

      if ($form->isValid() )
      {
        error_log("Valid");
      }
      if ($form->isBound() )
      {
        error_log("Bound");
      }

      $example_manager = $this->get('example_manager');

      if ($saved_example = $example_manager->save($example))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

      return $this->getAction($saved_example->getId());

    }


    public function putAction()
    {
        return $this->render('RedpillLinproExamplesBundle:Example:index.html.twig');
    }

    /**
     * @Route("/{key}/{value}", name="example_example")
     * @Template()
     */
    public function searchAction($key, $value)
    {
        $example_manager = $this->get('example_manager');

        $examples = $example_manager->findByKeyVal($key, $value);

        if (count($examples) == 1)
        {
          $example = $examples[0];
          $form = $this->get('form.factory')->create( new Form\ExampleForm(), $example);

          return 
              $this->render('RedpillLinproExamplesBundle:Example:example.html.twig', 
              array( 'form' => $form->createView(), 'id' => $example->getId()
              ));
        }

        if (count($examples) > 1)
        {
          return 
              $this->render('RedpillLinproExamplesBundle:Example:list.html.twig', 
              array( 'examples' => $examples ));
        }

        if (count($examples) < 1)
        {
          throw new NotFoundHttpException("No Example with that value (".$value.")");
        }

    }

    /**
     * @Route("/{id}", name="example_get")
     * @Template()
     */
    public function getAction($id)
    {
        $example_manager = $this->get('example_manager');

        $example = $example_manager->findOneById($id);

        if (!$example)
        {
          throw new NotFoundHttpException("No Example with that id (".$id.")");
        }

        $form = $this->get('form.factory')->create( new Form\ExampleForm(), $example);

        return $this->render('RedpillLinproExamplesBundle:Example:example.html.twig', 
              array( 'form' => $form->createView(), 
                    'id' => $example->getId(),
                    'fields' => array_keys($example->getFormSetup())
        ));
    }


}
