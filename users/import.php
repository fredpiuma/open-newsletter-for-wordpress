<?php
/* @var $this NewsletterUsersAdmin */
/* @var $controls NewsletterControls */

defined('ABSPATH') || exit;

$import_report = null;

if ($controls->is_action('import')) {
    // The nonce is verified by is_action().
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $file = $_FILES['import_file'] ?? null;
    if (empty($file) || empty($file['tmp_name']) || !empty($file['error']) || !is_uploaded_file($file['tmp_name'])) {
        $controls->errors = esc_html__('Select a CSV file to import', 'newsletter');
    } elseif ($file['size'] > 10 * MB_IN_BYTES) {
        $controls->errors = esc_html__('The file is too big (10 MB max)', 'newsletter');
    } else {
        $list = (int) ($controls->data['import_list'] ?? 0);
        $status = ($controls->data['import_status'] ?? 'C') === 'S' ? 'S' : 'C';
        $import_report = $this->import_csv($file['tmp_name'], $status, $list);
        if ($import_report === false) {
            $controls->errors = esc_html__('The file cannot be read', 'newsletter');
        }
    }
}
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <div id="tnp-heading">
        <h2><?php esc_html_e('Import/Export', 'newsletter') ?></h2>
        <?php include __DIR__ . '/nav.php' ?>
    </div>

    <div id="tnp-body" class="tnp-users tnp-users-import">

        <?php $controls->show(); ?>

        <?php if ($import_report) { ?>
            <div class="notice notice-success inline">
                <p>
                    <?php
                    /* translators: 1: imported, 2: already existing, 3: invalid */
                    echo esc_html(sprintf(__('Imported: %1$d. Already existing: %2$d. Invalid rows: %3$d.', 'newsletter'), $import_report['imported'], $import_report['existing'], $import_report['invalid']));
                    ?>
                </p>
            </div>
        <?php } ?>

        <form method="post" action="" enctype="multipart/form-data">
            <?php $controls->init(); ?>

            <h3><?php esc_html_e('Import', 'newsletter') ?></h3>
            <p>
                <?php esc_html_e('CSV file, one subscriber per row. Columns: email, name, surname (a header row is optional; comma or semicolon separated, UTF-8).', 'newsletter') ?>
            </p>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('File', 'newsletter') ?></th>
                    <td><input type="file" name="import_file" accept=".csv,.txt,text/csv"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Status', 'newsletter') ?></th>
                    <td>
                        <?php $controls->data['import_status'] = $controls->data['import_status'] ?? 'C'; ?>
                        <?php $controls->select('import_status', ['C' => TNP_User::get_status_label('C'), 'S' => TNP_User::get_status_label('S')]); ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Add to list', 'newsletter') ?></th>
                    <td><?php $controls->lists_select('import_list', __('None', 'newsletter')); ?></td>
                </tr>
            </table>
            <p><?php $controls->btn('import', __('Import', 'newsletter')); ?></p>
        </form>

        <hr>

        <form method="get" action="<?php echo esc_attr(admin_url('admin.php')) ?>">
            <input type="hidden" name="page" value="newsletter_users_import">
            <input type="hidden" name="export" value="1">
            <?php wp_nonce_field('tnp-users-export', '_wpnonce', false); ?>

            <h3><?php esc_html_e('Export', 'newsletter') ?></h3>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e('Status', 'newsletter') ?></th>
                    <td>
                        <select name="status">
                            <option value=""><?php esc_html_e('All', 'newsletter') ?></option>
                            <?php foreach (['C', 'S', 'U', 'B', 'P'] as $code) { ?>
                                <option value="<?php echo esc_attr($code) ?>"><?php echo esc_html(TNP_User::get_status_label($code)) ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('List', 'newsletter') ?></th>
                    <td>
                        <select name="list">
                            <option value=""><?php esc_html_e('All', 'newsletter') ?></option>
                            <?php foreach (NewsletterAdmin::instance()->get_lists() as $list_item) { ?>
                                <option value="<?php echo (int) $list_item->id ?>"><?php echo esc_html($list_item->name) ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </table>
            <p><button type="submit" class="button button-primary"><?php esc_html_e('Export CSV', 'newsletter') ?></button></p>
        </form>

    </div>

    <?php include NEWSLETTER_ADMIN_FOOTER; ?>

</div>
