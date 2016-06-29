<?php if (isset($siteLogo)) : ?>
<span class="site-logo" >
    <a href="<?= $this->url($homeLink) ?>">
        <img src="<?= $this->asset($siteLogo) ?>" alt="<?= $siteLogoAlt ?>">
    </a>
</span>
<?php endif; ?>

<?php if (isset($siteLogoText)) : ?>
<span class="site-logo-text" >
    <a href="<?= $this->url($homeLink) ?>">
        <?php if (isset($siteLogoTextIcon)) : ?>
        <span class="site-logo-text-icon" >
            <img src="<?= $this->asset($siteLogoTextIcon) ?>" alt="<?= $siteLogoTextIconAlt ?>">
        </span>
        <?php endif; ?>
        <?= $siteLogoText ?>
    </a>
</span>
<?php endif; ?>

<?php if ($this->regionHasContent("navbar2")) : ?>
<nav class="navbar2" role="navigation">
    <?php $this->renderRegion("navbar2") ?>
</nav>
<?php endif; ?>

<?php if ($this->regionHasContent("search")) : ?>
    <?php $this->renderRegion("search") ?>
<?php endif; ?>

<?php if ($this->regionHasContent("profile")) : ?>
    <?php $this->renderRegion("profile") ?>
<?php endif; ?>

<?php if (isset($siteTitle)) : ?>
<span class="site-title"><?= $siteTitle ?></span>
<?php endif; ?>

<?php if (isset($siteSlogan)) : ?>
<span class="site-slogan"><?= $siteSlogan ?></span>
<?php endif; ?>
