<?php

namespace Anax\Content;

/**
 * Pages based on file content.
 */
class CFileBasedContent
{
    use \Anax\TConfigure,
        \Anax\DI\TInjectionAware,
        TFBCBreadcrumb,
        TFBCLoadAdditionalContent,
        TFBCUtilities;



    /**
     * All routes.
     */
    private $index = null;

    /**
     * All authors.
     */
    private $author = null;

    /**
     * All categories.
     */
    private $category = null;

    /**
     * All routes having meta.
     */
    private $meta = null;

    /**
     * Use cache or recreate each time.
     */
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
     * Create the index of all content into an array.
     *
     * @param string $type of index to load.
     *
     * @return void.
     */
    private function load($type)
    {
        $index = $this->$type;
        if ($index) {
            return;
        }

        $cache = $this->di->get("cache");
        $key = $cache->createKey(__CLASS__, $type);
        $index = $cache->get($key);

        if (is_null($index) || $this->ignoreCache) {
            $createMethod = "create$type";
            $index = $this->$createMethod();
            $cache->put($key, $index);
        }

        $this->$type = $index;
    }




    // = Create and manage index ==================================

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
            // TODO ciamge doc, can be replaced by __toc__ in meta?
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
                "level"    => $level,  // TODO ?
                "internal" => $this->isInternalRoute($filepath),
                "tocable"  => $this->allowInToc($filepath),
            ];
        }

        return $index;
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



    // = Create and manage meta ==================================

    /**
     * Generate an index for meta files.
     *
     * @return array as meta index.
     */
    private function createMeta()
    {
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter-frontmatter"];
        $pattern  = $this->config["meta"];
        $path     = "$basepath/$pattern";
        $textfilter = $this->di->get("textFilter");

        $index = [];
        foreach (glob_recursive($path) as $file) {
            // The key entry to index
            $key = dirname(substr($file, strlen($basepath) + 1));

            // Get info from base document
            $src = file_get_contents($file);
            $filtered = $textfilter->parse($src, $filter);
            $index[$key] = $filtered->frontmatter;

            // Add Toc to the data array
            $index[$key]["__toc__"] = $this->createBaseRouteToc($key);
        }

        // Add author details
        $this->meta = $index;
        $this->createAuthor();

        return $this->meta;
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



    // = Deal with authors ====================================
    
    /**
     * Generate a lookup index for authors that maps into the meta entry
     * for the author.
     *
     * @return void.
     */
    private function createAuthor()
    {
        $pattern = $this->config["author"];

        $index = [];
        $matches = [];
        foreach ($this->meta as $key => $entry) {
            if (preg_match($pattern, $key, $matches)) {
                $acronym = $matches[1];
                $index[$acronym] = $key;
                $this->meta[$key]["acronym"] = $acronym;
                
                // Get content for byline
                $route = "$key/byline";
                $data = $this->getDataForAdditionalRoute($route);
                $this->meta[$key]["byline"] = $data["content"];
            }
        }

        return $index;
    }



    /**
     * Find next and previous links of current content.
     *
     * @param array|string $author with details on the author(s).
     *
     * @return array with more details on the authors(s).
     */
    private function loadAuthorDetails($author)
    {
        if (is_array($author) && is_array(array_values($author)[0])) {
            return $author;
        }

        if (!is_array($author)) {
            $tmp = $author;
            $author = [];
            $author[] = $tmp;
        }

        $authors = [];
        foreach ($author as $acronym) {
            if (isset($this->author[$acronym])) {
                $key = $this->author[$acronym];
                $authors[$acronym] = $this->meta[$key];
                unset($authors[$acronym]["__toc__"]);
            }
        }

        return $authors;
    }



    // == Used by meta and breadcrumb (to get title) ===========================
    // TODO REFACTOR THIS?
    // Support getting only frontmatter.
    // Merge with function that retrieves whole filtered since getting
    // frontmatter will involve full parsing of document.
    // Title is retrieved from the HTML code.
    // Also do cacheing of each retrieved and parsed document
    // in this cycle, to gather code that loads and parses a individual
    // document. 
    
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
        $filter1  = $this->config["textfilter-frontmatter"];
        $filter2  = $this->config["textfilter"];
        $filter = array_merge($filter1, $filter2);
        
        $path = $basepath . "/" . $file;
        $src = file_get_contents($path);
        $filtered = $this->di->get("textFilter")->parse($src, $filter);
        return $filtered->frontmatter;
    }



    // = Section X to be labeled  ==================================

    /**
     * Load the content from filtered and parse it step two.
     *
     * @param string $file to get content from.
     *
     * @return object as filtered content.
     */
