<?php if ($siteLogo) : ?>
<a href="<?= $this->url->create($homeLink) ?>">
    <img class="site-logo" src="<?= $this->url->asset($siteLogo) ?>" alt="<?= $siteLogoAlt ?>">
</a>
<?php endif; ?>

<?php if ($siteLogoText) : ?>
<a href="<?= $this->url->create($homeLink) ?>">
    <span class="site-logo"><?= $siteLogoText ?></span>
</a>
<?php endif; ?>

<?php if ($siteTitle) : ?>
<span class="site-title"><?= $siteTitle ?></span>
<?php endif; ?>

<?php if ($siteSlogan) : ?>
<span class="site-slogan"><?= $siteSlogan ?></span>
<?php endif; ?>
