<?php
?>
<ul class="tnp-nav">
    <li class="tnp-nav-title"><?php esc_html_e('Dashboard', 'newsletter') ?></li>
    <li class="<?php echo $_GET['page'] === 'newsletter_main_index'?'active':''?>"><a href="?page=newsletter_main_index"><?php esc_html_e('Overview', 'newsletter')?></a></li>
</ul>
