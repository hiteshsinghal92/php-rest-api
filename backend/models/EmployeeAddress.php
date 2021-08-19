<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "employee_address".
 *
 * @property int $id
 * @property int|null $employee_id
 * @property string|null $address
 * @property int|null $phone
 * @property string|null $created
 * @property string|null $updated
 *
 * @property Employee $employee
 */
class EmployeeAddress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'employee_address';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['address', 'phone'], 'required'],
            [['employee_id', 'phone'], 'integer'],
            [['address'], 'string'],
            [['created', 'updated'], 'safe'],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_id' => 'Employee ID',
            'address' => 'Address',
            'phone' => 'Phone',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Gets query for [[Employee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'employee_id']);
    }
}
