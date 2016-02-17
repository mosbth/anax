<?php if ($siteLogo) : ?>
<a href="<?= $this->url->asset($homeLink) ?>">
    <img class="sitelogo" src="<?= $this->url->asset($siteLogo) ?>" alt="<?= $siteLogoAlt ?>">
</a>
<?php endif; ?>

<?php if ($siteLogo) : ?>
<span class="sitetitle"><?= $siteTitle ?></span>
<?php endif; ?>

<?php if ($siteSlogan) : ?>
<span class="siteslogan"><?= $siteSlogan ?></span>
<?php endif; ?>
