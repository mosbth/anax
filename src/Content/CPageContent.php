<?php

namespace Anax\Content;

/**
 * Pages based on file content.
 *
 */
class CPageContent
{
    use \Anax\TConfigure,
        \Anax\TInjectionAware;



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
            throw new \Anax\Exception\NotFoundException("The documentation page does not exists.");
        }

        $title = $pages[$route]['title'];
        $file  = isset($pages[$route]['file'])
            ? $pages[$route]['file']
            : $route . ".md";

        $app->theme->setTitle($title);

        $content = $app->fileContent->get($file);
        $content = $app->textFilter->doFilter($content, 'shortcode, markdown');

        $app->views->add('default/article', [
            'content' => $content,
        ]);

    }
}
