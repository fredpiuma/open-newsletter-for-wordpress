<?php
defined('ABSPATH') || exit;
?>
<div id="tnp-footer">
    <div>
        <ul>
            <li><a href="https://github.com/fredpiuma/open-newsletter-for-wordpress" target="_blank">Open Newsletter</a></li>
        </ul>
    </div>
    <div>
        <ul>
            <li><a href="<?php echo esc_attr(admin_url('admin.php?page=newsletter_system_support')); ?>"><?php esc_html_e('Support', 'newsletter') ?></a></li>
            <li><a href="https://github.com/fredpiuma/open-newsletter-for-wordpress/issues" target="_blank"><?php esc_html_e('Issues', 'newsletter') ?></a></li>
        </ul>
    </div>
</div>