/*
    private function loadPureContentPhase2($filtered)
    {
        $filter = $this->config["textfilter"];
        $text = $filtered->text;

        // Get new filtered content
        $new = $this->di->get("textFilter")->parse($text, $filter);
        $filtered->text = $new->text;

        // Update all anchor urls to use baseurl, needs info about baseurl
        // from merged frontmatter
        //$baseurl = $this->getBaseurl($content["views"]);
        //$this->addBaseurl2AnchorUrls($filtered, $baseurl);

        return $filtered;
    }

*/


    // == Look up route in index ===================================
    
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



    // = Get view data by merging from meta and current frontmatter =========
    
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

        if ($frontmatter) {
            $data = array_merge_recursive_distinct($data, $frontmatter);
        }
        $views["main"]["data"] = array_merge_recursive_distinct($views["main"]["data"], $data);

        return $views;
    }



    // == Create and load content ===================================

    /**
     * Map url to content, even internal content, if such mapping can be done.
     *
     * @param string $route route to look up.
     *
     * @return object with content and filtered version.
     */
    private function createContentForInternalRoute($route)
    {
        // Load index and map route to content
        $this->load("index");
        $this->load("meta");
        $this->load("author");
        list($routeIndex, $content, $filtered) = $this->mapRoute2Content($route);

        // Create and arrange the content as views, merge with .meta,
        // frontmatter is complete.
        $content["views"] = $this->getViews($routeIndex, $filtered->frontmatter);

        // Do process content step two when all frontmatter is included.
        $this->processMainContentPhaseTwo($content, $filtered);
        
        // Set details of content
        $content["views"]["main"]["data"]["content"] = $filtered->text;
        $content["views"]["main"]["data"]["excerpt"] = $filtered->excerpt;
        $this->loadAdditionalContent($content["views"], $route, $routeIndex);

        // TODO Should not supply all frontmatter to theme, only the
        // parts valid to the index template. Separate that data into own
        // holder in frontmatter. Do not include whole frontmatter? Only
        // on debg?
        $content["frontmatter"] = $filtered->frontmatter;

        return (object) $content;
    }



    /**
     * Look up the route in the index and use that to retrieve the filtered
     * content.
     *
     * @param string $route to look up.
     *
     * @return array with content and filtered version.
     */
    private function mapRoute2Content($route)
    {
        // Look it up in the index
        list($keyIndex, $content) = $this->mapRoute2Index($route);
        $filtered = $this->loadFileContentPhaseOne($keyIndex);

        return [$keyIndex, $content, $filtered];
    }



    /**
     * Load content file and frontmatter, this is the first time we process
     * the content.
     *
     * @param string $key     to index with details on the route.
     *
     * @throws NotFoundException when mapping can not be done.
     *
     * @return void.
     */
    private function loadFileContentPhaseOne($key)
    {
        // Settings from config
        $basepath = $this->config["basepath"];
        $filter   = $this->config["textfilter-frontmatter"];

        // Whole path to file
        $path = $basepath . "/" . $this->index[$key]["file"];

        // Load content from file
        if (!is_file($path)) {
            $msg = t("The content '!ROUTE' does not exists as a file '!FILE'.", ["!ROUTE" => $key, "!FILE" => $path]);
            throw new \Anax\Exception\NotFoundException($msg);
        }

        // Get filtered content
        $src = file_get_contents($path);
        $filtered = $this->di->get("textFilter")->parse($src, $filter);

        return $filtered;
    }



    // == Process content phase 2 ===================================
    // TODO REFACTOR THIS?
    
    /**
     * Look up the route in the index and use that to retrieve the filtered
     * content.
     *
     * @param array  &$content   to process.
     * @param object &$filtered to use for settings.
     *
     * @return array with content and filtered version.
     */
     private function processMainContentPhaseTwo(&$content, &$filtered)
     {
        // From configuration
         $filter = $this->config["textfilter"];
         $revisionStart = $this->config["revision-history"]["start"];
         $revisionEnd   = $this->config["revision-history"]["end"];
         $revisionClass = $this->config["revision-history"]["class"];
         
         $textFilter = $this->di->get("textFilter");
         $text = $filtered->text;

         // Check if revision history is to be included
         if (isset($content["views"]["main"]["data"]["revision"])) {
             $text = $textFilter->addRevisionHistory(
                 $text,
                 $content["views"]["main"]["data"]["revision"],
                 $revisionStart,
                 $revisionEnd,
                 $revisionClass
             );
         }

         // Get new filtered content (and updated frontmatter)
         $new = $textFilter->parse($text, $filter);
         $filtered->text = $new->text;
         if ($filtered->frontmatter) {
             $filtered->frontmatter = array_merge_recursive_distinct($filtered->frontmatter, $new->frontmatter);
         } else {
             $filtered->frontmatter = $new->frontmatter;
         }

        // Load details on author if set.
        if (isset($content["views"]["main"]["data"]["author"])) {
            $content["views"]["main"]["data"]["author"] = $this->loadAuthorDetails($content["views"]["main"]["data"]["author"]);
        }

         // Update all anchor urls to use baseurl, needs info about baseurl
         // from merged frontmatter
         $baseurl = isset($content["views"]["main"]["baseurl"])
            ? $content["views"]["main"]["baseurl"]
            : null;
         $this->addBaseurl2AnchorUrls($filtered, $baseurl);

         // Add excerpt and hasMore, if available
         $textFilter->addExcerpt($filtered);
    }



    // == Public methods ============================================
    
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
            $route = $this->di->get("request")->getRoute();
        }

        // Check cache for content or create cached version of content
        $slug = $this->di->get("url")->slugify($route);
        $key = $this->di->cache->createKey(__CLASS__, "route-$slug");
        $content = $this->di->cache->get($key);

        if (!$content || $this->ignoreCache) {
            $content = $this->createContentForInternalRoute($route);
            $this->di->cache->put($key, $content);
        }

        return $content;
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
}
