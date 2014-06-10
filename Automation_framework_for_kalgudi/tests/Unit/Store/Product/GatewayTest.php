<?php

namespace Unit\Store\Product;

use Store_Product_Gateway;
use Interspire_Event;
use Store_Event;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    private function getEventHandler($self, $id, $before, $after, &$handled)
    {
        return function(Interspire_Event $event) use ($self, $id, $before, $after, &$handled) {
            $expectedBefore = $before;

            if (!array_key_exists('prodlastmodified', $expectedBefore)) {
                $expectedBefore['prodlastmodified'] = null;
            }

            $expectedAfter = $after;

            $self->assertEquals($id, $event->data['id'], 'id does not match');
            $self->assertEquals($expectedBefore, $event->data['before'], 'before data does not match');

            $self->assertNotEmpty($event->data['after']['prodlastmodified']);

            if (!array_key_exists('prodlastmodified', $after)) {
                unset($event->data['after']['prodlastmodified']);
            }

            $self->assertEquals($expectedAfter, $event->data['after'], 'after data does not match');

            $handled = true;
        };

    }

    private function assertEventHandled($eventName, $id, $before, $after, $expectedBefore = null)
    {
        if ($expectedBefore === null) {
            $expectedBefore = $before;
        }

        $handled = false;
        $handler = $this->getEventHandler($this, $id, $expectedBefore, $after, $handled);

        Interspire_Event::bind($eventName, $handler);

        Store_Product_Gateway::triggerEventsForChangedProductData($id, $after, $before);

        $this->assertTrue($handled, $eventName . ' was not handled');

        Interspire_Event::unbind($eventName, $handler);
    }

    public function testTriggerEventsForChangedProductDataForSingleField()
    {
        $id = rand(1, 1000);

        $after = array(
            'prodcode'=> 'NEWCODE',
        );

        $before = array(
            'prodcode' => 'OLDCODE',
        );

        // only prodcode changing should trigger EVENT_PRODUCT_CHANGED and include only prodcode

        $this->assertEventHandled(Store_Event::EVENT_PRODUCT_CHANGED, $id, $before, $after);
    }

    public function testTriggerEventsForChangedProductDataOnlyAddsLastModifiedIfMissing()
    {
        $id = rand(1, 1000);
        $timeAfter = time() + 100;
        $timeBefore = time();

        $after = array(
            'prodcode'=> 'NEWCODE',
            'prodlastmodified' => $timeAfter,
        );

        $before = array(
            'prodcode' => 'OLDCODE',
            'prodlastmodified' => $timeBefore,
        );

        // only prodcode changing should trigger EVENT_PRODUCT_CHANGED and include only prodcode

        $this->assertEventHandled(Store_Event::EVENT_PRODUCT_CHANGED, $id, $before, $after);
    }

    public function testTriggerEventsForChangedProductDataIncludesExtraFields()
    {
        $id = rand(1, 1000);

        $after = array(
            'prodconfigfields'      => 'something',
            'prodeventdaterequired' => 1,
            'product_type_id'       => 15,
        );

        $before = array(
            'prodconfigfields'      => 'something',
            'prodeventdaterequired' => 1,
            'product_type_id'       => 30,
        );

        // only product_type_id changing should trigger EVENT_PRODUCT_CHANGED_PRODUCT_TYPE and include
        // prodconfigfields and prodeventdaterequired.

        $this->assertEventHandled(Store_Event::EVENT_PRODUCT_CHANGED_PRODUCT_TYPE, $id, $before, $after);
    }

    public function testTriggerEventsForChangedProductDataExcludesExtraFieldFromBeforeIfMissingFromAfter()
    {
        $id = rand(1, 1000);

        $after = array(
            'product_type_id'       => 15,
        );

        $before = array(
            'prodconfigfields'      => 'something',
            'prodeventdaterequired' => 1,
            'product_type_id'       => 30,
        );

        // with prodconfigfields and prodeventdaterequired missing from after data,
        // they should be excluded from the before data

        $expectedBefore = array(
            'product_type_id' => 30,
        );

        $this->assertEventHandled(Store_Event::EVENT_PRODUCT_CHANGED_PRODUCT_TYPE, $id, $before, $after, $expectedBefore);
    }
}
