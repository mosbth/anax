<?php

namespace Anax\Content;

/**
 * Pages based on file content.
 */
class CFileBasedContent
{
    use \Anax\TConfigure,
        \Anax\DI\TInjectionAware;



    /**
     * Properties.
     */
    private $index = null;
    private $meta = null;
    private $ignoreCache = false;



    /**
     * Should the cache be used or ignored.
     *
     * @param boolean $use true to use the cache or false to ignore the cache.
     *
     * @return this.
     */
    public function useCache($use)
    {
        $this->ignoreCache = !$use;

        return $this;
    }



    /**
     * Get the index as an array.
     *
     * @return array as index.
     */
    public function getIndex()
    {
        return $this->loadIndex();
    }



    /**
     * Create the index of all content into an array.
     *
     * @return array as index.
     */
    private function loadIndex()
    {
        if ($this->index) {
            return $this->index;
        }

        $key = $this->di->cache->createKey(__CLASS__, "index");
        $this->index = $this->di->cache->get($key);

        if (!$this->index || $this->ignoreCache) {
            $this->index = $this->createIndex();
            $this->di->cache->put($key, $this->index);
        }

        return $this->index;
    }



    /**
     * Generate an index from the directory structure.
     *
     * @return array as table of content.
     */
    private function createIndex()
    {
        $basepath   = $this->config["basepath"];
        $pattern    = $this->config["pattern"];
        $path       = "$basepath/$pattern";
        
        $index = [];
        foreach (glob_recursive($path) as $file) {
            $filepath = substr($file, strlen($basepath) + 1);

            // Find content files
            $matches = [];
            preg_match("#^(\d*)_*([^\.]+)\.md$#", basename($filepath), $matches);
            $dirpart = dirname($filepath) . "/";
            if ($dirpart === "./") {
                $dirpart = null;
            }
            $key = $dirpart . $matches[2];

            // Create level depending on the file id
            $id = $matches[1];
            $level = 2;
            if ($id % 100 === 0) {
                $level = 0;
            } elseif ($id % 10 === 0) {
                $level = 1;
            }

            $index[$key] = [
                "file"    => $filepath,
                "section" => $matches[1],
                "level"   => $level,
            ];
        }

        return $index;
    }



    /**
     * Create the index of all meta content into an array.
     *
     * @return array as index.
     */
    private function loadMetaIndex()
    {
        if ($this->meta) {
            return $this->meta;
        }

        $key = $this->di->cache->createKey(__CLASS__, "meta");
        $this->meta = $this->di->cache->get($key);

        if (!$this->meta || $this->ignoreCache) {
            $this->meta = $this->createMetaIndex();
            $this->di->cache->put($key, $this->meta);
        }

        return $this->meta;
    }



    /**
     * Generate an index for meta files.
     *
     * @return array as table of content.
     */
    private function createMetaIndex()
    {
        $basepath = $this->config["basepath"];
        $filter   = $this->config["metafilter"];
        $meta     = $this->config["meta"];
        $path     = "$basepath/$meta";
        
        $meta = [];
        foreach (glob_recursive($path) as $file) {
            $filepath = substr($file, strlen($basepath) + 1);
            $src = file_get_contents($file);
            $filtered = $this->di->textFilter->parse($src, $filter);
            
            $key = dirname($filepath); 
            $meta[$key] = $filtered->frontmatter;
            
            // Add Toc to the data array
            if (isset($meta[$key]["toc"])) {
                $meta[$key]["toc"]["data"]["toc"] = $this->createBaseRouteToc(dirname($filepath));
            }
        }

        return $meta;
    }



    /**
     * Get a reference to meta data for specific route.
     *
     * @param string $route current route used to access page.
     *
     * @return array as table of content.
     */
    private function getMetaForRoute($route)
    {
        $base = dirname($route);
        return isset($this->meta[$base])
            ? $this->meta[$base]
            : null;
    }



    /**
     * Get the title of a document.
     *
     * @param string $file to get title from.
     *
     * @return string as the title.
     */
    private function getTitle($file)
    {
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter"];

        $path = $basepath . "/" . $file;
        $src = file_get_contents($path);
        $filtered = $this->di->textFilter->parse($src, $filter);
        return $filtered->frontmatter["title"];
    }



