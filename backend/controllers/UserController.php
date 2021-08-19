<?php

namespace backend\controllers;


use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * User controller
 */
class UserController extends Controller
{

      /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

}