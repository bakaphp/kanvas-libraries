<?php

namespace Helper;

// use Canvas\Bootstrap\IntegrationTests;
use Codeception\Module;
use Codeception\TestInterface;
use Kanvas\Packages\Tests\Support\Models\Users;
use Niden\Mvc\Model\AbstractModel;
use Phalcon\Config as PhConfig;
use Phalcon\DI\FactoryDefault as PhDI;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Integration extends Module
// class Integration extends PhalconUnit
{
    /**
     * @var null|PhDI
     */
    protected $diContainer = null;
    protected $savedModels = [];
    protected $savedRecords = [];
    protected $config = ['rollback' => false];

    /**
     * Test initializer.
     */
    public function _before(TestInterface $test)
    {
        PhDI::reset();
        // $app = new IntegrationTests();
        // $app->setup();
        // $this->diContainer = $app->getContainer();

        // if ($this->config['rollback']) {
        //     $this->diContainer->get('db')->begin();
        // }

        //Set default user
        $user = new Users();
        $this->diContainer->setShared('userData', $user);
        $this->savedModels = [];
        $this->savedRecords = [];
    }

    public function _after(TestInterface $test)
    {
    }

    /**
     * After all is done.
     *
     * @return void
     */
    public function _afterSuite()
    {
        //Phinx::dropTables();
    }

    /**
     * @return mixed
     */
    public function grabDi()
    {
        return $this->diContainer;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function grabFromDi(string $name)
    {
        return $this->diContainer->get($name);
    }

    /**
     * Returns the relationships that a model has.
     *
     * @param string $class
     *
     * @return array
     */
    public function getModelRelationships(string $class) : array
    {
        /** @var AbstractModel $class */
        $model = new $class();
        $manager = $model->getModelsManager();
        $relationships = $manager->getRelations($class);
        $data = [];
        foreach ($relationships as $relationship) {
            $data[] = [
                $relationship->getType(),
                $relationship->getFields(),
                $relationship->getReferencedModel(),
                $relationship->getReferencedFields(),
                $relationship->getOptions(),
            ];
        }
        return $data;
    }

    /**
     * @param array $configData
     */
    public function haveConfig(array $configData)
    {
        $config = new PhConfig($configData);
        $this->diContainer->set('config', $config);
    }

    /**
     * Checks model fields.
     *
     * @param string $modelName
     * @param array  $fields
     */
    public function haveModelDefinition(string $modelName, array $fields)
    {
        /** @var AbstractModel $model */
        $model = new $modelName;
        $metadata = $model->getModelsMetaData();
        $attributes = $metadata->getAttributes($model);
        $this->assertEquals(
            count($fields),
            count($attributes),
            "Field count not correct for $modelName"
        );
        foreach ($fields as $value) {
            $this->assertContains(
                $value,
                $attributes,
                "Field not exists in $modelName"
            );
        }
    }

    /**
     * Create a record for $modelName with fields provided.
     *
     * @param string $modelName
     * @param array  $fields
     *
     * @return mixed
     */
    public function haveRecordWithFields(string $modelName, array $fields = [])
    {
        $record = new $modelName;
        foreach ($fields as $key => $val) {
            $record->set($key, $val);
        }
        $this->savedModels[$modelName] = $fields;
        $result = $record->save();
        $this->assertNotSame(false, $result);
        $this->savedRecords[] = $record;
        return $record;
    }

    /**
     * @param string $name
     * @param mixed  $service
     */
    public function haveService(string $name, $service)
    {
        $this->diContainer->set($name, $service);
    }

    /**
     * @param string $name
     */
    public function removeService(string $name)
    {
        if ($this->diContainer->has($name)) {
            $this->diContainer->remove($name);
        }
    }

    /**
     * Checks that record exists and has provided fields.
     *
     * @param $model
     * @param $by
     * @param $fields
     */
    public function seeRecordSaved($model, $by, $fields)
    {
        $this->savedModels[$model] = array_merge($by, $fields);
        $record = $this->seeRecordFieldsValid(
            $model,
            array_keys($by),
            array_keys($by)
        );
        $this->savedRecords[] = $record;
    }
}
