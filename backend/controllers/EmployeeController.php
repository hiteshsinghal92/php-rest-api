<?php

namespace backend\controllers;

use backend\models\Employee;
use backend\models\EmployeeAddress;
use yii\filters\VerbFilter;

class EmployeeController extends \yii\web\Controller {

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
        $model = new Employee();
      
        $data = $this->data;

        if (!empty($data)) {
            $model->employee_code = $data['employee_code'];
            $model->employee_name = $data['employee_name'];
            $model->employee_email = $data['employee_email'];
            $model->status = $data['status'];
            $model->department_id = $data['department_id'];
        }

        $errors = $this->EmpDetailsvalidate($data['address']);
        if ($model->validate() && empty($errors)) {
            $model->save();

            $this->InsertUpdateEmpAddress($model->id, $data['address']);
            echo $this->messageReturn("success", 200);
        } else {
            $error = $model->getErrors();
            //print_r($errors);
            array_push($error, $errors);
            echo $this->messageReturn("error", 404, "", $error);
        }
        exit;
    }

    /**
     * @function For validation
     * @param type $address
     * @return type if error in post data 
     */
    private function EmpDetailsvalidate($address) {
        $errors = [];
        if (!empty($address)) {

            $modelemp = new EmployeeAddress();
            foreach ($address as $value) {
                $modelemp->address = $value['address'];
                $modelemp->phone = $value['phone'];
                //$modelemp->employee_id = $id;
                if (!$modelemp->validate()) {
                    $errors[] = $modelemp->getErrors();
                }
            }
        }
        return $errors;
    }

    /**
     * @function for Insert and update Address
     * @param type $id
     * @param type $address
     * 
     */
    private function InsertUpdateEmpAddress($id, $address) {
        if (!empty($address)) {
            foreach ($address as $value) {
                if (!empty($value['id'])) {
                    $modelemp = $this->findEmployeeModel($value['id']);
                } else {
                    $modelemp = new EmployeeAddress();
                }
                $modelemp->address = $value['address'];
                $modelemp->phone = $value['phone'];
                $modelemp->employee_id = $id;
                $modelemp->save();
            }
        }
    }

    /*
     * Show all Action
     * Return Response All data in json 
     */

    public function actionView() {

           $data = $this->data;

        if ($data['employee'] == "All") {

            $services = Employee::find()->all();
            $service_data = [];

            foreach ($services as $value) {
                $service_data[] = [
                    "id" => $value['id'],
                    "employee_code" => $value->employee_code,
                    "employee_name" => $value->employee_name,
                    "employee_email" => $value->employee_email,
                    "department_id" => $value->department_id,
                    "department_name" => $value->department->department_name,
                    "created" => $value->created,
                    "updated" => $value->updated,
                    "address" => $this->GetEmpAddress($value->employeeAddresses)
                ];
            }
            if (!empty($service_data)) {
                echo $this->messageReturn("success", 200, $service_data);
            } else {
                echo $this->messageReturn("error", 400, "", "No Data Found");
            }
        } else {
            echo $this->messageReturn("failed", 201, "", "Please send all parameter");
        }
        exit;
    }

    /**
     * @function Get All employee address
     * @param type $employeeAddresses
     * @return type
     */
    private function GetEmpAddress($employeeAddresses) {
        $address = [];
        if (!empty($employeeAddresses)) {

            foreach ($employeeAddresses as $valueadd) {
                $address[] = [
                    "id" => $valueadd->id,
                    "address" => $valueadd->address,
                    "phone" => $valueadd->phone
                ];
            }
        }
        return $address;
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
        $model = $this->findModel($data['employee_id']);
        if ($model) {
            $model->employee_code = $data['employee_code'];
            $model->employee_name = $data['employee_name'];
            $model->employee_email = $data['employee_email'];
            $model->status = $data['status'];
            $model->department_id = $data['department_id'];

            $errors = $this->EmpDetailsvalidate($data['address']);
            if ($model->validate() && empty($errors)) {
                $model->save();
                $this->InsertUpdateEmpAddress($model->id, $data['address']);
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
        if (isset($data['employee_id']) && !empty($data['employee_id'])) {
            $model = $this->findModel($data['employee_id']);
            if ($model) {
                $this->findModel($data['employee_id'])->delete();
                EmployeeAddress::deleteAll(['employee_id' => $data['employee_id']]);
                echo $this->messageReturn("success", 200);
                exit;
            } else {
                echo $this->messageReturn("failed", 201, "", "Employee id not found");
                exit;
            }
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
        if (($model = Employee::findOne($id)) !== null) {
            return $model;
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
    protected function findEmployeeModel($id) {
        $model = null;
        if (($model = EmployeeAddress::findOne($id)) !== null) {
            return $model;
        }
    }

    protected function findEmployeeModelEmployee($id) {
        $model = null;
        if (($model = EmployeeAddress::deleteAll(['employee_id' => $id])) !== null) {
            return $model;
        }
    }

}
