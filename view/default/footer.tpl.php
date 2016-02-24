<p>
    <?= $copyrightNotice ?>
</p>

<?php if ($links) : 
    $this->renderView("default/link-list", [
        "links" => $links,
        "class" => "footer-site-links"
    ]); 
endif; ?>
