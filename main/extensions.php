<?php
/* @var $this NewsletterMainAdmin */
/* @var $controls NewsletterControls */

defined('ABSPATH') || exit;

// Local, static list. Nothing here is downloaded or checked remotely.
$integrations = [
    'Subscription sources' => [
        'NewsletterWoocommerce' => 'WooCommerce (checkout and registration)',
        'NewsletterCF7' => 'Contact Form 7',
        'NewsletterWPForms' => 'WPForms',
        'NewsletterNinjaForms' => 'Ninja Forms',
        'NewsletterGravityForms' => 'Gravity Forms',
        'NewsletterElementor' => 'Elementor Forms',
        'NewsletterForminator' => 'Forminator',
        'NewsletterFormidable' => 'Formidable Forms',
        'NewsletterFluentForms' => 'Fluent Forms',
        'NewsletterPmpro' => 'Paid Memberships Pro',
        'NewsletterEdd' => 'Easy Digital Downloads',
        'NewsletterComments' => 'WordPress comments',
        'NewsletterWpUsers' => 'WordPress user registration',
        'NewsletterLeads' => 'Leads (popup, topbar, injection)',
    ],
    'Other' => [
        'NewsletterApi' => 'REST API',
        'NewsletterBlocks' => 'Extra composer blocks',
        'NewsletterImport' => 'Import / export',
    ],
];
?>

<div class="wrap tnp-main tnp-main-extensions" id="tnp-wrap">

    <?php include NEWSLETTER_ADMIN_HEADER ?>

    <div id="tnp-heading">
        <h2><?php esc_html_e('Integrations', 'newsletter') ?></h2>
    </div>

    <div id="tnp-body">

        <?php $controls->show() ?>

        <p>
            <?php esc_html_e('Open Newsletter already includes every feature of the core plugin, with no license required and no external connection.', 'newsletter') ?>
            <?php esc_html_e('The integrations below are provided by separate plugins that, when installed and active, are detected automatically. This list is static and is not synchronized with any remote service.', 'newsletter') ?>
        </p>

        <?php foreach ($integrations as $group => $items) { ?>
            <h3><?php echo esc_html($group) ?></h3>
            <table class="widefat" style="max-width: 600px">
                <?php foreach ($items as $class => $label) { ?>
                    <tr>
                        <td><?php echo esc_html($label) ?></td>
                        <td style="width: 10rem">
                            <?php if (class_exists($class)) { ?>
                                <span class="tnp-badge-green"><?php esc_html_e('Active', 'newsletter') ?></span>
                            <?php } else { ?>
                                <span class="tnp-badge-gray"><?php esc_html_e('Not installed', 'newsletter') ?></span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

    </div>

    <?php include NEWSLETTER_ADMIN_FOOTER ?>

</div>
