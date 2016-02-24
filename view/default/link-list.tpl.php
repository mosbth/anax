<?php 
$class = isset($class) ? $class : null;
?><ul <?= $this->classList($class) ?>>
    <?php foreach ($links as $link) : ?>
    <li><a href="<?= $this->url($link["url"]) ?>"><?= $link["text"] ?></a></li>
    <?php endforeach; ?>
</ul>
