<?php

namespace Anax\Content;

/**
 * Pages based on file content.
 *
 */
class CPageContent
{
    use \Anax\TConfigure,
        \Anax\DI\TInjectionAware;



    /**
     * Properties.
     */
    private $toc = null;



    /**
     * Map url to page if such mapping can be done.
     *
     * @throws NotFoundException when mapping can not be done.
     */
    public function getContentForRoute()
    {
        $route = $this->di->request->getRoute();
        $toc   = $this->getTableOfContent();

        if (!key_exists($route, $toc)) {
            throw new \Anax\Exception\NotFoundException(t('The page does not exists.'));
        }

        $baseroute  = dirname($route);
    
        $filter = $this->config['textfilter'];
        $title  = $toc[$route]['title'];
        $file   = $toc[$route]['filename'];

        $content = $this->di->fileContent->get($baseroute . '/' . $file);
        $content = $this->di->textFilter->doFilter($content, $filter);
        
        return [$title, $content, $toc];
    }



    /**
     * Extract title from content.
     *
     * @param string $file filenam to load load content from.
     *
     * @return string as the title for the content.
     */
    public function getTitleFromFirstLine($file)
    {
        $content = file_get_contents($file, false, null, -1, 512);
        $title = strstr($content, "\n", true);
        
        return $title;
    }



    /**
     * Get table of content for all pages.
     *
     * @return array as table of content.
     */
    public function getTableOfContent()
    {
        if ($this->toc) {
            return $this->toc;
        }

        $key = $this->di->cache->createKey(__CLASS__, 'toc');
        $this->toc = $this->di->cache->get($key);

        if (!$this->toc) {
            $this->toc = $this->createTableOfContent();
            $this->di->cache->put($key, $this->toc);
        }

        return $this->toc;
    }



    /**
     * Generate ToC from directory structure, containing url, title and filename
     * of each page.
     *
     * @return array as table of content.
     */
    public function createTableOfContent()
    {
        $basepath   = $this->config['basepath'];
        $pattern    = $this->config['pattern'];
        $route      = $this->di->request->getRoute();
        
        // if dir, add index if file exists.
        // partly for adding doc/index to work
        // partly to make doc/ generate proper toc.
        $baseroute  = dirname($route);
        $path       = $basepath . '/' . $baseroute . '/' . $pattern;

        $toc = [];
        foreach (glob($path) as $file) {
            $parts    = pathinfo($file);
            $filename = $parts['filename'];

            $title = $this->getTitleFromFirstLine($file);
            $file2route = substr($filename, strpos($filename, '_') + 1);

            $url = $baseroute . '/' . $file2route;
            /*
            if ($file2route == 'index' ) {
                $url = $baseroute;
            }*/

            $toc[$url] = [
                'title'     => $title,
                'filename'  => $parts['basename'] , 
            ];
        }

        return $toc;
    }
}
