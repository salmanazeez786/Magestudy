<?php

namespace Magestudy\Crud\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use Magestudy\Crud\Helper\Data;
use Magento\Framework\Model\AbstractModel;

abstract class AbstractEdit extends AbstractAction
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ObjectManagerInterface $objectManager
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam(Data::FRONTEND_ID);

        /** @var AbstractModel $model */
        if ($id) {
            try {
                $model = $this->_loadEditData($id);
            } catch (Exception $exception) {
                return $this->_getError();
            }
        } else {
            $model = $this->_createEditData();
        }

        $data = $this->_session->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register(strtolower($this->_getEntityTitle()), $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        $newTitle = __('New') . ' ' . $this->_getEntityTitle();
        $editTitle = __('Edit') . ' ' . $this->_getEntityTitle();

        $resultPage->addBreadcrumb(
            $id ? $editTitle : $newTitle,
            $id ? $editTitle : $newTitle
        );

        $resultPage->getConfig()
            ->getTitle()
            ->prepend($model->getId() ? $editTitle . ': ' . $this->getTitle($model) : $newTitle);
        return $resultPage;
    }

    /**
     * @param AbstractModel $model
     * @return string
     */
    abstract protected function getTitle($model);

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    protected function _getError()
    {
        $this->messageManager->addErrorMessage(__('This ' . strtolower($this->_getEntityTitle()) . ' no longer exists.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $manageTitle = __('Manage') . ' ' . $this->_getEntityTitle();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->addBreadcrumb($this->_getEntityTitle(), $this->_getEntityTitle());
        $resultPage->addBreadcrumb($manageTitle, $manageTitle);
        return $resultPage;
    }

    /**
     * @param int $id
     * @return AbstractModel
     */
    abstract protected function _loadEditData($id);

    /**
     * @return AbstractModel
     */
    abstract protected function _createEditData();
}