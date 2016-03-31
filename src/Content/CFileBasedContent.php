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
     * File name pattern, all files must match this pattern and the first
     * numbered part is optional, the second part becomes the route.
     */
    private $filenamePattern = "#^(\d*)_*([^\.]+)\.md$#";

    /**
     * Internal routes that is marked as internal content routes and not
     * exposed as public routes.
     */
    private $internalRouteDirPattern = [
        "#block/#",
    ];

    private $internalRouteFilePattern = [
        "#^block[_-]{1}#",
        "#^_#",
    ];

    /**
     * Routes that should be used in toc.
     */
    private $allowedInTocPattern = "([\d]+_(\w)+)";



    /**
     * Create a breadcrumb, append slash / to all dirs.
     *
     * @param string $route      current route.
     *
     * @return array with values for the breadcrumb.
     */
    public function createBreadcrumb($route)
    {
        $breadcrumbs = [];

        while ($route !== "./") {
            $routeIndex = $this->mapRoute2IndexKey($route);
            $item["url"] = $route;
            $item["text"] = $this->getTitle($this->index[$routeIndex]["file"]);
            $breadcrumbs[] = $item;
            $route = dirname($route) . "/";
        }

        krsort($breadcrumbs);
        return $breadcrumbs;
    }



/**
 * Get time when the content was last updated.
 *
 * @return string with the time.
 */
/*public function PublishTime() {
  if(!empty($this['published'])) {
    return $this['published'];
  } else if(isset($this['updated'])) {
    return $this['updated'];
  } else {
    return $this['created'];
  } 
}
*/
/**
 * Get the action for latest updated of the content.
 *
 * @return string with the time.
 */
