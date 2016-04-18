<?php

namespace Anax\Content;

/**
 * File Based Content, code for loading additional content into view through 
 * data["meta"].
 */
trait TFBCLoadAdditionalContent
{
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

                    case "single": // OBSOLETE
                    case "content":
                        // Support relative routes
                        $route = $meta["route"];
                        if (substr_compare($route, "./", 0, 2) === 0) {
                            $route = dirname($routeIndex) . "/" . substr($route, 2);
                        }

                        // Get the content
                        $data = $this->getDataForAdditionalRoute($route);
                        $views[$id]["data"] = array_merge_recursive_distinct($views[$id]["data"], $data);
                        break;

                    case "columns":
                        $columns = $meta["columns"];
                        foreach ($columns as $key => $value) {
                            $data = $this->getDataForAdditionalRoute($value["route"]);
                            $columns[$key] = $data;
                        }
                        $views[$id]["data"]["columns"] = $columns;
                        break;

                    case "toc":
                        $baseRoute = dirname($routeIndex);
                        $toc = $this->meta[$baseRoute]["__toc__"];
                        $this->orderAndlimitToc($toc, $meta);
                        $views[$id]["data"]["toc"] = $toc;
                        $views[$id]["data"]["meta"] = $meta;
                        break;

                    case "author":
                        if (isset($views["main"]["data"]["author"])) {
                            $views[$id]["data"]["author"] = $this->loadAuthorDetails($views["main"]["data"]["author"]);
                        }
                        break;

                    default:
                        throw new Exception(t("Unsupported data/meta/type '!TYPE' for additional content.", ["!TYPE" => $meta["type"]]));
                }
            }
        }
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
}
