<?php

namespace IWGB\Join;

use Doctrine\ORM\EntityManager;
use Guym4c\Airtable\Airtable;
use Monolog\Logger;
use Slim\Views\Twig;

class TypeHinter {

    /** @var $settings array */
    public $settings;

    /** @var $log Logger */
    public $log;

    /** @var $em EntityManager */
    public $em;

    /** @var $airtable Airtable */
    public $airtable;

    /** @var Twig */
    public $view;

}