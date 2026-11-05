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
     * Filters available in the per-subscriber report of a newsletter.
     *
     * @return array
     */
    function get_subscriber_filters() {
        return [
            'all' => __('All', 'newsletter'),
            'opened' => __('Opened', 'newsletter'),
            'not-opened' => __('Not opened', 'newsletter'),
            'clicked' => __('Clicked', 'newsletter'),
            'not-clicked' => __('Not clicked', 'newsletter'),
            'errors' => __('Errors', 'newsletter'),
        ];
    }

    /**
     * Builds the where clause (prepared) on the sent table (alias "s") for a filter and an optional clicked URL.
     */
    private function get_subscribers_where($email_id, $filter, $url = '') {
        global $wpdb;
        $where = $wpdb->prepare('s.email_id=%d', (int) $email_id);
        switch ($filter) {
            case 'opened':
                $where .= ' and s.open>=1';
                break;
            case 'not-opened':
                $where .= ' and s.open=0 and s.status=0';
                break;
            case 'clicked':
                $where .= ' and s.open>=2';
                break;
            case 'not-clicked':
                $where .= ' and s.open<2 and s.status=0';
                break;
            case 'errors':
                $where .= ' and s.status>0';
                break;
        }
        if ($url !== '' && $url !== null) {
            $where .= $wpdb->prepare(' and s.user_id in (select c.user_id from ' . NEWSLETTER_STATS_TABLE . ' c where c.email_id=%s and c.url=%s)', (string) (int) $email_id, $url);
        }
        return $where;
    }

    function count_subscribers($email_id, $filter = 'all', $url = '') {
        global $wpdb;
        $where = $this->get_subscribers_where($email_id, $filter, $url);
        return (int) $wpdb->get_var('select count(*) from ' . NEWSLETTER_SENT_TABLE . ' s where ' . $where);
    }

    /**
     * @return array Rows with the sent data and the subscriber data (null if the subscriber was removed).
     */
    function get_subscribers($email_id, $filter = 'all', $url = '', $limit = 50, $offset = 0) {
        global $wpdb;
        $where = $this->get_subscribers_where($email_id, $filter, $url);
        $sql = 'select s.user_id, u.email, u.name, u.surname, u.status, s.time as sent_time, s.status as sent_status, s.open as sent_open, s.ip, s.error'
                . ' from ' . NEWSLETTER_SENT_TABLE . ' s left join ' . NEWSLETTER_USERS_TABLE . ' u on u.id=s.user_id'
                . ' where ' . $where . ' order by s.time asc, s.user_id asc';
        if ($limit > 0) {
            $sql .= $wpdb->prepare(' limit %d offset %d', (int) $limit, (int) $offset);
        }
        $rows = $wpdb->get_results($sql);
        return is_array($rows) ? $rows : [];
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
     * Adds to a list the confirmed subscribers selected by a filter. Returns the number of subscribers changed.
     */
    function add_subscribers_to_list($list, $email_id, $filter = 'all', $url = '') {
        global $wpdb;
        $list = (int) $list;
        if ($list < 1 || $list > NEWSLETTER_LIST_MAX) {
            return 0;
        }
        $column = 'list_' . $list;
        $where = $this->get_subscribers_where($email_id, $filter, $url);
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
        $filter = sanitize_key($_GET['filter'] ?? 'all');
        if (!isset($this->get_subscriber_filters()[$filter])) {
            $filter = 'all';
        }
        $url = isset($_GET['url']) && is_string($_GET['url']) ? wp_unslash($_GET['url']) : '';

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletter-' . $email_id . '-' . $filter . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['email', 'name', 'surname', 'status', 'sent_on', 'delivered', 'opened', 'clicked', 'ip', 'error']);
        $offset = 0;
        do {
            $rows = $this->get_subscribers($email_id, $filter, $url, 1000, $offset);
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