/*public function PublishAction() {
  if(!empty($this['published'])) {
    //return t('Published');
    return t('Last updated');
  } else if(isset($this['updated'])) {
    return t('Updated');
  } else {
    return t('Created');
  } 
}
*/



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
     * Check if a filename is to be marked as an internal route..
     *
     * @param string $filepath as the basepath (routepart) to the file.
     *
     * @return boolean true if the route content is internal, else false
     */
    private function isInternalRoute($filepath)
    {
        foreach ($this->internalRouteDirPattern as $pattern) {
            if (preg_match($pattern, $filepath)) {
                return true;
            }
        }

        $filename = basename($filepath);
        foreach ($this->internalRouteFilePattern as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }



    /**
     * Check if filepath should be used as part of toc.
     *
     * @param string $filepath as the basepath (routepart) to the file.
     *
     * @return boolean true if the route content shoul dbe in toc, else false
     */
    private function allowInToc($filepath)
    {
        return (boolean) preg_match($this->allowedInTocPattern, $filepath);
    }



    /**
     * Generate an index from the directory structure.
     *
     * @return array as index for all content files.
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
            preg_match($this->filenamePattern, basename($filepath), $matches);
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
                "file"     => $filepath,
                "section"  => $matches[1],
                "level"    => $level,
                "internal" => $this->isInternalRoute($filepath),
                "tocable"  => $this->allowInToc($filepath),
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
            $filtered = $this->di->get("textFilter")->parse($src, $filter);

            $key = dirname($filepath);
            $meta[$key] = $filtered->frontmatter;

            // Add Toc to the data array
            $meta[$key]["__toc__"] = $this->createBaseRouteToc(dirname($filepath));
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
            : [];
    }



    /**
     * Get the title of a document.
     *
     * @deprecated in favor of getFrontmatter
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
     * Get the frontmatter of a document.
     *
     * @param string $file to get frontmatter from.
     *
     * @return array as frontmatter.
     */
    private function getFrontmatter($file)
    {
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter"];

        $path = $basepath . "/" . $file;
        $src = file_get_contents($path);
        $filtered = $this->di->textFilter->parse($src, $filter);
        return $filtered->frontmatter;
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
                if ($value["internal"] === false
                    && $value["tocable"] === true) {
                    $toc[$key] = $value;
                    
                    $frontm = $this->getFrontmatter($value["file"]);
                    $toc[$key]["title"] = $frontm["title"];
                    $toc[$key]["sectionHeader"] = isset($frontm["sectionHeader"]) ? $frontm["sectionHeader"] : null;
                    $toc[$key]["linkable"] = isset($frontm["linkable"]) ? $frontm["linkable"] : null;
                }
            }
        };

        return $toc;
    }



    /**
     * Map the route to the correct key in the index.
     *
     * @param string $route current route used to access page.
     *
     * @return string as key or false if no match.
     */
    private function mapRoute2IndexKey($route)
    {
        $route = rtrim($route, "/");

        if (key_exists($route, $this->index)) {
            return $route;
        } elseif (empty($route) && key_exists("index", $this->index)) {
            return "index";
        } elseif (key_exists($route . "/index", $this->index)) {
            return "$route/index";
        }

        return false;
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
        $routeIndex = $this->mapRoute2IndexKey($route);

        if ($routeIndex) {
            return [$routeIndex, $this->index[$routeIndex]];
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
        if (isset($meta[$key])) {
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
        // Arrange data into views
        $views = $this->getView($route, $frontmatter, "views", false);

        // Set defaults
        if (!isset($views["main"]["template"])) {
            $views["main"]["template"] = $this->config["template"];
        }
        if (!isset($views["main"]["data"])) {
            $views["main"]["data"] = [];
        }

        // Merge remaining frontmatter into view main data.
        $data = $this->getMetaForRoute($route);
        unset($data["__toc__"]);
        unset($data["views"]);
        unset($frontmatter["views"]);
        $data = array_merge_recursive_distinct($data, $frontmatter);
        $views["main"]["data"] = array_merge_recursive_distinct($views["main"]["data"], $data);

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
     * Order and limit toc items.
     *
     * @param string &$toc  array with current toc.
     * @param string &$meta on how to order and limit toc.
     *
     * @return void.
     */
    private function orderAndlimitToc(&$toc, &$meta)
    {
        $defaults = [
            "items" => 7,
            "offset" => 0,
            "orderby" => "section",
            "orderorder" => "asc",
        ];
        $options = array_merge($defaults, $meta);
        $orderby = $options["orderby"];
        $order   = $options["orderorder"];

        $meta["totalItems"] = count($toc);

        // TODO support pagination by adding entries to $meta

        uksort($toc, function ($a, $b) use ($toc, $orderby, $order) {
            $a = $toc[$a][$orderby];
            $b = $toc[$b][$orderby];

            if ($order == "asc") {
                return strcmp($a, $b);
            }
            return strcmp($b, $a);
        });

        $toc = array_slice($toc, $options["offset"], $options["items"]);
        $meta["displayedItems"] = count($toc);
    }



    /**
     * Find next and previous links of current content.
     *
     * @param string $routeIndex target route to find next and previous for.
     *
     * @return array with next and previous if found.
     */
    private function findNextAndPrevious($routeIndex)
    {
        $key = dirname($routeIndex);
        if (!isset($this->meta[$key]["__toc__"])) {
            return [null, null];
        }

        $toc = $this->meta[$key]["__toc__"];
        if (!isset($toc[$routeIndex])) {
            return [null, null];
        }

        $index2Key = array_keys($toc);
        $keys = array_flip($index2Key);
        $values = array_values($toc);
        $count = count($keys);

        $current = $keys[$routeIndex];
        $previous = null;
        for ($i = $current - 1; $i >= 0; $i--) {
            $isSectionHeader = $values[$i]["sectionHeader"];
            $isInternal = $values[$i]["internal"];
            if ($isSectionHeader || $isInternal) {
                continue;
            }
            $previous = $values[$i];
            $previous["route"] = $index2Key[$i];
            break;
        }
        
        $next = null;
        for ($i = $current + 1; $i < $count; $i++) {
            $isSectionHeader = $values[$i]["sectionHeader"];
            $isInternal = $values[$i]["internal"];
            if ($isSectionHeader || $isInternal) {
                continue;
            }
            $next = $values[$i];
            $next["route"] = $index2Key[$i];
            break;
        }

        return [$next, $previous];
    }



    /**
     * Load extra info into views based of meta information provided in each
     * view.
     *
     * @param array  &$views     with all views.
     * @param string $route      current route
     * @param string $routeIndex route with appended /index
     *
     * @throws NotFoundException when mapping can not be done.
     *
     * @return void.
     */
    private function loadAdditionalContent(&$views, $route, $routeIndex)
    {
        foreach ($views as $id => $view) {
            $meta = isset($view["data"]["meta"])
                ? $view["data"]["meta"]
                : null;

            if (is_array($meta)) {
                switch ($meta["type"]) {
                    case "article-toc":
                        $content = $views["main"]["data"]["content"];
                        $views[$id]["data"]["articleToc"] = $this->di->textFilter->createToc($content);
                        break;

                    case "breadcrumb":
                        $views[$id]["data"]["breadcrumb"] = $this->createBreadcrumb($route);
                        break;

                    case "next-previous":
                        $baseRoute = dirname($routeIndex);
                        list($next, $previous) = $this->findNextAndPrevious($routeIndex);
                        $views[$id]["data"]["next"] = $next;
                        $views[$id]["data"]["previous"] = $previous;
                        break;

                    case "single":
                        $views[$id] = $this->getAdditionalViewDataForRoute($view, $meta["route"]);
                        break;

                    case "toc":
                        $baseRoute = dirname($routeIndex);
                        $toc = $this->meta[$baseRoute]["__toc__"];
                        $this->orderAndlimitToc($toc, $meta);
                        $views[$id]["data"]["toc"] = $toc;
                        $views[$id]["data"]["meta"] = $meta;
                        break;

                    default:
                        throw new Exception(t("Unsupported data/meta/type for additional content."));
                }
            }
        }
    }



    /**
     * Parse text, find and update all a href to use baseurl.
     *
     * @param object &$filtered with text and excerpt to process.
     * @param array  $views     data for all views.
     *
     * @return void.
     */
    private function addBaseurl2AnchorUrls(&$filtered, $views)
    {
        $textf  = $this->di->get("textFilter");
        $url    = $this->di->get("url");
        $baseurl = isset($views["main"]["data"]["baseurl"])
            ? $views["main"]["data"]["baseurl"]
            : null;

        // Use callback to url->create() instead of string concat
        $callback = function ($route) use ($url) {
            return $url->create($route);
        };

        $filtered->text =
            $textf->addBaseurlToRelativeLinks($filtered->text, $baseurl, $callback);
        $filtered->excerpt =
            $textf->addBaseurlToRelativeLinks($filtered->excerpt, $baseurl, $callback);
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
            $msg = t("The content '!ROUTE' does not exists as a file '!FILE'.", ["!ROUTE" => $key, "!FILE" => $path]);
            throw new \Anax\Exception\NotFoundException($msg);
        }

        // Get filtered content
        $src = file_get_contents($path);
        $filtered = $this->di->get("textFilter")->parse($src, $filter);

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
     * Map url to content if such mapping can be done, exclude internal routes.
     *
     * @param string $route optional route to look up.
     *
     * @return object with content and filtered version.
     */
    public function contentForRoute($route = null)
    {
        $content = $this->contentForInternalRoute($route);
        if ($content->internal === true) {
            $msg = t("The content '!ROUTE' does not exists as a public route.", ["!ROUTE" => $route]);
            throw new \Anax\Exception\NotFoundException($msg);
        }

        return $content;
    }



    /**
     * Map url to content, even internal content, if such mapping can be done.
     *
     * @param string $route optional route to look up.
     *
     * @return object with content and filtered version.
     */
    public function contentForInternalRoute($route = null)
    {
        // Get the route
        if (is_null($route)) {
            $route = $this->di->request->getRoute();
        }

        // TODO cache route content.

        // Load index and map route to content
        $this->loadIndex();
        $this->loadMetaIndex();
        list($routeIndex, $content, $filtered) = $this->mapRoute2Content($route);

        // TODO Should not supply all frontmatter to theme, only the
        // parts valid to the index template. Separate that data into own
        // holder in frontmatter. Do not include whole frontmatter? Only
        // on debg?
        $content["frontmatter"] = $filtered->frontmatter;

        // Create and arrange the content as views, merge with .meta,
        // frontmatter is complete.
        $content["views"] = $this->getViews($routeIndex, $filtered->frontmatter);

        // Update all anchor urls to use baseurl
        $this->addBaseurl2AnchorUrls($filtered, $content["views"]);

        //
        // TODO Load content, pure or use data available
        // own function
        // perhaps load in separate view
        //
        $content["views"]["main"]["data"]["content"] = $filtered->text;
        $content["views"]["main"]["data"]["excerpt"] = $filtered->excerpt;
        $this->loadAdditionalContent($content["views"], $route, $routeIndex);

        return (object) $content;
    }
}
