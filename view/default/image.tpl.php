<?php 
$class = isset($class)
    ? $class 
    : null;

$alt = isset($alt)
    ? " alt=\"$alt\""
    : null;



?><img <?= $this->classList($class) ?> src="<?= $this->asset($src) ?>"<?= $alt ?>>
