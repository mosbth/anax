<?php if (isset($siteLogo)) : ?>
<span class="site-logo" >
    <a href="<?= $this->url($homeLink) ?>">
        <img src="<?= $this->asset($siteLogo) ?>" alt="<?= $siteLogoAlt ?>">
    </a>
</span>
<?php endif; ?>

<?php if (isset($siteLogoText)) : ?>
<span class="site-logo" >
    <a href="<?= $this->url($homeLink) ?>">
        <span class="site-logo"><?= $siteLogoText ?></span>
    </a>
</span>
<?php endif; ?>

<?php if ($this->regionHasContent("navbar2")) : ?>
<nav class="navbar2" role="navigation">
    <?php $this->renderRegion("navbar2") ?>
</nav>
<?php endif; ?>

<nav class="search" role="navigation">
    <div>
        <a href="<?=$this->url("")?>" class="search-link" title="<?= t("Search this site") ?>"><i class="fa fa-search fa-lg"></i></a>
    </div>
</nav>

<?php if (isset($siteTitle)) : ?>
<span class="site-title"><?= $siteTitle ?></span>
<?php endif; ?>

<?php if (isset($siteSlogan)) : ?>
<span class="site-slogan"><?= $siteSlogan ?></span>
<?php endif; ?>
