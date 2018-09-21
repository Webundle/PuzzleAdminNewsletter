<?php
namespace Puzzle\Admin\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\NewsletterBundle\Form\Type\TemplateCreateType;
use Puzzle\Admin\NewsletterBundle\Form\Type\TemplateUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class TemplateController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'content', 'eventName'];
    }
    
	/***
	 * List templates
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request){
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $templates = $apiClient->pull('/newsletter/templates');
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $templates = [];
        }
		
		return $this->render("PuzzleAdminNewsletterBundle:Template:list.html.twig",[
		    'templates' => $templates
		]);
	}
	
    /***
     * Create a new template
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, []);
        
        $form = $this->createForm(TemplateCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_newsletter_template_create')
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            try {
	            $postData = $form->getData();
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('post', '/newsletter/templates', $postData);
	            
	            if ($request->isXmlHttpRequest() === true) {
	                return new JsonResponse(true);
	            }
	            
	            $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
	        }catch (BadResponseException $e) {
	            /** @var EventDispatcher $dispatcher */
	            $dispatcher = $this->get('event_dispatcher');
	            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        }
            
            return $this->redirectToRoute('admin_newsletter_template_list');
        }
        
        return $this->render("PuzzleAdminNewsletterBundle:Template:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show template
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        $template = $apiClient->pull('/newsletter/templates/'.$id);
        
        return $this->render("PuzzleAdminNewsletterBundle:Template:show.html.twig", array(
            'template' => $template
        ));
    }
    
    /***
	 * Update template
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
	        $template = $apiClient->pull('/newsletter/templates/'.$id);
	    }catch (BadResponseException $e) {
	        /** @var EventDispatcher $dispatcher */
	        $dispatcher = $this->get('event_dispatcher');
	        $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
	        $template = [];
	    }
	    
	    $data = PuzzleApiObjectManager::hydratate($this->fields, $template);
	    $form = $this->createForm(TemplateUpdateType::class, $data, [
	        'method' => 'POST',
	        'action' => $this->generateUrl('admin_newsletter_template_update', ['id' => $id])
	    ]);
	    $form->handleRequest($request);
	    
	    if ($form->isSubmitted() === true && $form->isValid() === true) {
	        try {
	            $postData = $form->getData();
	            $postData = PuzzleApiObjectManager::sanitize($postData);
	            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
	            $apiClient = $this->get('puzzle_connect.api_client');
	            $apiClient->push('put', '/newsletter/templates/'.$id, $postData);
	            
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
	    
	    return $this->render('PuzzleAdminNewsletterBundle:Template:update.html.twig', [
	        'template'    => $template,
	        'form'     => $form->createView()
	    ]);
	}
	
	
	/**
	 * Delete a template
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @Security("has_role('ROLE_NEWSLETTER') or has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
	    try {
	        /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
    	    $apiClient = $this->get('puzzle_connect.api_client');
    	    $apiClient->push('delete', '/newsletter/templates/'.$id);
	        
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
	    
		return $this->redirectToRoute('admin_newsletter_template_list');
	}
}
