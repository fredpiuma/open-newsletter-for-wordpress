<?php

defined('ABSPATH') || exit;

class NewsletterStatisticsAdmin extends NewsletterModuleAdmin {

    static $instance;

    const SENT_NONE = 0;
    const SENT_READ = 1;
    const SENT_CLICK = 2;

    const SUBSCRIBERS_PER_PAGE = 50;

    /**
     * @return NewsletterStatisticsAdmin
     */
    static function instance() {
        if (self::$instance == null) {
            self::$instance = new NewsletterStatisticsAdmin();
        }
        return self::$instance;
    }

    function __construct() {
        parent::__construct('statistics');
        add_action('admin_init', [$this, 'hook_admin_init']);
    }

    function admin_menu() {
        $this->add_admin_page('index', 'Statistics');
        $this->add_admin_page('newsletters', 'Statistics');
        $this->add_admin_page('view', 'Statistics');
        $this->add_admin_page('users', 'Statistics');
        $this->add_admin_page('urls', 'Statistics');

        //$this->add_admin_page('newsletters', 'Statistics');
        $this->add_admin_page('settings', 'Statistics');
        //$this->add_admin_page('view_retarget', 'Statistics');
        $this->add_admin_page('view-urls', 'Statistics');
        $this->add_admin_page('view-users', 'Statistics');
    }

    function get_statistics_url($email_id) {
        $page = apply_filters('newsletter_statistics_view', 'newsletter_statistics_view');
        return 'admin.php?page=' . $page . '&amp;id=' . $email_id;
    }

    function get_index_url() {
        $page = apply_filters('newsletter_statistics_index', 'newsletter_statistics_index');
        return 'admin.php?page=' . $page;
    }

    /**
     * @deprecated
     *
     * @param type $email_id
     * @return type
     */
    function get_total_count($email_id) {
        $report = $this->get_statistics($email_id);
        return $report->total;
    }

    /**
     * @deprecated
     *
     * @param type $email_id
     * @return type
     */
    function get_open_count($email_id) {
        $report = $this->get_statistics($email_id);
        return $report->open_count;
    }

    /**
     * @deprecated
     *
     * @param type $email_id
     * @return type
     */
    function get_error_count($email_id) {
        return 0;
    }

    /**
     * @deprecated
     *
     * @param type $email_id
     * @return type
     */
    function get_click_count($email_id) {
        $report = $this->get_statistics($email_id);
        return $report->click_count;
    }

    /**
     * @deprecated
     *
     * @global wpdb $wpdb
     * @param TNP_Email $email
     */
    function maybe_fix_sent_stats($email) {
        global $wpdb;

        // Very old emails was missing the send_on
        if ($email->send_on == 0) {
            $this->query($wpdb->prepare("update " . NEWSLETTER_EMAILS_TABLE . " set send_on=unix_timestamp(created) where id=%d limit 1", $email->id));
            $email = $this->get_email($email->id);
        }

        if ($email->status == 'sending') {
            return;
        }

        if ($email->type == 'followup') {
            return;
        }

        $count = $wpdb->get_var($wpdb->prepare("select count(*) from " . NEWSLETTER_SENT_TABLE . " where email_id=%d", $email->id));

        if ($count) {
            return;
        }

        if (empty($email->query)) {
            $email->query = "select * from " . NEWSLETTER_USERS_TABLE . " where status='C'";
        }

        $query = $email->query . " and unix_timestamp(created)<" . $email->send_on;

        $query = str_replace('*', 'id, ' . $email->id . ', ' . $email->send_on, $query);
        $this->query("insert ignore into " . NEWSLETTER_SENT_TABLE . " (user_id, email_id, time) " . $query);
    }

