<?php

namespace backend\controllers;

use backend\models\Department;
use yii\filters\VerbFilter;

class DepartmentController extends \yii\web\Controller {

    public $enableCsrfValidation = false;
    public $data = [];

    public function __construct($id, $module, $config = []) {
        //\yii\helpers\VarDumper::dump([$id, $module, $config]);
        parent::__construct($id, $module, $config);
        $post = file_get_contents("php://input");
        if (!$this->is_json($post)) {
            echo $this->messageReturn("failed", 202, "", "Json format not correct");
            exit;
        } else {
            $this->data = json_decode($post, TRUE);
        }
    }

    function is_json($string, $return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

    public function behaviors() {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // restrict access to
                    'Origin' => (YII_ENV_PROD) ? [''] : ['*'],
                    // Allow only POST and PUT methods
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'POST', 'PUT'],
                    // Allow only headers 'X-Wsse'
                    'Access-Control-Request-Headers' => ['X-Wsse', 'Content-Type'],
                    // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                    'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'contact' => ['POST'],
                ],
            ],
        ];
    }

    /*
     * Create Action
     * Return Response of insert or error in Json 
     */

    public function actionCreate() {
        $model = new Department();

        $data = $this->data;
        if (!empty($data)) {
            $model->department_name = $data['department_name'];
            $model->status = $data['status'];
        }

        if ($model->validate()) {
            $model->save();
            echo $this->messageReturn("success", 200);
        } else {
            $error = $model->getErrors();

            echo $this->messageReturn("error", 404, "", $error);
        }
        exit;
    }

    /*
     * Show all Action
     * Return Response All data in json 
     */

    public function actionView() {

        $data = $this->data;

        if ($data['department'] == "All") {

            $services = Department::find()->asArray()->all();
            if (!empty($services)) {
                echo $this->messageReturn("success", 200, $services);
            } else {
                echo $this->messageReturn("error", 400, "", "No Data Found");
            }
        } else {
            echo $this->messageReturn("failed", 201, "", "Please send all parameter");
        }
        exit;
    }

    /**
     * Updates an existing Department model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws Json return if the model cannot be found
     */
    public function actionUpdate() {

        $data = $this->data;
        $model = $this->findModel($data['department_id']);
        if ($model) {
            $model->department_name = $data['department_name'];
            $model->status = $data['status'];
            if ($model->validate()) {
                $model->save();
                echo $this->messageReturn("success", 200);
                exit;
            } else {
                $error = $model->getErrors();
                echo $this->messageReturn("error", 404, "", $error);
                exit;
            }
        } else {
            echo $this->messageReturn("failed", 201, "", "Please sent correct parameter");
            exit;
        }
    }

    private function messageReturn($msg, $code, $data = null, $error = null) {
        $response = [];
        $response["status"] = "OK";
        $response["successMessage"] = $msg;
        $response["errorMessage"] = $error;
        $response["statusCode"] = $code;
        $response["data"] = $data;
        return json_encode($response);
    }

    /**
     * Deletes an existing Service model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete() {
        $data = $this->data;
        $model = $this->findModel($data['department_id']);
        if ($model) {
            $this->findModel($data['department_id'])->delete();
            echo $this->messageReturn("success", 200);
            exit;
        } else {
            echo $this->messageReturn("failed", 201, "", "Please sent correct parameter");
            exit;
        }
    }

    /**
     * Finds the class Department extends \yii\db\ActiveRecord
      model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Department the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        $model = null;
        if (($model = Department::findOne($id)) !== null) {
            return $model;
        }
    }

}
