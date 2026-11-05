<?php

namespace Newsletter;

defined('ABSPATH') || exit;

/**
 * Compatibility layer. Open Newsletter has no license and no locked features:
 * every method answers as if a valid license were always active, without any network access.
 * It only exists because add-ons written for the original plugin may call it.
 */
class License {

    static function get_key() {
        return \Newsletter::instance()->get_license_key();
    }

    static function get_data($refresh = false) {
        return \Newsletter::instance()->get_license_data($refresh);
    }

    static function update() {
        // Nothing to update.
    }

    static function get_badge() {
        return '';
    }

    static function is_premium() {
        return true;
    }

    static function is_free() {
        return false;
    }

    static function is_personal() {
        return true;
    }

    static function is_reseller() {
        return false;
    }
}
