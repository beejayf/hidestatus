<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Hidestatus extends Module
{
    public function __construct()
    {
        $this->name = 'hidestatus';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Björn Foldenauer';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Hide Status Options');
        $this->description = $this->l('Versteckt bestimmte Statusoptionen im Backend basierend auf Suchbegriffen.');
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_'.$this->name)) {
            $searchTerm = Tools::getValue('HIDESTATUS_SEARCH');
            Configuration::updateValue('HIDESTATUS_SEARCH', $searchTerm);
            Configuration::updateValue('HIDESTATUS_FILTER_DETAILS', (int)Tools::getValue('HIDESTATUS_FILTER_DETAILS'));
            $output .= $this->displayConfirmation($this->l('Suchbegriff gespeichert.'));
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Einstellungen'),
            ],
            'input' => [
                [
                    'type' => 'html',
                    'label' => $this->l(''),
                    'name' => 'HIDESTATUS_GREETING',
                    'html_content' => '<p>Hallo und herzlich willkommen im HideStatus-Modul!</p> <p>Dieses kleine Helferlein blendet alle 
                        Bestell-Stati aus, in denen einer der Suchbegriffe vorkommt. mehrere Begriffe einfach mit einem Komma trennen. 
                        Dies wirkt in den Bestelldetails - nicht in der Übersicht - so können alle Werte gesetzt werden, und beim Abarbeiten 
                        von Bestellungen seht ihr nur die für Euch wichtigen Stati.</p> <p>Und klar:
                        Leer lassen heißt nichts verstecken 😄</p><br /><strong>Hallo von Björn 👋<br /><br /></strong>'
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Suchbegriff zum Ausblenden'),
                    'name' => 'HIDESTATUS_SEARCH',
                    'size' => 20,
                    'required' => false
                ],
		[
                    'type' => 'switch',
                    'label' => $this->l('Filter aktivieren'),
                    'name' => 'HIDESTATUS_FILTER_DETAILS',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Ja')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Nein')],
                    ]
                 ]
            ],
                'submit' => [
                'title' => $this->l('Speichern'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submit_'.$this->name;
        $helper->fields_value = [
            'HIDESTATUS_SEARCH' => Configuration::get('HIDESTATUS_SEARCH'),
            'HIDESTATUS_GREETING' => Configuration::get('HIDESTATUS_GREETING'),
            'HIDESTATUS_FILTER_DETAILS' => Configuration::get('HIDESTATUS_FILTER_DETAILS')
        ];

        return $helper->generateForm($fields_form);
    }



    public function hookDisplayBackOfficeHeader()
    {
        // Nur aktiv auf der Bestell-Detailseite
        $searchTerm = Configuration::get('HIDESTATUS_SEARCH');
        $filterDetails = Configuration::get('HIDESTATUS_FILTER_DETAILS');
        //$controller = Tools::getValue('controller');
        $controller = $this->context->controller->controller_name;
        if (!empty($searchTerm)) {
            if ( $controller == 'AdminOrders' && $filterDetails ) {
                $terms = explode(',', $searchTerm);
                $terms = array_map('trim', $terms);

                // Übergabe der Begriffe an JavaScript
                Media::addJsDef([
                    'hidestatus_terms' => $terms
                ]);

                // JS-Datei einbinden
                $this->context->controller->addJS($this->_path.'views/js/hidestatus.js');
            }
	}
    }
}

