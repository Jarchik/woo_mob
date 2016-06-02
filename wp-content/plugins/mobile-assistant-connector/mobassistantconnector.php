<?php
/*
Plugin Name: Mobile Assistant Connector
Plugin URI: http://woocommerce-manager.com
Description:  This plugin allows you to keep your online business under control wherever you are. All you need is just to have on hand your android mobile phone and Internet connection.
Author: eMagicOne
Author URI: http://woocommerce-manager.com
Version: 1.1.1
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

/*-----------------------------------------------------------------------------+
| eMagicOne                                                                                                                                          |
| Copyright (c) 2015 eMagicOne.com <contact@emagicone.com>		                                              |
| All rights reserved                                                                                                                               |
+------------------------------------------------------------------------------+
|                                                                                                                                                             |
| Mobile Assistant Connector					                                                                              |
|                                                                                                                                                             |
| Developed by eMagicOne,                                                                                                                    |
| Copyright (c) 2015                                            	                                                                               |
+-----------------------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */

if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins'))))
{
    if (!class_exists('ma_connector'))
    {
        register_activation_hook( __FILE__, array( 'ma_connector','mobileassistantconnector_activation' ));
        register_deactivation_hook( __FILE__, array( 'ma_connector','mobileassistantconnector_deactivate' ));
        register_uninstall_hook(__FILE__, array( 'ma_connector','mobileassistantconnector_uninstall'));

        define('PUSH_TYPE_NEW_ORDER', 'new_order');
        define('PUSH_TYPE_CHANGE_ORDER_STATUS', 'order_changed');
        define('PUSH_TYPE_NEW_CUSTOMER', 'new_customer');
        define('MOBASSIST_DEBUG_MODE', false);
        define('MOBASSIS_KEY', 'mobileassistantconnector');

        class ma_connector
        {
            var $accountEmails = array();

            public static function mobileassistantconnector_activation()
            {
                if (!current_user_can('activate_plugins')) {
                    return;
                }

                $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
                check_admin_referer("activate-plugin_{$plugin}");
            }

            public static function mobileassistantconnector_deactivate()
            {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
                check_admin_referer("deactivate-plugin_{$plugin}");

                remove_filter('query_vars', 'mobileassistantconnector_add_query_vars');
                remove_action('template_redirect', 'mobileassistantconnector_check_vars');
                remove_action('woocommerce_checkout_update_order_meta', 'mobassist_push_new_order');

                //exit( var_dump( $_GET ) );
            }

            public static function mobileassistantconnector_uninstall()
            {
                if (!current_user_can('activate_plugins')) {
                    return;
                }
                check_admin_referer('bulk-plugins');

                // Important: Check if the file is the one
                // that was registered during the uninstall hook.
                if (__FILE__ != WP_UNINSTALL_PLUGIN) {
                    return;
                }

                # Uncomment the following line to see the function in action
                //exit( var_dump( $_GET ) );
            }


            public function __construct()
            {
                $this->check_db();
                $this->checkDBFields();
                $this->insertDefaultUser();
//                $this->movePushDevices();

                if (isset($_GET['page']) && $_GET['page'] == 'connector') {
                    add_action('admin_enqueue_scripts', array(&$this, 'ema_option_styles'));
                    add_action('admin_enqueue_scripts', array(&$this, 'ema_option_scripts'));
                }

                add_filter('query_vars', array(&$this, 'add_query_vars'));
                add_action('template_redirect', array(&$this, 'the_template'));

                add_action('woocommerce_checkout_update_order_meta', 'mobassist_push_new_order');
                add_action('woocommerce_order_status_changed', 'mobassist_push_change_status');
                add_action('woocommerce_created_customer', 'mobassist_push_new_customer');

                $plugin = plugin_basename(__FILE__);
                add_filter("plugin_action_links_$plugin", array(&$this, 'setting_link'));
            }

            private function checkDBFields()
            {
                global $wpdb;

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_push_settings`
                    WHERE FIELD = %s", 'device_unique_id'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_push_settings` ADD COLUMN `device_unique_id` INT(10)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_push_settings`
                    WHERE FIELD = %s", 'status'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_push_settings` ADD COLUMN `status` TINYINT DEFAULT 1"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_push_settings`
                    WHERE FIELD = %s", 'user_id'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_push_settings` ADD COLUMN `user_id` INT(10)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_push_settings`
                    WHERE FIELD = %s", 'push_currency_code'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_push_settings` ADD COLUMN `push_currency_code` VARCHAR(5)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_devices`
                    WHERE FIELD = %s", 'account_email'
                    )
                );
                if ($is_exists_column) {

                    $account_emails = $wpdb->get_results(
                        "SELECT account_email, device_unique_id FROM `{$wpdb->prefix}mobileassistant_devices`", ARRAY_A
                    );

                    $this->accountEmails = $account_emails;
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_devices` CHANGE account_email account_id INT(10)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_devices`
                    WHERE FIELD = %s", 'account_id'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_devices` ADD COLUMN `account_id` INT(10)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW INDEX FROM `{$wpdb->prefix}mobileassistant_devices`
                    WHERE `key_name` = %s", 'UNQ_MOB_DEV_UNQ'
                    )
                );
                if ($is_exists_column) {
                    $wpdb->query("ALTER TABLE `{$wpdb->prefix}mobileassistant_devices` DROP INDEX UNQ_MOB_DEV_UNQ");
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW INDEX FROM `{$wpdb->prefix}mobileassistant_devices`
                    WHERE `key_name` = %s", 'UNQ_MOB_DEV_ID'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_devices` ADD CONSTRAINT UNIQUE KEY UNQ_MOB_DEV_ID (`device_unique`, `account_id`)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_users`
                    WHERE FIELD = %s", 'username'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_users` ADD COLUMN `username` VARCHAR(100)"
                    );
                }

                $is_exists_column = $wpdb->get_results(
                    $wpdb->prepare(
                        "SHOW COLUMNS FROM `{$wpdb->prefix}mobileassistant_session_keys`
                    WHERE FIELD = %s", 'user_id'
                    )
                );
                if (!$is_exists_column) {
                    $wpdb->query("TRUNCATE TABLE `{$wpdb->prefix}mobileassistant_session_keys`");
                    $wpdb->query(
                        "ALTER TABLE `{$wpdb->prefix}mobileassistant_session_keys` ADD COLUMN `user_id` INT(10)"
                    );
                }

            }

            private function check_db()
            {
                global $wpdb;

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_push_settings` (
                        `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                        `registration_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                        `user_id` INT(10),
                        `app_connection_id` int(5) NOT NULL,
                        `push_new_order` tinyint(1) NOT NULL DEFAULT '0',
                        `push_order_statuses` text COLLATE utf8_unicode_ci NOT NULL,
                        `push_new_customer` tinyint(1) NOT NULL DEFAULT '0',
                        `push_currency_code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
                        `device_unique_id` INT(10),
                        `status` TINYINT DEFAULT 1,
                        PRIMARY KEY (`setting_id`))
                "
                );

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_devices` (
                        `device_unique_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `device_unique` VARCHAR(100),
                        `account_id` INT(10),
                        `device_name` VARCHAR(150),
                        `last_activity` DATETIME NOT NULL,
                        PRIMARY KEY (`device_unique_id`),
                        UNIQUE KEY UNQ_MOB_DEV_UNQ (`device_unique`, `account_id`))
                "
                );

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_session_keys` (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `session_key` VARCHAR(100) NOT NULL,
                        `user_id` INT(10),
                        `date_added` DATETIME NOT NULL,
                        PRIMARY KEY (`id`))
                "
                );

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_failed_login` (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `ip` VARCHAR(20) NOT NULL,
                        `date_added` DATETIME NOT NULL,
                        PRIMARY KEY (`id`))
                "
                );

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_accounts` (
                        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `account_email` VARCHAR(100) NOT NULL,
                        `status` TINYINT,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY UNQ_MOB_ACCOUNT (`account_email`))
                "
                );

                $wpdb->query(
                    "
                    CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mobileassistant_users` (
                        `user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `username` VARCHAR(100) NOT NULL,
                        `password` VARCHAR(35) NOT NULL,
                        `allowed_actions` VARCHAR(1000),
                        `qr_code_hash` VARCHAR(70),
                        `status` TINYINT,
                        PRIMARY KEY (`user_id`),
                        UNIQUE KEY UNQ_MOB_USER (`username`))
                "
                );

                if (!(get_option('mobassistantconnector'))) {
                    $option_value = array(
                        'login' => '1',
                        'pass'  => 'c4ca4238a0b923820dcc509a6f75849b'
                    );
                    $wpdb->replace(
                        $wpdb->options,
                        array('option_name' => 'mobassistantconnector', 'option_value' => serialize($option_value))
                    );
                }

                if (!get_option('mobassistantconnector_cl_date')) {
                    $wpdb->replace(
                        $wpdb->options,
                        array('option_name' => 'mobassistantconnector_cl_date', 'option_value' => time())
                    );
                }
            }

            private function insertDefaultUser()
            {
                global $wpdb;

                $result = false;

                $exists_user_table = $wpdb->get_results(
                    "SHOW TABLES LIKE '{$wpdb->prefix}mobileassistant_users';"
                );
                if ($exists_user_table) {

                    $is_exists_user = $wpdb->get_results(
                            "SELECT user_id FROM `{$wpdb->prefix}mobileassistant_users`"
                    );
                    if (!$is_exists_user) {
                        $all_options = $this->getOptionCodes();

                        // Move user from old options
                        if ($user = get_option('mobassistantconnector')) {
                            $username = $user['login'];
                            $password = $user['pass'];

                            $wpdb->delete(
                                $wpdb->options,
                                array('option_name' => 'mobassistantconnector')
                            );

                            $wpdb->query(
                                $wpdb->prepare( "INSERT IGNORE
                                  INTO `$wpdb->options` ( `option_name`, `option_value`, `autoload` )
                                  VALUES ('mobassistantconnector', %s, 'yes') ", $user['mobassist_api_key'] )
                            );

                            // Create default user
                        } else {
                            $username = '1';
                            $password = md5('1');
                        }

                        // Insert first user
                        $sql = $wpdb->prepare(
                            "INSERT INTO `{$wpdb->prefix}mobileassistant_users` (`username`, `password`, `allowed_actions`, `qr_code_hash`, `status` )
                                   VALUES (%s, %s, %s, %s, 1)", $username, $password, implode(';', $all_options),
                            hash('sha256', time()), 1
                        );
                        $result = $wpdb->query($sql);

                        $this->setPushUser();
                        $this->createAccounts();
                    }
                }

                return $result;
            }

            private function createAccounts()
            {
                global $wpdb;

                $result = false;
                $used_emails = array();

                if($this->accountEmails) {
                    foreach ($this->accountEmails as $email) {
                        if(!in_array($email['account_meail'], $used_emails)) {
                            $sql = $wpdb->prepare(
                                "INSERT INTO `{$wpdb->prefix}mobileassistant_accounts` (`account_email`, `status`)
                                   VALUES (%s, 1)", $email['account_email']
                            );
                            $result = $wpdb->query($sql);
                            $used_emails[] = $email['account_email'];
                        }


                        $sql = $wpdb->prepare(
                            "UPDATE `{$wpdb->prefix}mobileassistant_devices` d
                            SET d.`account_id` =
                            (SELECT if(a.`id` >= 0, a.`id`, NULL)
                              FROM `{$wpdb->prefix}mobileassistant_accounts` a
                              WHERE a.`account_email` = %s
                            )
                            WHERE d.device_unique_id = %s
                              ", $email['account_email'],$email['device_unique_id']
                        );
                        $result = $wpdb->query($sql);
                    }
                }

                return $result;
            }

            private function setPushUser()
            {
                global $wpdb;

                $result = false;

                $is_exists_user = $wpdb->get_results(
                    "SELECT setting_id FROM `{$wpdb->prefix}mobileassistant_push_settings`"
                );

                if ($is_exists_user) {
                    // Update first user
                    $sql = "UPDATE `{$wpdb->prefix}mobileassistant_push_settings`
                            SET `user_id` = 1";
                    $result = $wpdb->query($sql);
                }

                return $result;
            }

            private function getOptionCodes()
            {
                $all_options = array();

                $groups = Mobassistantconnector_Functions::get_default_actions();
                foreach ($groups as $option_group) {
                    foreach ($option_group as $option) {
                        $all_options[] = $option['code'];
                    }
                }

                return $all_options;
            }

/*            private function getDefaultActions() {
                $default_actions = array(
                    'push_new_order' => 1,
                    'push_order_status_changed' => 1,
                    'push_new_customer' => 1,
                    'store_statistics' => 1,
                    'order_list' => 1,
                    'order_details' => 1,
                    'order_status_updating' => 1,
                    'customer_list' => 1,
                    'customer_details' => 1,
                    'product_list' => 1,
                    'product_details' => 1
                );

                return $default_actions;
            }*/

            public function add_query_vars($vars)
            {
                $vars[] = "callback";
                $vars[] = "call_function";
                $vars[] = "hash";
                $vars[] = "test_config";
                $vars[] = "get_store_title";
                $vars[] = "get_store_stats";
                $vars[] = "get_data_graphs";
                $vars[] = 'show';
                $vars[] = 'page';
                $vars[] = 'search_order_id';
                $vars[] = 'orders_from';
                $vars[] = 'orders_to';
                $vars[] = 'customers_from';
                $vars[] = 'customers_to';
                $vars[] = 'date_from';
                $vars[] = 'date_to';
                $vars[] = 'graph_from';
                $vars[] = 'graph_to';
                $vars[] = 'stats_from';
                $vars[] = 'stats_to';
                $vars[] = 'products_to';
                $vars[] = 'products_from';
                $vars[] = 'order_id';
                $vars[] = 'user_id';
                $vars[] = 'params';
                $vars[] = 'val';
                $vars[] = 'search_val';
                $vars[] = 'statuses';
                $vars[] = 'last_order_id';
                $vars[] = 'sort_by';
                $vars[] = 'product_id';
                $vars[] = 'get_statuses';
                $vars[] = 'cust_with_orders';
                $vars[] = 'data_for_widget';
                $vars[] = 'custom_period';
                $vars[] = 'connector';
                $vars[] = 'get_qr_code';

                return $vars;
            }

            public function the_template($template)
            {
                global $wp_query;

                if (!isset( $wp_query->query['connector']))
                    return $template;

                if ($wp_query->query['connector'] == 'mobileassistant')
                {
                    $this->execute_connector();
                    exit;
                }

                return $template;
            }

            public function execute_connector()
            {
                $MainClass = new MobileAssistantConnector();
                $call_func = $MainClass->call_function;

                if (!method_exists($MainClass, $call_func))
                {
                    $MainClass->generate_output('old_module');
                }

                $result = $MainClass->$call_func();
                $MainClass->generate_output($result);
            }



            public function ema_option_styles() {
                wp_register_style('ema_style', plugins_url('css/style.css', __FILE__));
                wp_enqueue_style('ema_style');

                wp_register_style('ema_style_bootstrap', plugins_url('css/bootstrap.min.css', __FILE__));
                wp_enqueue_style('ema_style_bootstrap');

            }

            public function ema_option_scripts() {
//                wp_register_script('ema_tb', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js');
//                wp_enqueue_script('ema_tb');
    
                wp_register_script('ema_qr', plugins_url('js/qrcode.min.js', __FILE__));
                wp_enqueue_script('ema_qr');

                wp_register_script('ema_qr_changed', plugins_url('js/qr_changed.js', __FILE__));
                wp_enqueue_script('ema_qr_changed');

                wp_register_script('ema_common', plugins_url('js/common.js', __FILE__));
                wp_enqueue_script('ema_common');

                wp_register_script('ema_bootstrap', plugins_url('js/bootstrap.min.js', __FILE__));
                wp_enqueue_script('ema_bootstrap');

//                wp_register_script('ema_datatables', plugins_url('js/datatables.min.js', __FILE__));
//                wp_enqueue_script('ema_datatables');
            }

            // Add settings link on plugin page
            public function setting_link($links) {
                $settings_link = '<a href="options-general.php?page=connector">Settings</a>';
                array_unshift($links, $settings_link);
                return $links;
            }

        }

        include_once('classes/class-mobassistantconnector-access.php');
        include_once('classes/class-mobassistantconnector-console.php');
        include_once('functions/functions.php');
        $GLOBALS['ma_connector'] = new ma_connector();
        include_once('option.php');
        include_once('sa.php');
    }
} else {
    add_action ('admin_notices', 'connector_admin_notices');
}

if ( ! function_exists( 'connector_admin_notices' ) ) {
    function connector_admin_notices()
    {
        echo '<div id="notice" class="error"><p>';
        echo '<b> Mobile Assistant Connector </b> add-on requires <a href="http://www.storeapps.org/woocommerce/"> WooCommerce </a> plugin. Please install and activate it.';
        echo '</p></div>', "\n";

    }
}
?>