<?php
/* @var $this NewsletterUsersAdmin */
/* @var $controls NewsletterControls */

defined('ABSPATH') || exit;

$user = $this->get_user((int) ($_GET['id'] ?? -1));
if (!$user) {
    die('User not found');
}

$stats_admin = NewsletterStatisticsAdmin::instance();
$history = $stats_admin->get_user_history($user->id);
$clicks_by_email = [];
foreach ($stats_admin->get_user_clicks($user->id) as $click) {
    $clicks_by_email[(int) $click->email_id][] = $click;
}
?>

<div class="wrap tnp-users tnp-users-edit" id="tnp-wrap">

    <?php include NEWSLETTER_ADMIN_HEADER ?>

    <div id="tnp-heading">
        <?php $controls->title_help('/subscribers-and-management/') ?>
        <h2><?php echo esc_html($user->email) ?></h2>
        <?php include __DIR__ . '/edit-nav.php' ?>
    </div>

    <div id="tnp-body">

        <?php $controls->show(); ?>

        <form method="post" action="">

            <?php $controls->init(); ?>

            <div id="tabs">

                <ul>
                    <li><a href="#tabs-newsletters"><?php esc_html_e('Newsletters', 'newsletter') ?></a></li>
                </ul>


                <div id="tabs-newsletters" class="tnp-tab">
                    <?php if (!$history) { ?>
                        <p><?php esc_html_e('No newsletters have been sent to this subscriber', 'newsletter') ?></p>
                    <?php } else { ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Newsletter', 'newsletter') ?></th>
                                    <th><?php esc_html_e('Sent on', 'newsletter') ?></th>
                                    <th><?php esc_html_e('Open', 'newsletter') ?></th>
                                    <th><?php esc_html_e('Click', 'newsletter') ?></th>
                                    <th><?php esc_html_e('Clicked links', 'newsletter') ?></th>
                                    <th><?php esc_html_e('Error', 'newsletter') ?></th>
                                </tr>
                            </thead>
                            <?php foreach ($history as $row) { ?>
                                <tr>
                                    <td>
                                        <a href="?page=newsletter_statistics_view&amp;id=<?php echo (int) $row->id ?>"><?php echo esc_html($row->subject) ?></a>
                                    </td>
                                    <td><?php echo $row->time ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $row->time)) : '-' ?></td>
                                    <td><?php echo $row->open >= 1 ? '&check;' : '&times;' ?></td>
                                    <td><?php echo $row->open >= 2 ? '&check;' : '&times;' ?></td>
                                    <td>
                                        <?php foreach ($clicks_by_email[(int) $row->id] ?? [] as $click) { ?>
                                            <div><?php echo esc_html($click->url) ?></div>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo esc_html($row->error) ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php } ?>
                    <?php
                    // Kept for add-ons written for the original plugin
                    do_action('newsletter_user_newsletters_tab', $user->id);
                    do_action('newsletter_users_edit_newsletters', $user->id);
                    ?>
                </div>

            </div>


        </form>
    </div>

    <?php include NEWSLETTER_ADMIN_FOOTER; ?>

</div>
