<?php
/* @var $this NewsletterSubscriptionAdmin */
/* @var $controls NewsletterControls */
/* @var $logger NewsletterLogger */

defined('ABSPATH') || exit;
?>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <div id="tnp-heading">
        <?php $controls->title_help('/subscription') ?>
<!--        <h2><?php esc_html_e('Newsletters', 'newsletter') ?></h2>-->
        <?php include __DIR__ . '/nav.php' ?>

    </div>

    <div id="tnp-body">


        <?php $controls->show(); ?>

        <?php if (!class_exists('NewsletterAutomated')) { ?>

            <p>
                <?php esc_html_e('Recurring newsletters are not available in this installation.', 'newsletter') ?>
            </p>


        <?php } else { ?>

            <p>
                Configure your recurring newsletters on the <a href="?page=newsletter_automated_index">Automated settings page</a>.
            </p>

        <?php } ?>

    </div>
</div>

