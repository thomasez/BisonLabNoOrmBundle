<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\SimpleDbBundle\Controller;

use RedpillLinpro\SimpleDbBundle\Form\BaseForm;
use RedpillLinpro\SimpleDbBundle\Model as Model;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContractController extends Controller
{
    /**
     * @Route("/", name="contract", requirements = { "_method"="get"})
     * @Template()
     */
    public function indexAction()
    {

        $contract_manager = $this->get('contract_manager');

        $contracts = $contract_manager->findAll();

        return $this->render('RedpillLinproSimpleDbBundle:Contract:list.html.twig', 
              array( 'contracts' => $contracts,
              ));

    }

    /**
     * @Route("/new", name="contract_new", requirements = { "_method"="get"})
     * @Template()
     */
    public function newAction()
    {

      $contract = new \RedpillLinpro\SimpleDbBundle\Model\Contract;
      $form = $this->get('form.factory')->create( new BaseForm(), $contract);

      return $this->render('RedpillLinproSimpleDbBundle:Contract:contract.html.twig', 
            array( 'form' => $form->createView(), 
                   'id' => null,
                   'fields' => array_keys($contract->getFormSetup()
            )));

    }

    /**
     * @Route("/{id}", name="contract_update", requirements = { "_method"="post"})
     * @Template()
     */
    public function updateAction($id)
    {
      $contract = new \RedpillLinpro\SimpleDbBundle\Model\Contract;
      $form = $this->get('form.factory')->create( new BaseForm(), $contract);

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

      $contract->setId($id);

      $contract_manager = $this->get('contract_manager');

      if ($contract_manager->save($contract))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

      return $this->getAction($contract->getId());

    }


    /**
     * @Route("/delete/{id}", name="contract_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
      $contract_manager = $this->get('contract_manager');

      if ($contract_manager->delete($id))
      {
        $this->get('session')->setFlash('notice', 'Sletta!');
      }

      return $this->indexAction();

    }

    /**
     * @Route("/", name="contract_insert", requirements = { "_method"="post"})
     * @Template()
     */
    public function insertAction()
    {

      $contract = new \RedpillLinpro\SimpleDbBundle\Model\Contract;

      $form = $this->get('form.factory')->create( new BaseForm(), $contract);

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

      $contract_manager = $this->get('contract_manager');

      if ($saved_contract = $contract_manager->save($contract))
      {
        $this->get('session')->setFlash('notice', 'Lagra!');
      }

      return $this->getAction($saved_contract->getId());

    }


    public function putAction()
    {
        return $this->render('RedpillLinproSimpleDbBundle:Contract:index.html.twig');
    }

    /**
     * @Route("/{key}/{value}", name="contract_contract")
     * @Template()
     */
    public function searchAction($key, $value)
    {
        $contract_manager = $this->get('contract_manager');

        $contracts = $contract_manager->findByKeyVal($key, $value);

        if (count($contracts) == 1)
        {
          $contract = $contracts[0];
          $form = $this->get('form.factory')->create( new BaseForm(), $contract);

          return 
              $this->render('RedpillLinproSimpleDbBundle:Contract:contract.html.twig', 
              array( 'form' => $form->createView(), 'id' => $contract->getId()
              ));
        }

        if (count($contracts) > 1)
        {
          return 
              $this->render('RedpillLinproSimpleDbBundle:Contract:list.html.twig', 
              array( 'contracts' => $contracts ));
        }

        if (count($contracts) < 1)
        {
          throw new NotFoundHttpException("No Contract with that value (".$value.")");
        }

    }

    /**
     * @Route("/{id}", name="contract_get")
     * @Template()
     */
    public function getAction($id)
    {
        $contract_manager = $this->get('contract_manager');

        $contract = $contract_manager->findOneById($id);

        if (!$contract)
        {
          throw new NotFoundHttpException("No Contract with that id (".$id.")");
        }

        $form = $this->get('form.factory')->create( new BaseForm(), $contract);

        return $this->render('RedpillLinproSimpleDbBundle:Contract:contract.html.twig', 
              array( 'form' => $form->createView(), 
                    'id' => $contract->getId(),
                    'fields' => array_keys($contract->getFormSetup())
        ));
    }


}
