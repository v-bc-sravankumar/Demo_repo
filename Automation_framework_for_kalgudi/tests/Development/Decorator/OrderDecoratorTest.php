<?php
define('SHOW_QUERIES', true);

class TestOrder
{
	protected $id;

	function __construct($id)
	{
		$this->id = $id;
	}

	function getId() {
		return $this->id;
	}
}

class Unit_Development_Decorator_OrderDecoratorTest extends PhpUnit_Framework_TestCase
{
	public function testAddressDecorator()
	{
		$orders[] = new TestOrder(10);
		$orders[] = new TestOrder(11);
		$orders[] = new TestOrder(12);

		$decorator = new Decorator(
			array(
				new Store_Order_Decorator_Products(
					array(
						new Store_Product_Decorator_Images(),
// 						new Store_Product_Decorator_CustomUrls(),
					)
				),
				new Store_Order_Decorator_Addresses(),
				new Store_Order_Decorator_Messages(),
			)
		);

		$decorator->setObjects($orders);
		$decorator->decorate();

		$decoratedObjects = $decorator->getObjects();

// 		var_dump($decoratedObjects);

		/*
		 * Test the expected product counts
		 */
		$this->assertEquals(3, count($decoratedObjects[10]->products));
		$this->assertEquals(2, count($decoratedObjects[11]->products));
		$this->assertEquals(1, count($decoratedObjects[12]->products));

		/*
		 * Test the product image counts
		 */
		$this->assertEquals(1, count($decoratedObjects[10]->products[1000]->images));
		$this->assertEquals(2, count($decoratedObjects[10]->products[2000]->images));
		$this->assertEquals(3, count($decoratedObjects[10]->products[3000]->images));

		/*
		 * Test the expected address counts.
		 */
		$this->assertEquals(2, count($decoratedObjects[10]->addresses));
		$this->assertEquals(1, count($decoratedObjects[11]->addresses));
		$this->assertEquals(3, count($decoratedObjects[12]->addresses));

		/*
		 * Test the expected message counts
		 */
		$this->assertEquals(1, count($decoratedObjects[10]->messages));
		$this->assertEquals(2, count($decoratedObjects[11]->messages));
		$this->assertEquals(3, count($decoratedObjects[12]->messages));

	}
}