<?php
namespace Opencart\Admin\Controller\Extension\Consentmanager\Module;

class Consentmanager extends \Opencart\System\Engine\Controller {

    private $error = array();

    public function index()
    {
        $this->load->language('extension/consentmanager/module/consentmanager');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_consentmanager', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['cmp_id'])) {
            $data['error_cmp_id'] = $this->error['cmp_id'];
        } else {
            $data['error_cmp_id'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/consentmanager/module/consentmanager', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/consentmanager/module/consentmanager', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['module_consentmanager_cmp_id'])) {
            $data['module_consentmanager_cmp_id'] = $this->request->post['module_consentmanager_cmp_id'];
        } else {
            $data['module_consentmanager_cmp_id'] = $this->config->get('module_consentmanager_cmp_id');
        }

        if (isset($this->request->post['module_consentmanager_mode'])) {
            $data['module_consentmanager_mode'] = $this->request->post['module_consentmanager_mode'];
        } else {
            $data['module_consentmanager_mode'] = $this->config->get('module_consentmanager_mode');
        }

        if (isset($this->request->post['module_consentmanager_custom_html'])) {
            $data['module_consentmanager_custom_html'] = $this->request->post['module_consentmanager_custom_html'];
        } else {
            $data['module_consentmanager_custom_html'] = $this->config->get('module_consentmanager_custom_html');
        }

        if (isset($this->request->post['module_consentmanager_status'])) {
            $data['module_consentmanager_status'] = $this->request->post['module_consentmanager_status'];
        } else {
            $data['module_consentmanager_status'] = $this->config->get('module_consentmanager_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/consentmanager/module/consentmanager', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/consentmanager/module/consentmanager'))
        {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (isset($this->request->post['module_consentmanager_cmp_id']) && !$this->request->post['module_consentmanager_cmp_id'])
        {
            $this->error['cmp_id'] = $this->language->get('error_cmp_id');
        }
        else if (is_numeric($this->request->post['module_consentmanager_cmp_id']))
        {
            $this->error['cmp_id'] = $this->language->get('error_cmp_id_numeric');
        }
        else if (!is_string($this->request->post['module_consentmanager_cmp_id']))
        {
            $this->error['cmp_id'] = $this->language->get('error_cmp_id_numeric');
        }
        else if ($this->request->post['module_consentmanager_cmp_id'] == '')
        {
            $this->error['cmp_id'] = $this->language->get('error_cmp_id_numeric');
        }
        return !$this->error;
    }

    public function install()
    {
        $this->load->model('setting/event');
		
		$eventData = [
            'code' => 'module_consentmanager_catalog_common_header',
            'description' => 'To add CMD Script',
            'trigger' => 'catalog/view/common/header/after',
            'action' => $this->getCompatibleRoute('extension/consentmanager/module/consentmanager', 'update_header'),
            'status' => 1,
            'sort_order' => 1
        ];
		
		$this->model_setting_event->addEvent($eventData);
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('module_consentmanager');
    }
	
	public function isVersion402()
    {
        $status = true;
        
        if (VERSION == '4.0.0.0' || VERSION == '4.0.1.0' || VERSION == '4.0.1.1') {
            $status = false;
        }
        
        return $status;
    }
    
    public function getCompatibleRoute($route, $method)
    {
        if ($this->isVersion402()) {
            return $route.'.'.$method;
        } else {
            return $route.'|'.$method;
        }
    }
}
