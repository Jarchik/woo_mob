<?php
require_once '../../../../wp-config.php';
include_once 'functions.php';
include_once '../sa.php';

$key          = isset( $_REQUEST['key'] ) ? (string) $_REQUEST['key'] : '';
$function     = isset( $_REQUEST['call_function'] ) ? (string) $_REQUEST['call_function'] : '';
$push_ids     = isset( $_REQUEST['push_ids'] ) ? (string) $_REQUEST['push_ids'] : '';
$value        = isset( $_REQUEST['value'] ) ? (string) $_REQUEST['value'] : '';
$user_id      = isset( $_REQUEST['user_id'] ) ? (string) $_REQUEST['user_id'] : '';
$del_user_id  = isset( $_REQUEST['mac_del_user_id'] ) ? (string) $_REQUEST['mac_del_user_id'] : '';

$new_user_login    = isset( $_REQUEST['new_user_login'] ) ? (string) $_REQUEST['new_user_login'] : '';
$new_user_password = isset( $_REQUEST['new_user_password'] ) ? (string) $_REQUEST['new_user_password'] : '';

$user    = isset( $_REQUEST['user'] ) ? $_REQUEST['user'] : array();
$user_allowed_actions = isset( $_REQUEST['user_allowed_actions'] ) ? $_REQUEST['user_allowed_actions'] : array();

//if ( ! is_authenticated( $key ) ) {
//    die( json_encode( 'Authentication error' ) );
//}

if ( $function && function_exists( $function ) ) {
    if ( $function == 'mac_get_user_data' ) {
        echo mac_get_user_data( $user_id );
    } elseif ( $function == 'mac_get_devices' ) {
        echo mac_get_devices( $user_id );
    } elseif ( $function == 'change_status' ) {
        echo change_status( $push_ids, $value );
    } elseif ( $function == 'delete_device' ) {
        echo delete_device( $push_ids );
    } elseif ( $function == 'mac_save_user' ) {
        header("content-type:application/json");
        echo mac_save_user( $user, $user_allowed_actions );
    } elseif ( $function == 'mac_delete_user' ) {
        header("content-type:application/json");
        echo mac_delete_user( $del_user_id );
    } elseif ( $function == 'mac_add_user' ) {
        header("content-type:application/json");
        echo mac_add_user( $new_user_login, $new_user_password );
    } else {
        echo call_user_func( $function );
    }
} else {
    die( json_encode( 'error' ) );
}

function is_authenticated( $key ) {
    $login_data = get_option( 'mobassistantconnector' );

    if ( hash( 'sha256', $login_data['login'] . $login_data['pass'] . AUTH_KEY ) == $key ) {
        return true;
    }

    return false;
}

function mac_delete_user($user_id) {
    global $wpdb;

    if (!empty($user_id)) {
        $user = $wpdb->get_results(
            $wpdb->prepare("SELECT `user_id` FROM {$wpdb->prefix}mobileassistant_users WHERE `user_id` = %s", $user_id)
        );

        if ($user) {

            $result = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM `{$wpdb->prefix}mobileassistant_users` WHERE `user_id` = %s",
                    $user_id
                )
            );

            if (false !== $result) {
                $wpdb->query("DELETE FROM {$wpdb->prefix}mobileassistant_push_settings WHERE `user_id` = {$user_id}");
                $wpdb->query("DELETE FROM {$wpdb->prefix}mobileassistant_session_keys WHERE `user_id` = {$user_id}");

                Mobassistantconnector_Functions::delete_empty_devices();
                Mobassistantconnector_Functions::delete_empty_accounts();

                $result = json_encode(array('success' => true, 'user_id' => $user_id));
            } else {
                $result = json_encode(array('success' => false, 'error' => 'User cannot be deleted.'));
            }

        } else {
            $result = json_encode(array('success' => true, 'user_id' => $user_id));
        }
    } else {
        $result = json_encode(array('success' => false, 'error' => 'Missed User ID.'));
    }
    return $result;
}

function mac_add_user($username, $password) {
    global $wpdb;

    $user = $wpdb->get_results( $wpdb->prepare( "SELECT `user_id` FROM {$wpdb->prefix}mobileassistant_users WHERE `username` = %s", $username ) );

    if ( ! $user ) {

        $all_options = array();

        $groups = Mobassistantconnector_Functions::get_default_actions();
        foreach ($groups as $option_group) {
            foreach ($option_group as $option) {
                $all_options[] = $option['code'];
            }
        }

        $sql = $wpdb->prepare(
            "INSERT INTO `{$wpdb->prefix}mobileassistant_users` (`username`, `password`, `allowed_actions`, `qr_code_hash`, `status` )
                                   VALUES (%s, %s, %s, %s, 1)", $username, md5($password), implode(';', $all_options),
            hash('sha256', time()), 1
        );

        $result = $wpdb->query($sql);

        if ( false !== $result ) {
            $user_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT `user_id` FROM `{$wpdb->prefix}mobileassistant_users` WHERE `username` = %s LIMIT 1",
                    $username
                )
            );

            $result = json_encode( array('success' => true, 'user_id' => $user_id) );
        }
    } else {
        $result = json_encode(array('success' => false, 'error' => 'User with name (' . $username . ') already exists.'));
    }

    return $result;
}

