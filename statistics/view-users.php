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

$filters = $this->get_subscriber_filters();
$filter = sanitize_key($_GET['filter'] ?? 'all');
if (!isset($filters[$filter])) {
    $filter = 'all';
}
$url = isset($_GET['url']) ? wp_unslash($_GET['url']) : '';
if (!is_string($url)) {
    $url = '';
}

if ($controls->is_action('set')) {
    $list = (int) ($controls->data['list'] ?? 0);
    if ($list < 1) {
        $controls->errors .= esc_html__('Select a list', 'newsletter');
    } else {
        $changed = $this->add_subscribers_to_list($list, $email->id, $filter, $url);
        /* translators: %d: number of subscribers */
        $controls->messages .= esc_html(sprintf(_n('%d confirmed subscriber added to the list.', '%d confirmed subscribers added to the list.', $changed, 'newsletter'), $changed));
    }
}

$total = $this->count_subscribers($email->id, $filter, $url);
$per_page = NewsletterStatisticsAdmin::SUBSCRIBERS_PER_PAGE;
$pages = max(1, (int) ceil($total / $per_page));
$page = min($pages, max(1, (int) ($_GET['paged'] ?? 1)));
$list = $this->get_subscribers($email->id, $filter, $url, $per_page, ($page - 1) * $per_page);

$base_args = ['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id];
if ($url !== '') {
    $base_args['url'] = $url;
}
$export_url = wp_nonce_url(add_query_arg(array_merge($base_args, ['filter' => $filter, 'export' => 1]), admin_url('admin.php')), 'tnp-statistics-export');

if (empty($email->track)) {
    $controls->warnings[] = __('This newsletter has the tracking disabled. No statistics will be available.', 'newsletter');
}
?>
<style>
<?php include __DIR__ . '/style.css'; ?>
    .tnp-yes, .tnp-no { display: inline-block; width: 18px; height: 18px; border-radius: 50%; color: #fff; text-align: center; line-height: 18px; font-size: 12px; }
    .tnp-yes { background: #27AE60; }
    .tnp-no { background: #ccc; }
    .tnp-stat-filters a { margin-right: 1rem; }
    .tnp-stat-filters a.current { font-weight: bold; text-decoration: none; color: #000; }
</style>
<div class="wrap tnp-statistics tnp-statistics-view" id="tnp-wrap">
    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <?php include __DIR__ . '/view-heading.php' ?>

    <div id="tnp-body">

        <?php $controls->show(); ?>

        <p class="tnp-stat-filters">
            <?php foreach ($filters as $key => $label) { ?>
                <a href="<?php echo esc_attr(add_query_arg(array_merge($base_args, ['filter' => $key]), admin_url('admin.php'))) ?>"
                   class="<?php echo $key === $filter ? 'current' : '' ?>"><?php echo esc_html($label) ?></a>
               <?php } ?>
        </p>

        <?php if ($url !== '') { ?>
            <p>
                <?php esc_html_e('Subscribers who clicked', 'newsletter') ?>:
                <code><?php echo esc_html($url) ?></code>
                <a href="<?php echo esc_attr(add_query_arg(['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id, 'filter' => $filter], admin_url('admin.php'))) ?>">(<?php esc_html_e('remove', 'newsletter') ?>)</a>
            </p>
        <?php } ?>

        <form action="" method="post">
            <?php $controls->init() ?>
            <p>
                <strong><?php echo (int) $total ?></strong> <?php esc_html_e('subscribers', 'newsletter') ?>
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
                    <th>IP</th>
                    <th><?php esc_html_e('Error', 'newsletter') ?></th>
                </tr>
            </thead>

            <?php if (!$list) { ?>
                <tr><td colspan="8"><?php esc_html_e('No subscribers found', 'newsletter') ?></td></tr>
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
                    <td><?php echo esc_html($sub->ip) ?></td>
                    <td><?php echo esc_html($sub->error) ?></td>
                </tr>
            <?php } ?>
        </table>

        <?php if ($pages > 1) { ?>
            <div class="tablenav"><div class="tablenav-pages">
                    <?php
                    echo wp_kses_post(paginate_links([
                        'base' => add_query_arg(array_merge($base_args, ['filter' => $filter, 'paged' => '%#%']), admin_url('admin.php')),
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
