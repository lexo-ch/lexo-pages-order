<?php

namespace LEXO\PO\Core;

use LEXO\PO\Core\Abstracts\Singleton;
use LEXO\PO\Core\Plugin\PluginService;
use LEXO\PO\Core\Notices\Notices;
use LEXO\PO\Core\Plugin\Order;

use const LEXO\PO\{
    DOMAIN,
    PATH,
    LOCALES
};

class Bootloader extends Singleton
{
    protected static $instance = null;

    public function run()
    {
        $order = new Order();

        add_action('init', [$this, 'onInit'], 10);
        add_action(DOMAIN . '/localize/admin-po.js', [$this, 'onAdminCpJsLoad']);
        add_action('after_setup_theme', [$this, 'onAfterSetupTheme']);

        add_action('save_post', [$order, 'saveSubpageSettingsData']);
        add_action('transition_post_status', [$order, 'setNewPageMenuOrder'], 10, 3);
        add_action('post_updated', [$order, 'updateMenuOrderOnParentChange'], 10, 3);
    }

    public function onInit()
    {
        do_action(DOMAIN . '/init');

        $plugin_settings = PluginService::getInstance();
        $plugin_settings->setNamespace(DOMAIN);
        $plugin_settings->registerNamespace();
        $plugin_settings->addPluginLinks();
        $plugin_settings->noUpdatesNotice();
        $plugin_settings->updateSuccessNotice();

        (new Notices())->run();
    }

    public function onAdminCpJsLoad()
    {
        PluginService::getInstance()->addAdminLocalizedScripts();
    }

    public function onAfterSetupTheme()
    {
        $this->loadPluginTextdomain();
        // PluginService::getInstance()->updater()->run();
    }

    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(DOMAIN, false, trailingslashit(trailingslashit(basename(PATH)) . LOCALES));
    }
}
