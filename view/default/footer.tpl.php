<footer class="site-footer">
    <p>
        <?= $copyrightNotice ?>
    </p>

    <?php if (isset($links)) : 
        $this->renderView("default/link-list", [
            "links" => $links,
            "class" => "footer-site-links"
        ]); 
    endif; ?>
</footer>
