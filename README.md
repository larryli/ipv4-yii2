# IPv4 Yii2 组件

## 通过 composer 安装

```shell
composer require larryli/ipv4-yii2
```

## 配置 ipv4 组件和命令

### 别名

可以在 ```config``` 文件先定义一个别名：

```php
Yii::setAlias('@ipv4-yii2', dirname(__DIR__) . '/vendor/larryli/ipv4-yii2');
```

### 组件

在 ```components``` 中增加：

```php
// ipv4 component
'ipv4' => [
    'class' => '\larryli\ipv4\yii2\IPv4',
    // 'db' => 'db',
    // 'database' => '\larryli\ipv4\yii2\Database',
    // 'prefix' => 'ipv4_',
    'providers' => [
        'monipdb' => [
            // 'class' => '\larryli\ipv4\MonipdbQuery',
            'filename' => '@runtime/17monipdb.dat',
        ],
        'qqwry' => [
            // 'class' => '\larryli\ipv4\QqwryQuery',
            'filename' => '@runtime/qqwry.dat',
        ],
        'full' => [
            // 'class' => '\larryli\ipv4\FullQuery',
            'providers' => ['monipdb', 'qqwry'], // ex. 'monipdb', 'qqwry', ['qqwry', 'monipdb']
        ],
        'mini' => [
            // 'class' => '\larryli\ipv4\MiniQuery',
            'providers' => 'full',   // ex. ['monipdb', 'qqwry'], 'monipdb', 'qqwry', ['qqwry', 'monipdb']
        ],
        'china' => [
            // 'class' => '\larryli\ipv4\MiniQuery',
            'providers' => 'full',
        ],
        'world' => [
            // 'class' => '\larryli\ipv4\MiniQuery',
            'providers' => 'full',
        ],
        'freeipip' => [
            // 'class' => '\larryli\ipv4\FreeipipQuery',
        ],
        // 'taobao' => [
            // 'class' => '\larryli\ipv4\TaobaoQuery',
        // ],
        // 'sina' => [
            // 'class' => '\larryli\ipv4\SinaQuery',
        // ],
        // 'BaiduMap' => [
            // 'class' => '\larryli\ipv4\BaidumapQuery',
        // ],
    ],
],
```

其中：

* ```class``` 指向组件自身；
* ```db``` 可用的 yii2 数据库连接，默认为 ```Yii::$app->db```；
* ```database``` 指向特定的 ```Database``` 类，默认使用 ```\larryli\ipv4\yii2\Database```；
* ```prefix``` 为数据库表前缀，默认为 ```ipv4_```；
* ```providers``` 配置可用的 ```\larryli\ipv4\Query``` 数据源；
    * ```class``` 数据源可以指定具体的类；
    * ```filename``` ，对于 ```\larryli\ipv4\FileQuery``` 需指定文件路径，其内容可以用别名，如 ```@runtime/foo.dat```；
    * ```providers``` 数据源的数据源，```\larryli\ipv4\DatabaseQuery``` 需要，可以为一个或两个；

### 命令

在 ```config``` 数组中增加 ```controllerMap``` 配置内容：

```php
// ipv4 command
'ipv4' => [
    'class' => 'larryli\ipv4\yii2\commands\Ipv4Controller',
],
```

使用：

```shell
./yii help ipv4
```

可以查看 ipv4 命令列表。

## 数据库迁移

复制数据库迁移脚本到当前 ```@app/migrations``` 下：

```shell
cp vendor/larryli/ipv4/src/yii2/migrations/*.php migrations/
```

或者参见[此页面的说明](https://github.com/yiisoft/yii2/issues/384)使用其他的方式处理。

然后，执行迁移：

```shell
./yii migrate/up
```

## 初始化

```shell
./yii ipv4/init
```

## 查询

```shell
./yii ipv4/query 127.0.0.1
```

## 杂项

```shell
./yii ipv4/benchmark        # 性能测试
./yii ipv4/clean            # 清除全部数据
./yii ipv4/clean file       # 清除下载的文件数据
./yii ipv4/clean database   # 清除生成的数据库数据
./yii ipv4/dump             # 导出原始数据
./yii ipv4/dump division    # 导出排序好的全部地址列表
./yii ipv4/dump division_id # 导出排序好的全部地址和猜测行政区域代码列表
```

注意：```dump``` 命令会耗费大量内存，请配置 PHP ```memory_limit``` 至少为 ```256M``` 或更多。

## 代码调用

### 使用组件

```php
use Yii;
Yii::$app->get('ipv4')->getQuery('full')->find(ip2long('127.0.0.1'));
Yii::$app->get('ipv4')->__get('full')->find(ip2long('127.0.0.1'));
Yii::$app->ipv4->full->find(ip2long('127.0.0.1'));

foreach (Yii::$app->ipv4->getQueries() as $query) {
    $query->find(ip2long('127.0.0.1'));
}
```

### 使用模型

仅支持生成的数据库 ```larryli\ipv4\DatabaseQuery``` 查询。

使用 yii2 模型可以不需要配置 ipv4 组件，但必须先使用 ipv4 组件生成好相关数据库。

也就是说，可以只在 console 应用中配置 ipv4 组件；然后在 web 应用中*不*配置 ipv4 组件直接使用相关模型。

#### Division 模型

```php
namespace app\models;

use larryli\ipv4\yii2\models\Division as BaseDivision;

/**
 * Class Division
 * @package app\models
 */
class Division extends BaseDivision
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%ipv4_divisions}}";
    }
}
```

使用 ```tableName``` 静态方法可以重载掉父类中调用组件查询 ```prefix``` 的代码。

#### Full/Mini/China/World 模型

```php
namespace app\models;

use larryli\ipv4\yii2\models\Index;

/**
 * Class Full
 * @package app\models
 *
 * @property string $ip
 * @property Division $division
 */
abstract class Full extends Index
{
    /**
     * @return string
     */
    static public function divisionClassName()
    {
        return Division::className();
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ipv4_full}}';
    }
}
```

使用 ```divisionClassName``` 静态方法可以重载掉 ```Division``` 父类中调用组件查询 ```prefix``` 的代码。

仅需要声明所需使用的查询模型即可。

查询：

```php
$model = Full::findOneByIp(ip2long('127.0.0.1'));
if (!empty($model) && !empty(!$model->division)) {
    echo $model->division->name;
}
```

## 相关包

* 核心 [larryli/ipv4](https://github.com/larryli/ipv4)
* 控制台命令 [larryli/ipv4-console](https://github.com/larryli/ipv4-console)
* Medoo 数据库支持 [larryli/ipv4-medoo](https://github.com/larryli/ipv4-medoo)
* Yii2 示例 [larryli/ipv4-yii2-sample](https://github.com/larryli/ipv4-yii2-sample)
