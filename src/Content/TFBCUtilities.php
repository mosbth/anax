<?php

namespace Anax\Content;

/**
 * File Based Content, code for loading additional content into view through 
 * data["meta"].
 */
trait TFBCUtilities
{
    /**
     * Support relative routes.
     *
     * @param string $route      to load.
     * @param string $routeIndex to use.
     *
     * @return string with active route.
     */
    private function getActiveRoute($route, $routeIndex)
    {
        if (substr_compare($route, "./", 0, 2) === 0) {
            $route = dirname($routeIndex) . "/" . substr($route, 2);
        }

        return $route;
    }



    /**
     * Load view data for additional route, merged with meta if any.
     *
     * @param string $route to load.
     *
     * @return array with view data details.
     */
    private function getDataForAdditionalRoute($route)
    {
        // From configuration
         $filter = $this->config["textfilter"];

        // Get filtered content from route
        list(, , $filtered) =
            $this->mapRoute2Content($route);

        // Set data to be content of frontmatter, merged with meta
        $meta = $this->getMetaForRoute($route);
        $data = $filtered->frontmatter;
        $data = array_merge_recursive_distinct($meta, $data);
        unset($data["__toc__"]);
        unset($data["views"]);

        // Do phase 2 processing
        $new = $this->di->get("textFilter")->parse($filtered->text, $filter);
        
        // Creates urls based on baseurl
        $baseurl = isset($data["baseurl"])
            ? isset($data["baseurl"])
            : null;
        $this->addBaseurl2AnchorUrls($new, $baseurl);
        $data["content"] = $new->text;

        return $data;
    }



    /**
     * Parse text, find and update all a href to use baseurl.
     *
     * @param object &$filtered with text and excerpt to process.
     * @param string $baseurl   add as baseurl for all relative urls.
     *
     * @return void.
     */
    private function addBaseurl2AnchorUrls(&$filtered, $baseurl)
    {
        $textf  = $this->di->get("textFilter");
        $url    = $this->di->get("url");

        // Use callback to url->create() instead of string concat
        $callback = function ($route) use ($url, $baseurl) {
            return $url->create($route, $baseurl);
        };

        $filtered->text =
            $textf->addBaseurlToRelativeLinks($filtered->text, $baseurl, $callback);
    }



    /**
     * Get published date.
     *
     * @param array $frontmatter with details on dates.
     *
     * @return integer as time for publish time.
     */
    private function getPublishTime($frontmatter)
    {
        list(, $date) = $this->di->get("view")->getPublishedDate($frontmatter);
        return strtotime($date);
    }
}
