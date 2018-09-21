<?php

namespace Puzzle\Admin\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\NewsletterBundle\Form\Type\SubscriberCreateType;
use Puzzle\Admin\NewsletterBundle\Form\Type\SubscriberUpdateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55gmail.com>
 * 
 */
class SubscriberController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'description'];
    }
    
	/***
	 * Show subscribers
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request, $current = null) {
	    try {
	        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	        $apiClient = $this->get('puzzle_connect.api_client');
	        $subscribers = $apiClient->pull('/newsletter/subscribers');
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        $subscribers = [];
	    }
	    
		return $this->render('PuzzleAdminNewsletterBundle:Subscriber:list.html.twig', [
		    'subscribers'  => $subscribers
		]);
	}
	
	/***
	 * Create subscriber
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function createAction(Request $request)
	{
	    $data = PuzzleApiObjectManager::hydratate($this->fields, []);
	    
	    $form = $this->createForm(SubscriberCreateType::class, $data, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_newsletter_subscriber_create')
	    ]);
	    $form->handleRequest($request);
	       
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        try {
	            $postData = $form->getData();
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('post', '/newsletter/subscribers', $postData);
	            
	            if ($request->isXmlHttpRequest() === true) {
	                return new JsonResponse(true);
	            }
	            
	            $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
	        }catch (BadResponseException $e) {
	            /** @var EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        }
	        
	        return $this->redirectToRoute('admin_newsletter_subscriber_list');
	    }
	    
	    return $this->render("PuzzleAdminNewsletterBundle:Subscriber:create.html.twig", ['form' => $form->createView()]);
	}
	
	/***
	 * Update subscriber
	 *
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function updateAction(Request $request, $id)
	{
	    /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	    $apiClient = $this->get('puzzle_connect.api_client');
	    
	    try {
	        $subscriber = $apiClient->pull('/newsletter/subscribers/'.$id);
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        $subscriber = [];
	    }
	    
	    $data = PuzzleApiObjectManager::hydratate($this->fields, $subscriber);
	    $form = $this->createForm(SubscriberUpdateType::class, $data, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_newsletter_subscriber_update', ['id' => $id])
	    ]);
	    $form->handleRequest($request);
	    
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        try {
	            $postData = $form->getData();
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('put', '/newsletter/subscribers/'.$id, $postData);
	            
	            if ($request->isXmlHttpRequest() === true) {
	                return new JsonResponse(true);
	            }
	            
	            $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
	        }catch (BadResponseException $e) {
	            /** @var EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        }
	    }
	    
	    return $this->render('PuzzleAdminNewsletterBundle:Subscriber:update.html.twig', [
	        'subscriber'    => $subscriber,
	        'form'     => $form->createView()
	    ]);
	}
	
	
	/**
	 * Delete a subscriber
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
	    try {
	        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    	    $apiClient = $this->get('puzzle_connect.api_client');
    	    $apiClient->push('delete', '/newsletter/subscribers/'.$id);
	        
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
	    
		return $this->redirectToRoute('admin_newsletter_subscriber_list');
	}
}
