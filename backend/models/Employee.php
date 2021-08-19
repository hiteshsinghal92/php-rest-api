<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "employee".
 *
 * @property int $id
 * @property string $employee_code
 * @property string|null $employee_name
 * @property string|null $employee_email
 * @property int|null $department_id
 * @property int|null $status
 * @property string|null $created
 * @property string|null $updated
 *
 * @property TblDepartment $department
 * @property EmployeeAddress[] $employeeAddresses
 */
class Employee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['employee_code'], 'required'],
            [['department_id', 'status'], 'integer'],
            [['created', 'updated'], 'safe'],
            [['employee_code'], 'string', 'max' => 50],
            [['employee_name', 'employee_email'], 'string', 'max' => 100],
            [['employee_code'], 'unique'],
            [['employee_email'], 'unique'],
            [['employee_email'], 'email'],
            [['department_id'], 'exist', 'skipOnError' => true, 'targetClass' => Department::className(), 'targetAttribute' => ['department_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_code' => 'Employee Code',
            'employee_name' => 'Employee Name',
            'employee_email' => 'Employee Email',
            'department_id' => 'Department ID',
            'status' => 'Status',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Gets query for [[Department]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id']);
    }

    /**
     * Gets query for [[EmployeeAddresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployeeAddresses()
    {
        return $this->hasMany(EmployeeAddress::className(), ['employee_id' => 'id']);
    }
}
