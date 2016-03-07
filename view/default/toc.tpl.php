<aside>
    <h4><?=isset($title) ? $title : t("Table Of Content")?></h4>
    
    <ul class="toc">
        <?php foreach ($toc as $url => $page) : ?>
        <li class="level-<?= $page["level"] ?>"><a href="<?= $this->url($url) ?>"><?= $page["title"] ?></a></li>
        <?php endforeach; ?>
    </ul>
</aside>
