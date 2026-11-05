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

if ($controls->is_action('set')) {
    $list = (int) ($controls->data['list'] ?? 0);
    $url = isset($controls->data['url']) && is_string($controls->data['url']) ? $controls->data['url'] : '';
    if ($list < 1 || $url === '') {
        $controls->errors .= esc_html__('Select a list', 'newsletter');
    } else {
        $changed = $this->add_subscribers_to_list($list, $email->id, 'all', $url);
        /* translators: %d: number of subscribers */
        $controls->messages .= esc_html(sprintf(_n('%d confirmed subscriber added to the list.', '%d confirmed subscribers added to the list.', $changed, 'newsletter'), $changed));
    }
}

$items = $this->get_clicked_urls($email->id);

$total = array_reduce($items, function ($carry, $item) {
    return $carry + (int) $item->number;
}, 0);

if (empty($email->track)) {
    $controls->warnings[] = __('This newsletter has the tracking disabled. No statistics will be available.', 'newsletter');
}
?>
<style>
<?php include __DIR__ . '/style.css'; ?>
</style>
<div class="wrap tnp-statistics tnp-statistics-view" id="tnp-wrap">
    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <?php include __DIR__ . '/view-heading.php' ?>

    <div id="tnp-body">

        <?php $controls->show(); ?>

        <table class="widefat">
            <colgroup>
                <col class="w-60">
                <col class="w-10">
                <col class="w-10">
                <col class="w-10">
            </colgroup>
            <thead>
                <tr class="text-left">
                    <th><?php esc_html_e('Clicked URLs', 'newsletter') ?></th>
                    <th><?php esc_html_e('Clicks', 'newsletter') ?></th>
                    <th>%</th>
                    <th><?php esc_html_e('Subscribers', 'newsletter') ?></th>
                    <th><?php esc_html_e('Who clicked...', 'newsletter') ?></th>
                </tr>
            </thead>
            <tbody>

                <?php if (!$items) { ?>
                    <tr><td colspan="5"><?php esc_html_e('No clicks recorded for this newsletter', 'newsletter') ?></td></tr>
                <?php } ?>

                <?php foreach ($items as $item) { ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url($item->url) ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($item->url); ?>
                            </a>
                        </td>
                        <td><?php echo (int) $item->number ?></td>
                        <td><?php echo esc_html(NewsletterModule::percent($item->number, $total)); ?></td>
                        <td>
                            <a href="<?php echo esc_attr(add_query_arg(['page' => 'newsletter_statistics_view-users', 'id' => (int) $email->id, 'url' => $item->url], admin_url('admin.php'))) ?>"><?php echo (int) $item->people ?></a>
                        </td>
                        <td>
                            <form action="" method="post">
                                <?php $controls->init() ?>
                                <?php $controls->data['url'] = $item->url; ?>
                                <?php $controls->hidden('url') ?>
                                <?php $controls->lists_select() ?>
                                <?php $controls->btn('set', __('Add to this list', 'newsletter'), ['secondary' => true]) ?>
                            </form>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>

    </div>
    <?php include NEWSLETTER_ADMIN_FOOTER; ?>
</div>
