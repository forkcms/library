<?php

use PHPUnit\Framework\TestCase;

$includePath = dirname(__FILE__, 4);
set_include_path(get_include_path() . PATH_SEPARATOR . $includePath);

require_once 'spoon/spoon.php';
require_once 'Collection.php';
require_once 'TestObject.php';

class SpoonTemplateCompilerTest extends TestCase
{
	protected $tpl;

	public function setUp(): void
	{
		// create a spoon template
		$this->tpl = new SpoonTemplate();
		$this->tpl->setForceCompile(true);
		$this->tpl->setCompileDirectory(__DIR__ . '/cache');
	}

	function testParseVariables()
	{
		$this->tpl->assign('variable', 'value');
		$this->runTests('value', 'variable.tpl');
	}

	function testParseArrays()
	{
		$this->tpl->assign(
			'array',
			['name' => 'Array name']
		);
		$this->runTests('Array name', 'array.tpl');
	}

	function testParseObjects()
	{
		// add an object
		$object = new TestObject();
		$object->setName('Object name');
		$this->tpl->assign('object', $object);
		$this->runTests('Object name', 'object.tpl');
	}

	function testParseNestedArrays()
	{
		$this->tpl->assign(
			'array',
			[
				'inner_array' => [
					'name' => 'Array name'
				]
			]
		);
		$this->runTests('Array name', 'nested_array.tpl');
	}

	function testParseNestedObjects()
	{
		$nestedObject = new TestObject();
		$nestedObject->setName('Object name');

		$object = new TestObject();
		$object->setNestedObject($nestedObject);

		$this->tpl->assign('object', $object);
		$this->runTests('Object name', 'nested_object.tpl');
	}

	function testParseArrayInObject()
	{
		$nestedArray = ['name' => 'Inside an object'];

		$object = new TestObject();
		$object->setArray($nestedArray);

		$this->tpl->assign('object', $object);
		$this->runTests('Inside an object', 'array_in_object.tpl');
	}

	function testIterationOverArray()
	{
		$this->tpl->assign(
			'array',
			[
				['name' => 'Foo'],
				['name' => 'Bar'],
			]
		);
		$this->runTests('FooBar', 'iteration_over_array.tpl');
	}

	function testIterationOverNestedArray()
	{
		$this->tpl->assign(
			'array',
			[
				'nested_array' => [
					['name' => 'Foo'],
					['name' => 'Bar'],
				]
			]
		);
		$this->runTests('FooBar', 'iteration_over_nested_array.tpl');
	}

	function testIterationOverArrayInObject()
	{
		$object = new TestObject();
		$object->setArray(
			[
				['name' => 'Foo'],
				['name' => 'Bar'],
			]
		);

		$this->tpl->assign('object', $object);
		$this->runTests('FooBar', 'iteration_over_array_in_object.tpl');
	}

	function testIterationOverArrayOfObjects()
	{
		$object1 = new TestObject();
		$object1->setName('Foo');

		$object2 = new TestObject();
		$object2->setName('Bar');

		$this->tpl->assign('array', [$object1, $object2]);
		$this->runTests('FooBar', 'iteration_over_array_of_objects.tpl');
	}

	function testIterationOverCollection()
	{
		$collection = new Collection(
			[
				['name' => 'Foo'],
				['name' => 'Bar'],
			]
		);

		$this->tpl->assign('collection', $collection);
		$this->runTests('FooBar', 'iteration_over_collection.tpl');
	}

	function testIterationOverCollectionOfObjects()
	{
		$object1 = new TestObject();
		$object1->setName('Object1');

		$object2 = new TestObject();
		$object2->setName('Object2');

		$collection = new Collection(
			[$object1, $object2]
		);

		$this->tpl->assign('collection', $collection);
		$this->runTests('Object1Object2', 'iteration_over_collection_of_objects.tpl');
	}

	function testCycle()
	{
		$this->tpl->assign(
			'array',
			[
				['number' => 'One'],
				['number' => 'Two'],
				['number' => 'Three'],
			]
		);
		$this->runTests('One: Odd, Two: Even, Three: Odd, ', 'cycle.tpl');
	}

	function testCycleOverArrayInObject()
	{
		$array = [
			['number' => '1'],
			['number' => '2'],
			['number' => '3'],
		];
		$object = new TestObject();
		$object->setArray($array);
		$this->tpl->assign('object', $array);
		$this->runTests('1: Odd, 2: Even, 3: Odd, ', 'cycle_over_array_in_object.tpl');
	}

	function testCycleOverCollection()
	{
		$array = [
			['number' => '0'],
			['number' => '1'],
			['number' => '2'],
		];
		$collection = new Collection($array);
		$this->tpl->assign('collection', $collection);
		$this->runTests('0: Even, 1: Odd, 2: Even, ', 'cycle_over_collection.tpl');
	}

	function testFirstAndLast()
	{
		$this->tpl->assign(
			'array',
			[
				['number' => 'One'],
				['number' => 'Two'],
				['number' => 'Three'],
			]
		);
		$this->runTests('First: One, Last: Three', 'first_last.tpl');
	}

	function testFirstAndLastForArrayInObject()
	{
		$array = [
			['number' => '1'],
			['number' => '2'],
			['number' => '3'],
		];
		$object = new TestObject();
		$object->setArray($array);
		$this->tpl->assign('object', $array);
		$this->runTests('First: 1, Last: 3', 'first_last_array_in_object.tpl');
	}

