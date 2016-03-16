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
     * Set default values from configuration.
     *
     * @return this.
     */
    public function setDefaultsFromConfiguration()
    {
        $this->ignoreCache = isset($this->config["ignoreCache"])
            ? $this->config["ignoreCache"]
            : $this->ignoreCache;

        return $this;
    }



    /**
     * Should the cache be used or ignored.
     *
     * @param boolean $use true to use the cache or false to ignore the cache
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

echo "TOC for $route<br>";
        foreach ($this->index as $key => $value) {
            if (substr($key, 0, $len) === $route) {
                echo "MATCH $key<br>";
                $toc[$key] = $value;
                $toc[$key]["title"] = $this->getTitle($value["file"]);
            }
        };
        echo "DONE<br>";

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

        throw new \Anax\Exception\NotFoundException(t("The route '!ROUTE' does not exists in the index.", ["!ROUTE" => $route]));
    }



    /**
     * Get view by mergin information from meta and frontmatter.
     *
     * @param string $route       current route used to access page.
     * @param array  $frontmatter for the content.
     * @param string $key         for the view to retrive.
     * @param string $distinct    how to merge the array.
     *
     * @return array with data to add as view.
     */
    private function getView($route, $frontmatter, $key, $distinct = true)
    {
        $view = [];

        // From meta frontmatter
        $meta = $this->getMetaForRoute($route);
        if ($meta && isset($meta[$key])) {
            $view = $meta[$key];
        }

        // From document frontmatter
        if (isset($frontmatter[$key])) {
            if ($distinct) {
                $view = array_merge_recursive_distinct($view, $frontmatter[$key]);
            } else {
                $view = array_merge($view, $frontmatter[$key]);
            }
        }

        return $view;
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
        $views = $this->getView($route, $frontmatter, "views", false);
        $views["toc"]  = $this->getView($route, $frontmatter, "toc");
        $views["main"] = $this->getView($route, $frontmatter, "main");

        if (!isset($views["main"]["template"])) {
            $views["main"]["template"] = $this->config["template"];
        }

        return $views;
    }



    /**
     * Load extra info intro views based of meta information provided in each
     * view.
     *
     * @param string $view  with current settings.
     * @param string $route to load view from.
     *
     * @return array with view details.
     */
    private function getAdditionalViewDataForRoute($view, $route)
    {
        // Get filtered content from route
        list(, , $filtered) =
            $this->mapRoute2Content($route);

        // From document frontmatter
        $view["data"] = array_merge_recursive_distinct($view["data"], $filtered->frontmatter);
        $view["data"]["content"] = $filtered->text;

        return $view;

    }



    /**
     * Load extra info intro views based of meta information provided in each
     * view.
     *
     * @param array &$views array with all views.
     *
     * @throws NotFoundException when mapping can not be done.
     *
     * @return void.
     */
    private function loadAdditionalContent(&$views)
    {
        foreach ($views as $id => $view) {
            $meta = isset($view["data"]["meta"])
                ? $view["data"]["meta"]
                : null;

            if (is_array($meta)) {
                switch ($meta["type"]) {
                    case "multi":

                    break;

                    case "single":
                        $views[$id] = $this->getAdditionalViewDataForRoute($view, $meta["route"]);
                    break;

                    default:
                        throw new Exception(t("Unsupported data/meta/type."));
                }
            }
        }
    }



    /**
     * Load extra info intro views based of meta information provided in each
     * view.
     *
     * @param string $key     array with all views.
     * @param string $content array with all views.
     *
     * @throws NotFoundException when mapping can not be done.
     *
     * @return void.
     */
    private function loadFileContent($key, $content)
    {
        // Settings from config
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter"];

        // Whole path to file
        $path = $basepath . "/" . $content["file"];
        $content["path"] = $path;

        // Load content from file
        if (!is_file($path)) {
            throw new \Anax\Exception\NotFoundException(t("The content '!ROUTE' does not exists as a file '!FILE'.", ["!ROUTE" => $key, "!FILE" => $path]));
        }

        // Get filtered content
        $src = file_get_contents($path);
        $filtered = $this->di->textFilter->parse($src, $filter);

        return [$content, $filtered];
    }



    /**
     * Look up the route in the index and use that to retrieve the filtered
     * content.
     *
     * @param string $route to look up.
     *
     * @return array with content and filtered version.
     */
    public function mapRoute2Content($route)
    {
        // Look it up in the index
        list($keyIndex, $content) = $this->mapRoute2Index($route);
        list($content, $filtered) = $this->loadFileContent($keyIndex, $content);

        return [$keyIndex, $content, $filtered];
    }



    /**
     * Map url to content if such mapping can be done.
     *
     * @param string $route optional route to look up.
     *
     * @return object with content and filtered version.
     */
    public function contentForRoute($route = null)
    {
        // Get the route
        if (is_null($route)) {
            $route = $this->di->request->getRoute();
        }

        // TODO cache route content.

        // Load index and map route to content
        $this->loadIndex();
        $this->loadMetaIndex();
        list($keyIndex, $content, $filtered) = $this->mapRoute2Content($route);

        // TODO Should not supply all frontmatter to theme, only the
        // parts valid to the index template. Separate that data into own
        // holder in frontmatter. Do not include whole frontmatter? Only
        // on debg?
        $content["frontmatter"] = $filtered->frontmatter;

        // Create and arrange the content as views
        $content["views"] = $this->getViews($keyIndex, $filtered->frontmatter);

        //
        // TODO Load content, pure or use data available
        // own functuion
        // perhaps load in separate view
        //
        $content["views"]["main"]["data"]["content"] = $filtered->text;
        $this->loadAdditionalContent($content["views"]);

        return (object) $content;
    }
}