    function update_stats($email) {
        global $wpdb;

        $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter_sent s1 join " . $wpdb->prefix . "newsletter_stats s2 on s1.user_id=s2.user_id and s1.email_id=s2.email_id and s1.email_id=%d set s1.open=1, s1.ip=s2.ip", $email->id));
        $wpdb->query($wpdb->prepare("update " . $wpdb->prefix . "newsletter_sent s1 join " . $wpdb->prefix . "newsletter_stats s2 on s1.user_id=s2.user_id and s1.email_id=s2.email_id and s2.url<>'' and s1.email_id=%d set s1.open=2, s1.ip=s2.ip", $email->id));
    }

    function reset_stats($email) {
        global $wpdb;
        $email_id = $this->to_int_id($email);
        $this->query("delete from " . $wpdb->prefix . "newsletter_sent where email_id=" . $email_id);
        $this->query("delete from " . $wpdb->prefix . "newsletter_stats where email_id=" . $email_id);
    }

    function add_click($url, $user_id, $email_id, $ip = null) {
        global $wpdb;
        if (is_null($ip)) {
            $ip = $this->get_remote_ip();
        }

        $ip = $this->process_ip($ip);

        $this->insert(NEWSLETTER_STATS_TABLE, array(
            'email_id' => $email_id,
            'user_id' => $user_id,
            'url' => $url,
            'ip' => $ip
                )
        );
    }

    /**
     * Returns an object with statistics values
     *
     * @global wpdb $wpdb
     * @param TNP_Email $email
     * @return TNP_Report
     */
    function get_statistics($email) {
        global $wpdb;

        if (!is_object($email)) {
            $email = $this->get_email($email);
        }

        $report = new TNP_Statistics();

        $report->email_id = $email->id;

        if ($email->status != 'new') {
            $data = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) as total,
            count(case when status>0 then 1 else null end) as `errors`,
            count(case when open>0 then 1 else null end) as `opens`,
            count(case when open>1 then 1 else null end) as `clicks`
            FROM " . NEWSLETTER_SENT_TABLE . " where email_id=%d", $email->id));

            $report->total = $data->total;
            $report->error_count = $data->errors;
            $report->open_count = $data->opens;
            $report->click_count = $data->clicks;
        }

        $report->update();

