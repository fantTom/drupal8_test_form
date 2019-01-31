<?php

/**
 * @file
 * Contains \Drupal\contact_module\Controller\UsersModuleController.
 */

namespace Drupal\contact_module\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ContactController
 * @package Drupal\contact_module\Controller
 */
class UsersModuleController  extends ControllerBase
{

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple render-able array.
     */
    public function content() {
        $build = [
            '#markup' => 'Hello World!',
        ];
        return $build;
    }

}