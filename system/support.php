<?php
/* @var $this NewsletterSystemAdmin */
/* @var $controls NewsletterControls */

defined('ABSPATH') || exit;
?>

<style>
<?php include __DIR__ . '/css/system.css' ?>
</style>

<div class="wrap" id="tnp-wrap">

    <?php include NEWSLETTER_ADMIN_HEADER; ?>

    <div id="tnp-heading">

        <h2><?php esc_html_e('Support', 'newsletter') ?></h2>

    </div>

    <div id="tnp-body">
        <?php $controls->show(); ?>

        <form method="post" action="">
            <?php $controls->init(); ?>
            <div class="tnp-dashboard">
                <div class="tnp-cards-container">
                    <div class="tnp-card">
                        <div class="tnp-card-title"><?php esc_html_e('How to get support', 'newsletter') ?></div>

                        <h3><i class="fas fa-book"></i> <?php esc_html_e('Documentation', 'newsletter') ?></h3>
                        <p>
                            <?php esc_html_e('The documentation and the source code are in the project repository.', 'newsletter') ?>
                            <a href="https://github.com/fredpiuma/open-newsletter-for-wordpress#readme" target="_blank">github.com/fredpiuma/open-newsletter-for-wordpress</a>
                        </p>

                        <h3><i class="fas fa-bug"></i> <?php esc_html_e('Bugs and ideas', 'newsletter') ?></h3>
                        <p>
                            <?php esc_html_e('Found a problem or have an idea? Open an issue on the repository.', 'newsletter') ?>
                            <a href="https://github.com/fredpiuma/open-newsletter-for-wordpress/issues" target="_blank"><?php esc_html_e('Open issues', 'newsletter') ?></a>
                        </p>
                    </div>

                    <div class="tnp-card">
                        <div class="tnp-card-title">Frederico de Castro</div>
                        <p><strong>Senior Web Developer &amp; Software Architect</strong></p>
                        <p><?php esc_html_e('Need a custom solution for your store or website? Get in touch.', 'newsletter') ?></p>

                        <p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/5511914636944" target="_blank">WhatsApp</a></p>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:contato@fredericodecastro.com.br">contato@fredericodecastro.com.br</a></p>
                        <p><i class="fas fa-globe"></i> <a href="https://www.fredericodecastro.com.br/links/" target="_blank">www.fredericodecastro.com.br</a></p>
                        <p><i class="fab fa-linkedin"></i> <a href="https://www.linkedin.com/in/fredericodecastro/" target="_blank">LinkedIn</a></p>
                        <p><i class="fab fa-telegram"></i> <a href="https://t.me/fredericomdecastro" target="_blank">Telegram</a></p>
                    </div>

                    <div class="tnp-card">
                        <div class="tnp-card-title"><?php esc_html_e('Credits', 'newsletter') ?></div>
                        <p>
                            <?php esc_html_e('Open Newsletter is a free fork (GPL-2.0-or-later) of the Newsletter plugin by Stefano Lissa & The Newsletter Team. See CREDITS.md in the plugin folder.', 'newsletter') ?>
                        </p>
                    </div>

                </div>
            </div>
        </form>
    </div>
    <?php include NEWSLETTER_ADMIN_FOOTER; ?>

</div>
