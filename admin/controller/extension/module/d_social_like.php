<?php
class ControllerExtensionModuleDSocialLike extends Controller {

    private $codename = 'd_social_like';
    private $route = 'extension/module/d_social_like';
    private $config_file = 'd_social_like';
    private $error = array();
    private $store_id = -1;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->d_shopunity = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_shopunity.json'));
        $this->d_opencart_patch = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_opencart_patch.json'));
        $this->d_admin_style = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_admin_style.json'));
        $this->extension = json_decode(file_get_contents(DIR_SYSTEM.'library/d_shopunity/extension/'.$this->codename.'.json'), true);
        $this->d_twig_manager = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_twig_manager.json'));
        $this->d_validator = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_validator.json'));
        $this->d_social_like_pro = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_social_like_pro.json'));
        if (isset($this->request->get['store_id'])) {
            $data['store_id'] = $this->request->get['store_id'];
        }
    }

    public function index()
    {

        if($this->d_shopunity){
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->validateDependencies($this->codename);
        }

        if ($this->d_twig_manager) {
            $this->load->model('extension/module/d_twig_manager');
            $this->model_extension_module_d_twig_manager->installCompatibility();
        }

        if ($this->d_admin_style) {
            $this->load->model('extension/d_admin_style/style');
            $this->model_extension_d_admin_style_style->getStyles('light');
        }

        if ($this->d_validator) {
            $this->load->model('extension/d_shopunity/d_validator');
            $this->model_extension_d_shopunity_d_validator->installCompatibility();
        }

        if (isset($this->request->get['module_id'])) {
            $module_id = $this->request->get['module_id'];
        }else{
            $module_id = 0;
        }

        $this->load->language($this->route);
        $this->load->model($this->route);
        $this->load->model('setting/setting');
        $this->load->model('extension/d_opencart_patch/module');
        $this->load->model('extension/d_opencart_patch/url');
        $this->load->model('extension/d_opencart_patch/load');
        $this->load->model('extension/d_opencart_patch/user');
        $this->load->model('extension/d_opencart_patch/cache');

        $this->model_extension_d_opencart_patch_cache->clearTwig();

        // Styles and scripts
        $this->document->addStyle('view/stylesheet/d_bootstrap_extra/bootstrap.css');
        $this->document->addScript('view/javascript/d_tinysort/tinysort.js');
        $this->document->addScript('view/javascript/d_tinysort/jquery.tinysort.min.js');
        $this->document->addScript('view/javascript/d_rubaxa_sortable/sortable.js');
        $this->document->addStyle('view/javascript/d_rubaxa_sortable/sortable.css');
        $this->document->addStyle('view/javascript/d_bootstrap_colorpicker/css/bootstrap-colorpicker.css');
        $this->document->addScript('view/javascript/d_bootstrap_colorpicker/js/bootstrap-colorpicker.js');
        $this->document->addStyle('view/stylesheet/d_social_like.css');

        $url = '';

        if(isset($this->request->get['module_id'])){
            $url .=  '&module_id='.$module_id;
        }

        if(isset($this->request->get['store_id'])){
            $url .=  '&store_id='.$this->request->get['store_id'];
        }

        // Heading
        $this->document->setTitle($this->language->get('heading_title_main'));
        $data['heading_title'] = $this->language->get('heading_title_main');
        $data['text_edit'] = $this->language->get('text_edit');

        // Variable
        $data['codename'] = $this->codename;
        $data['route'] = $this->route;
        $data['module_id'] = $module_id;
        $data['config'] = $this->config_file;
        $data['pro'] = $this->d_social_like_pro;
        $data['stores'] = $this->model_extension_module_d_social_like->getStores();
        $data['version'] = $this->extension['version'];
        $data['d_shopunity'] = $this->d_shopunity;
        $data['store_id'] = $this->store_id;

        $data['token'] = $this->model_extension_d_opencart_patch_user->getToken();

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['server'] = HTTPS_SERVER;
            $data['catalog'] = HTTPS_CATALOG;
        } else {
            $data['server'] = HTTP_SERVER;
            $data['catalog'] = HTTP_CATALOG;
        }

        // Action
        $data['module_link'] = $this->model_extension_d_opencart_patch_url->link($this->route);
        $data['action'] = $this->model_extension_d_opencart_patch_url->link($this->route . '/save', $url);
        $data['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionLink('module');
        $data['get_cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionAjax('module');


        // Tab
        $data['text_settings'] = $this->language->get('text_settings');
        $data['text_design'] = $this->language->get('text_design');
        $data['text_instructions'] = $this->language->get('text_instructions');
        $data['text_instructions_full'] = $this->language->get('text_instructions_full');

        // Button
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Entry
        $data['text_setup'] = $this->language->get('text_setup');
        $data['setup'] = $this->isSetup();
        $data['setup_link'] = $this->model_extension_d_opencart_patch_url->ajax($this->route . '/setup');
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_language'] = $this->language->get('entry_language');
        $data['entry_store'] = $this->language->get('entry_store');
        $data['entry_product'] = $this->language->get('entry_product');
        $data['entry_config_files'] = $this->language->get('entry_config_files');
        $data['entry_view'] = $this->language->get('entry_view');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['text_powered_by'] = $this->language->get('text_powered_by');
        $data['text_ticket'] = $this->language->get('text_ticket');
        $data['entry_ticket'] = $this->language->get('entry_ticket');

        $data['entry_icon_theme'] = $this->language->get('entry_icon_theme');
        $data['entry_icon_color'] = $this->language->get('entry_icon_color');
        $data['entry_icon_color_active'] = $this->language->get('entry_icon_color_active');
        $data['entry_background_color'] = $this->language->get('entry_background_color');
        $data['entry_background_color_active'] = $this->language->get('entry_background_color_active');
        $data['entry_api'] = $this->language->get('entry_api');
        $data['entry_width'] = $this->language->get('entry_width');
        $data['entry_border'] = $this->language->get('entry_border');
        $data['entry_border_color'] = $this->language->get('entry_border_color');
        $data['entry_box_shadow_color'] = $this->language->get('entry_box_shadow_color');
        $data['entry_box_shadow'] = $this->language->get('entry_box_shadow');
        $data['entry_border_radius'] = $this->language->get('entry_border_radius');
        $data['entry_popup_mobile'] = $this->language->get('entry_popup_mobile');
        $data['entry_custom_style'] = $this->language->get('entry_custom_style');

        // Help
        $data['help_width'] = $this->language->get('help_width');
        $data['help_config_files'] = $this->language->get('help_config_files');
        $data['help_icon_theme'] = $this->language->get('help_icon_theme');
        $data['help_api'] = $this->language->get('help_api');
        $data['help_custom_style'] = $this->language->get('help_custom_style');
        $data['help_url'] = $this->language->get('help_url');

        // Text
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_pro'] = $this->language->get('text_pro');

        $data['text_sort_order'] = $this->language->get('text_sort_order');

        // Notification
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->model_extension_d_opencart_patch_url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_modules'),
            'href' => $this->model_extension_d_opencart_patch_url->getExtensionLink('module')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->model_extension_d_opencart_patch_url->link($this->route, $url)
        );

        //setting
        $data['setting'] = $this->getSetting();

        if (isset($this->request->get['store_id'])) {
            $data['store_id'] = $this->request->get['store_id'];
        }elseif(isset($data['setting']['store_id'])){
            $data['store_id'] = $data['setting']['store_id'];
        }else{
            $data['store_id'] = $this->store_id;
        }

        $data['icon_themes'] = $this->getIconThemes();

        //Get views
        $data['views'] = array(
            0 => array('view_id' => 'left', 'name' => $this->language->get('text_view_left')),
            1 => array('view_id' => 'top', 'name' => $this->language->get('text_view_top')),
            2 => array('view_id' => 'right', 'name' => $this->language->get('text_view_right')),
            3 => array('view_id' => 'bottom', 'name' => $this->language->get('text_view_bottom')),
            4 => array('view_id' => 'inline', 'name' => $this->language->get('text_view_inline'))
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->model_extension_d_opencart_patch_load->view($this->route, $data));
    }

    public function getSetting(){
        $key = $this->codename.'_setting';

        if ($this->config_file) {
            $this->config->load($this->config_file);
        }

        if (isset($this->request->get['module_id'])) {
            $module_id = $this->request->get['module_id'];
        }else{
            $module_id = 0;
        }

        $result = ($this->config->get($key)) ? $this->config->get($key) : array();

        if (!isset($this->request->post['config'])) {

            $this->load->model('setting/setting');
            if (isset($this->request->post[$key])) {
                $setting = $this->request->post;

            } elseif ($this->model_extension_d_opencart_patch_module->getModule($module_id)) {
                $setting[$key] = $this->model_extension_d_opencart_patch_module->getModule($module_id);
            }

            if (isset($setting[$key])) {
                foreach ($setting[$key] as $key => $value) {
                    $result[$key] = $value;
                }
            }
        }
        //get social like settings
        $social_logins = array();
        foreach(glob(DIR_CONFIG.'d_social_like/*.php') as $file) {
            $social_logins[] = substr(basename($file), 0, -4);
        }

        foreach($social_logins as $social_like_id){
            if(!isset($result['social_likes'][$social_like_id])){
                $this->config->load('d_social_like/'.$social_like_id);
                if($this->config->get('d_social_like_'.$social_like_id)){
                    $result['social_likes'][$social_like_id] = $this->config->get('d_social_like_'.$social_like_id);
                }
            }
        }

        foreach($result['social_likes'] as $social_like_id => $social_like){
            if(!in_array($social_like_id, $social_logins)){
                unset($result['social_likes'][$social_like_id]);
            }
        }
        return $result;
    }

    private function getIconThemes(){
        $icon_themes = array();
        foreach(glob(DIR_CATALOG.'view/theme/default/image/d_social_like/*') as $file) {
            $icon_themes[] = basename($file, GLOB_ONLYDIR);
        }
        return $icon_themes;
    }

    public function setup()
    {
        $this->load->language($this->route);
        $this->load->model('extension/module/d_social_like');
        $this->load->model('extension/d_opencart_patch/module');
        $this->load->model('extension/d_opencart_patch/url');
        $data = $this->getSetting();
        $data['name'] = 'd_social_like';
        $data['status'] = 1;
        $this->model_extension_d_opencart_patch_module->addModule($this->codename, $data);
        $module_id = $this->db->getLastId();

        $this->model_extension_module_d_social_like->addToLayoutFromSetup($module_id);

        $this->session->data['success'] = $this->language->get('success_setup');
        $this->response->redirect($this->model_extension_d_opencart_patch_url->link($this->route.'&module_id='.$module_id));
    }

    public function install()
    {
        if ($this->d_shopunity) {
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->installDependencies($this->codename);
        }
    }

    public function isSetup()
    {
        $this->load->model('extension/d_opencart_patch/module');
        $module = $this->model_extension_d_opencart_patch_module->getModulesByCode('d_social_like');
        if ($module) {
            return true;
        }
        return false;
    }

    public function save()
    {
        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('setting/setting');
        $this->load->model('extension/d_opencart_patch/module');

        if (isset($this->request->get['module_id'])) {
            $module_id = $this->request->get['module_id'];
        } else {
            $module_id = 0;
        }

        $json['success'] = '';
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            if (!$module_id) {
                $this->model_extension_d_opencart_patch_module->addModule($this->codename, $this->request->post[$this->codename.'_setting']);
            } else {
                $this->model_extension_d_opencart_patch_module->editModule($module_id, $this->request->post[$this->codename.'_setting']);
            }
            $json['success'] = $this->language->get('text_success');
        }

        if (isset($this->request->get['exit'])) {
            if ($json['success']) {
                $this->session->data['success'] = $json['success'];
            } else {
                unset($this->session->data['success']);
            }
        }

        $json['error'] = $this->error;

        $this->response->setOutput(json_encode($json));
    }

    private function validate($permission = 'modify')
    {
        if (isset($this->request->post['config'])) {
            return false;
        }

        $this->language->load($this->route);

        if (!$this->user->hasPermission($permission, $this->route)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        $setting = $this->request->post[$this->codename.'_setting'];

        if ((utf8_strlen($setting['name']) < 3) || (utf8_strlen($setting['name']) > 64)) {
            $this->error['warning'] = $this->language->get('error_warning');
            $this->error['name'] = $this->language->get('error_name');
            return false;
        }

        return true;
    }
}
?>
