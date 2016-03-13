<?php 
$class = isset($class) ? $class : null;

?><article <?= $this->classList($class) ?>>
<?=$content?>
</article>
