<?php

defined('ABSPATH') || exit;

class NewsletterUsersAdmin extends NewsletterModuleAdmin {

    static $instance;

    /**
     * @return NewsletterUnsubscriptionAdmin
     */
    static function instance() {
        if (self::$instance == null) {
            self::$instance = new NewsletterUsersAdmin();
        }
        return self::$instance;
    }

    function __construct() {
        parent::__construct('users', '1.0.7');
        add_action('admin_init', [$this, 'hook_admin_init']);
    }

    function hook_admin_init() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if (empty($_GET['export']) || ($_GET['page'] ?? '') !== 'newsletter_users_import') {
            return;
        }
        if (!Newsletter::instance()->is_allowed()) {
            wp_die('Forbidden', '', ['response' => 403]);
        }
        check_admin_referer('tnp-users-export');
        $this->export_csv(sanitize_text_field(wp_unslash($_GET['status'] ?? '')), (int) ($_GET['list'] ?? 0));
    }

    /**
     * Streams the subscribers as CSV (UTF-8 with BOM).
     */
    private function export_csv($status, $list) {
        global $wpdb;
        $where = '1=1';
        if (in_array($status, ['C', 'S', 'U', 'B', 'P'], true)) {
            $where .= $wpdb->prepare(' and status=%s', $status);
        }
        if ($list >= 1 && $list <= NEWSLETTER_LIST_MAX) {
            $where .= ' and list_' . $list . '=1';
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="subscribers.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['email', 'name', 'surname', 'status', 'created', 'ip', 'lists']);
        $last_id = 0;
        do {
            $rows = $wpdb->get_results($wpdb->prepare('select * from ' . NEWSLETTER_USERS_TABLE . ' where ' . $where . ' and id>%d order by id asc limit 1000', $last_id));
            foreach ($rows as $row) {
                $last_id = (int) $row->id;
                $lists = [];
                for ($i = 1; $i <= NEWSLETTER_LIST_MAX; $i++) {
                    $key = 'list_' . $i;
                    if (!empty($row->$key)) {
                        $lists[] = $i;
                    }
                }
                fputcsv($out, [
                    $this->csv_cell($row->email), $this->csv_cell($row->name), $this->csv_cell($row->surname),
                    $row->status, $row->created, $row->ip, implode(' ', $lists)
                ]);
            }
        } while (count($rows) === 1000);
        fclose($out);
        die();
    }

    private function csv_cell($value) {
        $value = (string) $value;
        if ($value !== '' && strpbrk($value[0], "=+-@\t\r") !== false) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * Imports subscribers from a CSV file. Existing subscribers are not modified.
     *
     * @return array|false Counters or false when the file is not readable.
     */
    function import_csv($path, $status = 'C', $list = 0) {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return false;
        }
        $first_line = fgets($handle);
        if ($first_line === false) {
            fclose($handle);
            return ['imported' => 0, 'existing' => 0, 'invalid' => 0];
        }
        $first_line = preg_replace('/^\xEF\xBB\xBF/', '', $first_line);
        $delimiter = substr_count($first_line, ';') > substr_count($first_line, ',') ? ';' : ',';
        rewind($handle);

        $map = ['email' => 0, 'name' => 1, 'surname' => 2];
        $report = ['imported' => 0, 'existing' => 0, 'invalid' => 0];
        $row_number = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row_number++;
            if ($row === [null]) {
                continue;
            }
            if ($row_number === 1) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);
                // Header row: map by column names
                if (strpos(implode('', $row), '@') === false) {
                    $map = [];
                    foreach ($row as $index => $label) {
                        $label = strtolower(trim($label));
                        if (in_array($label, ['email', 'name', 'surname'], true)) {
                            $map[$label] = $index;
                        }
                    }
                    if (!isset($map['email'])) {
                        fclose($handle);
                        return ['imported' => 0, 'existing' => 0, 'invalid' => 0];
                    }
                    continue;
                }
            }
            $email = $this->sanitize_email(trim((string) ($row[$map['email']] ?? '')));
            if (empty($email)) {
                $report['invalid']++;
                continue;
            }
            $user = [
                'email' => $email,
                'name' => $this->sanitize_name((string) ($row[$map['name'] ?? -1] ?? '')),
                'surname' => $this->sanitize_name((string) ($row[$map['surname'] ?? -1] ?? '')),
                'status' => $status,
                'sex' => 'n',
            ];
            if ($list >= 1 && $list <= NEWSLETTER_LIST_MAX) {
                $user['list_' . $list] = 1;
            }
            if ($this->save_user($user) === false) {
                $report['existing']++;
            } else {
                $report['imported']++;
            }
        }
        fclose($handle);
        return $report;
    }

    function admin_menu() {
        //$this->add_menu_page('index', __('Subscribers', 'newsletter'));
        $this->add_admin_page('new', __('New subscriber', 'newsletter'));
        $this->add_admin_page('edit', __('Subscriber Edit', 'newsletter'));
        $this->add_admin_page('logs', __('Logs', 'newsletter'));
        $this->add_admin_page('newsletters', __('Newsletters', 'newsletter'));
        $this->add_admin_page('autoresponders', __('Autoresponders', 'newsletter'));
        $this->add_admin_page('massive', __('Subscribers Maintenance', 'newsletter'));
        //$this->add_admin_page('export', __('Export', 'newsletter'));
        $this->add_admin_page('import', __('Import/Export', 'newsletter'));
        $this->add_admin_page('statistics', __('Statistics', 'newsletter'));
    }
}

class TNP_Subscribers_Stats {

    var $total;
    var $confirmed;
    var $unconfirmed;
    var $bounced;

}