function mac_save_user($user, $actions) {
    global $wpdb;

    $user_data = $wpdb->get_results( $wpdb->prepare( "SELECT `user_id`, `password`  FROM {$wpdb->prefix}mobileassistant_users WHERE `user_id` = %s", $user['user_id'] ) , ARRAY_A);

    if ( ! $user_data ) {
        $user_data = array();
    } else {
        $user_data = array_shift($user_data);
    }

    if ( !empty($user_data) ) {

        $updated_user = $user;
        if ($user_data['password'] !== $user['password']) {
            $updated_user['password'] = md5($user['password']);
        }

        $allowed_actions = array_keys($actions);

        $sql = $wpdb->prepare(
            "UPDATE `{$wpdb->prefix}mobileassistant_users`
                       SET `username` = %s, `password` = %s, `allowed_actions` = %s, `qr_code_hash` = %s, `status` = %d
                       WHERE `user_id` = %d", $updated_user['username'], $updated_user['password'], implode(';', $allowed_actions),
            hash('sha256', time()), (int)$updated_user['status'], $user['user_id']
        );

        $result = $wpdb->query($sql);

        if (!in_array('push_notification_settings_new_order', $allowed_actions)
            &&  !in_array('push_notification_settings_new_customer', $allowed_actions)
            &&  !in_array('push_notification_settings_order_statuses', $allowed_actions)
        ) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}mobileassistant_session_keys WHERE `user_id` = {$user_data['user_id']}");

            Mobassistantconnector_Functions::delete_empty_devices();
            Mobassistantconnector_Functions::delete_empty_accounts();
        }

        if ( false !== $result ) {
            $result = json_encode( array('success' => true, 'user_id' => $user['user_id']) );
        } else {
            $result = json_encode(array('success' => false, 'error' => 'Cannot save the user.'));
        }
    } else {
        $result = json_encode(array('success' => false, 'error' => 'Cannot save the user.'));
    }

    return $result;
}

