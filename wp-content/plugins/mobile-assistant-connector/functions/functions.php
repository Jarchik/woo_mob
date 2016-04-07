<?php
function delete_empty_devices() {
    global $wpdb;

    $sql = "DELETE md FROM `{$wpdb->prefix}mobileassistant_devices` md
			LEFT JOIN `{$wpdb->prefix}mobileassistant_push_settings` mpn ON mpn.`device_unique_id` = md.`device_unique_id`
			WHERE mpn.`device_unique_id` IS NULL";
    $wpdb->query($sql);
}