<?php

namespace PWE\Modules;

interface BreadcrumbsGenerator
{
    /**
     * Returns array of breadcrumb items like this:
     *
     *  $res = array();
     *  $res[] = array(
     *        'selected' => 1,
     *        '!a' => array(
     *             'link' => $this->testID . '/',
     *             'title' => $this->testData['projectKey'] . '-' . $this->testID
     *        )
     *  );
     *  return $res;
     * @return array
     */
    public function generateBreadcrumbs();
}

?>