function mac_get_users() {
    global $wpdb;

    $users = $wpdb->get_results( "SELECT
            mu.user_id,
            mu.username,
            mu.password,
            mu.allowed_actions,
            mu.qr_code_hash,
            mu.status
        FROM `{$wpdb->prefix}mobileassistant_users` mu", ARRAY_A );

    if ( ! $users ) {
        $users = array();
    }

//    $users_count = count($users);

    $users     = replace_null( $users );
//    $devices = form_devices( $devices, $statuses );

    return json_encode( $users );
}

function mac_get_user_data($user_id) {
    global $wpdb;

    $user = $wpdb->get_results( $wpdb->prepare( "SELECT
            mu.user_id,
            mu.username,
            mu.password,
            mu.allowed_actions,
            mu.qr_code_hash,
            mu.status,
            mps.setting_id,
            mps.app_connection_id,
            mps.push_new_order,
            mps.push_order_statuses,
            mps.push_new_customer,
            mps.device_unique_id,
            -- mps.status,
            mps.push_currency_code,
            md.`device_unique_id`,
            md.`account_id`,
            md.`device_name`,
            md.`last_activity`,
            ma.`account_email`
            -- ma.`status`
        FROM `{$wpdb->prefix}mobileassistant_users` mu
        LEFT JOIN `{$wpdb->prefix}mobileassistant_push_settings` mps
           ON mu.user_id = mps.user_id
        LEFT JOIN `{$wpdb->prefix}mobileassistant_devices` md
           ON md.device_unique_id = mps.device_unique_id
        LEFT JOIN `{$wpdb->prefix}mobileassistant_accounts` ma
           ON ma.id = md.account_id
        WHERE mu.user_id = %d", $user_id)
    , ARRAY_A );

    if ( ! $user ) {
        $user = array();
    } else {
        $user = array_shift($user);
        $qr_config = array(
            'url' => get_site_url(),
            'login' => $user['username'],
            'password' => $user['password']
        );

        $user['qr_code_data'] = base64_encode(json_encode($qr_config));
    }

//    $users_count = count($users);

    if (!empty($user['allowed_actions'])) {
        $user_actions = explode(';', $user['allowed_actions']);
        $user['allowed_actions'] = $user_actions;
    }

//    $user     = replace_null( $user );
//    $devices = form_devices( $devices, $statuses );

    return json_encode( array($user) );
}

function mac_get_devices($user_id) {
    global $wpdb;

    $devices = $wpdb->get_results( $wpdb->prepare( "SELECT
            mpn.`setting_id` AS id,
            mpn.`push_new_order` AS new_order,
            mpn.`push_new_customer` AS new_customer,
            mpn.`push_order_statuses` AS order_statuses,
            mpn.`app_connection_id`,
            mpn.`status`,
            mpn.`device_unique_id`,
            md.`device_name`,
            md.`last_activity`,
            md.`account_id`,
            ma.`account_email`,
            ma.`status`
        FROM `{$wpdb->prefix}mobileassistant_push_settings` mpn
        LEFT JOIN `{$wpdb->prefix}mobileassistant_devices` md ON md.`device_unique_id` = mpn.`device_unique_id`
        INNER JOIN `{$wpdb->prefix}mobileassistant_accounts` ma ON ma.`id` = md.`account_id`
        LEFT JOIN `{$wpdb->prefix}mobileassistant_users` mu ON mpn.`user_id` = mu.`user_id`
        WHERE mu.user_id = %d", $user_id), ARRAY_A );

    if ( ! $devices ) {
        $devices = array();
    }

    $devices     = replace_null( $devices );
    $statuses_db = _get_order_statuses();
    $statuses    = array();

    foreach ( $statuses_db as $code => $status ) {
        $statuses[ $code ] = $status;
    }

    $devices = form_devices( $devices, $statuses );

    return json_encode( $devices );
}

function change_status( $ids, $value ) {
    global $wpdb;

    $ids = prepare_ids( $ids );

    if ( ! $ids ) {
        return json_encode( 'Parameters are incorrect' );
    }

    $result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}mobileassistant_push_settings SET `status` = %d WHERE `setting_id` IN ({$ids})", $value ) );

    if ( false !== $result ) {
        return json_encode( 'success' );
    }

    return json_encode( 'Some error occurred' );
}

function delete_device( $ids ) {
    global $wpdb;

    $ids = prepare_ids( $ids );

    if ( ! $ids ) {
        return json_encode( 'Parameters are incorrect' );
    }

    $result = $wpdb->query( "DELETE FROM {$wpdb->prefix}mobileassistant_push_settings WHERE `setting_id` IN ({$ids})" );
    Mobassistantconnector_Functions::delete_empty_devices();

    if ( false !== $result ) {
        return json_encode( 'success' );
    }

    return json_encode( 'Some error occurred' );
}

function form_devices( $devices, $statuses ) {
    $count_devices  = count( $devices );
    $devices_output = array();

    for ( $i = 0; $i < $count_devices; $i++ ) {
        $device_unique = ! $devices[ $i ]['device_unique_id'] ? 'Unknown' : $devices[ $i ]['device_unique_id'];

        if ( $devices[ $i ]['order_statuses'] ) {
            if ( (int) $devices[ $i ]['order_statuses'] == -1 ) {
                $devices[ $i ]['order_statuses'] = 'All';
            } else {
                $push_statuses       = explode( '|', $devices[ $i ]['order_statuses'] );
                $count_push_statuses = count( $push_statuses );
                $view_statuses       = array();

                for ( $j = 0; $j < $count_push_statuses; $j++ ) {
                    if ( isset( $statuses[ $push_statuses[ $j ] ] ) ) {
                        $view_statuses[] = $statuses[ $push_statuses[ $j ]];
                    }
                }

                $devices[ $i ]['order_statuses'] = implode( ', ', $view_statuses );
            }
        }

        if ( $devices[ $i ]['last_activity'] == '0000-00-00 00:00:00' ) {
            $devices[ $i ]['last_activity'] = '';
        }

        if ( $device_unique == 'Unknown' ) {
            $devices[ $i ]['device_name'] = 'Unknown';
        }

        $devices_output[ $device_unique ]['device_name']   = ! $devices[ $i ]['device_name'] ? '-' : $devices[ $i ]['device_name'];
        $devices_output[ $device_unique ]['account_email'] = ! $devices[ $i ]['account_email'] ? '-' : $devices[ $i ]['account_email'];
        $devices_output[ $device_unique ]['last_activity'] = ! $devices[ $i ]['last_activity'] ? '-' : $devices[ $i ]['last_activity'];
        $devices_output[ $device_unique ]['pushes'][]      = array(
            'id'                => $devices[ $i ]['id'],
            'new_order'         => $devices[ $i ]['new_order'],
            'new_customer'      => $devices[ $i ]['new_customer'],
            'order_statuses'    => ! $devices[ $i ]['order_statuses'] ? '-' : $devices[ $i ]['order_statuses'],
            'app_connection_id' => $devices[ $i ]['app_connection_id'],
            'status'            => $devices[ $i ]['status'],
        );
    }

    return $devices_output;
}

function replace_null( $data )
{
    if ( ! is_array( $data ) ) {
        $data = array();
    }

    foreach ( $data as $index => $values ) {
        foreach ( $values as $key => $value ) {
            if ( $value === null ) {
                $data[ $index ][ $key ] = '';
            }
        }
    }

    return $data;
}

function prepare_ids( $data )
{
    if ( ! $data ) {
        return false;
    }

    $ids   = array();
    $arr   = explode( ',', $data );
    $count = count( $arr );

    for ( $i = 0; $i < $count; $i++ ) {
        $ids[] = (int) trim( $arr[ $i ] );
    }

    return implode( ',', $ids );
}