    /**
     * Create a table of content for routes at particular level.
     *
     * @param string $route base route to use.
     *
     * @return array as the toc.
     */
    private function createBaseRouteToc($route)
    {
        $toc = [];
        $len = strlen($route);

        foreach ($this->index as $key => $value) {
            if (substr($key, 0, $len) === $route) {
                $toc[$key] = $value;
                $toc[$key]["title"] = $this->getTitle($value["file"]);
            }
        }

        return $toc;
    }



    /**
     * Map the route to the correct entry in the index.
     *
     * @param string $route current route used to access page.
     *
     * @return array as the matched route.
     */
    private function mapRoute2Index($route)
    {
        if (key_exists($route, $this->index)) {
            return [$route, $this->index[$route]];
        } elseif (empty($route) && key_exists("index", $this->index)) {
            return ["index", $this->index["index"]];
        } elseif (key_exists($route . "/index", $this->index)) {
            return ["$route/index", $this->index["$route/index"]];
        }

        throw new \Anax\Exception\NotFoundException(t("The content does not exists in the index."));
    }



    /**
     * Set the template to use.
     *
     * @param string $route       current route used to access page.
     *
     * @return template to use for content.
     */
    private function getTemplate($route)
    {
        // Default from config
        $template = $this->config["template"];

        // From meta frontmatter
        $meta = $this->getMetaForRoute($route);
        if ($meta && isset($meta["template"])) {
            $template = $meta["template"];
        }

        return $template;
    }



    /**
     * Get TOC as view.
     *
     * @param string $route       current route used to access page.
     * @param array  $frontmatter for the content.
     *
     * @return array with TOC data to add as view.
     */
    private function getTocView($route, $frontmatter)
    {
        $toc = [];

        // From meta frontmatter
        $meta = $this->getMetaForRoute($route);
        if ($meta && isset($meta["toc"])) {
            $toc = $meta["toc"];
        }

        // From document frontmatter
        if (isset($frontmatter["views"])) {
            $toc = array_merge($toc, $frontmatter["toc"]);
        }

        return $toc;
    }



    /**
     * Get main content as view.
     *
     * @param string $route       current route used to access page.
     * @param array  $frontmatter for the content.
     *
     * @return array with TOC data to add as view.
     */
    private function getMainView($route, $frontmatter)
    {
        $main = [];

        // From meta frontmatter
        $meta = $this->getMetaForRoute($route);
        if ($meta && isset($meta["main"])) {
            $main = $meta["main"];
        }

        // From document frontmatter
        if (isset($frontmatter["view"])) {
            $main = array_merge_recursive_distinct($main, $frontmatter["view"]);
        }

        return $main;
    }



    /**
     * Get details on extra views.
     *
     * @param string $route       current route used to access page.
     * @param array  $frontmatter for the content.
     *
     * @return array with page data to send to view.
     */
    private function getViews($route, $frontmatter)
    {
        $views = [];

        // From meta frontmatter
        $meta = $this->getMetaForRoute($route);
        if ($meta && isset($meta["views"])) {
            $views = $meta["views"];
        }

        // From document frontmatter
        if (isset($frontmatter["views"])) {
            $views = array_merge($views, $frontmatter["views"]);
        }

        // Add standard views if they exists
        $views["toc"] = $this->getTocView($route, $frontmatter);
        $views["main"] = $this->getMainView($route, $frontmatter);
        if (!isset($views["main"]["template"])) {
            $views["main"]["template"] = $this->getTemplate($route);
        }

        return $views;
    }



    /**
     * Map url to content if such mapping can be done.
     *
     * @throws NotFoundException when mapping can not be done.
     */
    public function contentForRoute()
    {
        // Settings from config
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter"];

        // Get the route
        $route = $this->di->request->getRoute();

        // Load index and map route to entry
        $this->loadIndex();
        $this->loadMetaIndex();
        list($key, $content) = $this->mapRoute2Index($route);

        // Whole path to file
        $path = $basepath . "/" . $content["file"];
        $content["path"] = $path;

        // Load content from file
        if (!is_file($path)) {
            throw new \Anax\Exception\NotFoundException(t("The content does not exists as a file."));
        }

        // Get filtered content
        $src = file_get_contents($path);
        $filtered = $this->di->textFilter->parse($src, $filter);

        $frontmatter = $filtered->frontmatter;

        // TODO Should not supply all frontmatter to content, only the
        // parts valid to the index template. Everything else goes in the views
        $content["frontmatter"] = $frontmatter;

        // Create the content as views
        $content["views"] = $this->getViews($key, $frontmatter);
        $content["views"]["main"]["data"]["content"] = $filtered->text;

        return (object) $content;
    }
}
