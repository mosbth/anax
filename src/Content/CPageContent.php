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
     * Properties
     *
     */
    private $path;



    /**
     * Map url to page if such mapping can be done.
     *
     * @throws NotFoundException when mapping can not be done.
     */
    public function get()
    {
        $route = $this->di->request->getRoute();
        $pages = $this->config['pages'];
        
        if (!isset($pages[$route])) {
            throw new \Anax\Exception\NotFoundException("The page does not exists.");
        }

        $view   = $this->config['view'];
        $filter = $this->config['textfilter'];
        $page   = $pages[$route];
        $title  = $page['title'];
        $file   = $page['file'];
        
        $this->di->theme->setTitle($title);
        
        $content = $this->di->fileContent->get($file);
        $content = $this->di->textFilter->doFilter($content, $filter);

        $this->di->views->add($view, [
            'content' => $content,
        ]);

    }
}