        return $report;
    }

    /**
     * Three-state criteria of the per-subscriber report: any, yes or no.
     *
     * @return array key => label
     */
    function get_subscriber_criteria() {
        return [
            'delivery' => __('Sent without errors', 'newsletter'),
            'opened' => __('Opened', 'newsletter'),
            'clicked' => __('Clicked', 'newsletter'),
        ];
    }

    /**
     * Quick filters (presets) of the per-subscriber report, each one a set of filter parameters.
     *
     * @return array key => [label, params]
     */
    function get_subscriber_presets() {
        return [
            'all' => [__('All', 'newsletter'), []],
            'sent' => [__('Sent', 'newsletter'), ['delivery' => 'yes']],
            'errors' => [__('Errors', 'newsletter'), ['delivery' => 'no']],
            'opened' => [__('Opened', 'newsletter'), ['opened' => 'yes']],
            'clicked' => [__('Clicked', 'newsletter'), ['clicked' => 'yes']],
            'opened-not-clicked' => [__('Opened but not clicked', 'newsletter'), ['opened' => 'yes', 'clicked' => 'no']],
            'not-opened' => [__('Not opened', 'newsletter'), ['opened' => 'no']],
        ];
    }

    /**
     * Normalizes the filters of the per-subscriber report from a request (usually $_GET). Single source of
     * truth for the screen, the CSV export and the "add to list" action.
     *
     * Legacy parameter "filter" (all, opened, not-opened, clicked, not-clicked, errors) is still accepted.
     *
     * @param array $src
     * @return array ['delivery', 'opened', 'clicked' (any|yes|no), 'lists' (int[]), 'lists_mode' (any|all), 'url' (string)]
     */
    function parse_subscriber_filters($src) {
        $f = ['delivery' => 'any', 'opened' => 'any', 'clicked' => 'any', 'lists' => [], 'lists_mode' => 'any', 'url' => ''];

        $legacy = [
            'opened' => ['opened' => 'yes'],
            'not-opened' => ['opened' => 'no'],
            'clicked' => ['clicked' => 'yes'],
            'not-clicked' => ['clicked' => 'no'],
            'errors' => ['delivery' => 'no'],
        ];
        $filter = isset($src['filter']) && is_string($src['filter']) ? sanitize_key(wp_unslash($src['filter'])) : '';
        if (isset($legacy[$filter])) {
            $f = array_merge($f, $legacy[$filter]);
        }

        foreach (array_keys($this->get_subscriber_criteria()) as $key) {
            $value = isset($src[$key]) && is_string($src[$key]) ? sanitize_key(wp_unslash($src[$key])) : '';
            if (in_array($value, ['any', 'yes', 'no'], true)) {
                $f[$key] = $value;
            }
        }

        if (isset($src['lists'])) {
            $raw = $src['lists'];
            if (is_string($raw)) {
                $raw = explode(',', $raw);
            }
            if (is_array($raw)) {
                $valid = [];
                foreach (NewsletterAdmin::instance()->get_lists() as $list) {
                    $valid[(int) $list->id] = true;
                }
                foreach ($raw as $id) {
                    $id = (int) $id;
                    if (isset($valid[$id])) {
                        $f['lists'][$id] = $id;
                    }
                }
                $f['lists'] = array_values($f['lists']);
                sort($f['lists']);
            }
        }
        if (isset($src['lists_mode']) && $src['lists_mode'] === 'all') {
            $f['lists_mode'] = 'all';
        }

        if (isset($src['url']) && is_string($src['url'])) {
            $f['url'] = wp_unslash($src['url']);
        }
        return $f;
    }

    /**
     * Filter parameters as a query string array (only the non default ones).
     */
    function get_subscriber_filter_args($f) {
        $args = [];
        foreach (array_keys($this->get_subscriber_criteria()) as $key) {
            if ($f[$key] !== 'any') {
                $args[$key] = $f[$key];
            }
        }
        if ($f['lists']) {
            $args['lists'] = implode(',', $f['lists']);
            if ($f['lists_mode'] === 'all') {
                $args['lists_mode'] = 'all';
            }
        }
        if ($f['url'] !== '') {
            $args['url'] = $f['url'];
        }
        return $args;
    }

    /**
     * Short slug that describes the active filters, used in the CSV file name.
     */
    function get_subscriber_filter_slug($f) {
        $parts = [];
        $names = ['delivery' => 'sent', 'opened' => 'opened', 'clicked' => 'clicked'];
        foreach ($names as $key => $name) {
            if ($f[$key] === 'yes') {
                $parts[] = $name;
            } elseif ($f[$key] === 'no') {
                $parts[] = $key === 'delivery' ? 'errors' : 'not-' . $name;
            }
        }
        if ($f['lists']) {
            $parts[] = 'lists-' . implode('-', $f['lists']);
        }
        if ($f['url'] !== '') {
            $parts[] = 'url';
        }
        return $parts ? implode('_', $parts) : 'all';
    }

    /**
     * Builds the where clause (prepared) on the sent table (alias "s") joined with the subscribers (alias "u").
     */
    private function get_subscribers_where($email_id, $f) {
        global $wpdb;
        $where = $wpdb->prepare('s.email_id=%d', (int) $email_id);

        if ($f['delivery'] === 'yes') {
            $where .= ' and s.status=0';
        } elseif ($f['delivery'] === 'no') {
            $where .= ' and s.status>0';
        } elseif ($f['opened'] === 'no' || $f['clicked'] === 'no') {
            // Who had an error did not really have the chance to open or click
            $where .= ' and s.status=0';
        }

        if ($f['opened'] === 'yes') {
            $where .= ' and s.open>=1';
        } elseif ($f['opened'] === 'no') {
            $where .= ' and s.open=0';
        }

        if ($f['clicked'] === 'yes') {
            $where .= ' and s.open>=2';
        } elseif ($f['clicked'] === 'no') {
            $where .= ' and s.open<2';
        }

        if ($f['lists']) {
            $conditions = [];
            foreach ($f['lists'] as $id) {
                $conditions[] = 'u.list_' . (int) $id . '=1'; // Validated integers only
            }
            $where .= ' and (' . implode($f['lists_mode'] === 'all' ? ' and ' : ' or ', $conditions) . ')';
        }

        if ($f['url'] !== '') {
            $where .= $wpdb->prepare(' and s.user_id in (select c.user_id from ' . NEWSLETTER_STATS_TABLE . ' c where c.email_id=%s and c.url=%s)', (string) (int) $email_id, $f['url']);
        }
        return $where;
    }

    function count_subscribers($email_id, $f) {
        global $wpdb;
        $where = $this->get_subscribers_where($email_id, $f);
        return (int) $wpdb->get_var('select count(*) from ' . NEWSLETTER_SENT_TABLE . ' s left join ' . NEWSLETTER_USERS_TABLE . ' u on u.id=s.user_id where ' . $where);
    }

    /**
     * @return array Rows with the sent data and the subscriber data (null if the subscriber was removed). The
     * "lists" property is the array of list IDs the subscriber is in now.
     */
    function get_subscribers($email_id, $f, $limit = 50, $offset = 0) {
        global $wpdb;
        $where = $this->get_subscribers_where($email_id, $f);
        $list_ids = [];
        $columns = '';
        foreach (NewsletterAdmin::instance()->get_lists() as $list) {
            $list_ids[] = (int) $list->id;
            $columns .= ', u.list_' . (int) $list->id;
        }
        $sql = 'select s.user_id, u.email, u.name, u.surname, u.status, s.time as sent_time, s.status as sent_status, s.open as sent_open, s.ip, s.error' . $columns
                . ' from ' . NEWSLETTER_SENT_TABLE . ' s left join ' . NEWSLETTER_USERS_TABLE . ' u on u.id=s.user_id'
                . ' where ' . $where . ' order by s.time asc, s.user_id asc';
        if ($limit > 0) {
            $sql .= $wpdb->prepare(' limit %d offset %d', (int) $limit, (int) $offset);
        }
        $rows = $wpdb->get_results($sql);
        if (!is_array($rows)) {
            return [];
        }
        foreach ($rows as $row) {
            $row->lists = [];
            foreach ($list_ids as $id) {
                $key = 'list_' . $id;
                if (!empty($row->$key)) {
                    $row->lists[] = $id;
                }
            }
        }
        return $rows;
    }

    /**
     * Names of the lists (from a list of IDs) separated by the given string.
     */
    function get_list_names($ids, $separator = ', ') {
        $names = [];
        $lists = NewsletterAdmin::instance()->get_lists();
        foreach ($ids as $id) {
            if (isset($lists['' . $id])) {
                $names[] = $lists['' . $id]->name;
            }
        }
        return implode($separator, $names);
    }

    /**
     * URLs clicked in a newsletter with the number of clicks and of distinct subscribers.
     */
    function get_clicked_urls($email_id) {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare('select url, count(*) as number, count(distinct user_id) as people from ' . NEWSLETTER_STATS_TABLE . ' where email_id=%s and url<>\'\' group by url order by number desc, url asc limit 500', (string) (int) $email_id));
        return is_array($rows) ? $rows : [];
    }

    /**
     * Adds to a list the confirmed subscribers selected by the filters. Returns the number of subscribers changed.
     */
    function add_subscribers_to_list($list, $email_id, $f) {
        global $wpdb;
        $list = (int) $list;
        if ($list < 1 || $list > NEWSLETTER_LIST_MAX) {
            return 0;
        }
        $column = 'list_' . $list;
        $where = $this->get_subscribers_where($email_id, $f);
        $wpdb->query("update " . NEWSLETTER_USERS_TABLE . " u join " . NEWSLETTER_SENT_TABLE . " s on s.user_id=u.id set u." . $column . "=1 where u.status='C' and u." . $column . "=0 and " . $where);
        return (int) $wpdb->rows_affected;
    }

    /**
     * Newsletters sent to a subscriber with open/click state.
     */
    function get_user_history($user_id) {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare('select e.id, e.subject, s.time, s.open, s.error from ' . NEWSLETTER_SENT_TABLE . ' s join ' . NEWSLETTER_EMAILS_TABLE . ' e on e.id=s.email_id where s.user_id=%d order by s.time desc, e.id desc limit 200', (int) $user_id));
        return is_array($rows) ? $rows : [];
    }

    /**
     * Distinct links clicked by a subscriber.
     */
    function get_user_clicks($user_id) {
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare('select email_id, url from ' . NEWSLETTER_STATS_TABLE . ' where user_id=%d and url<>\'\' group by email_id, url order by email_id desc limit 1000', (int) $user_id));
        return is_array($rows) ? $rows : [];
    }

    function hook_admin_init() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if (empty($_GET['export']) || ($_GET['page'] ?? '') !== 'newsletter_statistics_view-users') {
            return;
        }
        if (!Newsletter::instance()->is_allowed()) {
            wp_die('Forbidden', '', ['response' => 403]);
        }
        check_admin_referer('tnp-statistics-export');
        $this->export_subscribers();
    }

    private function export_subscribers() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $email_id = (int) ($_GET['id'] ?? 0);
        $f = $this->parse_subscriber_filters($_GET);

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-' . $email_id . '-' . $this->get_subscriber_filter_slug($f) . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['email', 'name', 'surname', 'status', 'sent_on', 'delivered', 'opened', 'clicked', 'ip', 'error', 'lists']);
        $offset = 0;
        do {
            $rows = $this->get_subscribers($email_id, $f, 1000, $offset);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $this->csv_cell($row->email),
                    $this->csv_cell($row->name),
                    $this->csv_cell($row->surname),
                    $row->status,
                    $row->sent_time ? gmdate('Y-m-d H:i:s', (int) $row->sent_time) : '',
                    $row->sent_status ? 0 : 1,
                    $row->sent_open >= 1 ? 1 : 0,
                    $row->sent_open >= 2 ? 1 : 0,
                    $row->ip,
                    $this->csv_cell($row->error),
                    $this->csv_cell($this->get_list_names($row->lists, ' | ')),
                ]);
            }
            $offset += 1000;
        } while (count($rows) === 1000);
        fclose($out);
        die();
    }

    /**
     * Neutralizes spreadsheet formula injection.
     */
    private function csv_cell($value) {
        $value = (string) $value;
        if ($value !== '' && strpbrk($value[0], "=+-@\t\r") !== false) {
            return "'" . $value;
        }
        return $value;
    }

    public function get_date_badge($email) {
        if (!$email->send_on) {
            return '';
        } else {
            return '<span>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $email->send_on)) . '</span>';
        }
    }
}

class TNP_Statistics {

    var $email_id;
    var $total = 0;
    var $error_count = 0;
    var $open_count = 0;
    var $open_rate = 0;
    var $click_count = 0;
    var $click_rate = 0;
    var $reactivity = 0;

    /**
     * Recomputes the rates using the absolute values already set.
     */
    function update() {
        if ($this->total > 0) {
            $this->open_rate = round($this->open_count / $this->total * 100, 2);
            $this->click_rate = round($this->click_count / $this->total * 100, 2);
        } else {
            $this->open_rate = 0;
            $this->click_rate = 0;
        }

        if ($this->open_count > 0) {
            $this->reactivity = round($this->click_count / $this->open_count * 100, 2);
        }
    }
}
