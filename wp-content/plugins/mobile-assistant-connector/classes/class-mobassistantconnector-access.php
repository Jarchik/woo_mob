<?php
/**
 *	This file is part of Mobile Assistant Connector.
 *
 *   Mobile Assistant Connector is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   Mobile Assistant Connector is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with Mobile Assistant Connector.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @author    eMagicOne <contact@emagicone.com>
 *  @copyright 2014-2015 eMagicOne
 *  @license   http://www.gnu.org/licenses   GNU General Public License
 */

class Mobassistantconnector_Access
{
    const HASH_ALGORITHM     = 'sha256';
    const MAX_LIFETIME       = 86400; /* 24 hours */
    const TABLE_SESSION_KEYS = 'mobileassistant_session_keys';
    const TABLE_FAILED_LOGIN = 'mobileassistant_failed_login';

    public static function clear_old_data()	{
        global $wpdb;

        $timestamp       = time();
        $date_clear_prev = get_option( 'mobassistantconnector_cl_date' );
        $date            = date( 'Y-m-d H:i:s', ( $timestamp - self::MAX_LIFETIME ) );

        if ( $date_clear_prev === false || ( $timestamp - (int) $date_clear_prev ) > self::MAX_LIFETIME ) {
            $wpdb->query( $wpdb->prepare( 'DELETE FROM `' . $wpdb->prefix . self::TABLE_SESSION_KEYS . '` WHERE `date_added` < %s', $date ) );
            $wpdb->query( $wpdb->prepare( 'DELETE FROM `' . $wpdb->prefix . self::TABLE_FAILED_LOGIN . '` WHERE `date_added` < %s', $date ) );
            $wpdb->update( $wpdb->options, array( 'option_value' => $timestamp ), array( 'option_name' => 'mobassistantconnector_cl_date' ), '%d', '%s' );
        }
    }

    public static function get_session_key( $hash ) {
        $login_data = get_option( 'mobassistantconnector' );

        if ( hash( self::HASH_ALGORITHM, $login_data['login'] . $login_data['pass'] ) == $hash ) {
            return self::generate_session_key( $login_data['login'] );
        } else {
            self::add_failed_attempt();
        }

        return false;
    }

    public static function check_session_key( $key )
    {
        global $wpdb;

        $timestamp = time();
        $db_key = $wpdb->get_var( $wpdb->prepare( 'SELECT `session_key` FROM `' . $wpdb->prefix . self::TABLE_SESSION_KEYS
            . '` WHERE `session_key` = %s AND `date_added` > %s', $key, date( 'Y-m-d H:i:s', ( $timestamp - self::MAX_LIFETIME ) ) ) );

        if ( $db_key ) {
            return true;
        } else {
            self::add_failed_attempt();
        }

        return false;
    }

    private static function generate_session_key( $username )
    {
        global $wpdb;

        $timestamp = time();
        $key = hash( self::HASH_ALGORITHM, AUTH_KEY . $username . $timestamp );
        $wpdb->insert( $wpdb->prefix . self::TABLE_SESSION_KEYS, array( 'session_key' => $key, 'date_added' => date( 'Y-m-d H:i:s', $timestamp ) ),
            array( '%s', '%s' ) );

        return $key;
    }

    public static function add_failed_attempt()
    {
        global $wpdb;

        $timestamp = time();
        $wpdb->insert( $wpdb->prefix . self::TABLE_FAILED_LOGIN,
            array( 'ip' => $_SERVER['REMOTE_ADDR'], 'date_added' => date( 'Y-m-d H:i:s', $timestamp ) ),
            array( '%s', '%s' ) );

        // Get count of failed attempts for last 24 hours and set delay
        $count_failed_attempts = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(`id`) FROM `' . $wpdb->prefix . self::TABLE_FAILED_LOGIN
            . '` WHERE `ip` = %s AND `date_added` > %s', $_SERVER['REMOTE_ADDR'], date( 'Y-m-d H:i:s', ( $timestamp - self::MAX_LIFETIME ) ) ) );
        self::set_delay( (int) $count_failed_attempts );
    }

    private static function set_delay( $count_attempts )
    {
        if ( $count_attempts <= 10 )
            sleep( 1 );
        elseif ( $count_attempts <= 20 )
            sleep( 2 );
        elseif ( $count_attempts <= 50 )
            sleep( 5 );
        else
            sleep( 10 );
    }
}