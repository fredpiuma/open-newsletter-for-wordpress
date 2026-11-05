<?php
// phpcs:disable WordPress.Security.NonceVerification.Recommended

/* @var $this NewsletterStatisticsAdmin */
/* @var $controls NewsletterControls */

defined('ABSPATH') || exit;

$email = $this->get_email((int) ($_GET['id'] ?? 0));
if (empty($email)) {
    echo 'Newsletter not found';
    return;
}

$f = $this->parse_subscriber_filters($_GET);
$criteria = $this->get_subscriber_criteria();
$presets = $this->get_subscriber_presets();
$all_lists = NewsletterAdmin::instance()->get_lists();

if ($controls->is_action('set')) {
    $list = (int) ($controls->data['list'] ?? 0);
    if ($list < 1) {
        $controls->errors .= esc_html__('Select a list', 'newsletter');
    } else {
        $changed = $this->add_subscribers_to_list($list, $email->id, $f);
        /* translators: %d: number of subscribers */
        $controls->messages .= esc_html(sprintf(_n('%d confirmed subscriber added to the list.', '%d confirmed subscribers added to the list.', $changed, 'newsletter'), $changed));
    }
}

$total = $this->count_subscribers($email->id, $f);
$per_page = NewsletterStatisticsAdmin::SUBSCRIBERS_PER_PAGE;
$pages = max(1, (int) ceil($total / $per_page));
$page = min($pages, max(1, (int) ($_GET['paged'] ?? 1)));
$list = $this->get_subscribers($email->id, $f, $per_page, ($page - 1) * $per_page);

