<?php

namespace frontend\models;

use Yii;

class Product extends \common\models\Product
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            // [['field1', 'field2'], 'required', 'message' => '{attribute} is required'],
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            // 'scenario' => ['field1', 'field2'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'field1' => 'description1',
            // 'field2' => 'description2',
        ]);
    }
    //获取首页三个上架的产品
    public static function getIndexProduct()
    {
        $products = self::find()->where(['on_sale' => self::ON_SALE_YES, 'state' => self::STATE_VALID])->limit(3)->orderBy('hot DESC')->all();
        $arr = [];
        foreach ($products as $product) {
            $arr[$product->id]['name'] = $product->name; 
            $arr[$product->id]['table_name'] = $product->table_name; 
            $arr[$product->id]['source'] = $product->source; 
            $newData = DataAll::newProductPrice($product->id);
            $arr[$product->id]['price'] = $newData->price; 
            $arr[$product->id]['close'] = $newData->close; 
        }
        return $arr;
    }

    //获取上架的产品数组
    public static function getProductArray()
    {
        return self::find()->where(['on_sale' => self::ON_SALE_YES, 'state' => self::STATE_VALID])->orderBy('hot DESC')->map('id', 'name');
    }
}
