<?php

/**
 * Controller for bidding a project
 * @author Sergey Mitroshin <sergeymitr@gmail.com>
 *
 */
class ProposalController extends Zend_Controller_Action
{
    
    /**
     * bidding a project
     * @param int $projectid
     * @throws Zend_Controller_Action_Exception
     */
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            $this->_redirect('/registration');
        }
        
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $id = (int)$this->_getParam('projectid');
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Empty project ID', 404);
        }
        
        $projectModel = new Application_Model_DbTable_Projects();
        $project = $projectModel->find($id)->current();
        if (null === $project) {
            throw new Zend_Controller_Action_Exception("Project '{$id}' is not found", 404);
        }
        
        /*if (!$account->isPaid() && $account->getBidsCount() >= Application_Model_DbTable_Bids::BIDS_FREE_LIMIT) {
            $this->_redirect('/payment/limit');
        }*/
        
        if (!$account->isTrial() && !$account->isPaid()) {
            $this->_redirect('/payment/limit');
        }
        
        $account->bid($project->id);
        
        $platformModel = new Application_Model_DbTable_Platforms();
        $platform = $platformModel->find($project->platform_id)->current();
        $project->setBidUrlTemplate($platform->bid_url);
        
        //$connections = $account->getConnections();
        $isConnected = true; //in_array($project->platform_id, $connections);
        if ($isConnected) {
            $this->_helper->layout->setLayout('ajaxlayout');
        }
        
        $this->view->isConnected = $isConnected;
        $this->view->project = $project;
        $this->view->platform = $platform;
    }
    
}