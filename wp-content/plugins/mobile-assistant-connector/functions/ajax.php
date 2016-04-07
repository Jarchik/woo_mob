<?php
require_once '../../../../wp-config.php';
include_once 'functions.php';
include_once '../sa.php';

$key      = isset( $_REQUEST['key'] ) ? (string) $_REQUEST['key'] : '';
$function = isset( $_REQUEST['call_function'] ) ? (string) $_REQUEST['call_function'] : '';
$push_ids = isset( $_REQUEST['push_ids'] ) ? (string) $_REQUEST['push_ids'] : '';
$value    = isset( $_REQUEST['value'] ) ? (string) $_REQUEST['value'] : '';

if ( ! is_authenticated( $key ) ) {
    die( json_encode( 'Authentication error' ) );
}

if ( $function && function_exists( $function ) ) {
    if ( $function == 'change_status' ) {
        echo change_status( $push_ids, $value );
    } elseif ( $function == 'delete_device' ) {
        echo delete_device( $push_ids );
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

function get_devices() {
    global $wpdb;

    $devices = $wpdb->get_results( "SELECT
            mpn.`setting_id` AS id,
            mpn.`push_new_order` AS new_order,
            mpn.`push_new_customer` AS new_customer,
            mpn.`push_order_statuses` AS order_statuses,
            mpn.`app_connection_id`,
            mpn.`status`,
            mpn.`device_unique_id`,
            md.`account_email`,
            md.`device_name`,
            md.`last_activity`
        FROM `{$wpdb->prefix}mobileassistant_push_settings` mpn
        LEFT JOIN `{$wpdb->prefix}mobileassistant_devices` md ON md.`device_unique_id` = mpn.`device_unique_id`", ARRAY_A );

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
    delete_empty_devices();

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