$base_args = array_merge(['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id], $this->get_subscriber_filter_args($f));
$export_url = wp_nonce_url(add_query_arg(array_merge($base_args, ['export' => 1]), admin_url('admin.php')), 'tnp-statistics-export');

// Human readable summary of the active filters
$summary = [];
$state_labels = ['yes' => __('Yes', 'newsletter'), 'no' => __('No', 'newsletter')];
foreach ($criteria as $key => $label) {
    if ($f[$key] !== 'any') {
        $summary[] = $label . ': ' . $state_labels[$f[$key]];
    }
}
if ($f['lists']) {
    $summary[] = __('Lists', 'newsletter') . ': ' . $this->get_list_names($f['lists'])
            . ' (' . ($f['lists_mode'] === 'all' ? __('all of them', 'newsletter') : __('any of them', 'newsletter')) . ')';
}

if (empty($email->track)) {
    $controls->warnings[] = __('This newsletter has the tracking disabled. No statistics will be available.', 'newsletter');
}
?>
<style>
<?php include __DIR__ . '/style.css'; ?>
    .tnp-yes, .tnp-no { display: inline-block; width: 18px; height: 18px; border-radius: 50%; color: #fff; text-align: center; line-height: 18px; font-size: 12px; }
    .tnp-yes { background: #27AE60; }
    .tnp-no { background: #ccc; }
    .tnp-stat-presets a { margin-right: 1rem; }
    #tnp-body .tnp-stat-presets a.current { font-weight: bold; text-decoration: none; color: #000; }
    .tnp-stat-filter-form { background: #fff; border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; }
    .tnp-stat-filter-form .tnp-stat-row { display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-end; margin-bottom: .75rem; }
    .tnp-stat-filter-form label { display: block; font-weight: bold; margin-bottom: 3px; }
    .tnp-stat-filter-form .tnp-stat-lists label { display: inline-block; font-weight: normal; margin-right: 1rem; }
</style>
<div class="wrap tnp-statistics tnp-statistics-view" id="tnp-wrap">
    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <?php include __DIR__ . '/view-heading.php' ?>

    <div id="tnp-body">

        <?php $controls->show(); ?>

        <p class="tnp-stat-presets">
            <?php foreach ($presets as $key => $preset) { ?>
                <?php
                $preset_f = $this->parse_subscriber_filters(array_merge($preset[1], ['lists' => $f['lists'], 'lists_mode' => $f['lists_mode'], 'url' => $f['url']]));
                $is_current = $this->get_subscriber_filter_args(array_merge($f, ['lists' => [], 'url' => ''])) == $this->get_subscriber_filter_args(array_merge($preset_f, ['lists' => [], 'url' => '']));
                ?>
                <a href="<?php echo esc_attr(add_query_arg(array_merge(['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id], $this->get_subscriber_filter_args($preset_f)), admin_url('admin.php'))) ?>"
                   class="<?php echo $is_current ? 'current' : '' ?>"><?php echo esc_html($preset[0]) ?></a>
               <?php } ?>
        </p>

        <form action="<?php echo esc_attr(admin_url('admin.php')) ?>" method="get" class="tnp-stat-filter-form">
            <input type="hidden" name="page" value="newsletter_statistics_view-users">
            <input type="hidden" name="id" value="<?php echo (int) $email->id ?>">
            <?php if ($f['url'] !== '') { ?>
                <input type="hidden" name="url" value="<?php echo esc_attr($f['url']) ?>">
            <?php } ?>

            <div class="tnp-stat-row">
                <?php foreach ($criteria as $key => $label) { ?>
                    <div>
                        <label for="tnp-f-<?php echo esc_attr($key) ?>"><?php echo esc_html($label) ?></label>
                        <select name="<?php echo esc_attr($key) ?>" id="tnp-f-<?php echo esc_attr($key) ?>">
                            <option value="any" <?php selected($f[$key], 'any') ?>><?php esc_html_e('Any state', 'newsletter') ?></option>
                            <option value="yes" <?php selected($f[$key], 'yes') ?>><?php esc_html_e('Yes', 'newsletter') ?></option>
                            <option value="no" <?php selected($f[$key], 'no') ?>><?php esc_html_e('No', 'newsletter') ?></option>
                        </select>
                    </div>
                <?php } ?>
            </div>

            <?php if ($all_lists) { ?>
                <div class="tnp-stat-row">
                    <div class="tnp-stat-lists">
                        <label><?php esc_html_e('Lists', 'newsletter') ?> <span class="description">(<?php esc_html_e('none selected = all subscribers; it uses the lists the subscriber is in now', 'newsletter') ?>)</span></label>
                        <?php foreach ($all_lists as $item) { ?>
                            <label><input type="checkbox" name="lists[]" value="<?php echo (int) $item->id ?>" <?php checked(in_array((int) $item->id, $f['lists'], true)) ?>> <?php echo esc_html($item->name) ?></label>
                        <?php } ?>
                    </div>
                    <div>
                        <label for="tnp-f-lists-mode"><?php esc_html_e('Match', 'newsletter') ?></label>
                        <select name="lists_mode" id="tnp-f-lists-mode">
                            <option value="any" <?php selected($f['lists_mode'], 'any') ?>><?php esc_html_e('Any of the selected lists', 'newsletter') ?></option>
                            <option value="all" <?php selected($f['lists_mode'], 'all') ?>><?php esc_html_e('All the selected lists', 'newsletter') ?></option>
                        </select>
                    </div>
                </div>
            <?php } ?>

            <p style="margin: 0">
                <button type="submit" class="button button-primary"><?php esc_html_e('Filter', 'newsletter') ?></button>
                <a class="button button-secondary" href="<?php echo esc_attr(add_query_arg(['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id], admin_url('admin.php'))) ?>"><?php esc_html_e('Clear', 'newsletter') ?></a>
            </p>
        </form>

        <?php if ($f['url'] !== '') { ?>
            <p>
                <?php esc_html_e('Subscribers who clicked', 'newsletter') ?>:
                <code><?php echo esc_html($f['url']) ?></code>
                <a href="<?php echo esc_attr(add_query_arg(array_diff_key($base_args, ['url' => 1]), admin_url('admin.php'))) ?>">(<?php esc_html_e('remove', 'newsletter') ?>)</a>
            </p>
        <?php } ?>

        <form action="" method="post">
            <?php $controls->init() ?>
            <p>
                <strong><?php echo (int) $total ?></strong> <?php esc_html_e('subscribers', 'newsletter') ?>
                <?php if ($summary) { ?>
                    <span class="description">&mdash; <?php echo esc_html(implode(' · ', $summary)) ?></span>
                <?php } ?>
                &nbsp;
                <a class="button button-secondary" href="<?php echo esc_attr($export_url) ?>"><?php esc_html_e('Export CSV', 'newsletter') ?></a>
                &nbsp;&nbsp;
                <?php $controls->lists_select('list', __('Add the confirmed ones to the list...', 'newsletter')) ?>
                <?php $controls->btn('set', __('Add', 'newsletter'), ['secondary' => true]) ?>
            </p>
        </form>

        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Email/Name', 'newsletter') ?></th>
                    <th><?php esc_html_e('Status', 'newsletter') ?></th>
                    <th><?php esc_html_e('Sent on', 'newsletter') ?></th>
                    <th><?php esc_html_e('Delivered', 'newsletter') ?></th>
                    <th><?php esc_html_e('Open', 'newsletter') ?></th>
                    <th><?php esc_html_e('Click', 'newsletter') ?></th>
                    <th><?php esc_html_e('Lists', 'newsletter') ?></th>
                    <th>IP</th>
                    <th><?php esc_html_e('Error', 'newsletter') ?></th>
                </tr>
            </thead>

            <?php if (!$list) { ?>
                <tr><td colspan="9"><?php esc_html_e('No subscribers found', 'newsletter') ?></td></tr>
            <?php } ?>

            <?php foreach ($list as $sub) { ?>
                <tr>
                    <td>
                        <?php if ($sub->email) { ?>
                            <a href="?page=newsletter_users_edit&id=<?php echo (int) $sub->user_id ?>"><?php echo esc_html($sub->email) ?></a><br>
                            <?php echo esc_html(trim($sub->name . ' ' . $sub->surname)) ?>
                        <?php } else { ?>
                            <em><?php esc_html_e('Subscriber removed', 'newsletter') ?></em>
                        <?php } ?>
                    </td>
                    <td>
                        <?php
                        if ($sub->status) {
                            echo $this->get_user_status_label($sub, true);
                        }
                        ?>
                    </td>
                    <td><?php echo $sub->sent_time ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $sub->sent_time)) : '-' ?></td>
                    <td><span class="<?php echo $sub->sent_status ? 'tnp-no' : 'tnp-yes' ?>"><?php echo $sub->sent_status ? '&times;' : '&check;' ?></span></td>
                    <td><span class="<?php echo $sub->sent_open >= 1 ? 'tnp-yes' : 'tnp-no' ?>"><?php echo $sub->sent_open >= 1 ? '&check;' : '&times;' ?></span></td>
                    <td><span class="<?php echo $sub->sent_open >= 2 ? 'tnp-yes' : 'tnp-no' ?>"><?php echo $sub->sent_open >= 2 ? '&check;' : '&times;' ?></span></td>
                    <td><?php echo esc_html($this->get_list_names($sub->lists)) ?></td>
                    <td><?php echo esc_html($sub->ip) ?></td>
                    <td><?php echo esc_html($sub->error) ?></td>
                </tr>
            <?php } ?>
        </table>

        <?php if ($pages > 1) { ?>
            <div class="tablenav"><div class="tablenav-pages">
                    <?php
                    echo wp_kses_post(paginate_links([
                        'base' => add_query_arg(array_merge($base_args, ['paged' => '%#%']), admin_url('admin.php')),
                        'format' => '',
                        'current' => $page,
                        'total' => $pages,
                    ]));
                    ?>
                </div></div>
        <?php } ?>
    </div>
    <?php include NEWSLETTER_ADMIN_FOOTER; ?>
</div>