	function testFirstAndLastForCollection()
	{
		$array = [
			['number' => '0'],
			['number' => '1'],
			['number' => '2'],
		];
		$collection = new Collection($array);
		$this->tpl->assign('object', $collection);
		$this->runTests('First: 0, Last: 2', 'first_last_array_in_object.tpl');
	}

	function testOption()
	{
		// boolean true
		$this->tpl->assign('test', true);
		$this->runTests('Yes', 'option.tpl');

		// integer that isn't zero
		$this->tpl->assign('test', 1);
		$this->runTests('Yes', 'option.tpl');

		// string that isn't empty
		$this->tpl->assign('test', 'tralala');
		$this->runTests('Yes', 'option.tpl');

		// array with content
		$this->tpl->assign('test', ['tralala']);
		$this->runTests('Yes', 'option.tpl');

		// object
		$this->tpl->assign('test', new TestObject());
		$this->runTests('Yes', 'option.tpl');

		// boolean false
		$this->tpl->assign('test', false);
		$this->runTests('No', 'option.tpl');

		// integer 0
		$this->tpl->assign('test', 0);
		$this->runTests('No', 'option.tpl');

		// empty string
		$this->tpl->assign('test', '');
		$this->runTests('No', 'option.tpl');

		// empty array
		$this->tpl->assign('test', []);
		$this->runTests('No', 'option.tpl');
	}

	function testOptionInArray()
	{
		$this->tpl->assign('array', ['boolean' => true]);
		$this->runTests('Yes', 'option_in_array.tpl');

		$this->tpl->assign('array', ['boolean' => false]);
		$this->runTests('No', 'option_in_array.tpl');
	}

	function testOptionInObject()
	{
		$object = new TestObject();
		$object->setBoolean(true);
		$this->tpl->assign('object', $object);
		$this->runTests('Yes', 'option_in_object.tpl');

		$object->setBoolean(false);
		$this->tpl->assign('object', $object);
		$this->runTests('No', 'option_in_object.tpl');

		// test boolean with ->get style getter instead off ->is style getter
		$object->setVisible(true);
		$this->tpl->assign('object', $object);
		$this->runTests('Visible', 'option_in_object_get.tpl');

		$object->setVisible(false);
		$this->tpl->assign('object', $object);
		$this->runTests('Invisible', 'option_in_object_get.tpl');
	}

	function testOptionInIteration()
	{
		// in an array
		$array = [
			[
				'boolean' => true,
				'name' => 'True',
			],
			[
				'boolean' => false,
				'name' => 'False',
			],
		];
		$this->tpl->assign('items', $array);
		$this->runTests('True', 'option_in_iteration.tpl');

		// in an object
		$object1 = new TestObject();
		$object1->setBoolean(true);
		$object1->setName('True');
		$object2 = new TestObject();
		$object2->setBoolean(false);
		$object2->setName('False');
		$array = [$object1, $object2];

		$this->tpl->assign('items', $array);
		$this->runTests('True', 'option_in_iteration.tpl');
	}

	function testOptionNotInIteration()
	{
		// in an array
		$array = [
			[
				'boolean' => true,
				'name' => 'True',
			],
			[
				'boolean' => false,
				'name' => 'False',
			],
		];
		$this->tpl->assign('items', $array);
		$this->runTests('False', 'option_not_in_iteration.tpl');

		// in an object
		$object1 = new TestObject();
		$object1->setBoolean(true);
		$object1->setName('True');
		$object2 = new TestObject();
		$object2->setBoolean(false);
		$object2->setName('False');
		$array = [$object1, $object2];

		$this->tpl->assign('items', $array);
		$this->runTests('False', 'option_not_in_iteration.tpl');
	}

	function testIncludes()
	{
		// add an object
		$object = new TestObject();
		$object->setName('Object name');
		$this->tpl->assign('object', $object);
		$this->runTests('Object name', 'include.tpl');

		// add an object
		$object = new TestObject();
		$object->setName('Object name');
		$this->tpl->assign('object', $object);
		$this->runTests('Object name', 'include_with_quotes.tpl');
	}

	function testTemplateModifier()
	{
		$this->tpl->assign('variable', 'value');
		$this->runTests('Value', 'template_modifier.tpl');
	}

	function testTemplateModifierObject()
	{
		// add an object
		$object = new TestObject();
		$object->setName('object name');
		$this->tpl->assign('object', $object);
		$this->runTests('Object name', 'template_modifier_object.tpl');
	}

	function testTemplateModifierWithVariables()
	{
		$this->tpl->assign('string', '%1$s %2$s');
		$this->tpl->assign('array', ['foo' => 'foo', 'bar' => 'bar']);
		$this->runTests('foo bar', 'template_modifier_with_vars.tpl');
	}

	function testTemplateModifierInIterationOverArrayOfObjects()
	{
		$object1 = new TestObject();
		$object1->setName('foo');

		$object2 = new TestObject();
		$object2->setName('bar');

		$this->tpl->assign('array', [$object1, $object2]);
		$this->runTests('FooBar', 'template_modifier_iteration_over_array_of_objects.tpl');
	}

	/**
	 * Check if the given templates gives the wanted output in debug and non
	 * debug mode
	 *
	 * @param  string $output   The output we want from SpoonTemplate
	 * @param  string $template The filename of the template
	 */
	protected function runTests($output, $template)
	{
		Spoon::setDebug(true);
		$this->assertEquals(
			$output, $this->tpl->getContent($this->getTemplatePath($template))
		);
		Spoon::setDebug(false);
		$this->assertEquals(
			$output, $this->tpl->getContent($this->getTemplatePath($template))
		);
	}

	protected function getTemplatePath($templateName)
	{
		return __DIR__ . '/templates/' . $templateName;
	}
}
