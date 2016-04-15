<?php
// Prepare classes
$classes[] = "author-byline";
if (isset($class)) {
    $classes[] = $class;
}


foreach ($author as $val) :
    extract($val);
?><div <?= $this->classList($classes) ?>>
<?= $byline ?>
</div>
<?php endforeach;
