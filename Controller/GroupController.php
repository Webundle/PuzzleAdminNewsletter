<?php

namespace Puzzle\Admin\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\NewsletterBundle\Form\Type\GroupCreateType;
use Puzzle\Admin\NewsletterBundle\Form\Type\GroupUpdateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * @author AGNES Gnagne Cedric <cecenho55gmail.com>
 */
class GroupController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'description'];
    }
    
	/***
	 * Show groups
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request, $current = null) {
	    try {
	        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	        $apiClient = $this->get('puzzle_connect.api_client');
	        $groups = $apiClient->pull('/newsletter/groups');
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        $groups = [];
	    }
		
		return $this->render('PuzzleAdminNewsletterBundle:Group:list.html.twig', [
		    'groups'  => $groups
		]);
	}
	
	/***
	 * Create group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function createAction(Request $request)
	{
	    $data = PuzzleApiObjectManager::hydratate($this->fields, []);
	    /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	    $apiClient = $this->get('puzzle_connect.api_client');
	    $subscribers = [];
	    
	    try {
    	    $items = $apiClient->pull('/newsletter/subscribers', ['fields' => 'email, id']);
    	    
    	    foreach ($items as $item) {
    	        $subscribers[$item['email']] = $item['id'];
    	    }
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	    }
	    
	    $form = $this->createForm(GroupCreateType::class, $data, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_newsletter_group_create')
	    ]);
	    $form ->add('subscribers', ChoiceType::class, array(
	        'translation_domain' => 'admin',
	        'label' => 'newsletter.subscriber.base',
	        'label_attr' => ['class' => 'form-label'],
	        'attr' => ['class' => 'select'],
	        'choices' => $subscribers,
	        'multiple' => true,
	        'required' => false
	    ));
	    
	    $form->handleRequest($request);
	       
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        try {
	            $postData = $form->getData();
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('post', '/newsletter/groups', $postData);
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            
	            if ($request->isXmlHttpRequest() === true) {
	                return new JsonResponse(true);
	            }
	            
	            $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
	        }catch (BadResponseException $e) {
	            /** @var EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        }
	        
	        return $this->redirectToRoute('admin_newsletter_group_list');
	    }
	    
	    return $this->render("PuzzleAdminNewsletterBundle:Group:create.html.twig", ['form' => $form->createView()]);
	}
	
	/***
	 * Show group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function showAction(Request $request, $id){
	    /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	    $apiClient = $this->get('puzzle_connect.api_client');
	    $group = $apiClient->pull('/newsletter/groups/'.$id);
	    
	    if (isset($group['subscribers'])) {
	        $subscribers = $apiClient->pull('/newsletter/subscribers', ['filter' => 'id=:'.implode(',', $group['subscribers'])]);
	    }else {
	        $subscribers = null;
	    }
	    
	    return $this->render('PuzzleAdminNewsletterBundle:Group:show.html.twig', array(
	        'group'        => $group,
	        'subscribers'  => $subscribers
	    ));
	}
	
	
	/***
	 * Update group
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function updateAction(Request $request, $id)
	{
	    /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	    $apiClient = $this->get('puzzle_connect.api_client');
	    $group = $subscribers = [];
	    try {
	        $group = $apiClient->pull('/newsletter/groups/'.$id);
	        $items = $apiClient->pull('/newsletter/subscribers', ['fields' => 'email,id']);
	        
	        
	        foreach ($items as $item) {
	            $subscribers[$item['email']] = $item['id'];
	        }
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	    }
	    
	    $data = PuzzleApiObjectManager::hydratate($this->fields, $group);
	    $form = $this->createForm(GroupCreateType::class, $data, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_newsletter_group_create')
	    ]);
	    $form ->add('subscribers', ChoiceType::class, array(
	        'translation_domain' => 'admin',
	        'label' => 'newsletter.subscriber.base',
	        'label_attr' => ['class' => 'form-label'],
	        'attr' => ['class' => 'select'],
	        'choices' => $subscribers,
	        'multiple' => true,
	        'required' => false
	    ));
	    $form->handleRequest($request);
	    
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        try {
	            $postData = $form->getData();
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('put', '/newsletter/groups/'.$id, $postData);
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            
	            if ($request->isXmlHttpRequest() === true) {
	                return new JsonResponse(true);
	            }
	            
	            $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
	        }catch (BadResponseException $e) {
	            /** @var EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        }
	        
	        return $this->redirectToRoute('admin_newsletter_group_list');
	    }
	    
	    return $this->render('PuzzleAdminNewsletterBundle:Group:update.html.twig', [
	        'group'    => $group,
	        'form'     => $form->createView()
	    ]);
	}
	
	
	/**
	 * Delete a group
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
	    try {
	        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	        $apiClient = $this->get('puzzle_connect.api_client');
	        $apiClient->push('delete', '/newsletter/groups/'.$id);
	        
	        if ($request->isXmlHttpRequest() === true) {
	            return new JsonResponse(['status' => true]);
	        }
	        
	        $this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        
	        if ($request->isXmlHttpRequest()) {
	            return $event->getResponse();
	        }
	    }
	    
		return $this->redirectToRoute('admin_newsletter_group_list');
	}
}
